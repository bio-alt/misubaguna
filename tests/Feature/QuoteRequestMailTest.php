<?php

namespace Tests\Feature;

use App\Mail\QuoteConfirmation;
use App\Mail\QuoteRequest as QuoteRequestMail;
use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class QuoteRequestMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_mail_attaches_small_files_and_lists_links(): void
    {
        Storage::fake('local');
        $inquiry = $this->inquiry([['a.pdf', 1000], ['b.dwg', 2000]]);

        $mail = new QuoteRequestMail($inquiry);
        $html = $mail->render();

        $this->assertCount(2, $mail->attachments());
        $this->assertStringContainsString('a.pdf', $html);
        $this->assertStringContainsString('b.dwg', $html);
        $this->assertStringContainsString('/inquiries/files/'.$inquiry->id.'/1', $html);
    }

    public function test_internal_mail_skips_attachments_over_15_mb(): void
    {
        Storage::fake('local');
        $inquiry = $this->inquiry([['a.pdf', 9 * 1024 * 1024], ['b.pdf', 7 * 1024 * 1024]]);

        $mail = new QuoteRequestMail($inquiry);

        $this->assertSame([], $mail->attachments());
        $this->assertStringContainsString('/inquiries/files/'.$inquiry->id.'/0', $mail->render());
    }

    public function test_internal_mail_subject_and_reply_to(): void
    {
        $mail = new QuoteRequestMail($this->inquiry());

        $mail->assertHasReplyTo('budi@example.com');
        $mail->assertHasSubject('Quote request Q-2026-ABC234 — Fabric Expansion Joint — PT Industri Nusantara');
        $mail->assertSeeInHtml('2000 x 1500 rectangular');
    }

    public function test_confirmation_is_in_buyer_locale(): void
    {
        $id = (new QuoteConfirmation($this->inquiry([], ['locale' => 'id'])))->locale('id');
        $id->assertHasSubject('Permintaan penawaran Q-2026-ABC234 sudah kami terima');
        $id->assertSeeInHtml('Tim sales kami akan memeriksanya dan membalas.');

        $en = (new QuoteConfirmation($this->inquiry([], ['reference' => 'Q-2026-ABC235'])))->locale('en');
        $en->assertHasSubject('We received your quote request Q-2026-ABC235');
        $en->assertSeeInHtml('Our sales team will review it and reply.');
        $en->assertDontSeeInHtml('/inquiries/files/');
    }

    public function test_buyer_replies_reach_sales(): void
    {
        (new QuoteConfirmation($this->inquiry()))->assertHasReplyTo('marketing@misubaguna.com');
    }

    public function test_signed_download_serves_file(): void
    {
        Storage::fake('local');
        $inquiry = $this->inquiry([['drawing.pdf', 10]]);

        $this->get(URL::temporarySignedRoute('public.inquiry-file', now()->addDays(60), [$inquiry->id, 0]))
            ->assertOk()
            ->assertDownload('drawing.pdf');
    }

    public function test_tampered_or_expired_link_is_rejected(): void
    {
        Storage::fake('local');
        $inquiry = $this->inquiry([['a.pdf', 10], ['b.pdf', 10]]);
        $url = URL::temporarySignedRoute('public.inquiry-file', now()->addDays(60), [$inquiry->id, 0]);

        $this->get(str_replace('/0?', '/1?', $url))->assertForbidden();

        $this->travel(61)->days();
        $this->get($url)->assertForbidden();
    }

    public function test_missing_index_is_404(): void
    {
        Storage::fake('local');
        $inquiry = $this->inquiry([['a.pdf', 10]]);

        $this->get(URL::temporarySignedRoute('public.inquiry-file', now()->addDay(), [$inquiry->id, 5]))->assertNotFound();
    }

    public function test_download_uses_original_file_name(): void
    {
        Storage::fake('local');
        $inquiry = $this->inquiry([['Gambar teknik—rev 2 (final).pdf', 10]]);

        $response = $this->get(URL::temporarySignedRoute('public.inquiry-file', now()->addDay(), [$inquiry->id, 0]))->assertOk();

        $this->assertStringContainsString(rawurlencode('Gambar teknik—rev 2 (final).pdf'), $response->headers->get('Content-Disposition'));
    }

    /** @param array<int, array{0: string, 1: int}> $files name and size in bytes */
    private function inquiry(array $files = [], array $extra = []): Inquiry
    {
        $stored = [];
        foreach ($files as $i => [$name, $size]) {
            $path = "inquiries/2026/10/file{$i}.bin";
            Storage::disk('local')->put($path, str_repeat('x', min($size, 64)));
            $stored[] = ['path' => $path, 'name' => $name, 'size' => $size];
        }

        return Inquiry::create(array_merge([
            'kind' => Inquiry::KIND_QUOTE,
            'reference' => 'Q-2026-ABC234',
            'product_slug' => 'fabric-expansion-joint',
            'product_name' => 'Fabric Expansion Joint',
            'item' => 'Fabric Expansion Joint',
            'name' => 'Budi Santoso',
            'company' => 'PT Industri Nusantara',
            'email' => 'budi@example.com',
            'phone' => '0812-3456-7890',
            'answers' => [['label' => 'Duct size and shape (round or rectangular)', 'value' => '2000 x 1500 rectangular']],
            'files' => $stored,
            'locale' => 'en',
        ], $extra));
    }
}
