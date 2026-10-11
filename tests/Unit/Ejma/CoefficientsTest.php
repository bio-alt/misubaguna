<?php

namespace Tests\Unit\Ejma;

use App\Ejma\Coefficients;
use App\Ejma\EjmaDataUnavailable;
use App\Ejma\EjmaInputException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\TestCase;

/**
 * Appendix I interpolation (eqs. I-1 to I-5) and data loading, run against synthetic tables
 * (Fixtures/synthetic-coefficients.php): the real EJMA tables are private and are checked by
 * the Private suite (tests/Private/Ejma/CoefficientTablesTest.php).
 */
class CoefficientsTest extends TestCase
{
    private const SYNTHETIC = __DIR__.'/Fixtures/synthetic-coefficients.php';

    #[After]
    protected function restoreDataFile(): void
    {
        Coefficients::useDataFile(null);
    }

    public function test_parameters(): void
    {
        $this->assertEqualsWithDelta(0.5 / 1.44, Coefficients::T(0.25, 1.44), 1e-12);
        $this->assertEqualsWithDelta(1.82 * 0.25 / sqrt(25.5 * 0.03), Coefficients::M(0.25, 25.5, 0.03), 1e-12);
    }

    public function test_grid_points_return_table_values(): void
    {
        Coefficients::useDataFile(self::SYNTHETIC);

        $this->assertEqualsWithDelta(0.6 + 2.5, Coefficients::cp(0.60, 2.5), 1e-12);
        $this->assertEqualsWithDelta(0.2, Coefficients::cp(0.0, 0.2), 1e-12);
        $this->assertEqualsWithDelta(2.0 + 0.2, Coefficients::cf(1.0, 0.2), 1e-12);
        $this->assertEqualsWithDelta(1.0 + 2.0, Coefficients::cd(1.0, 1.0), 1e-12);
        $this->assertSame(20.0, Coefficients::b1(20));
        $this->assertSame(0.0, Coefficients::b2(0));
        $this->assertSame(60.0, Coefficients::b3(20));
    }

    public function test_bilinear_and_linear_interpolation(): void
    {
        Coefficients::useDataFile(self::SYNTHETIC);

        // The synthetic tables are linear in T and M, so eqs. I-3 to I-5 reproduce them exactly
        // between grid lines, including across the uneven M spacing (1.6 → 2.0 → 2.5).
        foreach ([[0.63, 2.3], [0.07, 0.31], [0.999, 3.75], [0.5, 1.8]] as [$T, $M]) {
            $this->assertEqualsWithDelta($T + $M, Coefficients::cp($T, $M), 1e-12, "cp($T, $M)");
            $this->assertEqualsWithDelta(2 * $T + $M, Coefficients::cf($T, $M), 1e-12, "cf($T, $M)");
            $this->assertEqualsWithDelta($T + 2 * $M, Coefficients::cd($T, $M), 1e-12, "cd($T, $M)");
        }
        $this->assertEqualsWithDelta(7.25, Coefficients::b1(7.25), 1e-12);
        $this->assertEqualsWithDelta(2 * 13.4, Coefficients::b2(13.4), 1e-12);
        $this->assertEqualsWithDelta(3 * 0.5, Coefficients::b3(0.5), 1e-12);
    }

    public function test_table_edges_tolerate_rounding(): void
    {
        Coefficients::useDataFile(self::SYNTHETIC);

        $this->assertEqualsWithDelta(1.0 + 4.0, Coefficients::cp(1.0 + 1e-12, 4.0 + 1e-12), 1e-9);
        $this->assertEqualsWithDelta(0.2, Coefficients::cp(-1e-12, 0.2), 1e-9);
    }

    public function test_out_of_range_throws(): void
    {
        Coefficients::useDataFile(self::SYNTHETIC);

        foreach ([
            fn () => Coefficients::cp(1.2, 2.0),
            fn () => Coefficients::cf(0.5, 0.1),
            fn () => Coefficients::cd(0.5, 4.5),
            fn () => Coefficients::cp(-0.1, 1.0),
            fn () => Coefficients::cp(NAN, 1.0),
            fn () => Coefficients::b1(30),
            fn () => Coefficients::b2(-1),
            fn () => Coefficients::b3(INF),
        ] as $i => $call) {
            try {
                $call();
                $this->fail("Case {$i} did not throw");
            } catch (EjmaInputException $e) {
                $this->assertSame('geometry', $e->field);
                $this->assertStringContainsString('outside EJMA chart range', $e->getMessage());
            }
        }
    }

    public function test_missing_data_is_unavailable_and_lookups_throw(): void
    {
        Coefficients::useDataFile(__DIR__.'/Fixtures/does-not-exist.php');

        $this->assertFalse(Coefficients::available());
        foreach ([
            fn () => Coefficients::cp(0.5, 1.0),
            fn () => Coefficients::cf(0.5, 1.0),
            fn () => Coefficients::cd(0.5, 1.0),
            fn () => Coefficients::b1(1.0),
        ] as $i => $call) {
            try {
                $call();
                $this->fail("Case {$i} did not throw");
            } catch (EjmaDataUnavailable $e) {
                $this->assertInstanceOf(\RuntimeException::class, $e);
                $this->assertStringContainsString('not installed', $e->getMessage());
            }
        }
        // T and M are plain equations and need no data.
        $this->assertEqualsWithDelta(0.5, Coefficients::T(0.25, 1.0), 1e-12);
    }

    public function test_incomplete_data_is_unavailable(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ejma');
        file_put_contents($file, '<?php return ["cp" => [], "cf" => [], "cd" => [], "b" => []];');
        try {
            Coefficients::useDataFile($file);
            $this->assertFalse(Coefficients::available());

            $this->expectException(EjmaDataUnavailable::class);
            $this->expectExceptionMessage("table 'cp'");
            Coefficients::cp(0.5, 1.0);
        } finally {
            @unlink($file);
        }
    }

    public function test_hook_switches_and_restores_data(): void
    {
        Coefficients::useDataFile(self::SYNTHETIC);
        $this->assertTrue(Coefficients::available());
        $this->assertSame(self::SYNTHETIC, Coefficients::dataFile());

        Coefficients::useDataFile(null);
        $this->assertSame(Coefficients::defaultDataFile(), Coefficients::dataFile());
        $this->assertStringEndsWith('/resources/ejma-private/coefficients.php', str_replace('\\', '/', Coefficients::defaultDataFile()));
        $this->assertSame(is_file(Coefficients::defaultDataFile()), Coefficients::available());
    }
}
