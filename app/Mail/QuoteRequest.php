<?php

namespace App\Mail;

use App\Models\Inquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * Internal email to sales for a quote request. Files are attached when they fit the mail
 * server's limit; every file is also reachable through a signed download link.
 */
class QuoteRequest extends Mailable
{
    use Queueable, SerializesModels;

    public const ATTACH_LIMIT_BYTES = 15 * 1024 * 1024;

    public const LINK_DAYS = 60;

    public function __construct(public Inquiry $inquiry)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address($this->inquiry->email, $this->inquiry->name)],
            subject: 'Quote request '.$this->inquiry->reference
                .' — '.($this->inquiry->product_name ?: $this->inquiry->item)
                .' — '.$this->inquiry->company,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-request', with: [
            'fileLinks' => $this->fileLinks(),
            'attached' => $this->attachesFiles(),
        ]);
    }

    public function attachments(): array
    {
        if (! $this->attachesFiles()) {
            return [];
        }

        return array_map(
            fn (array $file) => Attachment::fromStorageDisk('local', $file['path'])->as($file['name']),
            $this->inquiry->files ?? []
        );
    }

    private function attachesFiles(): bool
    {
        return ! empty($this->inquiry->files) && $this->inquiry->filesTotalSize() <= self::ATTACH_LIMIT_BYTES;
    }

    /** @return array<int, array{name: string, size: int, url: string}> */
    private function fileLinks(): array
    {
        $expires = now()->addDays(self::LINK_DAYS);

        return array_map(fn (array $file, int $index) => [
            'name' => $file['name'],
            'size' => (int) $file['size'],
            'url' => URL::temporarySignedRoute('public.inquiry-file', $expires, [$this->inquiry->id, $index]),
        ], $this->inquiry->files ?? [], array_keys($this->inquiry->files ?? []));
    }
}
