# EJMA Bellows Calculator — Phase 3 (Design Mode) Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a "Design it for me" mode that searches company tooling for the smallest passing unreinforced bellows and reports it through the existing gated pipeline.

**Architecture:** New Laravel-free `DesignSearch` in `app/Ejma` that enumerates candidates from `config/ejma/tooling.php` and evaluates each through the existing `EjmaCalculator`; web layer adds a mode switch, storage columns and a PDF section.

**Tech Stack:** PHP 8.4, Laravel 13, PHPUnit 12, Blade + vanilla JS, dompdf.

**Spec:** `docs/superpowers/specs/2026-10-11-ejma-calculator-phase3-design.md`

## Global Constraints

- Engine (`app/Ejma/**`) has no Laravel imports; US units inside.
- Phase 1–2 behaviour unchanged; every existing test keeps passing (add to, never loosen).
- All physics goes through `EjmaCalculator::calculate` — no duplicated equations in the search.
- Tooling config in metric (mm), shipped EMPTY; company fills it.
- Rulings carried: R3 field names = form field names; R13 rate limit counts successful calculations (a design run = 1); no calculated values or proposed geometry in visitor HTML; no red/orange/amber; teal `#0d9488`.
- New public form field names: `mode` (`check` | `design`); design mode reuses `Db`, `assembly_type`, conditions, `bellows_material`, forming fields, contact fields.
- NEVER connect to the remote DB: the worktree `.env` points to a local sqlite file; tests use sqlite in-memory. For browser checks use `php -S 127.0.0.1:8125 -t public` from the worktree.
- Pint only on changed files. Commits end with `Co-Authored-By: Claude Opus 5.5 <noreply@anthropic.com>`.

## Review Focus

1. Tooling profile whose Db range contains Db but geometry is outside the Appendix I chart → candidate skipped, search continues. → Task 1 test.
2. Every candidate fails → REVIEW verdict with a closest-candidate note, no exception. → Task 1 test.
3. Design mode POST when tooling empty (tampered form) → field error on `mode`, nothing stored. → Task 3 test.
4. Stale geometry fields in a design-mode POST → ignored. → Task 3 test.
5. Phase 1–2 rows (no `mode`) still render and resend. → Task 4 test.

---

### Task 1: Tooling data and design search

**Files:**
- Create: `config/ejma/tooling.php`, `app/Ejma/Data/Tooling.php`, `app/Ejma/Data/FormingProfile.php`, `app/Ejma/DesignRequest.php`, `app/Ejma/DesignCandidate.php`, `app/Ejma/DesignResult.php`, `app/Ejma/DesignSearch.php`
- Modify: `app/Providers/AppServiceProvider.php` (singletons for Tooling and DesignSearch from config)
- Test: `tests/Unit/Ejma/DesignSearchTest.php`, `tests/Unit/Ejma/ToolingConfigTest.php`

**Interfaces:**
- `Data\Tooling::__construct(array $config)`; `isAvailable(): bool` (≥1 profile and ≥1 thickness); `profilesFor(float $DbIn): FormingProfile[]`; `thicknessesIn(): float[]`; `maxPlies(): int`; `maxConvolutions(): int`; `tangentLengthIn(): float`. Invalid config → `\InvalidArgumentException` naming the entry.
- `Data\FormingProfile` readonly: `string $id, string $name, float $wIn, float $qIn, float $rmIn, float $dbMinIn, float $dbMaxIn`.
- `DesignRequest` readonly: `float $Db` (in), `DesignConditions $conditions`, `float $designTempF`, `string $bellowsMaterial`, `FormingCondition $condition`, `FormingMethod $method`, `float $Cwb = 1.0`.
- `DesignCandidate` readonly: `FormingProfile $profile, float $t, int $n, int $N, BellowsGeometry $geometry, CheckResult $result`; `utilisation(): float` (max value/limit over 'le' lines with finite positive limits).
- `DesignResult` readonly: `?DesignCandidate $best, array $alternatives, int $evaluated, int $passing, ?DesignCandidate $closestFailing`; `verdict(): Verdict` (Pass if best, else Review); `toArray(): array` (JSON-safe: profile, t, n, N, geometry fields, counts; alternatives same shape without results).
- `DesignSearch::__construct(EjmaCalculator $calc, Tooling $tooling)`; `search(DesignRequest $r): DesignResult` — per spec §3.2/§3.3; collarless geometry (tc = 0), `Lt` = tooling tangent length, Unreinforced type.

- [ ] **Step 1: Failing tests** — finds passing design with benchmark-1 profile (w 1.44, q 1, rm .25 in; t .03 in) for case-1-like conditions; `test_minimal` brute-forces the fixture table and asserts no earlier-sorted candidate passes; empty tooling → `isAvailable()` false and search returns null best; chart-range candidate skipped; all-fail → Review + closestFailing set; ordering tie-breaks; performance (10×8×5×30 table < 2 s); ToolingConfigTest validates the real config shape.
- [ ] **Step 2: Run** `php artisan test --filter="DesignSearchTest|ToolingConfigTest"` — FAIL.
- [ ] **Step 3: Implement.**
- [ ] **Step 4: Run** `php artisan test` — PASS.
- [ ] **Step 5: Commit** `feat(ejma): tooling data and design search`

### Task 2: Storage

**Files:**
- Create: `database/migrations/2026_10_11_000003_add_mode_and_design_to_ejma_calculations_table.php`
- Modify: `app/Models/EjmaCalculation.php`
- Test: extend `tests/Feature/EjmaCalculationModelTest.php`

**Interfaces:** columns `mode` string nullable default 'check' (after `assembly_type`), `design` json nullable; model fillable + `design` array cast; `isDesign(): bool`.

- [ ] Steps: failing test (round trip of mode/design; old rows default check) → implement → full suite → commit `feat(ejma): store design-mode results`.

### Task 3: Form, validation, controller

**Files:**
- Modify: `app/Http/Requests/EjmaCalculationRequest.php`, `app/Http/Controllers/PublicSite/EjmaCalculatorController.php`, `resources/views/public/toolkit/ejma-bellows-calculator.blade.php`, `app/Ejma/Verdict.php` (design-mode labels via a method, e.g. `designLabel()`), `app/Providers/AppServiceProvider.php` if needed
- Test: `tests/Feature/EjmaDesignModeTest.php`

**Interfaces:** `mode` select (`check` default); in design mode only `Db`, `assembly_type` (+ its fields), conditions, `bellows_material`, `forming_condition`, `forming_method`, `Cwb`, contact are validated/used — geometry, bellows type, collar/reinforcement materials excluded server-side; `EjmaCalculationRequest::toDesignRequest(): DesignRequest`; controller branches on mode, runs `DesignSearch`, stores mode/design/results (best candidate's CheckResult, or a CheckResult with no lines and the closest-failing note when none), verdict from `DesignResult::verdict()`; R13 counts one per run; design mode unavailable (tooling empty) → disabled option + "contact us for a custom design" line, POST → error on `mode`. Result screen uses design labels "A suitable bellows design was found" / "Your conditions need a custom design by our engineers" in design mode.

- [ ] Steps: failing tests (design submission stores + verdict only; unavailable mode; stale geometry ignored; check-mode unchanged; no values/geometry in HTML) → implement → browser check with `php -S` and temporary tooling/material data NOT committed → full suite → commit `feat(ejma): design-it-for-me mode on the calculator form`.

### Task 4: Report

**Files:**
- Modify: `app/Mail/EjmaCalculationReport.php`, `resources/views/pdf/ejma-calculation.blade.php`, `resources/views/emails/ejma-calculation.blade.php`
- Test: extend `tests/Feature/EjmaReportTest.php`

**Interfaces:** design-mode subject `EJMA design {ref} — {company} — {Db mm} mm{ assembly label if not single} — {VERDICT}`; check-mode subject unchanged; PDF "Proposed design" section first (profile name, w, q, rm, t, plies, N per bellows, Lt — both unit systems), alternatives table, then the existing sections for the best candidate; no-design PDF shows request summary, evaluated count, closest failing candidate and its governing check (first failing line).

- [ ] Steps: failing tests (subjects; proposed-design section; no-design PDF; Phase 1–2 rows without mode render) → implement → render sample design-mode PDF to the scratchpad and view page 1 → full suite → commit `feat(ejma): proposed design in the calculation report`.
