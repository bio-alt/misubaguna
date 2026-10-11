<?php

namespace Tests\Unit\Ejma;

use App\Ejma\Data\Tooling;
use PHPUnit\Framework\TestCase;

/** Guards the shape of the shipped config/ejma/tooling.php so a bad edit fails CI instead of the live page. */
class ToolingConfigTest extends TestCase
{
    private static function config(): array
    {
        return require dirname(__DIR__, 3).'/config/ejma/tooling.php';
    }

    private static function valid(): array
    {
        return [
            'thicknesses_mm' => [0.762, 0.5],
            'max_plies' => 5,
            'max_convolutions' => 30,
            'tangent_length_mm' => 25.4,
            'profiles' => [
                ['id' => 'u36', 'name' => 'U 36', 'w_mm' => 36.576, 'q_mm' => 25.4, 'rm_mm' => 6.35, 'db_min_mm' => 500, 'db_max_mm' => 700],
            ],
        ];
    }

    private function invalid(array $config, string $needle): void
    {
        try {
            new Tooling($config);
            $this->fail('expected InvalidArgumentException');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString($needle, $e->getMessage());
        }
    }

    public function test_shipped_config_has_a_valid_shape(): void
    {
        $c = self::config();

        $this->assertIsArray($c['thicknesses_mm'] ?? null, 'thicknesses_mm');
        foreach ($c['thicknesses_mm'] as $i => $t) {
            $this->assertTrue((is_int($t) || is_float($t)) && $t > 0, "thicknesses_mm[$i]");
        }
        $this->assertIsInt($c['max_plies'] ?? null, 'max_plies');
        $this->assertGreaterThanOrEqual(1, $c['max_plies']);
        $this->assertLessThanOrEqual(5, $c['max_plies'], 'EJMA bellows have at most 5 plies');
        $this->assertIsInt($c['max_convolutions'] ?? null, 'max_convolutions');
        $this->assertGreaterThanOrEqual(1, $c['max_convolutions']);
        $this->assertLessThanOrEqual(200, $c['max_convolutions']);
        $this->assertTrue(is_int($c['tangent_length_mm'] ?? null) || is_float($c['tangent_length_mm'] ?? null), 'tangent_length_mm');
        $this->assertGreaterThan(0, $c['tangent_length_mm']);

        $this->assertIsArray($c['profiles'] ?? null, 'profiles');
        foreach ($c['profiles'] as $i => $p) {
            $this->assertIsString($p['id'] ?? null, "profiles[$i] id");
            $this->assertIsString($p['name'] ?? null, "profiles[$i] name");
            foreach (['w_mm', 'q_mm', 'rm_mm', 'db_min_mm', 'db_max_mm'] as $k) {
                $this->assertTrue((is_int($p[$k] ?? null) || is_float($p[$k] ?? null)) && $p[$k] > 0, "profiles[$i] $k");
            }
            $this->assertLessThanOrEqual($p['db_max_mm'], $p['db_min_mm'], "profiles[$i] db range");
        }
    }

    public function test_shipped_config_builds_tooling(): void
    {
        $tooling = new Tooling(self::config());
        $this->assertSame(self::config()['max_plies'], $tooling->maxPlies());
        $this->assertSame(self::config()['max_convolutions'], $tooling->maxConvolutions());
    }

    public function test_empty_config_is_unavailable(): void
    {
        $this->assertFalse((new Tooling([]))->isAvailable());
        $this->assertFalse((new Tooling(['profiles' => self::valid()['profiles']]))->isAvailable());
        $this->assertFalse((new Tooling(['thicknesses_mm' => [0.5]]))->isAvailable());
        $this->assertTrue((new Tooling(self::valid()))->isAvailable());
    }

    public function test_converts_to_inches_and_filters_by_diameter(): void
    {
        $tooling = new Tooling(self::valid());

        $this->assertEqualsWithDelta([0.5 / 25.4, 0.03], $tooling->thicknessesIn(), 1e-12);
        $this->assertEqualsWithDelta(1.0, $tooling->tangentLengthIn(), 1e-12);

        $profiles = $tooling->profilesFor(24.0);
        $this->assertCount(1, $profiles);
        $p = $profiles[0];
        $this->assertSame('u36', $p->id);
        $this->assertSame('U 36', $p->name);
        $this->assertEqualsWithDelta(1.44, $p->wIn, 1e-12);
        $this->assertEqualsWithDelta(1.0, $p->qIn, 1e-12);
        $this->assertEqualsWithDelta(0.25, $p->rmIn, 1e-12);

        $this->assertSame([], $tooling->profilesFor(30.0));
        $this->assertCount(1, $tooling->profilesFor(700 / 25.4), 'upper bound is inclusive');
    }

    public function test_invalid_entries_are_named(): void
    {
        $c = self::valid();

        $bad = $c;
        $bad['profiles'][0]['w_mm'] = 0;
        $this->invalid($bad, 'u36');

        $bad = $c;
        $bad['profiles'][0]['db_min_mm'] = 800;
        $this->invalid($bad, 'u36');

        $bad = $c;
        unset($bad['profiles'][0]['id']);
        $this->invalid($bad, 'profiles[0]');

        $bad = $c;
        $bad['profiles'][1] = $bad['profiles'][0];
        $this->invalid($bad, 'u36');

        $bad = $c;
        $bad['thicknesses_mm'][] = -1;
        $this->invalid($bad, 'thicknesses_mm');

        $bad = $c;
        $bad['max_plies'] = 6;
        $this->invalid($bad, 'max_plies');

        $bad = $c;
        $bad['max_convolutions'] = 0;
        $this->invalid($bad, 'max_convolutions');

        // Capped so a typo cannot make a design search run for minutes.
        $bad = $c;
        $bad['max_convolutions'] = 201;
        $this->invalid($bad, 'max_convolutions');
        $ok = $c;
        $ok['max_convolutions'] = 200;
        $this->assertSame(200, (new Tooling($ok))->maxConvolutions());

        $bad = $c;
        $bad['tangent_length_mm'] = 0;
        $this->invalid($bad, 'tangent_length_mm');
    }
}
