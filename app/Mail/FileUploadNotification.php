<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FileUploadNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $fileName;
    public $userName;

    public function __construct($fileName, $userName)
    {
        $this->fileName = $fileName;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'File Upload Notification'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.file-upload-notification',
            with: [
                'fileName' => $this->fileName,
                'userName' => $this->userName,
            ],
        );
    }
}
