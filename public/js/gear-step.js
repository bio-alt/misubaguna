// STEP (ISO 10303-21, AP214) solid models of spur and helical gears.
// Each gear is one closed B-rep solid: two planar end faces and one B-spline
// face per profile segment (root land, radial foot, involute flank, tip land).
// Helical faces follow the transverse profile twisted along the helix, so the
// file opens as a real solid in SolidWorks, Fusion, Inventor, FreeCAD and CAM.
// Units are millimetres; the gear axis is Z, the end faces at z = 0 and z = b.
import { inv, rad } from './gear-math.js';

const FLANK_SAMPLES = 14; // involute points per flank (interpolated by a cubic B-spline)
const ARC_SAMPLES = 6;
const TWIST_STEP = 0.1; // rad of helix twist per interpolated section

/* ------------------------------------------------------------------ */
/* B-spline interpolation                                              */
/* ------------------------------------------------------------------ */

/** Values of all basis functions N(i, p) at t (NURBS Book A2.2). */
function basis(knots, p, n, t) {
    const N = new Array(n + 1).fill(0);
    if (t >= knots[n + 1]) { N[n] = 1; return N; }
    let s = p;
    while (s < n && t >= knots[s + 1]) s++;
    const left = [], right = [], Nl = [1];
    for (let j = 1; j <= p; j++) {
        left[j] = t - knots[s + 1 - j];
        right[j] = knots[s + j] - t;
        let saved = 0;
        for (let r = 0; r < j; r++) {
            const tmp = Nl[r] / (right[r + 1] + left[j - r]);
            Nl[r] = saved + right[r + 1] * tmp;
            saved = left[j - r] * tmp;
        }
        Nl[j] = saved;
    }
    for (let j = 0; j <= p; j++) N[s - p + j] = Nl[j];
    return N;
}

function solve(A, B) {
    const n = A.length, dim = B[0].length;
    A = A.map((r) => r.slice());
    B = B.map((r) => r.slice());
    for (let c = 0; c < n; c++) {
        let piv = c;
        for (let r = c + 1; r < n; r++) if (Math.abs(A[r][c]) > Math.abs(A[piv][c])) piv = r;
        [A[c], A[piv]] = [A[piv], A[c]];
        [B[c], B[piv]] = [B[piv], B[c]];
        for (let r = c + 1; r < n; r++) {
            const f = A[r][c] / A[c][c];
            if (!f) continue;
            for (let k = c; k < n; k++) A[r][k] -= f * A[c][k];
            for (let k = 0; k < dim; k++) B[r][k] -= f * B[c][k];
        }
    }
    const X = Array.from({ length: n }, () => new Array(dim).fill(0));
    for (let r = n - 1; r >= 0; r--) {
        for (let k = 0; k < dim; k++) {
            let s = B[r][k];
            for (let c = r + 1; c < n; c++) s -= A[r][c] * X[c][k];
            X[r][k] = s / A[r][r];
        }
    }
    return X;
}

/** Clamped knot vector by averaging (NURBS Book eq. 9.8) for parameters t. */
function knotsFor(t, p) {
    const n = t.length - 1;
    const U = new Array(p + 1).fill(0);
    for (let j = 1; j <= n - p; j++) {
        let s = 0;
        for (let i = j; i < j + p; i++) s += t[i];
        U.push(s / p);
    }
    for (let i = 0; i <= p; i++) U.push(1);
    return U;
}

/** Interpolation setup for given parameters: degree, knots and the solver. */
function interpolator(t) {
    const n = t.length - 1;
    const p = Math.min(3, n);
    const U = knotsFor(t, p);
    const A = t.map((tk) => basis(U, p, n, tk));
    return { p, U, fit: (pts) => solve(A, pts) };
}

function chordParams(pts) {
    const d = [0];
    for (let i = 1; i < pts.length; i++) d.push(d[i - 1] + Math.hypot(pts[i][0] - pts[i - 1][0], pts[i][1] - pts[i - 1][1]));
    const L = d[d.length - 1];
    return d.map((v, i) => (i === d.length - 1 ? 1 : v / L));
}

/** Point on a B-spline curve {p, U, ctrl}. */
export function curvePoint({ p, U, ctrl }, t) {
    const N = basis(U, p, ctrl.length - 1, t);
    return ctrl[0].map((_, k) => ctrl.reduce((s, c, i) => s + N[i] * c[k], 0));
}

/* ------------------------------------------------------------------ */
/* Transverse profile as segments                                      */
/* ------------------------------------------------------------------ */

/**
 * Closed transverse profile of a spur/helical gear, counter-clockwise, as a
 * list of point runs: per tooth the radial foot (when the root is inside the
 * base circle), involute flank, tip land, flank, foot, then the root land to
 * the next tooth. Same geometry as gearOutline(): simplified root, no fillet.
 * Consecutive runs share their end point (same array).
 */
export function profileSegments(g, rotation = Math.PI / 2) {
    const { z } = g;
    const rRef = g.d / 2, rb = g.db / 2, ra = g.da / 2, rf = g.df / 2;
    const invAt = inv(rad(g.alphaTDeg));
    const psi = (r) => g.st / (2 * rRef) + invAt - inv(Math.acos(Math.min(1, rb / r)));
    const r0 = Math.max(rb, rf);
    let rTop = ra;
    if (psi(ra) < 0) {
        let lo = r0, hi = ra;
        for (let i = 0; i < 60; i++) { const mid = (lo + hi) / 2; if (psi(mid) > 0) lo = mid; else hi = mid; }
        rTop = lo;
    }
    // involute samples spaced by roll angle, denser near the base circle
    const roll = (r) => Math.sqrt(Math.max(0, (r / rb) ** 2 - 1));
    const t0 = roll(r0), t1 = roll(rTop);
    const radii = [];
    for (let k = 0; k <= FLANK_SAMPLES; k++) {
        const s = k / FLANK_SAMPLES;
        radii.push(k === 0 ? r0 : k === FLANK_SAMPLES ? rTop : rb * Math.sqrt(1 + (t0 + (t1 - t0) * s ** 1.5) ** 2));
    }
    const pitch = 2 * Math.PI / z;
    const halfRoot = psi(r0);
    const tipHalf = Math.max(psi(rTop), 0);
    const pol = (r, a) => [r * Math.cos(a), r * Math.sin(a)];
    const arc = (r, a0, a1) => Array.from({ length: ARC_SAMPLES }, (_, j) => pol(r, a0 + (a1 - a0) * j / (ARC_SAMPLES - 1)));

    const segs = [];
    for (let i = 0; i < z; i++) {
        const c = rotation + i * pitch;
        if (rf < rb) segs.push({ kind: 'foot', pts: [pol(rf, c - halfRoot), pol(rb, c - halfRoot)] });
        segs.push({ kind: 'flank', pts: radii.map((r) => pol(r, c - psi(r))) });
        if (2 * rTop * tipHalf > 1e-4) segs.push({ kind: 'tip', pts: arc(rTop, c - tipHalf, c + tipHalf) });
        segs.push({ kind: 'flank', pts: radii.slice().reverse().map((r) => pol(r, c + psi(r))) });
        if (rf < rb) segs.push({ kind: 'foot', pts: [pol(rb, c + halfRoot), pol(rf, c + halfRoot)] });
        if (pitch - 2 * halfRoot > 1e-9) segs.push({ kind: 'root', pts: arc(rf, c + halfRoot, c + pitch - halfRoot) });
    }
    for (let k = 0; k < segs.length; k++) segs[k].pts[0] = segs[(k + segs.length - 1) % segs.length].pts.at(-1);
    return segs;
}

/* ------------------------------------------------------------------ */
/* Solid                                                               */
/* ------------------------------------------------------------------ */

/**
 * B-rep of one gear: { name, segs: [{ u: {p, U}, net[i][j] }], v: {p, U} }.
 * net[i][j] is the control point for profile index i and section j (z up).
 * Right hand twists counter-clockwise (seen from +z) as z increases.
 */
export function gearSolid(g, { b, center = [0, 0], rotation = Math.PI / 2, hand = g.hand, name = 'Gear' }) {
    if (!(b > 0)) throw new Error('Face width is required for a 3D model.');
    const sgn = hand === 'LH' ? -1 : 1;
    const twist = g.beta > 0 ? sgn * b * Math.tan(rad(g.beta)) / (g.d / 2) : 0;
    const m = twist === 0 ? 2 : Math.max(4, Math.ceil(Math.abs(twist) / TWIST_STEP) + 1);
    const vt = Array.from({ length: m }, (_, j) => j / (m - 1));
    const v = interpolator(vt);
    const sections = vt.map((s) => ({ z: s * b, c: Math.cos(s * twist), s: Math.sin(s * twist) }));
    const place = ([x, y], { z, c, s }) => [center[0] + x * c - y * s, center[1] + x * s + y * c, z];

    // one v-column per distinct control point, shared by neighbouring faces
    const columns = new Map();
    const column = (P) => {
        let col = columns.get(P);
        if (!col) { col = v.fit(sections.map((sec) => place(P, sec))); columns.set(P, col); }
        return col;
    };
    const segs = profileSegments(g, rotation).map(({ kind, pts }) => {
        const u = interpolator(chordParams(pts));
        const ctrl = u.fit(pts);
        ctrl[0] = pts[0]; // clamped ends interpolate exactly; keep the shared vertex identity
        ctrl[ctrl.length - 1] = pts.at(-1);
        return { kind, u: { p: u.p, U: u.U }, net: ctrl.map(column) };
    });
    return { name, b, twist, v: { p: v.p, U: v.U }, segs };
}

/** Point on a face of a gearSolid at (u, w) in [0, 1]². */
export function facePoint(solid, k, u, w) {
    const seg = solid.segs[k];
    const Nu = basis(seg.u.U, seg.u.p, seg.net.length - 1, u);
    const Nv = basis(solid.v.U, solid.v.p, seg.net[0].length - 1, w);
    const out = [0, 0, 0];
    seg.net.forEach((col, i) => col.forEach((P, j) => { for (let d = 0; d < 3; d++) out[d] += Nu[i] * Nv[j] * P[d]; }));
    return out;
}

/* ------------------------------------------------------------------ */
/* Part 21 writer                                                      */
/* ------------------------------------------------------------------ */

const GREEK = { 'α': 'alpha', 'β': 'beta', 'Σ': 'Sum', 'Δ': 'delta' };
const ascii = (s) => String(s).replace(/°/g, ' deg').replace(/[αβΣΔ]/g, (c) => GREEK[c]).replace(/[·×]/g, 'x')
    .replace(/[−–—]/g, '-').replace(/[^\x20-\x7e]/g, '?').replace(/'/g, "''").replace(/\\/g, '\\\\');
const str = (s) => `'${ascii(s)}'`;

const real = (v) => {
    const r = Math.round(v * 1e9) / 1e9;
    if (r === 0) return '0.';
    const s = String(r);
    if (s.includes('e')) {
        const [m, e] = s.split('e');
        return `${m.includes('.') ? m : `${m}.`}E${e}`;
    }
    return s.includes('.') ? s : `${s}.`;
};

/** Distinct knot values and their multiplicities, as STEP lists. */
function knotLists(U) {
    const vals = [], mults = [];
    for (const k of U) {
        if (vals.length && Math.abs(vals.at(-1) - k) < 1e-12) mults[mults.length - 1]++;
        else { vals.push(k); mults.push(1); }
    }
    return { mults: `(${mults.join(',')})`, vals: `(${vals.map(real).join(',')})` };
}

/**
 * STEP file text for one or more gear solids (one part, one body per gear).
 * stamp: ISO time string for the header (defaults to now).
 */
export function toStep(solids, { name = 'Gear', description = '', stamp = new Date().toISOString().slice(0, 19) } = {}) {
    const lines = [];
    let id = 0;
    const ent = (s) => { lines.push(`#${++id}=${s};`); return `#${id}`; };
    const points = new Map();
    const pt = (P) => {
        let r = points.get(P);
        if (!r) { r = ent(`CARTESIAN_POINT('',(${P.map(real).join(',')}))`); points.set(P, r); }
        return r;
    };
    const vertices = new Map();
    const vertex = (P) => {
        let r = vertices.get(P);
        if (!r) { r = ent(`VERTEX_POINT('',${pt(P)})`); vertices.set(P, r); }
        return r;
    };
    const curve = (ctrl, { p, U }) => {
        const k = knotLists(U);
        return ent(`B_SPLINE_CURVE_WITH_KNOTS('',${p},(${ctrl.map(pt).join(',')}),.UNSPECIFIED.,.F.,.F.,${k.mults},${k.vals},.UNSPECIFIED.)`);
    };
    const edge = (ctrl, basisDef) => ent(`EDGE_CURVE('',${vertex(ctrl[0])},${vertex(ctrl.at(-1))},${curve(ctrl, basisDef)},.T.)`);
    const oriented = (e, sense) => ent(`ORIENTED_EDGE('',*,*,${e},${sense ? '.T.' : '.F.'})`);
    const face = (orientedEdges, surface, sense) => {
        const loop = ent(`EDGE_LOOP('',(${orientedEdges.join(',')}))`);
        const bound = ent(`FACE_OUTER_BOUND('',${loop},.T.)`);
        return ent(`ADVANCED_FACE('',(${bound}),${surface},${sense ? '.T.' : '.F.'})`);
    };
    const plane = (z) => {
        const ax = ent(`AXIS2_PLACEMENT_3D('',${pt([0, 0, z])},${ent('DIRECTION(\'\',(0.,0.,1.))')},${ent('DIRECTION(\'\',(1.,0.,0.))')})`);
        return ent(`PLANE('',${ax})`);
    };

    const ctx = ent(`APPLICATION_CONTEXT('automotive design')`);
    ent(`APPLICATION_PROTOCOL_DEFINITION('international standard','automotive_design',2000,${ctx})`);
    const pctx = ent(`PRODUCT_CONTEXT('',${ctx},'mechanical')`);
    const product = ent(`PRODUCT(${str(name)},${str(name)},${str(description)},(${pctx}))`);
    ent(`PRODUCT_RELATED_PRODUCT_CATEGORY('part',$,(${product}))`);
    const pdf = ent(`PRODUCT_DEFINITION_FORMATION('','',${product})`);
    const pdc = ent(`PRODUCT_DEFINITION_CONTEXT('part definition',${ctx},'design')`);
    const pd = ent(`PRODUCT_DEFINITION('design','',${pdf},${pdc})`);
    const pds = ent(`PRODUCT_DEFINITION_SHAPE('','',${pd})`);
    const mm = ent('(LENGTH_UNIT()NAMED_UNIT(*)SI_UNIT(.MILLI.,.METRE.))');
    const radian = ent('(NAMED_UNIT(*)PLANE_ANGLE_UNIT()SI_UNIT($,.RADIAN.))');
    const sr = ent('(NAMED_UNIT(*)SI_UNIT($,.STERADIAN.)SOLID_ANGLE_UNIT())');
    const unc = ent(`UNCERTAINTY_MEASURE_WITH_UNIT(LENGTH_MEASURE(1.E-06),${mm},'distance_accuracy_value','confusion accuracy')`);
    const gctx = ent(`(GEOMETRIC_REPRESENTATION_CONTEXT(3)GLOBAL_UNCERTAINTY_ASSIGNED_CONTEXT((${unc}))GLOBAL_UNIT_ASSIGNED_CONTEXT((${mm},${radian},${sr}))REPRESENTATION_CONTEXT('Context #1','3D Context with UNIT and UNCERTAINTY'))`);

    const breps = solids.map((sol) => {
        const K = sol.segs.length;
        const top = sol.segs[0].net[0].length - 1;
        const kv = knotLists(sol.v.U);
        // bottom and top profile edges, and the seam at the start of each segment
        const bottom = sol.segs.map((seg) => edge(seg.net.map((col) => col[0]), seg.u));
        const topE = sol.segs.map((seg) => edge(seg.net.map((col) => col[top]), seg.u));
        const seam = sol.segs.map((seg) => edge(seg.net[0], sol.v));
        const faces = sol.segs.map((seg, k) => {
            const ku = knotLists(seg.u.U);
            const rows = seg.net.map((col) => `(${col.map(pt).join(',')})`).join(',');
            const surf = ent(`B_SPLINE_SURFACE_WITH_KNOTS('',${seg.u.p},${sol.v.p},(${rows}),.UNSPECIFIED.,.F.,.F.,.F.,${ku.mults},${kv.mults},${ku.vals},${kv.vals},.UNSPECIFIED.)`);
            return face([
                oriented(bottom[k], true),
                oriented(seam[(k + 1) % K], true),
                oriented(topE[k], false),
                oriented(seam[k], false),
            ], surf, true);
        });
        faces.push(face(bottom.map((e) => oriented(e, false)).reverse(), plane(0), false));
        faces.push(face(topE.map((e) => oriented(e, true)), plane(sol.b), true));
        const shell = ent(`CLOSED_SHELL('',(${faces.join(',')}))`);
        return ent(`MANIFOLD_SOLID_BREP(${str(sol.name)},${shell})`);
    });

    const origin = ent(`AXIS2_PLACEMENT_3D('',${pt([0, 0, 0])},${ent('DIRECTION(\'\',(0.,0.,1.))')},${ent('DIRECTION(\'\',(1.,0.,0.))')})`);
    const rep = ent(`ADVANCED_BREP_SHAPE_REPRESENTATION(${str(name)},(${[origin, ...breps].join(',')}),${gctx})`);
    ent(`SHAPE_DEFINITION_REPRESENTATION(${pds},${rep})`);

    const fileName = `${ascii(name).toLowerCase().replace(/[^a-z0-9]+/g, '-')}.step`;
    return [
        'ISO-10303-21;',
        'HEADER;',
        `FILE_DESCRIPTION((${str(description || name)}),'2;1');`,
        `FILE_NAME('${fileName}','${stamp}',('misubaguna.com'),('PT Misuba Guna Indonesia'),'misubaguna.com gear calculator','misubaguna.com gear calculator','');`,
        "FILE_SCHEMA(('AUTOMOTIVE_DESIGN { 1 0 10303 214 1 1 1 1 }'));",
        'ENDSEC;',
        'DATA;',
        ...lines,
        'ENDSEC;',
        'END-ISO-10303-21;',
        '',
    ].join('\r\n');
}
