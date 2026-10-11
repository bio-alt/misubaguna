<?php

namespace Tests\Feature;

use App\Ejma\BellowsType;
use App\Ejma\UnreinforcedBellows;
use App\Ejma\Verdict;
use App\Models\EjmaCalculation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class EjmaCalculationModelTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function makeRow(string $reference, array $extra = []): EjmaCalculation
    {
        return EjmaCalculation::create(array_merge([
            'reference' => $reference,
            'bellows_type' => 'unreinforced',
            'verdict' => 'pass',
            'inputs' => ['a' => 1],
            'results' => ['b' => 2],
            'contact' => ['email' => 'x@example.com'],
        ], $extra));
    }

    public function test_first_reference_of_year(): void
    {
        Carbon::setTestNow('2026-10-11 10:00:00');

        $this->assertSame('EJ-2026-0001', EjmaCalculation::nextReference());
    }

    public function test_sequence_increments(): void
    {
        Carbon::setTestNow('2026-10-11 10:00:00');
        $this->makeRow('EJ-2026-0001');

        $this->assertSame('EJ-2026-0002', EjmaCalculation::nextReference());
    }

    public function test_sequence_restarts_next_year(): void
    {
        Carbon::setTestNow('2026-10-11 10:00:00');
        $this->makeRow('EJ-2026-0001');
        Carbon::setTestNow('2027-01-02 10:00:00');

        $this->assertSame('EJ-2027-0001', EjmaCalculation::nextReference());
    }

    public function test_result_round_trips(): void
    {
        $b = Benchmarks::case1();
        $result = (new UnreinforcedBellows)->check($b['geometry'], $b['conditions'], $b['properties']);

        $row = $this->makeRow('EJ-2026-0001', [
            'bellows_type' => $result->type->value,
            'verdict' => $result->verdict()->value,
            'results' => $result->toArray(),
        ]);
        $fresh = EjmaCalculation::find($row->id);

        $this->assertSame($result->toArray(), $fresh->result()->toArray());
        $this->assertSame($result->verdict(), $fresh->verdictEnum());
        $this->assertSame(BellowsType::Unreinforced->value, $fresh->bellows_type);
        $this->assertInstanceOf(Verdict::class, $fresh->verdictEnum());
    }

    public function test_mode_and_design_round_trip(): void
    {
        $design = ['verdict' => 'pass', 'candidates' => [['n' => 1, 'x' => 2.5]]];
        $row = $this->makeRow('EJ-2026-0001', ['mode' => 'design', 'design' => $design]);
        $fresh = EjmaCalculation::find($row->id);

        $this->assertSame('design', $fresh->mode);
        $this->assertSame($design, $fresh->design);
        $this->assertTrue($fresh->isDesign());
    }

    public function test_rows_without_mode_default_to_check(): void
    {
        $fresh = EjmaCalculation::find($this->makeRow('EJ-2026-0001')->id);

        $this->assertSame('check', $fresh->mode);
        $this->assertNull($fresh->design);
        $this->assertFalse($fresh->isDesign());
        $this->assertFalse((new EjmaCalculation(['mode' => null]))->isDesign());
    }
}
