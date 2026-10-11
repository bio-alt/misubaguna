<?php

namespace Tests\Unit\Ejma;

use App\Ejma\AssemblyGeometry;
use App\Ejma\AssemblyType;
use App\Ejma\BellowsGeometry;
use App\Ejma\BellowsType;
use App\Ejma\CalculationRequest;
use App\Ejma\CheckLine;
use App\Ejma\CheckResult;
use App\Ejma\Data\FormingProfile;
use App\Ejma\Data\MaterialRepository;
use App\Ejma\Data\Tooling;
use App\Ejma\DesignCandidate;
use App\Ejma\DesignConditions;
use App\Ejma\DesignRequest;
use App\Ejma\DesignSearch;
use App\Ejma\EjmaCalculator;
use App\Ejma\EjmaInputException;
use App\Ejma\FormingCondition;
use App\Ejma\FormingMethod;
use App\Ejma\LineStatus;
use App\Ejma\Verdict;
use PHPUnit\Framework\TestCase;

class DesignSearchTest extends TestCase
{
    private const IN = 25.4;

    /** Benchmark-1 convolution (EJMA §4.14 p097): w 1.44, q 1, rm 0.25 in. */
    private static function benchmarkProfile(string $id = 'bm1'): array
    {
        return ['id' => $id, 'name' => "Profile $id", 'w_mm' => 1.44 * self::IN, 'q_mm' => 1.0 * self::IN,
            'rm_mm' => 0.25 * self::IN, 'db_min_mm' => 500, 'db_max_mm' => 700];
    }

    /** T = 2rm/w = 1.39 > 1: outside the Appendix I charts for every thickness. */
    private static function offChartProfile(): array
    {
        return ['id' => 'aa_offchart', 'name' => 'Off chart', 'w_mm' => 1.44 * self::IN, 'q_mm' => 2.5 * self::IN,
            'rm_mm' => 1.0 * self::IN, 'db_min_mm' => 500, 'db_max_mm' => 700];
    }

    private static function tooling(array $profiles, array $thicknessesIn = [0.03], int $plies = 3, int $convolutions = 20): Tooling
    {
        return new Tooling([
            'thicknesses_mm' => array_map(fn ($t) => $t * self::IN, $thicknessesIn),
            'max_plies' => $plies,
            'max_convolutions' => $convolutions,
            'tangent_length_mm' => 1.0 * self::IN,
            'profiles' => $profiles,
        ]);
    }

    private static function calculator(array $mutate = []): EjmaCalculator
    {
        $config = MaterialRepositoryTest::config();
        foreach ($mutate as $k => $v) {
            $config['materials']['aus'][$k] = $v;
        }

        return new EjmaCalculator(new MaterialRepository($config));
    }

    /** Benchmark-1 service conditions (P 40 psi, x 1/0.5 in, y 0.06 in, θ 5°) with a cycle requirement. */
    private static function conditions(float $P = 40, ?AssemblyGeometry $assembly = null, int $cycles = 1000): DesignConditions
    {
        return new DesignConditions(P: $P, xComp: 1, xExt: 0.5, y: 0.06, theta: deg2rad(5), requiredCycles: $cycles,
            assembly: $assembly ?? new AssemblyGeometry);
    }

    private static function request(?DesignConditions $c = null, float $tF = 70.0): DesignRequest
    {
        return new DesignRequest(24.0, $c ?? self::conditions(), $tF, 'aus', FormingCondition::Annealed, FormingMethod::MandrelOrRoll);
    }

    public function test_finds_a_passing_design_with_the_benchmark_profile(): void
    {
        $result = (new DesignSearch(self::calculator(), self::tooling([self::benchmarkProfile()])))->search(self::request());

        $this->assertSame(Verdict::Pass, $result->verdict());
        $best = $result->best;
        $this->assertNotNull($best);
        $this->assertSame('bm1', $best->profile->id);
        $this->assertEqualsWithDelta(0.03, $best->t, 1e-12);
        $this->assertSame(Verdict::Pass, $best->result->verdict());
        $this->assertGreaterThan(0, $result->passing);
        $this->assertGreaterThanOrEqual($result->passing, $result->evaluated);
        $this->assertNull($result->closestFailing);
        $this->assertNull($result->reviewNote());

        $g = $best->geometry;
        $this->assertSame(BellowsType::Unreinforced, $g->type);
        $this->assertSame(24.0, $g->Db);
        $this->assertSame($best->n, $g->n);
        $this->assertSame($best->N, $g->N);
        $this->assertEqualsWithDelta(1.44, $g->w, 1e-12);
        $this->assertEqualsWithDelta(1.0, $g->q, 1e-12);
        $this->assertEqualsWithDelta(0.25, $g->rm, 1e-12);
        $this->assertEqualsWithDelta(1.0, $g->Lt, 1e-12);
        $this->assertSame(0.0, $g->tc);
        $this->assertSame(0.0, $g->Lc);
        $this->assertLessThanOrEqual(1.0, $best->utilisation());
    }

    public function test_the_proposed_design_is_minimal(): void
    {
        $profiles = [
            self::benchmarkProfile(),
            ['id' => 'small', 'name' => 'Small', 'w_mm' => 1.2 * self::IN, 'q_mm' => 0.9 * self::IN, 'rm_mm' => 0.22 * self::IN, 'db_min_mm' => 500, 'db_max_mm' => 700],
            ['id' => 'tall', 'name' => 'Tall', 'w_mm' => 1.8 * self::IN, 'q_mm' => 1.2 * self::IN, 'rm_mm' => 0.3 * self::IN, 'db_min_mm' => 500, 'db_max_mm' => 700],
            self::offChartProfile(),
        ];
        $tooling = self::tooling($profiles, [0.02, 0.03, 0.04], 3, 20);
        $calc = self::calculator();
        $request = self::request();

        $result = (new DesignSearch($calc, $tooling))->search($request);
        $this->assertNotNull($result->best);

        // Brute force every candidate without pruning.
        $passing = [];
        foreach ($tooling->profilesFor($request->Db) as $p) {
            foreach ($tooling->thicknessesIn() as $t) {
                for ($n = 1; $n <= $tooling->maxPlies(); $n++) {
                    if (! DesignSearch::fits($p, $t, $n)) {
                        continue;
                    }
                    for ($N = 1; $N <= $tooling->maxConvolutions(); $N++) {
                        $g = new BellowsGeometry(BellowsType::Unreinforced, $request->Db, $t, $n, $N, $p->qIn, $p->wIn, $p->rmIn, $tooling->tangentLengthIn());
                        try {
                            $r = $calc->calculate(new CalculationRequest($g, $request->conditions, $request->designTempF, 'aus', null, null,
                                $request->condition, $request->method, 1.0, 1.0, 1.0));
                        } catch (EjmaInputException) {
                            continue;
                        }
                        if ($r->verdict() === Verdict::Pass) {
                            $passing[] = new DesignCandidate($p, $t, $n, $N, $g, $r);
                        }
                    }
                }
            }
        }

        $this->assertNotEmpty($passing);
        foreach ($passing as $c) {
            $this->assertGreaterThanOrEqual(0, DesignSearch::compare($c, $result->best),
                sprintf('%s t=%.3f n=%d N=%d sorts before the proposal and passes', $c->profile->id, $c->t, $c->n, $c->N));
        }
        usort($passing, DesignSearch::compare(...));
        $this->assertSame(0, DesignSearch::compare($passing[0], $result->best));
        $this->assertSame([$passing[0]->profile->id, $passing[0]->n, $passing[0]->N], [$result->best->profile->id, $result->best->n, $result->best->N]);

        // Alternatives: the next two distinct (profile, t, n) combinations, in order.
        $this->assertCount(2, $result->alternatives);
        $keys = array_map(fn (DesignCandidate $c) => $c->profile->id.'|'.$c->t.'|'.$c->n, [$result->best, ...$result->alternatives]);
        $this->assertSame($keys, array_values(array_unique($keys)));
        $this->assertLessThanOrEqual(0, DesignSearch::compare($result->best, $result->alternatives[0]));
        $this->assertLessThanOrEqual(0, DesignSearch::compare($result->alternatives[0], $result->alternatives[1]));
    }

    public function test_empty_tooling_gives_no_design(): void
    {
        $tooling = new Tooling([]);
        $this->assertFalse($tooling->isAvailable());

        $result = (new DesignSearch(self::calculator(), $tooling))->search(self::request());
        $this->assertNull($result->best);
        $this->assertSame([], $result->alternatives);
        $this->assertSame(0, $result->evaluated);
        $this->assertSame(Verdict::Review, $result->verdict());
        $this->assertNotNull($result->reviewNote());
    }

    public function test_no_profile_for_the_diameter_gives_no_design(): void
    {
        $request = new DesignRequest(40.0, self::conditions(), 70.0, 'aus', FormingCondition::Annealed, FormingMethod::MandrelOrRoll);
        $result = (new DesignSearch(self::calculator(), self::tooling([self::benchmarkProfile()])))->search($request);

        $this->assertNull($result->best);
        $this->assertSame(0, $result->evaluated);
        $this->assertSame(Verdict::Review, $result->verdict());
    }

    public function test_chart_range_candidates_are_skipped(): void
    {
        $search = new DesignSearch(self::calculator(), self::tooling([self::offChartProfile(), self::benchmarkProfile()]));
        $result = $search->search(self::request());
        $this->assertSame('bm1', $result->best?->profile->id);

        $only = (new DesignSearch(self::calculator(), self::tooling([self::offChartProfile()])))->search(self::request());
        $this->assertNull($only->best);
        $this->assertSame(0, $only->evaluated);
        $this->assertNull($only->closestFailing);
        $this->assertSame(Verdict::Review, $only->verdict());
    }

    public function test_request_level_errors_are_not_swallowed(): void
    {
        $search = new DesignSearch(self::calculator(['creep_limit_c' => 50]), self::tooling([self::benchmarkProfile()]));
        try {
            $search->search(self::request(tF: 200.0));
            $this->fail('expected EjmaInputException');
        } catch (EjmaInputException $e) {
            $this->assertSame('design_temperature', $e->field);
        }
    }

    public function test_all_failing_gives_review_with_closest_candidate(): void
    {
        $result = (new DesignSearch(self::calculator(), self::tooling([self::benchmarkProfile()], [0.02, 0.03], 2, 10)))
            ->search(self::request(self::conditions(P: 2000)));

        $this->assertNull($result->best);
        $this->assertSame(0, $result->passing);
        $this->assertGreaterThan(0, $result->evaluated);
        $this->assertSame(Verdict::Review, $result->verdict());
        $closest = $result->closestFailing;
        $this->assertNotNull($closest);
        $this->assertNotSame(Verdict::Pass, $closest->result->verdict());
        $governing = $closest->governingLine();
        $this->assertNotNull($governing);
        $this->assertSame(LineStatus::Fail, $governing->status);
        $this->assertStringContainsString($governing->label, $result->reviewNote());

        $array = $result->toArray();
        $this->assertNull($array['best']);
        $this->assertSame($governing->key, $array['closest_failing']['governing']['key']);
        $this->assertSame('review', $array['verdict']);
    }

    public function test_universal_assembly_prunes_when_lu_is_exceeded(): void
    {
        $assembly = new AssemblyGeometry(type: AssemblyType::Universal, Lu: 36);
        $result = (new DesignSearch(self::calculator(), self::tooling([self::benchmarkProfile()], [0.03], 3, 30)))
            ->search(self::request(self::conditions(assembly: $assembly)));

        $this->assertNotNull($result->best);
        $this->assertSame(Verdict::Pass, $result->best->result->verdict());
        // Lu = 36 fits at most N = 17 per bellows (2Lb < Lu); the loop stops instead of throwing to N = 30.
        $this->assertLessThan(3 * 17, $result->evaluated);
    }

    /** Reviewer repro (ruling P3-R4): w 5 mm, rm 2.45 mm, t 1.4 mm: 4 or 5 plies (5.6/7.0 mm) cannot fit the die. */
    public function test_packs_that_do_not_fit_the_convolution_are_never_evaluated(): void
    {
        $profile = ['id' => 'small', 'name' => 'Small die', 'w_mm' => 5.0, 'q_mm' => 10.0, 'rm_mm' => 2.45, 'db_min_mm' => 200, 'db_max_mm' => 400];
        $request = new DesignRequest(300 / self::IN, self::conditions(P: 15, cycles: 100), 70.0, 'aus', FormingCondition::Annealed, FormingMethod::MandrelOrRoll);
        $search = fn (int $plies) => (new DesignSearch(self::calculator(), self::tooling([$profile], [1.4 / self::IN], $plies, 30)))->search($request);

        $five = $search(5);
        // n = 3 (4.2 mm) is the largest pack below both w and 2rm: allowing 5 plies evaluates nothing more.
        $this->assertSame($search(3)->evaluated, $five->evaluated);
        $this->assertGreaterThan(0, $five->evaluated);
        foreach (array_filter([$five->best, ...$five->alternatives, $five->closestFailing]) as $c) {
            $this->assertLessThan($c->profile->wIn, $c->n * $c->t);
            $this->assertLessThan(2 * $c->profile->rmIn, $c->n * $c->t);
        }
    }

    public function test_a_pack_exactly_filling_the_convolution_is_skipped(): void
    {
        $p = fn (float $w, float $rm) => new FormingProfile('p', 'p', $w, 1.0, $rm, 20.0, 30.0);

        $this->assertTrue(DesignSearch::fits($p(0.5, 1.0), 0.25, 1));
        $this->assertFalse(DesignSearch::fits($p(0.5, 1.0), 0.25, 2), 'n·t = w');
        $this->assertFalse(DesignSearch::fits($p(2.0, 0.25), 0.25, 2), 'n·t = 2rm');
        $this->assertFalse(DesignSearch::fits($p(2.0, 0.25), 0.25, 3), 'n·t > 2rm');

        // Through the search: Db 100 mm, t 1.4 mm, rm 1.4 mm: n = 2 equals 2rm exactly (in inches too) and is skipped.
        $profile = ['id' => 'eq', 'name' => 'Equal', 'w_mm' => 6.0, 'q_mm' => 10.0, 'rm_mm' => 1.4, 'db_min_mm' => 50, 'db_max_mm' => 200];
        $request = new DesignRequest(100 / self::IN, self::conditions(P: 15, cycles: 100), 70.0, 'aus', FormingCondition::Annealed, FormingMethod::MandrelOrRoll);
        $search = fn (int $plies) => (new DesignSearch(self::calculator(), self::tooling([$profile], [1.4 / self::IN], $plies, 30)))->search($request);
        $this->assertGreaterThan(0, $search(1)->evaluated);
        $this->assertSame($search(1)->evaluated, $search(2)->evaluated);
    }

    public function test_ordering_tie_breaks(): void
    {
        $p = fn (string $id) => new FormingProfile($id, $id, 1.44, 1.0, 0.25, 20.0, 30.0);
        $c = fn (string $id, float $t, int $n, int $N, float $util) => new DesignCandidate($p($id), $t, $n, $N,
            new BellowsGeometry(BellowsType::Unreinforced, 24, $t, $n, $N, 1.0, 1.44, 0.25, 1.0),
            new CheckResult(BellowsType::Unreinforced, [
                new CheckLine('hoop', 'Hoop', '4-27', $util * 1000, 1000.0, 'Sab', LineStatus::Pass, 'S1'),
                new CheckLine('fatigue', 'Fatigue', '4-34', 5000.0, 1000.0, 'Nc', LineStatus::Pass, 'Nc', CheckLine::GE),
            ], []));

        // (1) fewer convolutions first, regardless of everything else.
        $this->assertLessThan(0, DesignSearch::compare($c('z', 0.05, 3, 8, 0.9), $c('a', 0.01, 1, 9, 0.1)));
        // (2) less total metal n·t first.
        $this->assertLessThan(0, DesignSearch::compare($c('z', 0.02, 2, 8, 0.9), $c('a', 0.03, 2, 8, 0.1)));
        // (3) same n·t (2 × 0.03 = 3 × 0.02): fewer plies first.
        $this->assertLessThan(0, DesignSearch::compare($c('z', 0.03, 2, 8, 0.9), $c('a', 0.02, 3, 8, 0.1)));
        // (4) lower utilisation first.
        $this->assertLessThan(0, DesignSearch::compare($c('z', 0.03, 2, 8, 0.5), $c('a', 0.03, 2, 8, 0.6)));
        // (5) profile id.
        $this->assertLessThan(0, DesignSearch::compare($c('a', 0.03, 2, 8, 0.5), $c('b', 0.03, 2, 8, 0.5)));
        $this->assertSame(0, DesignSearch::compare($c('a', 0.03, 2, 8, 0.5), $c('a', 0.03, 2, 8, 0.5)));

        // Utilisation ignores 'ge' lines.
        $this->assertEqualsWithDelta(0.5, $c('a', 0.03, 2, 8, 0.5)->utilisation(), 1e-12);
    }

    public function test_identical_profiles_tie_on_profile_id(): void
    {
        $result = (new DesignSearch(self::calculator(), self::tooling([self::benchmarkProfile('b'), self::benchmarkProfile('a')])))
            ->search(self::request());

        $this->assertSame('a', $result->best?->profile->id);
        $this->assertSame('b', $result->alternatives[0]->profile->id);
        $this->assertSame($result->best->N, $result->alternatives[0]->N);
    }

    public function test_to_array_is_json_safe(): void
    {
        $result = (new DesignSearch(self::calculator(), self::tooling([self::benchmarkProfile()], [0.02, 0.03])))->search(self::request());
        $array = $result->toArray();

        $this->assertNotFalse(json_encode($array, JSON_THROW_ON_ERROR));
        $this->assertSame('pass', $array['verdict']);
        $this->assertSame($result->evaluated, $array['evaluated']);
        $this->assertSame($result->passing, $array['passing']);
        $this->assertSame('bm1', $array['best']['profile']['id']);
        $this->assertSame('Profile bm1', $array['best']['profile']['name']);
        $this->assertSame($result->best->n, $array['best']['n']);
        $this->assertSame($result->best->N, $array['best']['N']);
        $this->assertEqualsWithDelta($result->best->t, $array['best']['t'], 1e-12);
        $this->assertSame('unreinforced', $array['best']['geometry']['type']);
        $this->assertEqualsWithDelta(1.0, $array['best']['geometry']['Lt'], 1e-12);
        $this->assertArrayNotHasKey('result', $array['best']);
        foreach ($array['alternatives'] as $alt) {
            $this->assertSame(array_keys($array['best']), array_keys($alt));
        }
        $this->assertNull($array['closest_failing']);
    }

    public function test_search_is_fast_on_a_full_size_table(): void
    {
        $profiles = [];
        for ($i = 0; $i < 10; $i++) {
            $profiles[] = ['id' => sprintf('p%02d', $i), 'name' => "P$i",
                'w_mm' => (1.2 + 0.06 * $i) * self::IN, 'q_mm' => (0.9 + 0.03 * $i) * self::IN,
                'rm_mm' => (0.22 + 0.01 * $i) * self::IN, 'db_min_mm' => 500, 'db_max_mm' => 700];
        }
        $tooling = self::tooling($profiles, [0.008, 0.01, 0.012, 0.015, 0.02, 0.025, 0.03, 0.04], 5, 30);
        $search = new DesignSearch(self::calculator(), $tooling);

        $start = hrtime(true);
        $result = $search->search(self::request());
        $seconds = (hrtime(true) - $start) / 1e9;

        $this->assertNotNull($result->best);
        $this->assertLessThan(2.0, $seconds, sprintf('search took %.3f s for %d candidates', $seconds, $result->evaluated));

        // Worst case: nothing passes, so every (profile, t, n) loop runs until it is pruned or exhausted.
        $start = hrtime(true);
        $none = $search->search(self::request(self::conditions(P: 2000)));
        $seconds = (hrtime(true) - $start) / 1e9;

        $this->assertNull($none->best);
        $this->assertLessThan(2.0, $seconds, sprintf('all-fail search took %.3f s for %d candidates', $seconds, $none->evaluated));

        // Heavy case: a long cycle life pushes most loops to large N before they pass or squirm.
        $start = hrtime(true);
        $long = $search->search(self::request(self::conditions(cycles: 1_000_000_000)));
        $seconds = (hrtime(true) - $start) / 1e9;

        $this->assertGreaterThan(4000, $long->evaluated);
        $this->assertLessThan(2.0, $seconds, sprintf('long-life search took %.3f s for %d candidates', $seconds, $long->evaluated));
    }
}
