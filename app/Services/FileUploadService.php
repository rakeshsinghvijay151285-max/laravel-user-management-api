<?php

namespace App\Services;

use App\Events\FileUploaded;
use App\Jobs\LogFileUpload;
use App\Mail\FileUploadNotification;
use App\Models\UserFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    /**
     * Upload file for user
     */
    public function uploadFile($user, $file, $description = null)
    {
        $fileName = $file->getClientOriginalName();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        // Store file
        $path = $file->store("uploads/user_{$user->id}", 'public');

        // Create database record
        $userFile = UserFile::create([
            'user_id' => $user->id,
            'file_name' => $fileName,
            'file_path' => $path,
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'uploaded_by' => $user->id,
        ]);

        // Dispatch events
        event(new FileUploaded($userFile->id, $user->id, $fileName, $path));
        LogFileUpload::dispatch($userFile->id, $user->id, $fileName, $path);

        // Send notification email
        Mail::to($user->email)->send(new FileUploadNotification($fileName, $user->name));

        return $userFile;
    }

    /**
     * Get user files
     */
    public function getUserFiles($userId)
    {
        return UserFile::where('user_id', $userId)->paginate(15);
    }

    /**
     * Delete file
     */
    public function deleteFile($fileId, $userId)
    {
        $file = UserFile::where('id', $fileId)->where('user_id', $userId)->first();

        if ($file) {
            Storage::disk('public')->delete($file->file_path);
            $file->delete();
        }

        return $file;
    }

    /**
     * Download file
     */
    public function downloadFile($fileId, $userId)
    {
        $file = UserFile::where('id', $fileId)->where('user_id', $userId)->first();

        if ($file && Storage::disk('public')->exists($file->file_path)) {
            return Storage::disk('public')->download($file->file_path);
        }

        return null;
    }
}
