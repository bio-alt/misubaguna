<?php

namespace App\Mail;

use App\Models\CareerApplication as CareerApplicationModel;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CareerApplication extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public CareerApplicationModel $application)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->application->email, $this->application->name)],
            subject: 'Job Application: '.$this->application->position.' - '.$this->application->name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.career-application');
    }

    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->application->cv_path)
                ->as($this->application->cv_original_name),
        ];
    }
}
