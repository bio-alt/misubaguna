<?php

namespace App\Mail;

use App\Models\Inquiry;
use App\Support\ProductContent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirmation to the buyer, in the language of the page they used.
 */
class QuoteConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Inquiry $inquiry)
    {
        $this->locale($inquiry->locale ?: 'en');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [new Address(config('mail.contact_to'), 'PT Misuba Guna Indonesia')],
            subject: __('We received your quote request :reference', ['reference' => $this->inquiry->reference], $this->inquiry->locale),
        );
    }

    public function content(): Content
    {
        $locale = $this->inquiry->locale ?: 'en';

        return new Content(view: 'emails.quote-confirmation', with: [
            'lang' => $locale,
            'waUrl' => $this->whatsappIn($locale),
        ]);
    }

    /** whatsappUrl() words its greeting by the app locale, so switch to the buyer's for the call. */
    private function whatsappIn(string $locale): string
    {
        $previous = app()->getLocale();
        app()->setLocale($locale);

        try {
            return ProductContent::whatsappUrl(__('quote request :reference', ['reference' => $this->inquiry->reference]));
        } finally {
            app()->setLocale($previous);
        }
    }
}
