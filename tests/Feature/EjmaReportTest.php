<?php

namespace Tests\Feature;

use App\Ejma\Data\MaterialRepository;
use App\Ejma\EjmaCalculator;
use App\Mail\EjmaCalculationReport;
use App\Models\EjmaCalculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EjmaReportTest extends TestCase
{
    use RefreshDatabase;

    private const URL = '/toolkit/ejma-bellows-calculator/';

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-11 10:00:00');

        $tables = [
            'allowable_mpa' => [20 => 138, 300 => 118],
            'modulus_gpa' => [20 => 195, 300 => 180],
            'yield_mpa' => [20 => 205, 300 => 150],
        ];
        config(['ejma.materials' => ['materials' => [
            'fixture_ss' => ['name' => 'Fixture stainless', 'family' => 'austenitic', 'roles' => ['bellows', 'collar', 'reinforcement'], 'creep_limit_c' => 250] + $tables,
        ]]]);
        $this->app->forgetInstance(MaterialRepository::class);
        $this->app->forgetInstance(EjmaCalculator::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** EJMA benchmark case 1 (unreinforced) converted to metric. */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'units' => 'metric', 'bellows_type' => 'unreinforced', 'design_pressure' => '2.7579',
            'design_temperature' => '21', 'required_cycles' => '1000', 'x_comp' => '25.4', 'x_ext' => '12.7',
            'lateral' => '1.524', 'angular' => '5', 'Db' => '609.6', 'w' => '36.576', 't' => '0.762', 'n' => '2',
            'N' => '12', 'q' => '25.4', 'rm' => '6.35', 'Lt' => '25.4', 'bellows_material' => 'fixture_ss',
            'forming_condition' => 'annealed', 'forming_method' => 'mandrel_roll', 'Cwb' => '1', 'Cwc' => '1',
            'Cwr' => '1', 'name' => 'Budi Santoso', 'company' => 'PT Industri Nusantara',
            'email' => 'budi@example.com', 'phone' => '0812-3456-7890', 'notes' => 'Quote for 2 pcs.', 'website' => '',
        ], $overrides);
    }

    public function test_submission_sends_report_with_pdf(): void
    {
        Mail::fake();

        $this->post(self::URL, $this->payload())->assertSessionHasNoErrors();

        $row = EjmaCalculation::first();
        Mail::assertSent(EjmaCalculationReport::class, function (EjmaCalculationReport $mail) use ($row) {
            $subject = $mail->envelope()->subject;

            return $mail->hasTo('marketing@misubaguna.com')
                && $mail->hasReplyTo('budi@example.com')
                && str_contains($subject, 'EJ-2026-0001')
                && str_contains($subject, 'PT Industri Nusantara')
                && str_contains($subject, '609.6 mm')
                && str_contains($subject, strtoupper($row->verdict))
                && count($mail->attachments()) === 1
                && $mail->attachments()[0]->as === 'EJ-2026-0001.pdf'
                && $mail->attachments()[0]->mime === 'application/pdf';
        });
        $this->assertNotNull($row->emailed_at);
    }

    public function test_pdf_contains_results(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload());
        $row = EjmaCalculation::first();
        $s1 = collect($row->results['lines'])->firstWhere('symbol', 'S1')['value'];

        $mail = new EjmaCalculationReport($row);
        $html = $mail->pdfHtml();

        $sab = (int) round($row->results['values']['prop_Sab']);
        $this->assertStringContainsString(number_format($sab), $html);
        $this->assertStringContainsString('Material properties used', $html);
        $this->assertStringContainsString('Cm (material strength factor)', $html);
        $this->assertStringContainsString('609.6 mm', $html);
        $this->assertStringNotContainsString('override_', $html);
        $this->assertStringContainsString('4-27', $html);
        $this->assertStringContainsString('Calculated per EJMA Standards, 9th Edition', $html);
        $this->assertStringContainsString(number_format($s1, 0), $html);
        $this->assertStringContainsString('EJ-2026-0001', $html);
        $this->assertStringContainsString('Assumptions &amp; notes', $html);
        $this->assertStringContainsString('Preliminary calculation for quotation purposes, to be confirmed by PT Misuba Guna Indonesia engineering. Not a substitute for a certified design.', $html);
        $pdf = $mail->renderPdf();
        $this->assertStringStartsWith('%PDF', $pdf);
        // Font subsetting keeps the attachment small (the full DejaVu fonts made it ~870 KB).
        $this->assertLessThan(300_000, strlen($pdf));
    }

    public function test_mail_failure_still_shows_result(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $response = $this->post(self::URL, $this->payload());
        $response->assertRedirect(self::URL.'#result');

        $row = EjmaCalculation::first();
        $this->assertNull($row->emailed_at);
        $this->followRedirects($response)->assertSee($row->verdictEnum()->label());
    }

    public function test_resend_failure_leaves_emailed_at_unchanged(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload());
        EjmaCalculation::first()->update(['emailed_at' => null]);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->artisan('ejma:resend EJ-2026-0001')->assertExitCode(1);

        $this->assertNull(EjmaCalculation::first()->emailed_at);
    }

    public function test_override_is_flagged_in_pdf(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload(['override_Sab' => '150']));
        $html = (new EjmaCalculationReport(EjmaCalculation::first()))->pdfHtml();

        $this->assertStringContainsString('Bellows allowable stress Sab (override)', $html);
        $this->assertStringContainsString('150 MPa', $html);
    }

    public function test_resend_command(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload());
        EjmaCalculation::first()->update(['emailed_at' => null]);

        $this->artisan('ejma:resend EJ-2026-0001')->assertExitCode(0);

        Mail::assertSent(EjmaCalculationReport::class, 2);
        $this->assertNotNull(EjmaCalculation::first()->emailed_at);

        $this->artisan('ejma:resend EJ-2026-9999')->assertExitCode(1);
    }
}
