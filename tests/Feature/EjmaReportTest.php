<?php

namespace Tests\Feature;

use App\Ejma\Data\MaterialRepository;
use App\Ejma\Data\Tooling;
use App\Ejma\DesignSearch;
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

    public function test_pdf_movement_limit_lines_are_in_inches(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload());
        $row = EjmaCalculation::first();
        $ec = collect($row->results['lines'])->firstWhere('symbol', 'ec');
        $this->assertSame('movement_ec_max', $ec['key']);

        $html = (new EjmaCalculationReport($row))->pdfHtml();

        $this->assertStringContainsString(number_format($ec['value'], 4).' in', $html);
        $this->assertStringContainsString('0.4400 in', $html, 'ec(max) = q − 2rm − nt = 0.44 in');
        $this->assertStringNotContainsString(number_format($ec['value'], 4).' psi', $html);
    }

    private function tiedPayload(array $overrides = []): array
    {
        return $this->payload(array_merge([
            'assembly_type' => 'tied_universal', 'Lu' => '800', 'tie_rods' => '4',
            'x_ext' => '', 'angular' => '2', 'lateral' => '2',
        ], $overrides));
    }

    public function test_universal_subject_names_the_assembly(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->tiedPayload())->assertSessionHasNoErrors();
        $subject = (new EjmaCalculationReport(EjmaCalculation::first()))->subjectLine();

        $this->assertStringContainsString('609.6 mm tied universal unreinforced — ', $subject);
    }

    public function test_single_subject_keeps_phase1_format(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload());
        $row = EjmaCalculation::first();
        $subject = (new EjmaCalculationReport($row))->subjectLine();

        $this->assertStringStartsWith('EJMA calc EJ-2026-0001 — PT Industri Nusantara — 609.6 mm unreinforced — '.strtoupper($row->verdict), $subject);
        $this->assertStringNotContainsString('single', $subject);
    }

    public function test_pdf_for_tied_universal_prints_assembly_section(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->tiedPayload());
        $row = EjmaCalculation::first();
        $v = $row->results['values'];
        $html = (new EjmaCalculationReport($row))->pdfHtml();

        $this->assertStringContainsString('Assembly', $html);
        $this->assertStringContainsString('Tied universal', $html);
        $this->assertSame(1, substr_count(strtolower($html), 'checks apply to each'), 'stated once, in the assembly note');
        $this->assertStringContainsString('Assembly: Tied universal (2 bellows; checks apply to each bellows)', $html);
        $this->assertStringContainsString('Number of tie rods', $html);
        $this->assertStringContainsString('Length Lu', $html);
        $this->assertStringContainsString('per tie rod', $html);
        $this->assertStringContainsString('Spring force at maximum axial movement (carried by the tie rods)', $html);
        $this->assertStringContainsString(number_format($v['hardware_per_component'], 0).' lbf', $html);
        $this->assertStringContainsString(number_format($v['hardware_per_component'] * 4.4482216152605, 0).' N', $html);
        $this->assertStringContainsString('Ku', $html);
        $this->assertStringContainsString('stresses and pressures are in psi', strtolower($html));
        $this->assertStringStartsWith('%PDF', (new EjmaCalculationReport($row))->renderPdf());
    }

    public function test_email_and_pdf_headers_name_the_assembly_except_single(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload());
        $this->post(self::URL, $this->tiedPayload());
        [$single, $tied] = EjmaCalculation::orderBy('id')->get()->all();

        $this->assertStringContainsString('Unreinforced bellows &middot;', (new EjmaCalculationReport($single))->render());
        $this->assertStringContainsString('<td>Unreinforced</td>', (new EjmaCalculationReport($single))->pdfHtml());
        $this->assertStringNotContainsString('carried by the tie rods', (new EjmaCalculationReport($single))->pdfHtml());
        $this->assertStringContainsString('Unreinforced bellows, tied universal assembly', (new EjmaCalculationReport($tied))->render());
        $this->assertStringContainsString('<td>Unreinforced, tied universal</td>', (new EjmaCalculationReport($tied))->pdfHtml());
    }

    public function test_pdf_for_reinforced_notes_ec_limit_basis(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload([
            'units' => 'us', 'bellows_type' => 'reinforced', 'design_pressure' => '100', 'design_temperature' => '70',
            'x_comp' => '1', 'x_ext' => '0.5', 'lateral' => '', 'angular' => '5',
            'Db' => '24', 't' => '0.03', 'n' => '2', 'N' => '8', 'q' => '1', 'w' => '1.44', 'rm' => '0.25', 'Lt' => '1.25',
            'tc' => '0.375', 'Ar' => '0.15', 'Atc' => '0.43', 'Zc' => '0.0872', 'ng' => '8', 'fw' => '14751',
            'collar_material' => 'fixture_ss', 'reinforcement_material' => 'fixture_ss',
        ]))->assertSessionHasNoErrors();
        $html = (new EjmaCalculationReport(EjmaCalculation::first()))->pdfHtml();

        $this->assertStringContainsString(e('ec(max) checked as q − 2rm − nt; equalizing-ring spacing not checked (integral reinforcement only).'), $html);
    }

    public function test_pdf_for_hinged_hides_ku(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload(['assembly_type' => 'hinged', 'x_comp' => '', 'x_ext' => '', 'lateral' => '', 'angular' => '3']))
            ->assertSessionHasNoErrors();
        $html = (new EjmaCalculationReport(EjmaCalculation::first()))->pdfHtml();

        $this->assertStringContainsString('per hinge pin', $html);
        $this->assertStringNotContainsString('Lateral distribution factor Ku', $html);
    }

    public function test_pdf_renders_for_phase1_row_without_assembly_type(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload());
        $row = EjmaCalculation::first();
        $row->forceFill(['assembly_type' => null])->save();
        $mail = new EjmaCalculationReport($row->fresh());

        $html = $mail->pdfHtml();
        $this->assertStringContainsString('Single', $html);
        $this->assertStringNotContainsString('Checks apply to each', $html);
        $this->assertStringStartsWith('%PDF', $mail->renderPdf());
        $this->assertStringNotContainsString('single', $mail->subjectLine());
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

    private function useTooling(array $overrides = []): void
    {
        config(['ejma.tooling' => $overrides + [
            'thicknesses_mm' => [0.762], 'max_plies' => 3, 'max_convolutions' => 20, 'tangent_length_mm' => 25.4,
            'profiles' => [['id' => 'bm1', 'name' => 'Benchmark die', 'w_mm' => 36.576, 'q_mm' => 25.4, 'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 700]],
        ]]);
        foreach ([Tooling::class, DesignSearch::class] as $class) {
            $this->app->forgetInstance($class);
        }
    }

    private function designRow(array $postOverrides = [], array $tooling = []): EjmaCalculation
    {
        Mail::fake();
        $this->useTooling($tooling);
        $payload = $this->payload($postOverrides + ['mode' => 'design', 'assembly_type' => 'single']);
        foreach (['bellows_type', 'w', 't', 'n', 'N', 'q', 'rm', 'Lt', 'Cwc', 'Cwr'] as $field) {
            unset($payload[$field]);
        }
        $this->post(self::URL, $payload)->assertSessionHasNoErrors();

        return EjmaCalculation::firstOrFail();
    }

    public function test_design_pass_subject_pdf_and_email(): void
    {
        $row = $this->designRow();
        $this->assertSame('pass', $row->verdict);
        $mail = new EjmaCalculationReport($row);

        $this->assertSame('EJMA design EJ-2026-0001 — PT Industri Nusantara — 609.6 mm — PASS', $mail->subjectLine());

        $html = $mail->pdfHtml();
        $best = $row->design['best'];
        $this->assertStringContainsString('Proposed design', $html);
        $this->assertStringContainsString('Alternatives', $html);
        $this->assertStringContainsString('Benchmark die', $html);
        $this->assertStringContainsString('36.58 mm', $html);
        $this->assertStringContainsString($best['N'].' convolutions', $html);
        $this->assertLessThan(strpos($html, '4. EJMA checks'), strpos($html, 'Proposed design'));
        $this->assertLessThan(strpos($html, '1. Inputs and materials'), strpos($html, 'Proposed design'));
        $this->assertStringStartsWith('%PDF', $mail->renderPdf());

        $email = $mail->render();
        $this->assertStringContainsString('A suitable bellows design was found', $email);
        $this->assertStringNotContainsString('Your design passes all EJMA checks', $email);
        $this->assertStringContainsString('Benchmark die', $email);
    }

    public function test_design_subject_names_a_universal_assembly(): void
    {
        $row = $this->designRow(['assembly_type' => 'universal', 'Lu' => '1500']);
        $subject = (new EjmaCalculationReport($row))->subjectLine();

        $this->assertStringStartsWith('EJMA design EJ-2026-0001 — PT Industri Nusantara — 609.6 mm universal — ', $subject);
    }

    public function test_design_review_pdf_lists_the_closest_candidate_without_a_check_table(): void
    {
        $row = $this->designRow(['design_pressure' => '20'], ['max_plies' => 1, 'max_convolutions' => 4]);
        $this->assertSame('review', $row->verdict);
        $mail = new EjmaCalculationReport($row);

        $this->assertSame('EJMA design EJ-2026-0001 — PT Industri Nusantara — 609.6 mm — REVIEW', $mail->subjectLine());

        $html = $mail->pdfHtml();
        $governing = $row->design['closest_failing']['governing'];
        $this->assertStringContainsString('Candidates evaluated: '.$row->design['evaluated'], $html);
        $this->assertStringContainsString('Closest candidate', $html);
        $this->assertStringContainsString('Benchmark die', $html);
        $this->assertStringContainsString($governing['label'], $html);
        $this->assertStringContainsString($governing['equation'], $html);
        $this->assertStringContainsString('Design pressure', $html);
        $this->assertStringNotContainsString('4. EJMA checks', $html);
        $this->assertStringNotContainsString('Proposed design', $html);
        $this->assertStringContainsString('Preliminary calculation for quotation purposes', $html);
        $this->assertStringStartsWith('%PDF', $mail->renderPdf());

        $email = $mail->render();
        $this->assertStringContainsString('Your conditions need a custom design by our engineers', $email);
    }

    public function test_row_verdict_drives_the_design_subject(): void
    {
        $row = $this->designRow(['design_pressure' => '20'], ['max_plies' => 1, 'max_convolutions' => 4]);
        $row->forceFill(['verdict' => 'pass'])->save();

        $this->assertStringEndsWith('— PASS', (new EjmaCalculationReport($row->fresh()))->subjectLine());
    }

    public function test_phase1_rows_without_mode_render_as_before(): void
    {
        Mail::fake();
        $this->post(self::URL, $this->payload())->assertSessionHasNoErrors();
        $row = EjmaCalculation::first();
        $row->forceFill(['mode' => null, 'design' => null])->save();
        $mail = new EjmaCalculationReport($row->fresh());

        $this->assertStringStartsWith('EJMA calc EJ-2026-0001 — PT Industri Nusantara — 609.6 mm unreinforced — ', $mail->subjectLine());
        $html = $mail->pdfHtml();
        $this->assertStringNotContainsString('Proposed design', $html);
        $this->assertStringContainsString('4. EJMA checks', $html);
        $this->assertStringContainsString('1. Inputs and materials', $html);
        $this->assertStringContainsString($row->verdictEnum()->label(), $mail->render());
        $this->assertStringStartsWith('%PDF', $mail->renderPdf());
    }
}
