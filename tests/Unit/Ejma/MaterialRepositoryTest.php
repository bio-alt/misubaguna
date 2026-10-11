<?php

namespace Tests\Unit\Ejma;

use App\Ejma\BellowsType;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\EjmaInputException;
use App\Ejma\FatigueConstants;
use PHPUnit\Framework\TestCase;

class MaterialRepositoryTest extends TestCase
{
    public static function config(): array
    {
        $tables = [
            'allowable_mpa' => [20 => 138, 300 => 118],
            'modulus_gpa' => [20 => 195, 300 => 180],
            'yield_mpa' => [20 => 205, 300 => 150],
        ];

        return ['materials' => [
            'aus' => ['name' => 'Aus', 'family' => 'austenitic', 'roles' => ['bellows', 'collar', 'reinforcement'], 'creep_limit_c' => 500] + $tables,
            'nick' => ['name' => 'Nick', 'family' => 'nickel', 'roles' => ['bellows'], 'creep_limit_c' => 500] + $tables,
            'empty' => ['name' => 'Empty', 'family' => 'austenitic', 'roles' => ['bellows'], 'creep_limit_c' => null,
                'allowable_mpa' => [], 'modulus_gpa' => [], 'yield_mpa' => []],
        ]];
    }

    public function test_interpolates_linearly(): void
    {
        $m = (new MaterialRepository(self::config()))->find('aus');
        $this->assertEqualsWithDelta(128 * 145.037738, $m->saPsi(160), 0.01);
    }

    public function test_outside_range_throws(): void
    {
        $m = (new MaterialRepository(self::config()))->find('aus');
        try {
            $m->saPsi(400);
            $this->fail('expected exception');
        } catch (EjmaInputException $e) {
            $this->assertSame('design_temperature', $e->field);
        }
    }

    public function test_material_without_data_hidden(): void
    {
        $repo = new MaterialRepository(self::config());
        $ids = array_map(fn ($m) => $m->id, $repo->availableFor('bellows'));
        $this->assertSame(['aus', 'nick'], $ids);
    }

    public function test_austenitic_defaults_to_figure_4_20_constants(): void
    {
        $f = (new MaterialRepository(self::config()))->find('aus')->fatigue(BellowsType::Unreinforced);
        $this->assertEquals(FatigueConstants::ejmaAustenitic(BellowsType::Unreinforced), $f);
    }

    public function test_nickel_without_constants_returns_null(): void
    {
        $this->assertNull((new MaterialRepository(self::config()))->find('nick')->fatigue(BellowsType::Unreinforced));
    }

    public function test_bellows_needs_yield_and_20c_modulus(): void
    {
        $c = self::config();
        $noYield = $c['materials']['aus'];
        $noYield['yield_mpa'] = [];
        $late = $c['materials']['aus'];
        $late['modulus_gpa'] = [40 => 195, 300 => 180];
        $c['materials']['noyield'] = $noYield;
        $c['materials']['late'] = $late;
        $repo = new MaterialRepository($c);

        $bellows = array_map(fn ($m) => $m->id, $repo->availableFor('bellows'));
        $this->assertSame(['aus', 'nick'], $bellows);
        $collar = array_map(fn ($m) => $m->id, $repo->availableFor('collar'));
        $this->assertSame(['aus', 'noyield', 'late'], $collar);
    }

    public function test_material_without_creep_limit_is_hidden_for_every_role(): void
    {
        $c = self::config();
        $c['materials']['nocreep'] = ['creep_limit_c' => null] + $c['materials']['aus'];
        $repo = new MaterialRepository($c);

        foreach (['bellows', 'collar', 'reinforcement'] as $role) {
            $ids = array_map(fn ($m) => $m->id, $repo->availableFor($role));
            $this->assertNotContains('nocreep', $ids, $role);
            $this->assertContains('aus', $ids, $role);
        }
    }
}
