/**
 * Gear geometry — pure, framework-free functions (no DOM, no globals).
 *
 * Involute cylindrical gears per ISO 21771 with the ISO 53 basic rack
 * (ha* = 1.0, hf* = 1.25), straight bevel gears (equal addendum, constant
 * clearance) and cylindrical worm gears per ISO/DIN 3975 (axial module).
 *
 * Conventions:
 *   - lengths in mm, angle inputs and angle outputs in degrees;
 *   - a value that cannot be computed is null, with the reason in `na[key]`;
 *   - impossible input combinations throw GearInputError with a plain-language message;
 *   - `checks` are { id, status: 'ok' | 'warn' | 'error', label, message }.
 *
 * Used by public/js/gear-calculator.js and tests/js/gear-math.test.js.
 */

export class GearInputError extends Error {
    constructor(message, field = null) {
        super(message);
        this.name = 'GearInputError';
        this.field = field;
    }
}

export const ISO_MODULES = [0.5, 0.6, 0.7, 0.8, 0.9, 1, 1.125, 1.25, 1.375, 1.5, 1.75, 2, 2.25, 2.5, 2.75, 3, 3.5, 4, 4.5, 5, 5.5, 6, 7, 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 25, 28, 32, 36, 40, 45, 50];
export const COMMON_DP = [1, 1.25, 1.5, 1.75, 2, 2.5, 3, 4, 5, 6, 7, 8, 10, 12, 14, 16, 18, 20, 24, 32];
export const PRESSURE_ANGLES = [14.5, 20, 25];

const PI = Math.PI;
export const rad = (deg) => deg * PI / 180;
export const deg = (r) => r * 180 / PI;

/** Involute function inv(α) = tan α − α, α in radians. */
export function inv(alpha) {
    return Math.tan(alpha) - alpha;
}

/**
 * Inverse involute: the α (radians, 0 < α < π/2) with tan α − α = v.
 * Newton–Raphson on f(α) = tan α − α − v, f′ = tan²α. Since f is convex and
 * increasing, starting right of the root converges monotonically; the start
 * α0 = ∛(3v) (from inv α ≈ α³/3) is always right of the root.
 */
export function invInverse(v, tol = 1e-12) {
    if (!(v >= 0) || !Number.isFinite(v)) {
        throw new GearInputError('Involute value must be a finite number ≥ 0.');
    }
    if (v === 0) return 0;
    let a = Math.min(Math.cbrt(3 * v), 1.5);
    for (let i = 0; i < 100; i++) {
        const t = Math.tan(a);
        const step = (t - a - v) / (t * t);
        a -= step;
        if (Math.abs(step) < tol) return a;
    }
    return a;
}

export const moduleFromDP = (dp) => 25.4 / dp;

/** Nearest ISO 54 module to m, with deviation of m from it in %. */
export function nearestModule(m, list = ISO_MODULES) {
    let best = list[0];
    for (const v of list) if (Math.abs(v - m) < Math.abs(best - m)) best = v;
    return { module: best, deviationPct: (m - best) / best * 100 };
}

/** Nearest common diametral pitch to module m, with its metric module 25.4/DP. */
export function nearestDP(m, list = COMMON_DP) {
    let best = list[0];
    for (const dp of list) if (Math.abs(moduleFromDP(dp) - m) < Math.abs(moduleFromDP(best) - m)) best = dp;
    const mm = moduleFromDP(best);
    return { dp: best, module: mm, deviationPct: (m - mm) / mm * 100 };
}

const isInt = (n) => Number.isInteger(n);

function requireTeeth(z, field, min = 6) {
    if (!isInt(z) || z < min) throw new GearInputError(`Teeth must be a whole number ≥ ${min}.`, field);
}
function requirePositive(v, field, label) {
    if (!(v > 0) || !Number.isFinite(v)) throw new GearInputError(`${label} must be greater than 0.`, field);
}
function requireHelix(beta, field = 'beta') {
    if (!(beta >= 0 && beta < 45)) throw new GearInputError('Helix angle must be between 0° and 45° (0 for spur).', field);
}
function requirePressureAngle(an, field = 'an') {
    if (!PRESSURE_ANGLES.includes(an)) throw new GearInputError('Pressure angle must be 14.5°, 20° or 25°.', field);
}
function requireFinite(v, field, label) {
    if (!Number.isFinite(v)) throw new GearInputError(`${label} must be a number.`, field);
}

/* ------------------------------------------------------------------ */
/* 3.1 Spur and helical gear                                           */
/* ------------------------------------------------------------------ */

/** Span measurement margin on the face width of a helical gear (mm). */
export const SPAN_FACE_MARGIN = 5;

/**
 * Single spur or helical gear (spur: beta = 0).
 * tipReduction is Δy of a mating pair (da is reduced by 2·mn·Δy to keep clearance).
 */
export function spurHelical({ z, mn, an = 20, beta = 0, x = 0, b = null, hand = 'RH', tipReduction = 0 }) {
    requireTeeth(z, 'z');
    requirePositive(mn, 'mn', 'Module');
    requirePressureAngle(an);
    requireHelix(beta);
    requireFinite(x, 'x', 'Profile shift');
    if (b !== null) requirePositive(b, 'b', 'Face width');

    const na = {};
    const checks = [];
    const alphaN = rad(an);
    const B = rad(beta);
    const helical = beta > 0;

    const mt = mn / Math.cos(B);
    const alphaT = Math.atan(Math.tan(alphaN) / Math.cos(B));
    const d = z * mt;
    const db = d * Math.cos(alphaT);
    const ha = mn * (1 + x - tipReduction);
    const hf = mn * (1.25 - x);
    const da = d + 2 * ha;
    const df = d - 2 * hf;
    const h = (da - df) / 2;
    const pn = PI * mn;
    const pt = PI * mt;
    const pb = PI * mt * Math.cos(alphaT);
    const sn = mn * (PI / 2 + 2 * x * Math.tan(alphaN));
    const st = sn / Math.cos(B);
    const betaB = Math.atan(Math.tan(B) * Math.cos(alphaT));
    const pz = helical ? PI * d / Math.tan(B) : null;
    if (!helical) na.pz = 'Spur gear: no lead.';
    const zn = z / (Math.cos(betaB) ** 2 * Math.cos(B));
    const invAt = inv(alphaT);

    if (df <= 0) throw new GearInputError('Root diameter would be ≤ 0: increase the teeth or the profile shift.', 'x');

    // Span measurement (base tangent length) over k teeth.
    let k = null, kTh = null, Wk = null, alphaM = null;
    const cosM = db / (d + 2 * x * mn);
    if (cosM > 0 && cosM <= 1) {
        alphaM = Math.acos(cosM);
        kTh = (z / PI) * (Math.tan(alphaM) / Math.cos(betaB) ** 2 - invAt - (2 * x / z) * Math.tan(alphaN)) + 0.5;
        k = Math.max(2, Math.round(kTh));
        if (k >= z) {
            na.Wk = 'Span would cover all teeth.';
            k = null;
        } else {
            Wk = spanLength({ z, mn, an, beta, x, k });
        }
    } else {
        na.Wk = na.k = 'Profile shift places the measuring circle inside the base circle.';
    }

    // Span measurable on a helical gear only if the face is wide enough.
    let wkMinFace = null;
    if (helical && Wk !== null) {
        wkMinFace = Wk * Math.sin(betaB) + SPAN_FACE_MARGIN;
        if (b !== null) {
            checks.push(b > wkMinFace
                ? { id: 'span', status: 'ok', label: 'Span measurable', message: `Face width ${fmt(b)} mm > Wk·sin βb + ${SPAN_FACE_MARGIN} mm = ${fmt(wkMinFace)} mm.` }
                : { id: 'span', status: 'warn', label: 'Span not measurable', message: `Face width ${fmt(b)} mm is too narrow for a span over ${k} teeth (needs > ${fmt(wkMinFace)} mm). Measure over balls/pins instead.` });
        }
    }

    // Tip tooth thickness.
    let alphaAt = null, sat = null, betaA = null, san = null;
    if (da > db) {
        alphaAt = Math.acos(db / da);
        sat = da * (st / d + invAt - inv(alphaAt));
        betaA = Math.atan(Math.tan(B) * da / d);
        san = sat * Math.cos(betaA);
        if (san <= 0) {
            checks.push({ id: 'tip', status: 'error', label: 'Pointed tooth', message: `Tip thickness is ${fmt(san)} mm: the flanks meet below the tip circle. Reduce the profile shift.` });
        } else if (san < 0.25 * mn) {
            checks.push({ id: 'tip', status: 'warn', label: 'Thin tooth tip', message: `Tip thickness ${fmt(san)} mm is below 0.25·mn = ${fmt(0.25 * mn)} mm.` });
        } else {
            checks.push({ id: 'tip', status: 'ok', label: 'Tip thickness', message: `${fmt(san)} mm ≥ 0.25·mn = ${fmt(0.25 * mn)} mm.` });
        }
    } else {
        na.sat = na.san = 'Tip circle is inside the base circle.';
    }

    // Undercut.
    const xMin = 1 - (zn * Math.sin(alphaN) ** 2) / 2;
    checks.push(x < xMin - 1e-9
        ? { id: 'undercut', status: 'warn', label: 'Undercut', message: `x = ${fmt(x, 4)} is below x_min = ${fmt(xMin, 4)}: the generating tool cuts into the flank root.` }
        : { id: 'undercut', status: 'ok', label: 'No undercut', message: `x = ${fmt(x, 4)} ≥ x_min = ${fmt(xMin, 4)}.` });

    return {
        type: helical ? 'helical' : 'spur',
        z, mn, an, beta, x, b, hand: helical ? hand : null, tipReduction,
        mt, alphaTDeg: deg(alphaT), d, db, da, df, h, ha, hf, pn, pt, pb, sn, st,
        betaBDeg: deg(betaB), pz, zn, invAlphaT: invAt,
        alphaMDeg: alphaM === null ? null : deg(alphaM), kTh, k, Wk, wkMinFace,
        alphaAtDeg: alphaAt === null ? null : deg(alphaAt), sat, betaADeg: betaA === null ? null : deg(betaA), san,
        xMin,
        checks, na,
    };
}

/** Base tangent length Wk over k teeth (normal plane). */
export function spanLength({ z, mn, an = 20, beta = 0, x = 0, k }) {
    const alphaN = rad(an);
    const alphaT = Math.atan(Math.tan(alphaN) / Math.cos(rad(beta)));
    return mn * Math.cos(alphaN) * (PI * (k - 0.5) + z * inv(alphaT)) + 2 * x * mn * Math.sin(alphaN);
}

/**
 * Identify a spur/helical gear from a sample: z, tip diameter da, optional span Wk over k teeth.
 * snap: null/'auto' = closest of ISO module or DP, or { module } to force a value.
 */
export function identifySpurHelical({ z, da, Wk = null, k = null, an = 20, beta = 0, b = null, hand = 'RH', snap = null }) {
    requireTeeth(z, 'z');
    requirePositive(da, 'da', 'Tip diameter');
    requirePressureAngle(an);
    requireHelix(beta);
    const alphaN = rad(an);
    const cosB = Math.cos(rad(beta));
    const alphaT = Math.atan(Math.tan(alphaN) / cosB);
    const sinA = Math.sin(alphaN);

    let mnEst, xEst, A = null, method;
    if (Wk !== null) {
        requirePositive(Wk, 'Wk', 'Span measurement');
        if (!isInt(k) || k < 2 || k >= z) throw new GearInputError('Teeth spanned k must be a whole number from 2 to z − 1.', 'k');
        A = Math.cos(alphaN) * (PI * (k - 0.5) + z * inv(alphaT));
        const den = A - sinA * (z / cosB + 2);
        if (Math.abs(den) < 1e-9) throw new GearInputError('These z and k give no solution: choose another number of teeth spanned.', 'k');
        mnEst = (Wk - da * sinA) / den;
        if (!(mnEst > 0)) throw new GearInputError('Tip diameter and span do not fit one gear: check both measurements and k.', 'Wk');
        xEst = (da / mnEst - z / cosB - 2) / 2;
        method = 'da+Wk';
    } else {
        mnEst = da / (z / cosB + 2);
        xEst = 0;
        method = 'da';
    }

    const nm = nearestModule(mnEst);
    const ndp = nearestDP(mnEst);
    let mn, snapSource;
    if (snap && typeof snap === 'object' && snap.module > 0) {
        mn = snap.module;
        snapSource = snap.source || 'user';
    } else if (Math.abs(ndp.deviationPct) < Math.abs(nm.deviationPct)) {
        mn = ndp.module;
        snapSource = 'dp';
    } else {
        mn = nm.module;
        snapSource = 'module';
    }

    // Recompute x for the snapped module: span is the more reliable input.
    const x = A !== null
        ? (Wk / mn - A) / (2 * sinA)
        : (da / mn - z / cosB - 2) / 2;

    const gear = spurHelical({ z, mn, an, beta, x, b, hand });
    // Keep the measured tip diameter: it may be reduced by chamfer, shortening or wear.
    return { method, mnEst, xEst, nearestModule: nm, nearestDP: ndp, mn, snapSource, x, daMeasured: da, daTheory: gear.da, gear };
}

/* ------------------------------------------------------------------ */
/* 3.2 Gear pair                                                       */
/* ------------------------------------------------------------------ */

function pairBase({ z1, z2, mn, an, beta }) {
    requireTeeth(z1, 'z1');
    requireTeeth(z2, 'z2');
    if (z2 < z1) throw new GearInputError('Wheel teeth z2 must be ≥ pinion teeth z1.', 'z2');
    requirePositive(mn, 'mn', 'Module');
    requirePressureAngle(an);
    requireHelix(beta);
    const alphaN = rad(an);
    const B = rad(beta);
    const alphaT = Math.atan(Math.tan(alphaN) / Math.cos(B));
    const a0 = (z1 + z2) * mn / (2 * Math.cos(B));
    return { alphaN, B, alphaT, a0 };
}

/** Σx for a given centre distance a. */
export function sumShiftFromCentre({ z1, z2, mn, an = 20, beta = 0, a }) {
    const { alphaN, alphaT, a0 } = pairBase({ z1, z2, mn, an, beta });
    requirePositive(a, 'a', 'Centre distance');
    const c = a0 * Math.cos(alphaT) / a;
    if (c >= 1) throw new GearInputError(`Centre distance must be greater than ${fmt(a0 * Math.cos(alphaT))} mm for these gears.`, 'a');
    const alphaWt = Math.acos(c);
    return (inv(alphaWt) - inv(alphaT)) * (z1 + z2) / (2 * Math.tan(alphaN));
}

/**
 * Balanced split of Σx giving more shift to the pinion (DIN 3992 guide line,
 * Niemann's approximation): x1 = Σx/2 + (0.5 − Σx/2)·lg u / lg(zn1·zn2/100).
 * For very small gear sets (zn1·zn2 ≤ 150) the log term is unstable, so
 * x1 = Σx/2 + 0.25·(u − 1)/(u + 1) is used instead.
 */
export function suggestShiftSplit({ sumX, z1, z2, beta = 0 }) {
    const c3 = Math.cos(rad(beta)) ** 3;
    const zn1 = z1 / c3, zn2 = z2 / c3;
    const u = z2 / z1;
    const x1 = zn1 * zn2 > 150
        ? sumX / 2 + (0.5 - sumX / 2) * Math.log10(u) / Math.log10(zn1 * zn2 / 100)
        : sumX / 2 + 0.25 * (u - 1) / (u + 1);
    return { x1, x2: sumX - x1 };
}

/** External spur/helical gear pair from profile shifts x1, x2. */
export function gearPair({ z1, z2, mn, an = 20, beta = 0, x1 = 0, x2 = 0, b = null, hand = 'RH' }) {
    const { alphaN, B, alphaT, a0 } = pairBase({ z1, z2, mn, an, beta });
    requireFinite(x1, 'x1', 'Profile shift x1');
    requireFinite(x2, 'x2', 'Profile shift x2');
    if (b !== null) requirePositive(b, 'b', 'Face width');

    const sumX = x1 + x2;
    const invAwt = 2 * Math.tan(alphaN) * sumX / (z1 + z2) + inv(alphaT);
    if (invAwt <= 0) throw new GearInputError('Σx is too negative: no operating pressure angle exists.', 'x2');
    const alphaWt = invInverse(invAwt);
    const a = a0 * Math.cos(alphaT) / Math.cos(alphaWt);
    return meshFrom({ z1, z2, mn, an, beta, x1, x2, b, hand, alphaN, B, alphaT, a0, a, alphaWt });
}

/**
 * Gear pair from a known centre distance: solves Σx, splits it (x1 suggested
 * unless given), then builds the mesh.
 */
export function gearPairFromCentre({ z1, z2, mn, an = 20, beta = 0, a, x1 = null, b = null, hand = 'RH' }) {
    const sumX = sumShiftFromCentre({ z1, z2, mn, an, beta, a });
    const suggested = suggestShiftSplit({ sumX, z1, z2, beta });
    const useX1 = x1 === null ? suggested.x1 : x1;
    if (x1 !== null) requireFinite(x1, 'x1', 'Profile shift x1');
    const pair = gearPair({ z1, z2, mn, an, beta, x1: useX1, x2: sumX - useX1, b, hand });
    return { ...pair, sumX, suggestedX1: suggested.x1, x1Suggested: x1 === null };
}

function meshFrom({ z1, z2, mn, an, beta, x1, x2, b, hand, alphaN, B, alphaT, a0, a, alphaWt }) {
    const sumX = x1 + x2;
    const u = z2 / z1;
    const dw1 = 2 * a * z1 / (z1 + z2);
    const dw2 = 2 * a * z2 / (z1 + z2);
    const y = (a - a0) / mn;
    const dy = sumX - y;
    const mt = mn / Math.cos(B);
    const otherHand = hand === 'RH' ? 'LH' : 'RH';

    const g1 = spurHelical({ z: z1, mn, an, beta, x: x1, b, hand, tipReduction: dy });
    const g2 = spurHelical({ z: z2, mn, an, beta, x: x2, b, hand: otherHand, tipReduction: dy });

    const na = {};
    const checks = [];
    let epsAlpha = null;
    const ra1 = g1.da / 2, rb1 = g1.db / 2, ra2 = g2.da / 2, rb2 = g2.db / 2;
    if (ra1 > rb1 && ra2 > rb2) {
        epsAlpha = (Math.sqrt(ra1 ** 2 - rb1 ** 2) + Math.sqrt(ra2 ** 2 - rb2 ** 2) - a * Math.sin(alphaWt)) / (PI * mt * Math.cos(alphaT));
    } else {
        na.epsAlpha = 'A tip circle is inside its base circle.';
    }
    const epsBeta = beta > 0 && b !== null ? b * Math.sin(B) / (PI * mn) : (beta > 0 ? null : 0);
    if (epsBeta === null) na.epsBeta = na.epsGamma = 'Enter a face width to compute the overlap ratio.';
    const epsGamma = epsAlpha !== null && epsBeta !== null ? epsAlpha + epsBeta : null;

    if (epsAlpha !== null) {
        checks.push(epsAlpha < 1.2
            ? { id: 'eps', status: 'error', label: 'Contact ratio too low', message: `εα = ${fmt(epsAlpha, 3)} < 1.2: the mesh will run rough or lose contact.` }
            : epsAlpha < 1.4
                ? { id: 'eps', status: 'warn', label: 'Low contact ratio', message: `εα = ${fmt(epsAlpha, 3)} is below 1.4.` }
                : { id: 'eps', status: 'ok', label: 'Contact ratio', message: `εα = ${fmt(epsAlpha, 3)} ≥ 1.4.` });
    }
    const tag = (c, n) => ({ ...c, id: `${c.id}${n}`, label: `Gear ${n}: ${c.label}` });
    g1.checks.forEach((c) => checks.push(tag(c, 1)));
    g2.checks.forEach((c) => checks.push(tag(c, 2)));
    if (beta > 0) {
        checks.push({ id: 'hand', status: 'ok', label: 'Opposite hand', message: `Gear 1 ${hand} → Gear 2 ${otherHand}.` });
    }

    return {
        type: 'pair', z1, z2, mn, an, beta, b, x1, x2, sumX, hand, hand2: beta > 0 ? otherHand : null,
        u, a0, a, alphaTDeg: deg(alphaT), alphaWtDeg: deg(alphaWt), invAlphaWt: inv(alphaWt),
        dw1, dw2, y, dy, epsAlpha, epsBeta, epsGamma,
        gear1: g1, gear2: g2, checks, na,
    };
}

/* ------------------------------------------------------------------ */
/* 3.3 Straight bevel gear pair                                        */
/* ------------------------------------------------------------------ */

function pitchAngles(z1, z2, sigma) {
    const S = rad(sigma);
    const d1 = Math.atan(Math.sin(S) / (z2 / z1 + Math.cos(S)));
    return { d1, d2: S - d1 };
}

/** Straight bevel gear pair, equal addendum, constant clearance; outer (heel) dimensions. */
export function bevelPair({ m, z1, z2, sigma = 90, alpha = 20, b = null }) {
    requirePositive(m, 'm', 'Module');
    requireTeeth(z1, 'z1');
    requireTeeth(z2, 'z2');
    if (z2 < z1) throw new GearInputError('Wheel teeth z2 must be ≥ pinion teeth z1.', 'z2');
    if (!(sigma > 0 && sigma < 180)) throw new GearInputError('Shaft angle must be between 0° and 180°.', 'sigma');
    requirePressureAngle(alpha, 'alpha');
    if (b !== null) requirePositive(b, 'b', 'Face width');

    const { d1: delta1, d2: delta2 } = pitchAngles(z1, z2, sigma);
    const ha = m, hf = 1.25 * m;
    const d1 = z1 * m, d2 = z2 * m;
    const Re = d1 / (2 * Math.sin(delta1));
    const thetaF = Math.atan(hf / Re);
    const thetaA = thetaF; // constant clearance: tip angle of one = root angle of the mate (equal hf)

    const gear = (z, d, delta) => {
        const da = d + 2 * ha * Math.cos(delta);
        const zv = z / Math.cos(delta);
        return {
            z, d, deltaDeg: deg(delta), deltaADeg: deg(delta + thetaA), deltaFDeg: deg(delta - thetaF),
            da, crownToApex: Re * Math.cos(delta) - ha * Math.sin(delta), zv,
            xMin: 1 - zv * Math.sin(rad(alpha)) ** 2 / 2,
        };
    };
    const g1 = gear(z1, d1, delta1);
    const g2 = gear(z2, d2, delta2);

    const checks = [];
    const na = {};
    if (b !== null) {
        const bMax = Math.min(Re / 3, 10 * m);
        checks.push(b <= bMax + 1e-9
            ? { id: 'face', status: 'ok', label: 'Face width', message: `b = ${fmt(b)} mm ≤ min(Re/3, 10·m) = ${fmt(bMax)} mm.` }
            : { id: 'face', status: 'warn', label: 'Face width too large', message: `b = ${fmt(b)} mm exceeds min(Re/3, 10·m) = ${fmt(bMax)} mm: the toe teeth become too small.` });
    } else {
        na.b = 'Enter a face width to check it.';
    }
    [g1, g2].forEach((g, i) => {
        checks.push(g.xMin > 1e-9
            ? { id: `undercut${i + 1}`, status: 'warn', label: `Gear ${i + 1}: undercut`, message: `zv = ${fmt(g.zv, 2)} is too few teeth for x = 0 (x_min = ${fmt(g.xMin, 4)}). Gleason unequal addenda avoid this.` }
            : { id: `undercut${i + 1}`, status: 'ok', label: `Gear ${i + 1}: no undercut`, message: `zv = ${fmt(g.zv, 2)}.` });
    });

    return {
        type: 'bevel', m, z1, z2, sigma, alpha, b, u: z2 / z1, ha, hf, Re,
        thetaFDeg: deg(thetaF), thetaADeg: deg(thetaA), Rm: b === null ? null : Re - b / 2,
        gear1: g1, gear2: g2, checks, na,
    };
}

/** Identify a straight bevel pair from the pinion's measured tip diameter. */
export function identifyBevel({ da1, z1, z2, sigma = 90, alpha = 20, b = null, snap = null }) {
    requirePositive(da1, 'da1', 'Tip diameter');
    requireTeeth(z1, 'z1');
    requireTeeth(z2, 'z2');
    if (!(sigma > 0 && sigma < 180)) throw new GearInputError('Shaft angle must be between 0° and 180°.', 'sigma');
    const { d1: delta1 } = pitchAngles(z1, z2, sigma);
    const mEst = da1 / (z1 + 2 * Math.cos(delta1));
    const nm = nearestModule(mEst);
    const ndp = nearestDP(mEst);
    let m, snapSource;
    if (snap && typeof snap === 'object' && snap.module > 0) {
        m = snap.module;
        snapSource = snap.source || 'user';
    } else if (Math.abs(ndp.deviationPct) < Math.abs(nm.deviationPct)) {
        m = ndp.module;
        snapSource = 'dp';
    } else {
        m = nm.module;
        snapSource = 'module';
    }
    return { mEst, nearestModule: nm, nearestDP: ndp, m, snapSource, daMeasured: da1, pair: bevelPair({ m, z1, z2, sigma, alpha, b }) };
}

/* ------------------------------------------------------------------ */
/* 3.4 Worm and wheel                                                  */
/* ------------------------------------------------------------------ */

/** Cylindrical worm and wheel, axial module m. Give q or d1. */
export function wormPair({ m, z1, z2, q = null, d1 = null, an = 20, x2 = 0, mu = 0.05 }) {
    requirePositive(m, 'm', 'Module');
    if (!isInt(z1) || z1 < 1 || z1 > 10) throw new GearInputError('Worm starts must be a whole number from 1 to 10.', 'z1');
    requireTeeth(z2, 'z2', 10);
    requirePressureAngle(an);
    requireFinite(x2, 'x2', 'Profile shift x2');
    if (!(mu >= 0 && mu < 1)) throw new GearInputError('Friction coefficient must be between 0 and 1.', 'mu');
    if (d1 !== null) {
        requirePositive(d1, 'd1', 'Worm reference diameter');
        q = d1 / m;
    } else {
        requirePositive(q, 'q', 'Diameter factor');
        d1 = q * m;
    }

    const d2 = z2 * m;
    const i = z2 / z1;
    const gamma = Math.atan(z1 / q);
    const px = PI * m;
    const pz = PI * m * z1;
    const a = (d1 + d2) / 2 + x2 * m;
    const da1 = d1 + 2 * m;
    const df1 = d1 - 2.4 * m;
    const da2 = d2 + 2 * m * (1 + x2);
    const df2 = d2 - 2 * m * (1.2 - x2);
    const de2 = da2 + (z1 === 1 ? 2 : z1 <= 3 ? 1.5 : 1) * m;
    const rhoP = Math.atan(mu / Math.cos(rad(an)));
    const eta = Math.tan(gamma) / Math.tan(gamma + rhoP);
    const selfLocking = gamma < rhoP;

    if (df1 <= 0) throw new GearInputError('Worm root diameter would be ≤ 0: increase q or d1.', 'q');

    const checks = [];
    checks.push(Math.abs(x2) > 0.5
        ? { id: 'x2', status: 'warn', label: 'Large wheel shift', message: `|x2| = ${fmt(Math.abs(x2), 4)} > 0.5: tooth form of the wheel suffers. Change q or the module.` }
        : { id: 'x2', status: 'ok', label: 'Wheel shift', message: `|x2| = ${fmt(Math.abs(x2), 4)} ≤ 0.5.` });
    checks.push(selfLocking
        ? { id: 'lock', status: 'warn', label: 'Likely self-locking', message: `γ = ${fmt(deg(gamma), 3)}° < ρ′ = ${fmt(deg(rhoP), 3)}°. Not a safety device: vibration can still back-drive it.` }
        : { id: 'lock', status: 'ok', label: 'Not self-locking', message: `γ = ${fmt(deg(gamma), 3)}° ≥ ρ′ = ${fmt(deg(rhoP), 3)}°: the wheel can back-drive the worm.` });

    return {
        type: 'worm', m, z1, z2, q, d1, d2, an, x2, mu, i,
        gammaDeg: deg(gamma), px, pz, a, da1, df1, da2, df2, de2,
        rhoPDeg: deg(rhoP), eta, selfLocking, checks, na: {},
    };
}

/** Wheel profile shift needed for a given centre distance. */
export function wormShiftFromCentre({ m, z2, q = null, d1 = null, a }) {
    requirePositive(m, 'm', 'Module');
    requirePositive(a, 'a', 'Centre distance');
    const dd1 = d1 !== null ? d1 : q * m;
    return (a - (dd1 + z2 * m) / 2) / m;
}

/* ------------------------------------------------------------------ */
/* Drawing geometry                                                    */
/* ------------------------------------------------------------------ */

/**
 * Transverse outline of an involute gear as [x, y] points (centre at origin,
 * tooth 0 centred on `rotation` radians). Simplified root: radial line from
 * the base circle to the root circle when rf < rb, no trochoidal fillet.
 * Tooth i lies at rotation + i·2π/z. Pointed tips are cut where the flanks meet.
 */
export function gearOutline({ z, d, db, da, df, st, alphaTDeg, rotation = 0, samples = 12, teeth = null }) {
    const rRef = d / 2, rb = db / 2, ra = da / 2, rf = df / 2;
    const invAt = inv(rad(alphaTDeg));
    const psi = (r) => st / (2 * rRef) + invAt - inv(Math.acos(Math.min(1, rb / r)));
    const r0 = Math.max(rb, rf);

    // Flank radii from r0 up to the tip, cut where the half-angle reaches 0 (pointed tooth).
    let rTop = ra;
    if (psi(ra) < 0) {
        let lo = r0, hi = ra;
        for (let i = 0; i < 60; i++) { const mid = (lo + hi) / 2; if (psi(mid) > 0) lo = mid; else hi = mid; }
        rTop = lo;
    }
    const radii = [];
    for (let i = 0; i <= samples; i++) radii.push(r0 + (rTop - r0) * i / samples);

    const pitch = 2 * PI / z;
    const halfRoot = psi(r0); // half-angle of the tooth at its foot
    const pts = [];
    const pol = (r, t) => [r * Math.cos(t), r * Math.sin(t)];
    const list = teeth === null ? [...Array(z).keys()] : teeth;
    for (const i of list) {
        const c = rotation + i * pitch;
        // root arc from the middle of the previous space to this tooth's foot
        const arcFrom = c - pitch / 2, arcTo = c - halfRoot;
        for (let j = 0; j <= 4; j++) pts.push(pol(rf, arcFrom + (arcTo - arcFrom) * j / 4));
        if (rf < rb) pts.push(pol(rb, c - halfRoot));
        for (const r of radii) pts.push(pol(r, c - psi(r)));
        const tipHalf = Math.max(psi(rTop), 0);
        for (let j = 1; j < 4; j++) pts.push(pol(rTop, c - tipHalf + 2 * tipHalf * j / 4));
        for (let k = radii.length - 1; k >= 0; k--) pts.push(pol(radii[k], c + psi(radii[k])));
        if (rf < rb) pts.push(pol(rf, c + halfRoot));
        const arc2From = c + halfRoot, arc2To = c + pitch / 2;
        for (let j = 0; j <= 4; j++) pts.push(pol(rf, arc2From + (arc2To - arc2From) * j / 4));
    }
    return pts;
}

/* ------------------------------------------------------------------ */

/** Fixed-decimal format for messages; '—' for non-finite. */
export function fmt(v, decimals = 3) {
    return Number.isFinite(v) ? v.toFixed(decimals) : '—';
}
