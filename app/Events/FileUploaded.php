<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FileUploaded
{
    use Dispatchable, SerializesModels;

    public $fileId;
    public $userId;
    public $fileName;
    public $filePath;

    public function __construct($fileId, $userId, $fileName, $filePath)
    {
        $this->fileId = $fileId;
        $this->userId = $userId;
        $this->fileName = $fileName;
        $this->filePath = $filePath;
    }
}
