<?php

namespace App\Services;

use FFMpeg\FFMpeg;
use FFMpeg\Coordinate\TimeCode;
use Illuminate\Support\Facades\Log;
class ThumbnailService
{
    private ?FFMpeg $ffmpeg = null;

    public function __construct()
    {
        $binary = config('app.ffmpeg_path');
        if (!$binary) {
            return;
        }

        $config = ['ffmpeg.binaries' => $binary];
        $ffprobePath = dirname($binary) . DIRECTORY_SEPARATOR . 'ffprobe.exe';
        if (file_exists($ffprobePath)) {
            $config['ffprobe.binaries'] = $ffprobePath;
        }
        try {
            $this->ffmpeg = FFMpeg::create($config);
        } catch (\Throwable $e) {
            Log::warning('ThumbnailService: ffmpeg load failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * التقاط إطار من الفيديو عند الثانية 1 واستخدامه كصورة مصغرة
     */
    public function generate(string $videoPath): ?string
    {
        if (!$this->ffmpeg || !file_exists($videoPath)) {
            return null;
        }

        $dir = 'thumbnails';
        $name = pathinfo($videoPath, PATHINFO_FILENAME) . '.jpg';

        // ponytail: inline mkdir check, no abstraction for a single mkdir call
        $fullDir = storage_path("app/public/$dir");
        if (!is_dir($fullDir)) {
            mkdir($fullDir, 0755, true);
        }

        $outputPath = "$fullDir/$name";

        try {
            $video = $this->ffmpeg->open($videoPath);
            $frame = $video->frame(TimeCode::fromSeconds(0));
            $frame->save($outputPath);

            return "$dir/$name";
        } catch (\Throwable $e) {
            Log::warning('ThumbnailService: failed to generate thumbnail', [
                'video' => $videoPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}