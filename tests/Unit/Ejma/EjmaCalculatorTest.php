<?php

namespace Tests\Unit\Ejma;

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
}
