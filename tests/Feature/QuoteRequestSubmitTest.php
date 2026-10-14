<?php

namespace Tests\Feature;

use App\Mail\QuoteConfirmation;
use App\Mail\QuoteRequest as QuoteRequestMail;
use App\Models\Inquiry;
use App\Models\PublicSite\PublicProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuoteRequestSubmitTest extends TestCase
{
    use RefreshDatabase;

    private const AT_LEAST_ONE = 'Please answer at least one question, attach a drawing, or describe what you need.';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->product('fabric-expansion-joint');
    }

    public function test_quote_is_stored_files_saved_and_both_mails_sent(): void
    {
        Mail::fake();

        $response = $this->post('/request-a-quote/', $this->payload([
            'answers' => [0 => '2000 x 1500 rectangular', 1 => '', 2 => '  '],
            'files' => [
                UploadedFile::fake()->create('layout.pdf', 200, 'application/pdf'),
                UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg'),
            ],
        ]));

        $inquiry = Inquiry::sole();
        $response->assertRedirect('/request-a-quote/thanks/'.$inquiry->reference.'/');

        $this->assertSame(Inquiry::KIND_QUOTE, $inquiry->kind);
        $this->assertMatchesRegularExpression('/^Q-\d{4}-[A-Z0-9]{6}$/', $inquiry->reference);
        $this->assertSame('fabric-expansion-joint', $inquiry->product_slug);
        $this->assertSame('Fabric Expansion Joint', $inquiry->product_name);
        $this->assertSame([['label' => 'Duct size and shape (round or rectangular)', 'value' => '2000 x 1500 rectangular']], $inquiry->answers);
        $this->assertCount(2, $inquiry->files);
        $this->assertSame('layout.pdf', $inquiry->files[0]['name']);
        foreach ($inquiry->files as $file) {
            Storage::disk('local')->assertExists($file['path']);
            $this->assertStringStartsWith('inquiries/'.now()->format('Y/m').'/', $file['path']);
        }
        $this->assertSame('en', $inquiry->locale);
        $this->assertNotNull($inquiry->emailed_at);
        $this->assertNotNull($inquiry->confirmed_at);

        Mail::assertSent(QuoteRequestMail::class, fn ($mail) => $mail->hasTo('marketing@misubaguna.com') && $mail->inquiry->is($inquiry));
        Mail::assertSent(QuoteConfirmation::class, fn ($mail) => $mail->hasTo('budi@example.com'));
    }

    public function test_at_least_one_detail_is_required(): void
    {
        Mail::fake();

        $this->post('/request-a-quote/', $this->payload(['answers' => ['', ''], 'message' => '']))
            ->assertSessionHasErrors(['answers' => self::AT_LEAST_ONE]);

        $this->assertSame(0, Inquiry::count());
        Mail::assertNothingSent();
    }

    public function test_any_single_detail_is_enough(): void
    {
        Mail::fake();

        $this->post('/request-a-quote/', $this->payload(['answers' => ['DN 300']]))->assertSessionHasNoErrors();
        $this->post('/request-a-quote/', $this->payload(['files' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')]]))->assertSessionHasNoErrors();
        $this->post('/request-a-quote/', $this->payload(['message' => 'Replacement for our boiler duct joint.']))->assertSessionHasNoErrors();

        $this->assertSame(3, Inquiry::count());
    }

    public function test_file_limits(): void
    {
        Mail::fake();
        $pdf = fn ($name = 'a.pdf', $kb = 10) => UploadedFile::fake()->create($name, $kb, 'application/pdf');

        $this->post('/request-a-quote/', $this->payload(['files' => [$pdf(), $pdf(), $pdf(), $pdf()]]))->assertSessionHasErrors('files');
        $this->post('/request-a-quote/', $this->payload(['files' => [UploadedFile::fake()->create('virus.exe', 10)]]))->assertSessionHasErrors('files.0');
        $this->post('/request-a-quote/', $this->payload(['files' => [$pdf('big.pdf', 11 * 1024)]]))->assertSessionHasErrors('files.0');
        $this->assertSame(0, Inquiry::count());

        $this->post('/request-a-quote/', $this->payload(['files' => [UploadedFile::fake()->create('plan.dwg', 50, 'application/octet-stream')]]))
            ->assertSessionHasNoErrors();
        $this->assertSame('plan.dwg', Inquiry::sole()->files[0]['name']);
    }

    public function test_item_required_without_product(): void
    {
        Mail::fake();

        $this->post('/request-a-quote/', $this->payload(['product' => null, 'item' => '']))->assertSessionHasErrors('item');
        $this->post('/request-a-quote/', $this->payload(['product' => null, 'item' => 'Gland packing 12 mm']))->assertSessionHasNoErrors();
    }

    public function test_honeypot_creates_nothing(): void
    {
        Mail::fake();

        $this->post('/request-a-quote/', $this->payload(['website' => 'http://spam.example']))
            ->assertRedirect('/request-a-quote/')
            ->assertSessionHas('quote_received');

        $this->assertSame(0, Inquiry::count());
        Mail::assertNothingSent();
    }

    public function test_mail_failure_still_stores_and_thanks(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));

        $response = $this->post('/request-a-quote/', $this->payload());

        $inquiry = Inquiry::sole();
        $response->assertRedirect('/request-a-quote/thanks/'.$inquiry->reference.'/');
        $this->assertNull($inquiry->emailed_at);
        $this->assertNull($inquiry->confirmed_at);
    }

    public function test_tampered_answer_indices_are_ignored(): void
    {
        Mail::fake();

        $this->post('/request-a-quote/', $this->payload(['answers' => [0 => 'y', 99 => 'x']]))->assertSessionHasNoErrors();

        $this->assertSame([['label' => 'Duct size and shape (round or rectangular)', 'value' => 'y']], Inquiry::sole()->answers);
    }

    public function test_unpublished_product_falls_back_to_generic(): void
    {
        Mail::fake();
        $this->product('draft-joint', ['is_published' => false]);

        $this->post('/request-a-quote/', $this->payload(['product' => 'draft-joint', 'item' => 'Draft joint', 'answers' => ['DN 100']]))
            ->assertSessionHasNoErrors();

        $inquiry = Inquiry::sole();
        $this->assertNull($inquiry->product_slug);
        $this->assertSame('Size or dimensions (or a drawing)', $inquiry->answers[0]['label']);
    }

    public function test_indonesian_submit_stays_in_indonesian(): void
    {
        Mail::fake();
        $this->product('rubber-expansion-joint');

        $response = $this->post('/id/request-a-quote/', $this->payload(['product' => 'rubber-expansion-joint']));

        $inquiry = Inquiry::sole();
        $response->assertRedirect('/id/request-a-quote/thanks/'.$inquiry->reference.'/');
        $this->assertSame('id', $inquiry->locale);
        Mail::assertSent(QuoteConfirmation::class, fn ($mail) => $mail->locale === 'id');
    }

    public function test_thanks_page_shows_reference_not_contact_details(): void
    {
        Mail::fake();
        $this->post('/request-a-quote/', $this->payload(['answers' => ['2000 x 1500 rectangular']]));
        $inquiry = Inquiry::sole();

        $this->get('/request-a-quote/thanks/'.$inquiry->reference.'/')
            ->assertOk()
            ->assertSee($inquiry->reference)
            ->assertSee('2000 x 1500 rectangular')
            ->assertSee('noindex', false)
            ->assertDontSee('budi@example.com')
            ->assertDontSee('0812-3456-7890');

        $this->get('/request-a-quote/thanks/Q-2026-NOPE22/')->assertNotFound();
    }

    private function payload(array $overrides = []): array
    {
        return array_filter(array_merge([
            'product' => 'fabric-expansion-joint',
            'name' => 'Budi Santoso',
            'company' => 'PT Industri Nusantara',
            'email' => 'budi@example.com',
            'phone' => '0812-3456-7890',
            'location' => 'Cilegon',
            'item' => 'Fabric Expansion Joint',
            'answers' => ['2000 x 1500 rectangular'],
            'message' => '',
        ], $overrides), fn ($v) => $v !== null);
    }

    private function product(string $slug, array $extra = []): PublicProduct
    {
        return PublicProduct::updateOrCreate(['slug' => $slug], array_merge([
            'url_path' => '/product/'.$slug.'/',
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'product_group' => 'Flexible Joint',
            'summary' => 'English summary.',
            'is_published' => true,
        ], $extra));
    }
}
