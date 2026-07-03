<?php

namespace App\Listeners;

use App\Events\FileUploaded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogFileUpload implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(FileUploaded $event): void
    {
        Log::info('File uploaded', [
            'file_id' => $event->fileId,
            'user_id' => $event->userId,
            'file_name' => $event->fileName,
            'file_path' => $event->filePath,
        ]);
    }
}
