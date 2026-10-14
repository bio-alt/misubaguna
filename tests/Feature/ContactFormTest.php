<?php

namespace Tests\Feature;

use App\Mail\ContactInquiry;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'company' => 'PT Industri Nusantara',
            'email' => 'budi@example.com',
            'phone' => '0812-3456-7890',
            'category' => 'Flexible Joint',
            'message' => 'Need DN200 expansion joint, 10 bar, 120C.',
        ], $overrides);
    }

    public function test_inquiry_is_emailed_to_marketing(): void
    {
        Mail::fake();

        $this->post('/contact-us/', $this->payload())
            ->assertRedirect('/contact-us/#inquiry-form')
            ->assertSessionHas('contact_success');

        Mail::assertSent(ContactInquiry::class, function (ContactInquiry $mail) {
            return $mail->hasTo('marketing@misubaguna.com')
                && $mail->hasReplyTo('budi@example.com')
                && $mail->inquiry['company'] === 'PT Industri Nusantara';
        });
    }

    public function test_invalid_inquiry_is_rejected(): void
    {
        Mail::fake();

        $this->post('/contact-us/', $this->payload(['email' => 'not-an-email', 'message' => '']))
            ->assertSessionHasErrors(['email', 'message']);

        Mail::assertNothingSent();
    }

    public function test_honeypot_submission_is_silently_dropped(): void
    {
        Mail::fake();

        $this->post('/contact-us/', $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHas('contact_success');

        Mail::assertNothingSent();
        $this->assertSame(0, Inquiry::count());
    }

    public function test_inquiry_is_stored_before_mailing(): void
    {
        Mail::fake();

        $this->post("/contact-us/", $this->payload());

        $inquiry = Inquiry::sole();
        $this->assertSame(Inquiry::KIND_CONTACT, $inquiry->kind);
        $this->assertNull($inquiry->reference);
        $this->assertSame("Flexible Joint", $inquiry->category);
        $this->assertSame("Need DN200 expansion joint, 10 bar, 120C.", $inquiry->message);
        $this->assertSame("en", $inquiry->locale);
        $this->assertNotNull($inquiry->emailed_at);
    }

    public function test_mail_failure_keeps_the_stored_inquiry_and_shows_error(): void
    {
        Mail::shouldReceive("to")->andThrow(new \RuntimeException("SMTP down"));

        $this->post("/contact-us/", $this->payload())->assertSessionHas("contact_error");

        $this->assertNull(Inquiry::sole()->emailed_at);
    }
}
