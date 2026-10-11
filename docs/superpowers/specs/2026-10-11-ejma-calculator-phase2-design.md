# EJMA Bellows Calculator — Phase 2 Design (Assemblies)

**Date:** 2026-10-11
**Status:** Approved by the user in advance ("go with all recommendations")
**Builds on:** `resources/ejma-private/docs/2026-10-11-ejma-calculator-phase1-design.md` (merged; private, not in git)
**Reference:** EJMA Standards, 9th Edition (2008)

## 1. Purpose

Extend the gated calculator from single expansion joints to the common metallic
expansion joint **assemblies**, at **joint level**: the visitor describes the assembly
and the movements imposed at its ends; the engine distributes the movement to each
bellows, runs every Phase 1 bellows check, and reports the forces and moments at the
joint ends plus the pressure thrust carried by the restraint hardware. Gating, storage,
email and PDF behaviour are unchanged from Phase 1.

Success criteria:

- EJMA §4.14 benchmark 2 (universal unreinforced) reproduced within the Phase 1
  tolerances.
- Joint-level quantities in the Appendix J examples that fall within scope
  (forces/moments at the joint ends, equivalent movement per convolution, pressure
  thrust) reproduced within tolerance.
- Phase 1 behaviour for single joints unchanged (all Phase 1 tests still pass).

## 2. Scope

**In:**

| Assembly | Movements accepted | Bellows | Hardware load reported |
|---|---|---|---|
| Single (Phase 1) | axial, lateral, angular | 1 | — (main-anchor pressure thrust) |
| Universal (untied) | axial, lateral, angular | 2 identical | — (main-anchor pressure thrust) |
| Tied universal | lateral, plus axial growth between tie-rod plates (angular restrained) | 2 identical | total and per-rod tie-rod load |
| Hinged | angular, one plane | 1 | total and per-pin hinge load |
| Gimbal | angular, two planes (resultant) | 1 | total and per-pin gimbal load |

A **3-hinge system** is calculated as three hinged joints. The visitor enters each
joint's rotation (EJMA §2.8 derives these from the piping geometry; that geometry
calculation is out of scope).

All three bellows types (unreinforced, reinforced, toroidal) are allowed in every
assembly.

**Additional checks (all assemblies, including single):**

- Maximum convolution movement limits (§4.2, p066): ec ≤ q − 2rm − nt and
  ee ≤ 6rm − q (U-shaped bellows only). For this check ee is per eq. 4-9 at the
  compression position, max(ey + eθ − |ex|, eθ/Kθ − |ex|), or the axial extension
  per convolution if larger, because lateral and angular movement extend one side of
  the convolutions even while the joint is compressed. The fatigue range e keeps the
  Phase 1 R4 definition.
- Maximum axial compression from instability (§4.7, p073):
  ex ≤ 1.25·Dm² / (N²·q), with N = total convolutions in all unguided interconnected
  bellows.

**Optional input:** universal center-spool dead weight Wcs and centerline angle θu
(§4.4 eqs. 4-12/4-13), added to the design movements.

**Out:** piping-system analysis (anchor and nozzle reactions from pipe layout,
Appendix J-style system equilibrium, §2.8 rotation from geometry); pressure-balanced
and rectangular joints; hardware sizing (tie rods, hinge plates, pins, gimbal rings);
cold springing (§4.5); rectangular bellows.

## 3. Engine changes (`app/Ejma`, still Laravel-free)

- `AssemblyType` enum: `single`, `universal`, `tied_universal`, `hinged`, `gimbal`.
- `AssemblyGeometry` value object: type; `Lu` (universal: distance between the
  outermost convolution ends); `tieRods` (tied universal, count ≥ 2); `pins` (hinged 2,
  gimbal 4, fixed); optional `Wcs` (lbf) and `thetaU` (rad); `theta2` (gimbal second
  plane, rad).
- `DesignConditions` gains `AssemblyGeometry $assembly` with a single-joint default, so
  the Phase 1 call sites are unchanged.
- **Movement distribution** (`Movement::of`) per assembly:
  - single: Phase 1 equations (Ku = 1.5).
  - universal: ex = x/(2N) (eq. 4-2), eθ = θ·Dm/(4N) (eq. 4-4),
    ey = Ku·Dm·y / (2N(Lu − Lb ± x/2)) (eqs. 4-5/4-6), Ku from Figure 4.1;
    dead-weight movements from eqs. 4-12/4-13 added when Wcs is given.
  - tied universal: as universal, where x is the axial thermal growth of the pipe
    between the tie-rod attachment plates (EJMA p065 note under eq. 4-6; Appendix J
    Example 4). The external axial movement is restrained by the tie rods (anchor
    Fa = 0); the tie-rod load is the pressure thrust plus fw·ex.
  - hinged: angular only (x = y = 0), eθ per eq. 4-3.
  - gimbal: angular only, θ = √(θ1² + θ2²) (vector sum of the two planes, §4.2 note).
  The ±/compression convention, Kθ and Kr follow Phase 1 ruling R4 (lateral and angular
  movement concurrent with compression).
- **Column squirm** for universal: N in Psc is the total in both bellows (notes to
  eqs. 4-35, 4-49, 4-60).
- **Joint loads** (`Loads`):
  - working spring rate fw (§1.3, p022): fw = fi when St ≤ 1.5·Sy, else 0.67·fi, unless
    the visitor enters fw;
  - Fa = fw·ex (4-14), Mθ = fw·Dm·eθ/4 (4-16), Ml = fw·Dm·ey/4 (4-15),
    Vl = fw·Dm·ey / (2(Lb ± x)) single (4-17) or /(2(Lu ± x)) universal (4-18);
  - pressure thrust T = P·π·Dm²/4;
  - hardware: tied universal tie rods carry T + fw·ex (per rod = total / rods); hinged pins carry T
    (per pin = T/2); gimbal T (per pin = T/2 for each ring axis). Untied single and
    universal report T as the main-anchor pressure thrust.
- New check lines: `movement_ec_max`, `movement_ee_max` (U-shaped only), and
  `instability_axial` (eq. 4.7, all types).

## 4. Form, storage, report

- New "Assembly" select at the top of the form (default Single). Assembly-specific
  fields shown or hidden like the bellows-type fields: Lu; number of tie rods; second
  plane rotation (gimbal); optional spool weight and angle (universal). Axial and lateral
  inputs are disabled for hinged and gimbal; for tied universal the axial compression
  field is relabelled "Axial growth between tie-rod plates" and axial extension is
  disabled.
- Validation by assembly type, with field-level errors as in Phase 1 (new public field
  names: `assembly_type`, `Lu`, `tie_rods`, `angular_2`, `spool_weight`,
  `spool_angle`).
- Storage: new nullable column `assembly_type` on `ejma_calculations` (default
  `single` for existing rows); inputs JSON includes the assembly fields.
- Email subject includes the assembly, e.g.
  `EJMA calc EJ-2026-0042 — PT Example — 600 mm universal unreinforced — PASS`.
- PDF: an "Assembly" section (type, per-bellows movements ex/ey/eθ/e, joint forces and
  moments, pressure thrust and per-component hardware load) before the check table; for
  universal the checks are stated to apply to **each** bellows.
- The visitor-facing result screen is unchanged: verdict, reference, email button.

## 5. Testing

- Benchmark 2 (p097–p098): every published result within the Phase 1 tolerances.
- Appendix J (p196–p231): joint-level values (ex, ey, eθ, e, Fa, Ml, Vl, Mθ, pressure
  thrust) from the examples within scope, each cited by example number.
- Unit tests per assembly for distribution, Ku, dead weight, gimbal resultant,
  hardware loads, new check lines.
- Feature tests: each assembly submits, stores `assembly_type`, mails a PDF containing
  the assembly section; hidden-field and validation behaviour; no calculated values in
  visitor HTML.
- All Phase 1 tests keep passing.
