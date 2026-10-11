<?php

namespace Tests\Unit\Ejma;

use App\Ejma\Data\MaterialRepository;
use PHPUnit\Framework\TestCase;

/** Guards the shape of the shipped config/ejma/materials.php so a bad edit fails CI instead of the live page. */
class MaterialsConfigTest extends TestCase
{
    private const TABLES = ['allowable_mpa', 'modulus_gpa', 'yield_mpa'];

    private static function config(): array
    {
        return require dirname(__DIR__, 3).'/config/ejma/materials.php';
    }

    public function test_shipped_config_has_a_valid_shape(): void
    {
        $config = self::config();
        $this->assertIsArray($config['materials'] ?? null, 'top-level materials array');

        foreach ($config['materials'] as $id => $m) {
            $this->assertIsString($m['name'] ?? null, "$id name");
            $this->assertContains($m['family'] ?? null, ['austenitic', 'nickel', 'other'], "$id family");

            $roles = $m['roles'] ?? null;
            $this->assertIsArray($roles, "$id roles");
            $this->assertNotEmpty($roles, "$id roles");
            $this->assertSame([], array_diff($roles, ['bellows', 'collar', 'reinforcement']), "$id roles");

            $this->assertArrayHasKey('creep_limit_c', $m, "$id creep_limit_c");
            $this->assertTrue($m['creep_limit_c'] === null || is_int($m['creep_limit_c']) || is_float($m['creep_limit_c']), "$id creep_limit_c");

            $anyData = false;
            foreach (self::TABLES as $table) {
                $this->assertIsArray($m[$table] ?? null, "$id $table");
                foreach ($m[$table] as $t => $v) {
                    $this->assertTrue(is_numeric($t), "$id $table key $t");
                    $this->assertTrue(is_int($v) || is_float($v), "$id $table value at $t");
                }
                $anyData = $anyData || $m[$table] !== [];
            }

            if ($anyData && in_array('bellows', $roles, true)) {
                $this->assertTrue(self::hasKey20($m['modulus_gpa']), "$id modulus_gpa needs a 20 deg C row");
                $this->assertTrue(self::hasKey20($m['yield_mpa']), "$id yield_mpa needs a 20 deg C row");
            }

            if (array_key_exists('fatigue', $m)) {
                $this->assertIsArray($m['fatigue'], "$id fatigue");
                foreach ($m['fatigue'] as $type => $c) {
                    $this->assertContains($type, ['unreinforced', 'reinforced', 'toroidal'], "$id fatigue type");
                    foreach (['a', 'b', 'c'] as $k) {
                        $this->assertTrue(isset($c[$k]) && (is_int($c[$k]) || is_float($c[$k])), "$id fatigue $type $k");
                    }
                }
            }
        }
    }

    public function test_shipped_config_builds_a_repository(): void
    {
        $repo = new MaterialRepository(self::config());
        $this->assertNotNull($repo->find('ss316'));
    }

    public function test_invalid_family_names_the_material(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('bad_mat');

        new MaterialRepository(['materials' => [
            'bad_mat' => ['name' => 'Bad', 'family' => 'ferritic', 'roles' => ['bellows'], 'creep_limit_c' => 400,
                'allowable_mpa' => [], 'modulus_gpa' => [], 'yield_mpa' => []],
        ]]);
    }

    /** @param  array<int|string, mixed>  $table */
    private static function hasKey20(array $table): bool
    {
        foreach (array_keys($table) as $k) {
            if (abs((float) $k - 20.0) < 1e-9) {
                return true;
            }
        }

        return false;
    }
}
