<?php

namespace App\Jobs;

use App\Models\Project;
use App\Models\Story;
use App\Services\VideoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class ProcessVideoJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $modelType,
        private int $modelId,
        private string $disk = 'public'
    ) {}

    /**
     * معالجة الفيديو في الخلفية للملفات الكبيرة
     */
    public function handle(VideoService $videoService): void
    {
        $model = $this->resolveModel();
        if (!$model) {
            Log::error('ProcessVideoJob: model not found', [
                'type' => $this->modelType,
                'id' => $this->modelId,
            ]);
            return;
        }

        $model->update(['video_status' => 'processing']);

        try {
            $sourcePath = storage_path("app/{$this->disk}/{$model->video}");
            if (!file_exists($sourcePath)) {
                $model->update(['video_status' => 'failed']);
                return;
            }

            $result = $videoService->processFromPath($sourcePath, $this->disk);

            $model->update([
                'video' => $result['video'] ?? $model->video,
                'video_thumbnail' => $result['video_thumbnail'],
                'video_status' => $result['video_status'],
            ]);

            Log::info('ProcessVideoJob: completed', [
                'type' => $this->modelType,
                'id' => $this->modelId,
                'status' => $result['video_status'],
            ]);
        } catch (\Throwable $e) {
            Log::error('ProcessVideoJob: failed', [
                'type' => $this->modelType,
                'id' => $this->modelId,
                'error' => $e->getMessage(),
            ]);
            $model->update(['video_status' => 'failed']);
        }
    }

    private function resolveModel(): Project|Story|null
    {
        return match ($this->modelType) {
            'project' => Project::find($this->modelId),
            'story' => Story::find($this->modelId),
            default => null,
        };
    }
}