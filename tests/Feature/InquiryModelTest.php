<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_has_quote_format_and_is_unique(): void
    {
        $ref = Inquiry::nextReference();

        $this->assertMatchesRegularExpression('/^Q-'.now()->year.'-[A-Z0-9]{6}$/', $ref);
        $this->assertNotSame($ref, Inquiry::nextReference());
    }

    public function test_answers_and_files_round_trip_as_arrays(): void
    {
        $inquiry = Inquiry::create([
            'kind' => Inquiry::KIND_QUOTE,
            'reference' => Inquiry::nextReference(),
            'name' => 'A',
            'company' => 'B',
            'email' => 'a@example.com',
            'phone' => '1',
            'locale' => 'en',
            'answers' => [['label' => 'Size', 'value' => 'DN200']],
            'files' => [
                ['path' => 'x', 'name' => 'a.pdf', 'size' => 100],
                ['path' => 'y', 'name' => 'b.pdf', 'size' => 50],
            ],
        ])->fresh();

        $this->assertSame('DN200', $inquiry->answers[0]['value']);
        $this->assertSame(150, $inquiry->filesTotalSize());
    }
}
