<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Format\Video\X264;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoService
{
    private ?FFMpeg $ffmpeg = null;
    private ?FFProbe $ffprobe = null;

    public function __construct()
    {
        $binary = config('app.ffmpeg_path');
        if (!$binary) {
            return;
        }

        $ffprobePath = dirname($binary) . DIRECTORY_SEPARATOR . 'ffprobe.exe';
        $config = ['ffmpeg.binaries' => $binary];
        if (file_exists($ffprobePath)) {
            $config['ffprobe.binaries'] = $ffprobePath;
        }
        try {
            $this->ffmpeg = FFMpeg::create($config);
            if (file_exists($ffprobePath)) {
                $this->ffprobe = FFProbe::create($config);
            }
        } catch (\Throwable $e) {
            Log::warning('VideoService: ffmpeg load failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * معالجة ملف فيديو مرفوع: تحويل إلى H.264 إذا لزم الأمر
     */
    public function process(UploadedFile $file, string $disk = 'public'): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($originalName) ?: 'video-' . Str::random(8);
        $tempPath = $file->getRealPath();

        $codec = $this->detectCodec($tempPath);

        if ($codec === 'h264') {
            $path = $file->store('projects/videos', $disk);
            $thumbnail = app(ThumbnailService::class)->generate(storage_path("app/$disk/$path"));

            return [
                'video' => $path,
                'video_thumbnail' => $thumbnail,
                'video_status' => 'completed',
            ];
        }

        $outputName = "{$slug}-{$this->randomId()}.mp4";
        $outputPath = "projects/videos/{$outputName}";
        $fullOutput = storage_path("app/$disk/{$outputPath}");

        try {
            $this->convertToH264($tempPath, $fullOutput);
            $thumbnail = app(ThumbnailService::class)->generate($fullOutput);

            return [
                'video' => $outputPath,
                'video_thumbnail' => $thumbnail,
                'video_status' => 'completed',
            ];
        } catch (\Throwable $e) {
            Log::error('VideoService: conversion failed', [
                'codec' => $codec,
                'error' => $e->getMessage(),
            ]);
            $path = $file->store('projects/videos', $disk);

            return [
                'video' => $path,
                'video_thumbnail' => null,
                'video_status' => 'failed',
            ];
        }
    }

    /**
     * معالجة فيديو من مسار موجود (للمعالجة في الخلفية)
     */
    public function processFromPath(string $sourcePath, string $disk = 'public'): array
    {
        $codec = $this->detectCodec($sourcePath);

        $diskPath = str_replace('\\', '/', storage_path("app/$disk/"));
        $sourceNorm = str_replace('\\', '/', $sourcePath);

        if ($codec === 'h264') {
            $thumbnail = app(ThumbnailService::class)->generate($sourcePath);
            $relativePath = str_replace($diskPath, '', $sourceNorm);
            return [
                'video' => $relativePath,
                'video_thumbnail' => $thumbnail,
                'video_status' => 'completed',
            ];
        }

        $outputName = pathinfo($sourcePath, PATHINFO_FILENAME) . "-h264.mp4";
        $relativePath = str_replace($diskPath, '', $sourceNorm);
        $outputPath = dirname($relativePath) . '/' . $outputName;
        $fullOutput = storage_path("app/$disk/" . $outputPath);

        try {
            $this->convertToH264($sourcePath, $fullOutput);
            @unlink($sourcePath);
            $thumbnail = app(ThumbnailService::class)->generate($fullOutput);

            return [
                'video' => $outputPath,
                'video_thumbnail' => $thumbnail,
                'video_status' => 'completed',
            ];
        } catch (\Throwable $e) {
            Log::error('VideoService: background conversion failed', [
                'codec' => $codec,
                'error' => $e->getMessage(),
            ]);

            return [
                'video' => null,
                'video_thumbnail' => null,
                'video_status' => 'failed',
            ];
        }
    }

    /**
     * كشف كوديك الفيديو باستخدام ffprobe
     */
    public function detectCodec(string $path): string
    {
        if (!$this->ffprobe) {
            return $this->detectCodecFromFile($path);
        }

        try {
            $streams = $this->ffprobe->streams($path)->videos();
            foreach ($streams as $stream) {
                $codec = $stream->get('codec_name');
                if (in_array($codec, ['hevc', 'h265', 'hvc1'])) {
                    return 'hevc';
                }
                if ($codec === 'libx264' || preg_match('/^h264|avc/i', $codec)) {
                    return 'h264';
                }
            }
        } catch (\Throwable $e) {
            Log::warning('VideoService: ffprobe failed, using fallback detection', ['error' => $e->getMessage()]);
        }

        return $this->detectCodecFromFile($path);
    }

    private function detectCodecFromFile(string $path): string
    {
        $bytes = file_get_contents($path, false, null, 4, 200);
        if (str_contains($bytes, 'hvc1') || str_contains($bytes, 'hev1')) {
            return 'hevc';
        }
        if (str_contains($bytes, 'avc1') || str_contains($bytes, 'avc3')) {
            return 'h264';
        }
        return 'unknown';
    }

    /**
     * تحويل الفيديو إلى H.264 باستخدام ffmpeg
     */
    private function convertToH264(string $input, string $output): void
    {
        if (!$this->ffmpeg) {
            throw new \RuntimeException('FFMpeg غير متوفر. تأكد من تثبيت ffmpeg على الخادم.');
        }

        $video = $this->ffmpeg->open($input);
        $format = new X264('aac', 'libx264');
        $format->setKiloBitrate(2000);
        $format->setAdditionalParameters(['-movflags', '+faststart']);
        $video->save($format, $output);
    }

    private function randomId(): string
    {
        return substr(str_replace(['+', '/', '='], '', base64_encode(Str::random(12))), 0, 12);
    }

    /**
     * التحقق من توفر ffmpeg
     */
    public function isAvailable(): bool
    {
        return $this->ffmpeg !== null;
    }
}