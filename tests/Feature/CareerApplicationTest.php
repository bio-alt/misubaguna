<?php

namespace Tests\Feature;

use App\Mail\CareerApplication as CareerApplicationMail;
use App\Models\CareerApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CareerApplicationTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'position' => 'Safety Officer (HSE / K3)',
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'phone' => '0812-3456-7890',
            'cv' => UploadedFile::fake()->create('cv-siti.pdf', 300, 'application/pdf'),
            'cover_letter' => 'Ahli K3 Umum, 4 years in petrochemical plants.',
        ], $overrides);
    }

    public function test_application_is_stored_and_emailed_with_cv_attached(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post('/career/', $this->payload())
            ->assertRedirect('/career/#apply')
            ->assertSessionHas('career_success');

        $application = CareerApplication::sole();
        $this->assertSame('Safety Officer (HSE / K3)', $application->position);
        $this->assertSame('cv-siti.pdf', $application->cv_original_name);
        $this->assertNotNull($application->emailed_at);
        Storage::disk('local')->assertExists($application->cv_path);

        Mail::assertSent(CareerApplicationMail::class, function (CareerApplicationMail $mail) use ($application) {
            return $mail->hasTo('marketing@misubaguna.com')
                && $mail->hasReplyTo('siti@example.com')
                && $mail->application->is($application)
                && count($mail->attachments()) === 1;
        });
    }

    public function test_cv_is_required_and_must_be_a_document(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post('/career/', $this->payload(['cv' => null]))->assertSessionHasErrors('cv');
        $this->post('/career/', $this->payload(['cv' => UploadedFile::fake()->create('virus.exe', 10)]))->assertSessionHasErrors('cv');
        $this->post('/career/', $this->payload(['cv' => UploadedFile::fake()->create('huge.pdf', 6000, 'application/pdf')]))->assertSessionHasErrors('cv');

        $this->assertSame(0, CareerApplication::count());
        Mail::assertNothingSent();
    }

    public function test_unknown_position_is_rejected(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post('/career/', $this->payload(['position' => 'CEO']))->assertSessionHasErrors('position');
        Mail::assertNothingSent();
    }

    public function test_general_application_is_accepted(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post('/career/', $this->payload(['position' => 'General Application']))
            ->assertSessionHas('career_success');
        Mail::assertSent(CareerApplicationMail::class);
    }

    public function test_honeypot_submission_is_silently_dropped(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->post('/career/', $this->payload(['website' => 'http://spam.example']))
            ->assertSessionHas('career_success');

        $this->assertSame(0, CareerApplication::count());
        Mail::assertNothingSent();
    }

    public function test_application_is_kept_when_email_fails(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP down'));
        Storage::fake('local');

        $this->post('/career/', $this->payload())
            ->assertSessionHas('career_success');

        $this->assertNull(CareerApplication::sole()->emailed_at);
    }

    public function test_career_page_posts_form_to_server(): void
    {
        $this->seedCareerPage();

        $this->get('/career/')
            ->assertOk()
            ->assertSee('action="/career/"', false)
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="cv"', false)
            ->assertDontSee('mailto:marketing@misubaguna.com?subject=" + subject', false);
    }

    public function test_resend_command_emails_unsent_applications(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('career-applications/cv.pdf', '%PDF-1.4');
        $application = CareerApplication::create([
            'position' => 'General Application',
            'name' => 'Siti Rahma',
            'email' => 'siti@example.com',
            'phone' => '0812',
            'cv_path' => 'career-applications/cv.pdf',
            'cv_original_name' => 'cv.pdf',
        ]);
        Mail::fake();

        $this->artisan('career:resend')->assertSuccessful();

        Mail::assertSent(CareerApplicationMail::class);
        $this->assertNotNull($application->fresh()->emailed_at);
    }

    private function seedCareerPage(): void
    {
        \App\Models\PublicSite\PublicPage::create([
            'slug' => 'career',
            'url_path' => '/career/',
            'title' => 'Careers',
            'is_published' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '0.6',
            'sitemap_changefreq' => 'monthly',
        ]);
    }
}
