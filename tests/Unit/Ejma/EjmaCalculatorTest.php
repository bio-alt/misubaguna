<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsGeometry;
use App\Ejma\CalculationRequest;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\EjmaCalculator;
use App\Ejma\EjmaInputException;
use App\Ejma\FormingCondition;
use App\Ejma\FormingMethod;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Ejma\Fixtures\Benchmarks;

class EjmaCalculatorTest extends TestCase
{
    private function calculator(array $mutate = []): EjmaCalculator
    {
        $config = MaterialRepositoryTest::config();
        foreach ($mutate as $k => $v) {
            $config['materials']['aus'][$k] = $v;
        }

        return new EjmaCalculator(new MaterialRepository($config));
    }

    private function request(string $material = 'aus', float $tF = 70.0, array $overrides = []): CalculationRequest
    {
        $b = Benchmarks::case1();

        return new CalculationRequest($b['geometry'], $b['conditions'], $tF, $material, null, null,
            FormingCondition::Annealed, FormingMethod::MandrelOrRoll, 1.0, 1.0, 1.0, $overrides);
    }

    private function field(callable $fn): string
    {
        try {
            $fn();
        } catch (EjmaInputException $e) {
            return $e->field.'|'.$e->getMessage();
        }
        $this->fail('expected EjmaInputException');
    }

    public function test_creep_range_rejected(): void
    {
        $r = $this->field(fn () => $this->calculator(['creep_limit_c' => 50])->calculate($this->request(tF: 200.0)));
        $this->assertSame('design_temperature|Design temperature is in the creep range; contact us for a creep-range design', $r);
    }

    public function test_unknown_material_rejected(): void
    {
        $this->assertStringStartsWith('bellows_material|', $this->field(fn () => $this->calculator()->calculate($this->request('nope'))));
        $this->assertStringStartsWith('bellows_material|', $this->field(fn () => $this->calculator()->calculate($this->request('empty'))));
    }

    public function test_full_run_returns_loads(): void
    {
        $result = $this->calculator()->calculate($this->request(overrides: ['Sab' => 20000.0]));
        $this->assertArrayHasKey('thrust', $result->values);
        $this->assertArrayHasKey('Ksr', $result->values);
        $this->assertArrayHasKey('spring_force', $result->values);
        $this->assertEqualsWithDelta($result->values['fi'] / 12, $result->values['Ksr'], 1e-6);
        $this->assertContains('Overrides used: Sab', $result->notes);
    }

    public function test_toroidal_requires_collar_and_reinforcement_materials(): void
    {
        $b = Benchmarks::case4();
        $make = fn (?string $collar, ?string $reinf) => new CalculationRequest($b['geometry'], $b['conditions'], 70.0, 'aus', $collar, $reinf,
            FormingCondition::Annealed, FormingMethod::MandrelOrRoll, 1.0, 1.0, 1.0, ['Sab' => 20000.0]);

        $this->assertStringStartsWith('reinforcement_material|', $this->field(fn () => $this->calculator()->calculate($make('aus', null))));
        $this->assertStringStartsWith('collar_material|', $this->field(fn () => $this->calculator()->calculate($make(null, 'aus'))));
    }

    public function test_collar_required_when_tc_positive(): void
    {
        $b = Benchmarks::case1();
        $g = $b['geometry'];
        $g = new BellowsGeometry($g->type, $g->Db, $g->t, $g->n, $g->N, $g->q, $g->w, $g->rm, $g->Lt, tc: 0.1);
        $r = new CalculationRequest($g, $b['conditions'], 70.0, 'aus', null, null,
            FormingCondition::Annealed, FormingMethod::MandrelOrRoll, 1.0, 1.0, 1.0, ['Sab' => 20000.0]);
        $this->assertStringStartsWith('collar_material|', $this->field(fn () => $this->calculator()->calculate($r)));
    }

    public function test_creep_check_covers_collar(): void
    {
        $config = MaterialRepositoryTest::config();
        $config['materials']['aus']['creep_limit_c'] = 500;
        $config['materials']['aus']['roles'] = ['bellows'];
        $config['materials']['col'] = $config['materials']['aus'];
        $config['materials']['col']['roles'] = ['collar', 'reinforcement'];
        $config['materials']['col']['creep_limit_c'] = 10;
        $b = Benchmarks::case4();
        $r = new CalculationRequest($b['geometry'], $b['conditions'], 200.0, 'aus', 'col', 'col',
            FormingCondition::Annealed, FormingMethod::MandrelOrRoll, 1.0, 1.0, 1.0, ['Sab' => 20000.0]);
        $this->assertStringStartsWith('design_temperature|', $this->field(fn () => (new EjmaCalculator(new MaterialRepository($config)))->calculate($r)));
    }

    public function test_sym_not_listed_when_sy_overridden(): void
    {
        $result = $this->calculator()->calculate($this->request(overrides: ['Sab' => 20000.0, 'Sy' => 40000.0, 'Sym' => 30000.0]));
        $this->assertContains('Overrides used: Sab, Sy', $result->notes);
    }
}
