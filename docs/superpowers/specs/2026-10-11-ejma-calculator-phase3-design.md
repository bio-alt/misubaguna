# EJMA Bellows Calculator — Phase 3 Design (Design Mode)

**Date:** 2026-10-11
**Status:** Pre-approved by the user (autonomous execution of all phases)
**Builds on:** Phase 1 and Phase 2 specs in this folder (merged)

## 1. Purpose

Add a **"Design it for me"** mode. The visitor gives only service conditions; the engine
searches the company's own tooling (forming dies, sheet thicknesses, ply counts) for the
smallest unreinforced U-shaped bellows that passes every EJMA check, and the company gets
the proposed design with its full calculation. This serves visitors who don't know
convolution geometry: most plant engineers and buyers.

Success criteria:

- With a tooling table containing the benchmark-1 profile, the search proposes a design
  that passes all checks for benchmark-like service conditions.
- The proposal is minimal under the stated ordering (§3.3): no candidate that sorts
  earlier passes.
- With no tooling data, design mode is unavailable (form shows the existing "contact us"
  notice for that mode) and nothing 500s.
- Check mode (Phases 1–2) is unchanged.

## 2. Scope

**In:** unreinforced U-shaped bellows; all Phase 2 assemblies (single, universal, tied
universal, hinged, gimbal); bellows material chosen by the visitor from the existing
materials list; required cycle life; design pressure and temperature; movements as in
check mode.

**Out:** designing reinforced or toroidal bellows (they need ring and collar design);
collar design (designs are proposed without a reinforcing collar, tc = 0); cost
optimisation beyond the ordering in §3.3.

## 3. Engine (`app/Ejma`, Laravel-free)

### 3.1 Tooling data — `config/ejma/tooling.php`

Edited by the company like `materials.php`, metric units:

- `thicknesses_mm`: list of available sheet thicknesses (one ply).
- `max_plies`: integer (e.g. 5; EJMA's bellows definition allows at most 5).
- `max_convolutions`: integer per bellows, 1 to 200 (capped so a typo cannot stall the search).
- `tangent_length_mm`: default straight tangent Lt.
- `profiles`: list of forming dies, each `{id, name, w_mm, q_mm, rm_mm, db_min_mm,
  db_max_mm}` (convolution height, pitch, mean radius, and the bellows inside-diameter
  range the die can form).

Seeded with empty lists. Design mode is available only when at least one profile and
one thickness exist. A config-shape test guards the real file, as for materials.

### 3.2 Search — `DesignSearch`

Input `DesignRequest`: Db (bellows inside diameter, in), design conditions (P, movements,
assembly incl. Lu, required cycles), design temperature, bellows material, forming
condition/method (default as-formed is NOT assumed: use the same default as check mode),
Cwb.

Candidates: every profile whose Db range contains Db × every thickness × plies
1…max_plies × N 1…max_convolutions. Each candidate is evaluated with the existing
`EjmaCalculator::calculate` (no duplicated physics). Candidates that raise
`EjmaInputException` (e.g. outside chart range) are skipped, not fatal.

Physical fit (ruling P3-R4): before evaluation, a (profile, t, n) combination whose ply
pack does not fit the die is skipped for every N: it must satisfy n·t < w AND n·t < 2rm
(strict; a pack exactly equal to w or 2rm is skipped). Skipped combinations are not
evaluated, not counted in `evaluated`, and can never be the closest failing candidate.

If nothing was evaluated and every candidate tried was rejected only on `Lu` (universal
joints: the bellows do not fit within Lu), the search raises an input error on `Lu`
("The distance between the bellows Lu is too short for any standard design.") so the
visitor sees a field error and nothing is stored (ruling P3-R6).

Pruning: for a fixed (profile, t, n), increase N from 1 and stop at the first passing N;
also stop when N exceeds what fits (instability or the Lb − x guard throws).

### 3.3 Ordering ("smallest")

Passing candidates sorted by: (1) total convolutions N × bellows count, (2) total metal
thickness n·t, (3) number of plies n, (4) lowest utilisation (max value/limit over the
'le' stress lines), (5) profile id. The first is proposed; the next two distinct
(profile, t, n) combinations are listed as alternatives.

### 3.4 Result — `DesignResult`

`?DesignCandidate $best` (geometry + CheckResult), `array $alternatives` (≤ 2),
`int $evaluated`, `int $passing`. Verdict: PASS when a design is found; REVIEW
("needs engineering review") when none passes, with a note giving the closest failing
candidate's governing check.

Closest failing candidate (ruling P3-R5): the evaluated, non-passing candidate with the
lowest closeness (`DesignCandidate::closeness()`), the largest ratio over all check lines:
'le' lines give value/limit (only a finite limit > 0 counts), 'ge' lines give limit/value
(an infinite value gives 0, a value ≤ 0 gives ∞), and a line that was not evaluated gives
∞. Ties follow the §3.3 order. Utilisation ('le' lines only) still orders passing
candidates (§3.3 step 4); it is not used for the closest failing candidate, because it
ignores the fatigue line and would favour the N = 1 thickest pack in fatigue-governed cases.

## 4. Web

- Mode switch at the top of the form: **Check my design** / **Design it for me**
  (`mode` = `check` | `design`, default `check` so Phase 1–2 payloads are unchanged).
- Design mode hides the geometry and bellows-type sections and collar/reinforcement
  materials; keeps assembly, conditions, bellows material, forming, contact. Adds `Db`
  (bellows inside diameter) as the only geometry input.
- When design mode is unavailable (no tooling), the switch shows design mode disabled
  with a short "contact us for a custom design" line.
- Result screen unchanged in shape: verdict + reference + email button. For design mode
  the verdict wording is "A suitable bellows design was found" (pass) or "Your
  conditions need a custom design by our engineers" (review). No geometry or values in
  visitor HTML.
- Storage: new nullable columns `mode` (default `check`) and `design` (JSON: chosen
  geometry, alternatives, evaluated/passing counts). `results` stores the best
  candidate's CheckResult (or an empty result with the review note when none passed).
- Rate limit: a design-mode run counts as one calculation (R13).

## 5. Report

- Subject adds "design" for design mode, e.g.
  `EJMA design EJ-2026-0042 — PT Example — 600 mm universal — PASS`.
- PDF: a "Proposed design" section first (profile name, w, q, rm, t, plies, N per
  bellows, Lt, in both unit systems), then alternatives (table), then the full Phase 1–2
  report for the proposed design. When no design passed: the request, the number of
  candidates evaluated, and the closest failing candidate with its governing check.

## 6. Testing

- Unit: search with a fixture tooling table (benchmark-1 profile: w 1.44 in, q 1 in,
  rm 0.25 in, t 0.03 in) finds a passing design; minimality (no earlier-sorted candidate
  passes — verified by brute force over the same small table); empty tooling → no
  design; candidate errors skipped; ordering ties broken as §3.3.
- Feature: design-mode submission stores mode/design, mails a PDF with the proposed
  design, shows only verdict/reference; unavailable when tooling empty; check-mode tests
  unchanged; no values in visitor HTML.
- Performance: a 10-profile × 8-thickness × 5-ply × 30-convolution table completes within
  2 s in the test environment.
