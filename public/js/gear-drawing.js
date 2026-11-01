// Gear drawings as plain entities in millimetres (y up). The same entities
// render to the on-screen SVG, the downloadable SVG and the DXF file
// (gear-dxf.js), so the drawing and the export never disagree.
//
// Entity shapes (layer names are the DXF layers, see LAYERS):
//   { t: 'poly', pts, closed, layer }      { t: 'line', a, b, layer }
//   { t: 'circle', c, r, layer }           { t: 'arc', c, r, a0, a1, layer }  (degrees, CCW)
//   { t: 'text', at, s, h, anchor, rot, layer }   anchor: start | middle | end
//   { t: 'solid', pts, layer }             (filled triangle, dimension arrows)
// Optional: cls (screen CSS class override), screen: false, dxf: false.
import { gearOutline, rad } from './gear-math.js';

export const LAYERS = {
    GEAR: { color: 7, lt: 'CONTINUOUS', cls: 's-gear' },
    GEAR2: { color: 7, lt: 'CONTINUOUS', cls: 's-gear s-gear2' },
    TIP: { color: 8, lt: 'DASHED', cls: 's-c s-tip' },
    REF: { color: 4, lt: 'CENTER', cls: 's-ref' },
    BASE: { color: 8, lt: 'PHANTOM', cls: 's-c s-base' },
    ROOT: { color: 8, lt: 'HIDDEN', cls: 's-c s-root' },
    AXIS: { color: 6, lt: 'CENTER', cls: 's-axis' },
    HELIX: { color: 5, lt: 'CONTINUOUS', cls: 's-helix' },
    LOA: { color: 1, lt: 'CONTINUOUS', cls: 's-loa' },
    DIM: { color: 3, lt: 'CONTINUOUS', cls: 's-dim' },
    TEXT: { color: 7, lt: 'CONTINUOUS', cls: 's-text' },
    NOTES: { color: 7, lt: 'CONTINUOUS', cls: 's-text s-note' },
};

const SAMPLES = 20; // involute points per flank, smooth enough for wire-cut / laser
const n = (v, d = 3) => String(Number(v.toFixed(d)));
const add = ([x, y], [u, v], k = 1) => [x + u * k, y + v * k];

/** Face width used for drawing when the user left b blank. */
export function drawingFaceWidth(g) {
    return g.b ?? Math.round(Math.max(10 * g.mn, 0.25 * g.d));
}

/* ------------------------------------------------------------------ */
/* Annotation helpers                                                  */
/* ------------------------------------------------------------------ */

/** Linear dimension from a to b, offset `off` along the left normal of a→b. */
export function dim(ents, a, b, off, label, h) {
    const L = Math.hypot(b[0] - a[0], b[1] - a[1]);
    const u = [(b[0] - a[0]) / L, (b[1] - a[1]) / L];
    const nv = [-u[1], u[0]];
    const sg = Math.sign(off) || 1;
    const A = add(a, nv, off), B = add(b, nv, off);
    ents.push({ t: 'line', a: add(a, nv, 0.3 * h * sg), b: add(A, nv, 0.5 * h * sg), layer: 'DIM' });
    ents.push({ t: 'line', a: add(b, nv, 0.3 * h * sg), b: add(B, nv, 0.5 * h * sg), layer: 'DIM' });
    ents.push({ t: 'line', a: A, b: B, layer: 'DIM' });
    const arrow = (tip, dir) => ents.push({ t: 'solid', layer: 'DIM', pts: [tip, add(add(tip, dir, 0.9 * h), nv, 0.25 * h), add(add(tip, dir, 0.9 * h), nv, -0.25 * h)] });
    arrow(A, u);
    arrow(B, [-u[0], -u[1]]);
    // keep text readable: direction in (−90°, 90°]
    let rot = Math.atan2(u[1], u[0]) * 180 / Math.PI;
    if (rot <= -90 || rot > 90) rot += rot > 0 ? -180 : 180;
    const up = [-Math.sin(rad(rot)), Math.cos(rad(rot))];
    const mid = [(A[0] + B[0]) / 2, (A[1] + B[1]) / 2];
    ents.push({ t: 'text', at: add(mid, up, 0.35 * h), s: label, h, anchor: 'middle', rot: Math.round(rot * 1e6) / 1e6, layer: 'DIM' });
}

const text = (ents, at, s, h, anchor = 'middle', extra = {}) => ents.push({ t: 'text', at, s, h, anchor, rot: 0, layer: 'TEXT', ...extra });

/* ------------------------------------------------------------------ */
/* Cylindrical gears                                                   */
/* ------------------------------------------------------------------ */

/** Transverse section (front view) of a cylindrical gear centred on c. */
function frontView(ents, g, { c = [0, 0], rot = Math.PI / 2, layer = 'GEAR', tip = true } = {}) {
    const pts = gearOutline({ ...g, rotation: rot, samples: SAMPLES }).map(([x, y]) => [x + c[0], y + c[1]]);
    ents.push({ t: 'poly', pts, closed: true, layer });
    if (tip) ents.push({ t: 'circle', c, r: g.da / 2, layer: 'TIP', dxf: false });
    ents.push({ t: 'circle', c, r: g.d / 2, layer: 'REF' });
    ents.push({ t: 'circle', c, r: g.db / 2, layer: 'BASE' });
    ents.push({ t: 'circle', c, r: g.df / 2, layer: 'ROOT' });
    const ext = g.da / 2 * 1.1;
    ents.push({ t: 'line', a: [c[0] - ext, c[1]], b: [c[0] + ext, c[1]], layer: 'AXIS' });
    ents.push({ t: 'line', a: [c[0], c[1] - ext], b: [c[0], c[1] + ext], layer: 'AXIS' });
}

/**
 * Side view (axis horizontal) of a cylindrical gear from x0 to x0 + b, axis
 * at height yc. Helical teeth are drawn as the projected tooth traces on the
 * reference cylinder; with a horizontal axis a right-hand helix reads "\".
 */
function sideView(ents, g, { x0, yc = 0, b, rot = Math.PI / 2, layer = 'GEAR', hand = g.hand } = {}) {
    const ra = g.da / 2, r = g.d / 2, rf = g.df / 2;
    ents.push({ t: 'poly', pts: [[x0, yc - ra], [x0 + b, yc - ra], [x0 + b, yc + ra], [x0, yc + ra]], closed: true, layer });
    const ext = Math.min(b, ra) * 0.12;
    for (const s of [-1, 1]) {
        ents.push({ t: 'line', a: [x0 - ext, yc + s * r], b: [x0 + b + ext, yc + s * r], layer: 'REF' });
        ents.push({ t: 'line', a: [x0, yc + s * rf], b: [x0 + b, yc + s * rf], layer: 'ROOT' });
    }
    ents.push({ t: 'line', a: [x0 - 2.5 * ext, yc], b: [x0 + b + 2.5 * ext, yc], layer: 'AXIS' });
    if (!(g.beta > 0)) return;

    // one trace per tooth, along the tooth centre on the reference cylinder
    const sgn = hand === 'LH' ? -1 : 1;
    const tb = Math.tan(rad(g.beta));
    const steps = 40;
    for (let i = 0; i < g.z; i++) {
        const phi0 = rot + i * 2 * Math.PI / g.z;
        let run = [];
        const flush = () => { if (run.length > 1) ents.push({ t: 'poly', pts: run, closed: false, layer: 'HELIX' }); run = []; };
        for (let k = 0; k <= steps; k++) {
            const s = b * k / steps;
            const phi = phi0 - sgn * s * tb / r;
            if (Math.cos(phi) > 0) run.push([x0 + s, yc + r * Math.sin(phi)]);
            else flush();
        }
        flush();
    }
}

/** Helix angle mark in the middle of a side view. */
function helixMark(ents, g, { x0, yc = 0, b, h, hand = g.hand, label = true }) {
    const sgn = hand === 'LH' ? -1 : 1;
    const L = Math.min(b * 0.9, g.d * 0.5);
    const mid = [x0 + b / 2, yc];
    const ang = -sgn * g.beta;
    const dir = [Math.cos(rad(ang)), Math.sin(rad(ang))];
    const mark = { layer: 'DIM', cls: 's-mark' };
    ents.push({ t: 'line', a: add(mid, dir, -L / 2), b: add(mid, dir, L / 2), ...mark });
    ents.push({ t: 'line', a: mid, b: add(mid, [1, 0], L / 2), ...mark });
    ents.push({ t: 'arc', c: mid, r: L * 0.42, a0: Math.min(0, ang), a1: Math.max(0, ang), ...mark });
    if (label) text(ents, [mid[0], yc + g.da / 2 + 0.6 * h], `β ${n(g.beta, 3)}° ${hand}`, h, 'middle', { layer: 'DIM' });
}

function bounds(ents, pad = 0) {
    let x0 = Infinity, y0 = Infinity, x1 = -Infinity, y1 = -Infinity;
    const take = ([x, y]) => { x0 = Math.min(x0, x); y0 = Math.min(y0, y); x1 = Math.max(x1, x); y1 = Math.max(y1, y); };
    for (const e of ents) {
        if (e.t === 'poly' || e.t === 'solid') e.pts.forEach(take);
        else if (e.t === 'line') { take(e.a); take(e.b); }
        else if (e.t === 'circle' || e.t === 'arc') { take([e.c[0] - e.r, e.c[1] - e.r]); take([e.c[0] + e.r, e.c[1] + e.r]); }
        else if (e.t === 'text') {
            const w = e.s.length * e.h * 0.6;
            const x = e.anchor === 'middle' ? e.at[0] - w / 2 : e.anchor === 'end' ? e.at[0] - w : e.at[0];
            if (Math.abs(e.rot) < 1) { take([x, e.at[1] - 0.25 * e.h]); take([x + w, e.at[1] + e.h]); }
            else { take([e.at[0] - e.h, e.at[1] - w / 2]); take([e.at[0] + 0.25 * e.h, e.at[1] + w / 2]); }
        }
    }
    return [x0 - pad, y0 - pad, x1 + pad, y1 + pad];
}

function viewOf(ents, fs, extra = {}) {
    return { ents, fs, bounds: bounds(ents, fs * 0.8), ...extra };
}

/** Spur or helical gear: front view and side view on one sheet, true scale. */
export function gearView(g, { notes = [], title = '', fs: textHeight = null } = {}) {
    const ents = [];
    const b = drawingFaceWidth(g);
    const ra = g.da / 2;
    const fs = textHeight ?? 0.026 * (2.3 * ra + b);
    frontView(ents, g);
    const x0 = ra * 1.1 + Math.max(0.35 * ra, 4 * fs);
    sideView(ents, g, { x0, b });
    dim(ents, [x0, -ra], [x0 + b, -ra], -1.8 * fs, `b ${n(b, 2)}`, fs);
    dim(ents, [x0 + b, -ra], [x0 + b, ra], -1.8 * fs, `⌀${n(g.da, 3)}`, fs);
    if (g.beta > 0) helixMark(ents, g, { x0, b, h: fs });
    text(ents, [0, -ra * 1.1 - 1.6 * fs], 'Front view', fs * 0.8, 'middle', { cls: 's-text s-muted' });
    text(ents, [x0 + b / 2, -ra - 4.4 * fs], 'Side view', fs * 0.8, 'middle', { cls: 's-text s-muted' });
    return viewOf(ents, fs, { notes, title, faceWidth: b, faceWidthAssumed: g.b === null });
}

/** Close-up of a gear pair at the pitch point (screen only, clipped). */
export function pairCloseUp(p) {
    const ents = [];
    const g1 = p.gear1, g2 = p.gear2;
    const rw1 = p.dw1 / 2, rw2 = p.dw2 / 2;
    const pt = Math.PI * p.mn / Math.cos(rad(p.beta));
    const W = 3.2 * pt, H = 2.4 * pt;
    const fs = Math.max(W, H) * 2 * 0.032;
    const n1 = Math.min(Math.ceil(W / (Math.PI * g1.d / g1.z)) + 2, Math.floor(g1.z / 2));
    const n2 = Math.min(Math.ceil(W / (Math.PI * g2.d / g2.z)) + 2, Math.floor(g2.z / 2));
    const range = (k) => Array.from({ length: 2 * k + 1 }, (_, i) => i - k);
    const o1 = gearOutline({ ...g1, rotation: Math.PI / 2, teeth: range(n1) }).map(([x, y]) => [x, y - rw1]);
    const o2 = gearOutline({ ...g2, rotation: -Math.PI / 2 + Math.PI / g2.z, teeth: range(n2) }).map(([x, y]) => [x, y + rw2]);
    ents.push({ t: 'poly', pts: [...o1, [0, -rw1]], closed: true, layer: 'GEAR' });
    ents.push({ t: 'poly', pts: [...o2, [0, rw2]], closed: true, layer: 'GEAR2' });
    ents.push({ t: 'circle', c: [0, -rw1], r: rw1, layer: 'REF' });
    ents.push({ t: 'circle', c: [0, rw2], r: rw2, layer: 'REF' });
    // line of action through P at αwt, from tangent point T1 to T2
    const aw = rad(p.alphaWtDeg);
    const u = [Math.cos(aw), Math.sin(aw)];
    const foot = ([ox, oy]) => { const t = -(ox * u[0] + oy * u[1]); return [-(t * u[0]), -(t * u[1])]; };
    ents.push({ t: 'line', a: foot([0, -rw1]), b: foot([0, rw2]), layer: 'LOA' });
    ents.push({ t: 'line', a: [0, -H], b: [0, H], layer: 'AXIS' });
    ents.push({ t: 'circle', c: [0, 0], r: fs * 0.18, layer: 'DIM', cls: 's-dot' });
    text(ents, [fs * 0.5, fs * 0.25], 'P', fs, 'start');
    text(ents, [W - fs * 0.3, -H + fs * 0.6], 'Gear 1', fs, 'end');
    text(ents, [W - fs * 0.3, H - fs * 1.1], 'Gear 2', fs, 'end');
    text(ents, [-W + fs * 0.3, H - fs * 1.1], 'Mesh close-up', fs * 0.8, 'start', { cls: 's-text s-muted' });
    return { ents, fs, bounds: [-W, -H, W, H], clip: true, dxf: false, title: 'Mesh close-up' };
}

/** Gear pair at centre distance: front views and side views, true scale. */
export function pairView(p, { notes = [], title = '' } = {}) {
    const ents = [];
    const g1 = p.gear1, g2 = p.gear2;
    const b = drawingFaceWidth(g1);
    const R = Math.max(g1.da, g2.da) / 2;
    const fs = 0.028 * (p.a + (g1.da + g2.da) / 2);
    const rot2 = -Math.PI / 2 + Math.PI / g2.z;
    frontView(ents, g1, { c: [0, 0] });
    frontView(ents, g2, { c: [0, p.a], rot: rot2, layer: 'GEAR2' });
    const x0 = R * 1.1 + Math.max(0.35 * R, 4 * fs);
    sideView(ents, g2, { x0, yc: p.a, b, rot: rot2, layer: 'GEAR2', hand: p.hand2 });
    sideView(ents, g1, { x0, yc: 0, b, layer: 'GEAR', hand: p.hand });
    dim(ents, [-g1.da / 2 * 1.1, 0], [-g1.da / 2 * 1.1, p.a], 1.4 * fs, `a ${n(p.a, 3)}`, fs);
    dim(ents, [x0, -g1.da / 2], [x0 + b, -g1.da / 2], -1.8 * fs, `b ${n(b, 2)}`, fs);
    if (p.beta > 0) {
        helixMark(ents, g1, { x0, b, h: fs, hand: p.hand, label: false });
        text(ents, [x0 + b + 1.2 * fs, -0.35 * fs], `β ${n(p.beta, 3)}° ${p.hand}`, fs, 'start', { layer: 'DIM' });
        text(ents, [x0 + b + 1.2 * fs, p.a - 0.35 * fs], `β ${n(p.beta, 3)}° ${p.hand2}`, fs, 'start', { layer: 'DIM' });
    }
    text(ents, [g1.da / 2 * 0.72, -g1.da / 2 * 0.95], 'Gear 1', fs, 'start');
    text(ents, [g2.da / 2 * 0.72, p.a + g2.da / 2 * 0.9], 'Gear 2', fs, 'start');
    return viewOf(ents, fs, { notes, title, faceWidth: b, faceWidthAssumed: g1.b === null });
}

/* ------------------------------------------------------------------ */
/* Bevel and worm (schematic sections)                                 */
/* ------------------------------------------------------------------ */

export function bevelView(p, { notes = [], title = '' } = {}) {
    const ents = [];
    const b = p.b ?? p.Re / 3;
    const S = rad(p.sigma);
    const tA = Math.tan(rad(p.thetaADeg)), tF = Math.tan(rad(p.thetaFDeg));
    const fs = p.Re * 1.6 * 0.032;
    const els = [
        { ang: p.gear1.deltaDeg, axis: 0 }, { ang: -p.gear1.deltaDeg, axis: 0 },
        { ang: p.sigma - p.gear2.deltaDeg, axis: p.sigma, two: true }, { ang: p.sigma + p.gear2.deltaDeg, axis: p.sigma, two: true },
    ];
    for (const e of els) {
        const f = rad(e.ang);
        const dir = [Math.cos(f), Math.sin(f)];
        const sgn = Math.sign(e.ang - e.axis) || 1;
        const nv = [-dir[1] * sgn, dir[0] * sgn];
        const at = (s, hh) => [s * dir[0] + hh * nv[0], s * dir[1] + hh * nv[1]];
        const Ri = p.Re - b;
        ents.push({ t: 'line', a: [0, 0], b: at(p.Re, 0), layer: 'REF' });
        ents.push({ t: 'poly', pts: [at(Ri, p.ha - b * tA), at(p.Re, p.ha), at(p.Re, -p.hf), at(Ri, -(p.hf - b * tF))], closed: true, layer: e.two ? 'GEAR2' : 'GEAR' });
    }
    const ax1 = p.Re * Math.cos(rad(p.gear1.deltaDeg)) * 1.1;
    const ax2 = p.Re * Math.cos(rad(p.gear2.deltaDeg)) * 1.1;
    ents.push({ t: 'line', a: [0, 0], b: [ax1, 0], layer: 'AXIS' });
    ents.push({ t: 'line', a: [0, 0], b: [ax2 * Math.cos(S), ax2 * Math.sin(S)], layer: 'AXIS' });
    const d1 = rad(p.gear1.deltaDeg);
    text(ents, [p.Re * Math.cos(d1) / 2, p.Re * Math.sin(d1) / 2 + fs * 0.6], `Re ${n(p.Re, 2)}`, fs);
    text(ents, [p.Re * 0.35, -fs * 0.9], `δ1 ${n(p.gear1.deltaDeg, 2)}°`, fs);
    text(ents, [0, -fs * 1.2], 'apex', fs * 0.8, 'middle', { cls: 's-text s-muted' });
    text(ents, [ax1, fs * 0.4], 'pinion axis', fs * 0.8, 'end', { cls: 's-text s-muted' });
    text(ents, [ax2 * Math.cos(S) + fs * 0.4, ax2 * Math.sin(S)], 'wheel axis', fs * 0.8, 'start', { cls: 's-text s-muted' });
    return viewOf(ents, fs, { notes, title, faceWidth: b, faceWidthAssumed: p.b === null });
}

export function wormView(w, { notes = [], title = '' } = {}) {
    const ents = [];
    const L = Math.max((11 + 0.06 * w.z2) * w.m, w.d1 * 1.2);
    const R2 = w.de2 / 2;
    const xmax = Math.max(R2, L / 2) * 1.08;
    const fs = Math.max(2 * xmax, R2 * 1.08 + w.a + w.da1 / 2) * 0.032;
    ents.push({ t: 'circle', c: [0, 0], r: w.de2 / 2, layer: 'ROOT' });
    ents.push({ t: 'circle', c: [0, 0], r: w.da2 / 2, layer: 'GEAR2', cls: 's-gear s-gear2' });
    ents.push({ t: 'circle', c: [0, 0], r: w.d2 / 2, layer: 'REF' });
    ents.push({ t: 'circle', c: [0, 0], r: w.df2 / 2, layer: 'BASE' });
    // worm axial section: rack-like thread profile top and bottom
    const r1 = w.d1 / 2, ra = w.da1 / 2, rf = w.df1 / 2;
    const tanA = Math.tan(rad(w.an));
    const thread = (side, offset) => {
        const out = [];
        const s = w.px / 2; // thread thickness at d1
        for (let x0 = -L / 2 - w.px + offset; x0 < L / 2 + w.px; x0 += w.px) {
            const tipHalf = s / 2 - (ra - r1) * tanA, rootHalf = s / 2 + (r1 - rf) * tanA;
            out.push([x0 - rootHalf, rf], [x0 - tipHalf, ra], [x0 + tipHalf, ra], [x0 + rootHalf, rf]);
        }
        return out.filter(([x]) => x >= -L / 2 && x <= L / 2).map(([x, r]) => [x, w.a + side * r]);
    };
    const top = thread(1, 0);
    const bot = thread(-1, (w.pz / 2) % w.px).reverse();
    ents.push({ t: 'poly', pts: [[-L / 2, w.a + rf], ...top, [L / 2, w.a + rf], [L / 2, w.a - rf], ...bot, [-L / 2, w.a - rf]], closed: true, layer: 'GEAR' });
    ents.push({ t: 'line', a: [-L / 2, w.a + r1], b: [L / 2, w.a + r1], layer: 'REF' });
    ents.push({ t: 'line', a: [-L / 2, w.a - r1], b: [L / 2, w.a - r1], layer: 'REF' });
    ents.push({ t: 'line', a: [-xmax, w.a], b: [xmax, w.a], layer: 'AXIS' });
    dim(ents, [-R2 * 0.55, 0], [-R2 * 0.55, w.a], 0, `a ${n(w.a, 2)}`, fs);
    text(ents, [-L / 2, w.a + ra + fs * 0.5], `d1 ${n(w.d1, 2)}`, fs, 'start');
    text(ents, [0, -w.d2 / 2 + fs * 1.2], `d2 ${n(w.d2, 2)}`, fs);
    return viewOf(ents, fs, { notes, title });
}

/* ------------------------------------------------------------------ */
/* Sheet layout and SVG                                                */
/* ------------------------------------------------------------------ */

function shift(e, dx, dy) {
    const m = (q) => [q[0] + dx, q[1] + dy];
    const o = { ...e };
    if (e.pts) o.pts = e.pts.map(m);
    if (e.a) o.a = m(e.a);
    if (e.b) o.b = m(e.b);
    if (e.c) o.c = m(e.c);
    if (e.at) o.at = m(e.at);
    return o;
}

/**
 * Places the exportable views left to right with their notes underneath and
 * the sheet title on top. Returns { ents, bounds } for SVG or DXF output.
 */
export function sheet(views, { title = '', footer = [] } = {}) {
    const ents = [];
    const list = views.filter((v) => v.dxf !== false);
    const fsMax = Math.max(...list.map((v) => v.fs));
    let x = 0, top = -Infinity, bottom = Infinity;
    for (const v of list) {
        const [x0, , x1, y1] = v.bounds;
        const dx = x - x0;
        const part = v.ents.filter((e) => e.dxf !== false).map((e) => shift(e, dx, 0));
        const hn = v.fs * 0.8;
        let y = v.bounds[1] - 1.5 * hn;
        if (v.title) { part.push({ t: 'text', at: [x, y], s: v.title, h: hn * 1.15, anchor: 'start', rot: 0, layer: 'NOTES' }); y -= 1.9 * hn; }
        for (const line of v.notes || []) {
            if (line) part.push({ t: 'text', at: [x, y], s: line, h: hn, anchor: 'start', rot: 0, layer: 'NOTES' });
            y -= 1.6 * hn;
        }
        ents.push(...part);
        top = Math.max(top, y1);
        bottom = Math.min(bottom, y);
        x += x1 - x0 + 3 * fsMax;
    }
    if (title) ents.push({ t: 'text', at: [0, top + 1.2 * fsMax], s: title, h: fsMax * 1.4, anchor: 'start', rot: 0, layer: 'NOTES' });
    let y = bottom - 0.6 * fsMax;
    for (const line of footer) {
        ents.push({ t: 'text', at: [0, y], s: line, h: fsMax * 0.75, anchor: 'start', rot: 0, layer: 'NOTES' });
        y -= 1.4 * fsMax * 0.75;
    }
    return { ents, bounds: bounds(ents, fsMax), fs: fsMax };
}

const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const f3 = (v) => (Math.round(v * 1000) / 1000).toString();
const P = ([x, y]) => `${f3(x)},${f3(-y)}`; // y up → SVG y down

function svgEntity(e) {
    const cls = esc(e.cls || LAYERS[e.layer].cls);
    switch (e.t) {
        case 'poly': return `<path class="${cls}" d="M${e.pts.map(P).join('L')}${e.closed ? 'Z' : ''}"/>`;
        case 'line': return `<path class="${cls}" d="M${P(e.a)}L${P(e.b)}"/>`;
        case 'circle': return `<circle class="${cls}" cx="${f3(e.c[0])}" cy="${f3(-e.c[1])}" r="${f3(e.r)}"/>`;
        case 'solid': return `<path class="s-arrow" d="M${e.pts.map(P).join('L')}Z"/>`;
        case 'arc': {
            const p0 = [e.c[0] + e.r * Math.cos(rad(e.a0)), e.c[1] + e.r * Math.sin(rad(e.a0))];
            const p1 = [e.c[0] + e.r * Math.cos(rad(e.a1)), e.c[1] + e.r * Math.sin(rad(e.a1))];
            const large = (e.a1 - e.a0) % 360 > 180 ? 1 : 0;
            return `<path class="${cls}" fill="none" d="M${P(p0)}A${f3(e.r)},${f3(e.r)} 0 ${large} 0 ${P(p1)}"/>`;
        }
        case 'text': {
            const rot = e.rot ? ` transform="rotate(${f3(-e.rot)} ${f3(e.at[0])} ${f3(-e.at[1])})"` : '';
            return `<text class="${cls}" x="${f3(e.at[0])}" y="${f3(-e.at[1])}" font-size="${f3(e.h)}" text-anchor="${e.anchor}"${rot}>${esc(e.s)}</text>`;
        }
        default: return '';
    }
}

// Print styles for the downloaded SVG (millimetre user units, 1:1 scale).
const SVG_STYLE = `
.s-gear{fill:#e6f2f1;stroke:#0f2a33;stroke-width:.35;stroke-linejoin:round}
.s-gear2{fill:#eef1f2}
.s-c,.s-ref,.s-axis,.s-helix,.s-loa,.s-dim{fill:none;stroke-width:.18}
.s-tip{stroke:#0f2a33;stroke-dasharray:1 1.5}
.s-ref{stroke:#0e7c86;stroke-dasharray:6 1.5 1 1.5}
.s-base{stroke:#5b6b72;stroke-dasharray:6 1.5 1 1.5 1 1.5}
.s-root{stroke:#7d8b91;stroke-dasharray:2 1}
.s-axis{stroke:#5b6b72;stroke-dasharray:8 1.5 1.5 1.5}
.s-helix{stroke:#5b6b72}
.s-mark{fill:none;stroke:#0b5f67;stroke-width:.4}
.s-loa,.s-dim{stroke:#0f2a33;stroke-width:.25}
.s-arrow,.s-dot{fill:#0f2a33}
.s-text{fill:#0f2a33;font-family:Arial,Helvetica,sans-serif}
.s-muted{fill:#5b6b72}`;

/**
 * SVG markup for a view or sheet. Screen use relies on the page's CSS classes;
 * `standalone` embeds print styles and sets a true-scale size in mm.
 */
export function toSvg(view, { standalone = false, label = '', clipId = 'gc-clip' } = {}) {
    const [x0, y0, x1, y1] = view.bounds;
    const w = x1 - x0, h = y1 - y0;
    const box = `${f3(x0)} ${f3(-y1)} ${f3(w)} ${f3(h)}`;
    const ents = view.ents.filter((e) => (standalone ? e.dxf !== false : e.screen !== false));
    let body = ents.map(svgEntity).join('');
    let defs = '';
    if (view.clip) {
        defs = `<clipPath id="${clipId}"><rect x="${f3(x0)}" y="${f3(-y1)}" width="${f3(w)}" height="${f3(h)}"/></clipPath>`;
        body = `<g clip-path="url(#${clipId})">${body}</g>`;
    }
    const size = standalone ? ` width="${f3(w)}mm" height="${f3(h)}mm"` : '';
    const style = standalone ? `<style>${SVG_STYLE}</style><rect x="${f3(x0)}" y="${f3(-y1)}" width="${f3(w)}" height="${f3(h)}" fill="#fff"/>` : '';
    const head = standalone ? '<?xml version="1.0" encoding="UTF-8"?>\n' : '';
    return `${head}<svg xmlns="http://www.w3.org/2000/svg" viewBox="${box}"${size} role="img" aria-label="${esc(label)}" preserveAspectRatio="xMidYMid meet">${style}${defs ? `<defs>${defs}</defs>` : ''}${body}</svg>`;
}
