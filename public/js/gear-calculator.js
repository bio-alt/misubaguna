// Gear calculator UI for /toolkit/gear-calculator/. All maths lives in gear-math.js.
import * as G from './gear-math.js';
import * as D from './gear-drawing.js';
import { toDxf } from './gear-dxf.js';

const SALES_EMAIL = 'sales@misubaguna.com';
const WA_NUMBER = '628118715671';
const SITE = 'https://misubaguna.com';

/* ------------------------------------------------------------------ */
/* Type, mode and field configuration                                  */
/* ------------------------------------------------------------------ */

const MODES = {
    spur: { a: 'Module & teeth known', b: 'Identify from a sample' },
    helical: { a: 'Module & teeth known', b: 'Identify from a sample' },
    pair: { a: 'Profile shifts known → centre distance', b: 'Centre distance known → required Σx' },
    bevel: { a: 'Module known', b: 'Identify from measured tip diameter' },
    worm: { a: 'Module & diameter factor known', b: 'Centre distance known → required x2' },
};

const TYPE_NAMES = { spur: 'Spur gear', helical: 'Helical gear', pair: 'Gear pair', bevel: 'Straight bevel gear pair', worm: 'Worm & wheel' };

const PA = { kind: 'select', options: G.PRESSURE_ANGLES.map((v) => [String(v), `${v}°`]) };
const HAND = { kind: 'select', options: [['RH', 'Right hand (RH)'], ['LH', 'Left hand (LH)']] };
const SNAP = { kind: 'select', options: [['auto', 'Closest standard (auto)'], ...G.ISO_MODULES.map((m) => [`m${m}`, `m ${m} (ISO 54)`]), ...G.COMMON_DP.map((dp) => [`dp${dp}`, `DP ${dp} (m ${fmtNum(25.4 / dp, 4)})`])] };

// key: URL/param name. kind: int | num | module | select. opt: optional (blank allowed).
const F = {
    z: { label: 'Number of teeth', sym: 'z', kind: 'int' },
    z1: { label: 'Pinion teeth', sym: 'z1', kind: 'int' },
    z2: { label: 'Wheel teeth', sym: 'z2', kind: 'int' },
    mn: { label: 'Normal module', sym: 'mn', unit: 'mm', kind: 'module' },
    m: { label: 'Module', sym: 'm', unit: 'mm', kind: 'num' },
    an: { label: 'Normal pressure angle', sym: 'αn', unit: '°', ...PA },
    beta: { label: 'Helix angle', sym: 'β', unit: '°', kind: 'num' },
    hand: { label: 'Hand of helix', sym: '', ...HAND },
    x: { label: 'Profile shift coefficient', sym: 'x', kind: 'num' },
    x1: { label: 'Profile shift, pinion', sym: 'x1', kind: 'num' },
    x2: { label: 'Profile shift, wheel', sym: 'x2', kind: 'num' },
    bw: { label: 'Face width', sym: 'b', unit: 'mm', kind: 'num', opt: true },
    da: { label: 'Measured tip diameter', sym: 'da', unit: 'mm', kind: 'num' },
    wk: { label: 'Measured span', sym: 'Wk', unit: 'mm', kind: 'num', opt: true, help: 'Optional, but more reliable than da.' },
    k: { label: 'Teeth spanned', sym: 'k', kind: 'int' },
    snap: { label: 'Use module', sym: '', ...SNAP },
    a: { label: 'Centre distance', sym: 'a', unit: 'mm', kind: 'num' },
    sig: { label: 'Shaft angle', sym: 'Σ', unit: '°', kind: 'num' },
    da1: { label: 'Measured pinion tip diameter', sym: 'da1', unit: 'mm', kind: 'num' },
    starts: { label: 'Worm starts', sym: 'z1', kind: 'int' },
    dmode: { label: 'Worm diameter given as', sym: '', kind: 'select', options: [['q', 'Diameter factor q'], ['d1', 'Reference diameter d1']] },
    q: { label: 'Diameter factor', sym: 'q', kind: 'num' },
    d1: { label: 'Worm reference diameter', sym: 'd1', unit: 'mm', kind: 'num' },
    mu: { label: 'Friction coefficient (estimate)', sym: 'μ', kind: 'num' },
};

const FIELDS = {
    'spur.a': { z: 30, mn: 3, an: 20, x: 0, bw: 30 },
    'helical.a': { z: 30, mn: 3, an: 20, beta: 15, hand: 'RH', x: 0, bw: 40 },
    'spur.b': { z: 30, da: 97.2, wk: 32.668, k: 4, an: 20, snap: 'auto' },
    'helical.b': { z: 30, da: 100.375, wk: 41.656, k: 5, an: 20, beta: 15, hand: 'RH', bw: 40, snap: 'auto' },
    'pair.a': { z1: 20, z2: 40, mn: 2, an: 20, beta: 0, hand: 'RH', x1: 0, x2: 0, bw: 20 },
    'pair.b': { z1: 17, z2: 53, mn: 4, an: 20, beta: 12, hand: 'RH', a: 145, x1: '', bw: 40 },
    'bevel.a': { m: 5, z1: 20, z2: 40, sig: 90, an: 20, bw: 30 },
    'bevel.b': { da1: 108.94, z1: 20, z2: 40, sig: 90, an: 20, bw: 30, snap: 'auto' },
    'worm.a': { m: 4, starts: 2, z2: 40, dmode: 'q', q: 10, d1: 40, an: 20, x2: 0, mu: 0.05 },
    'worm.b': { m: 4, starts: 2, z2: 40, dmode: 'q', q: 10, d1: 40, an: 20, a: 102, mu: 0.05 },
};

// Per-form label overrides and conditional visibility.
const SPUR = { mn: { label: 'Module', sym: 'm' }, an: { label: 'Pressure angle', sym: 'α' } };
const OVERRIDE = {
    'spur.a': SPUR,
    'spur.b': SPUR,
    'pair.b': { x1: { label: 'Profile shift, pinion (blank = suggested)', opt: true } },
    'pair.a': { hand: { label: 'Hand of gear 1' } },
    'bevel.a': { an: { label: 'Pressure angle', sym: 'α' } },
    'bevel.b': { an: { label: 'Pressure angle', sym: 'α' }, snap: { label: 'Use module' } },
};
const SHOW = {
    hand: (v, t) => t !== 'pair' || Number(v.beta) > 0,
    q: (v) => v.dmode !== 'd1',
    d1: (v) => v.dmode === 'd1',
};

/* ------------------------------------------------------------------ */
/* State                                                               */
/* ------------------------------------------------------------------ */

const state = { type: 'spur', mode: 'a', values: {}, unit: 'm' }; // unit: module input as 'm' or 'dp'
const store = {}; // remembered values per type.mode

const $ = (s, root = document) => root.querySelector(s);
const el = (tag, attrs = {}, ...kids) => {
    const n = document.createElement(tag);
    for (const [k, v] of Object.entries(attrs)) {
        if (v === null || v === undefined || v === false) continue;
        if (k === 'class') n.className = v; else if (k === 'text') n.textContent = v; else n.setAttribute(k, v === true ? '' : v);
    }
    for (const kid of kids) if (kid !== null && kid !== undefined) n.append(kid);
    return n;
};
const key = () => `${state.type}.${state.mode}`;
const fieldDef = (k) => ({ ...F[k], ...(OVERRIDE[key()]?.[k] || {}) });

function fmtNum(v, d = 3) {
    return Number.isFinite(v) ? v.toFixed(d) : '—';
}

/* ------------------------------------------------------------------ */
/* URL                                                                 */
/* ------------------------------------------------------------------ */

function readUrl() {
    const p = new URLSearchParams(location.search);
    if (MODES[p.get('t')]) state.type = p.get('t');
    if (p.get('mode') === 'b') state.mode = 'b';
    const values = { ...FIELDS[key()] };
    for (const k of Object.keys(values)) if (p.has(k)) values[k] = p.get(k);
    if ('mn' in values && p.has('dp')) { state.unit = 'dp'; values.mn = p.get('dp'); }
    state.values = values;
}

function shareUrl() {
    const p = new URLSearchParams({ t: state.type, mode: state.mode });
    for (const [k, v] of Object.entries(state.values)) {
        if (!isVisible(k)) continue;
        if (k === 'mn' && state.unit === 'dp') p.set('dp', v); else p.set(k, v);
    }
    return `${location.pathname}?${p}`;
}

/* ------------------------------------------------------------------ */
/* Form                                                                */
/* ------------------------------------------------------------------ */

function isVisible(k) {
    return SHOW[k] ? SHOW[k](state.values, state.type) : true;
}

function renderModes() {
    const box = $('#gc-modes');
    box.replaceChildren();
    for (const [m, label] of Object.entries(MODES[state.type])) {
        const id = `gc-mode-${m}`;
        box.append(el('label', { class: 'gc-chip', for: id },
            el('input', { type: 'radio', name: 'gc-mode', id, value: m, checked: m === state.mode }),
            el('span', { text: label })));
    }
}

function renderFields() {
    const form = $('#gc-fields');
    form.replaceChildren();
    for (const [k, v] of Object.entries(state.values)) {
        const f = fieldDef(k);
        const id = `gc-f-${k}`;
        const wrap = el('div', { class: 'gc-field', 'data-key': k });
        const lab = el('label', { for: id }, f.label, f.sym ? el('span', { class: 'gc-sym', text: ` ${f.sym}` }) : null, f.opt && !/blank/.test(f.label) ? el('span', { class: 'gc-opt', text: ' (optional)' }) : null);
        let input;
        if (f.kind === 'select') {
            input = el('select', { id, name: k });
            for (const [val, text] of f.options) input.append(el('option', { value: val, text, selected: String(v) === val }));
        } else {
            input = el('input', { id, name: k, type: 'text', inputmode: f.kind === 'int' ? 'numeric' : 'decimal', autocomplete: 'off', spellcheck: 'false', value: v, 'aria-describedby': `${id}-err` });
            if (f.kind === 'module') input.setAttribute('list', state.unit === 'dp' ? 'gc-dp-list' : 'gc-mod-list');
        }
        const row = el('div', { class: 'gc-input' }, input);
        if (f.kind === 'module') {
            const tg = el('div', { class: 'gc-unit', role: 'group', 'aria-label': 'Module or diametral pitch' });
            for (const [u, t] of [['m', 'Module'], ['dp', 'DP']]) {
                tg.append(el('button', { type: 'button', 'data-unit': u, 'aria-pressed': String(state.unit === u), text: t }));
            }
            row.append(tg);
        } else if (f.unit) {
            row.append(el('span', { class: 'gc-u', text: f.unit }));
        }
        wrap.append(lab, row);
        if (f.help) wrap.append(el('div', { class: 'gc-help', text: f.help }));
        if (f.kind === 'module') wrap.append(el('div', { class: 'gc-help', id: 'gc-mod-eq' }));
        wrap.append(el('div', { class: 'gc-err', id: `${id}-err`, 'aria-live': 'polite' }));
        form.append(wrap);
    }
    if (state.type === 'pair' && state.mode === 'b') {
        $('#gc-f-x1').setAttribute('placeholder', 'suggested');
    }
    syncVisibility();
}

function syncVisibility() {
    for (const w of document.querySelectorAll('#gc-fields .gc-field')) w.hidden = !isVisible(w.dataset.key);
    const eq = $('#gc-mod-eq');
    if (eq) {
        const v = Number(state.values.mn);
        eq.textContent = state.unit === 'dp'
            ? (v > 0 ? `DP ${v} = module ${fmtNum(25.4 / v, 4)} mm (m = 25.4 / DP)` : 'm = 25.4 / DP')
            : (v > 0 ? `≈ DP ${fmtNum(25.4 / v, 3)}` : '');
    }
}

/** Parse visible fields; returns { values } or { errors }. */
function parse() {
    const out = {};
    const errors = {};
    for (const [k, raw] of Object.entries(state.values)) {
        if (!isVisible(k)) continue;
        const f = fieldDef(k);
        if (f.kind === 'select') { out[k] = raw; continue; }
        const s = String(raw).trim().replace(',', '.');
        if (s === '') {
            if (f.opt) { out[k] = null; continue; }
            errors[k] = 'Required.';
            continue;
        }
        const n = Number(s);
        if (!Number.isFinite(n)) { errors[k] = 'Enter a number.'; continue; }
        if (f.kind === 'int' && !Number.isInteger(n)) { errors[k] = k.startsWith('z') ? 'Teeth must be a whole number ≥ 6.' : 'Enter a whole number.'; continue; }
        out[k] = n;
    }
    if ('mn' in out && out.mn !== null && state.unit === 'dp') out.mn = out.mn > 0 ? 25.4 / out.mn : out.mn;
    if ('an' in out) out.an = Number(out.an);
    return { values: out, errors };
}

function snapOption(v) {
    if (!v || v === 'auto') return null;
    if (v.startsWith('dp')) return { module: 25.4 / Number(v.slice(2)), source: 'dp' };
    return { module: Number(v.slice(1)), source: 'module' };
}

/* ------------------------------------------------------------------ */
/* Compute + present                                                   */
/* ------------------------------------------------------------------ */

const R = (name, sym, value, unit, formula, d) => ({ name, sym, value, unit, formula, d: d ?? (unit === '°' ? 4 : unit === 'mm' ? 3 : 4) });

function singleGearGroups(g, suffix = '') {
    const hel = g.beta > 0;
    const s = suffix;
    const na = (k) => g.na[k];
    return [
        { title: 'Geometry', rows: [
            R('Number of teeth', `z${s}`, g.z, '', null, 0),
            R(hel ? 'Normal module' : 'Module', 'mn', g.mn, 'mm', null, 4),
            hel && R('Transverse module', 'mt', g.mt, 'mm', 'mt = mn / cos β', 5),
            R(hel ? 'Normal pressure angle' : 'Pressure angle', 'αn', g.an, '°', null, 2),
            hel && R('Transverse pressure angle', 'αt', g.alphaTDeg, '°', 'αt = atan(tan αn / cos β)'),
            hel && R(`Helix angle (${g.hand})`, 'β', g.beta, '°', null),
            hel && R('Base helix angle', 'βb', g.betaBDeg, '°', 'βb = atan(tan β · cos αt)'),
            hel && R('Lead', 'pz', g.pz, 'mm', 'pz = π·d / tan β'),
            hel && R('Virtual number of teeth', 'zn', g.zn, '', 'zn = z / (cos²βb · cos β)', 3),
            R('Profile shift coefficient', `x${s}`, g.x, '', null),
        ] },
        { title: 'Diameters', rows: [
            R('Reference diameter', `d${s}`, g.d, 'mm', hel ? 'd = z·mt' : 'd = z·m'),
            R('Base diameter', `db${s}`, g.db, 'mm', 'db = d·cos αt'),
            R('Tip diameter', `da${s}`, g.da, 'mm', g.tipReduction ? 'da = d + 2·mn·(1 + x − Δy)' : 'da = d + 2·mn·(1 + x)'),
            R('Root diameter', `df${s}`, g.df, 'mm', 'df = d − 2·mn·(1.25 − x)'),
        ] },
        { title: 'Tooth', rows: [
            R('Addendum', 'ha', g.ha, 'mm', g.tipReduction ? 'ha = mn·(1 + x − Δy)' : 'ha = mn·(1 + x)'),
            R('Dedendum', 'hf', g.hf, 'mm', 'hf = mn·(1.25 − x)'),
            R('Tooth depth', 'h', g.h, 'mm', 'h = (da − df) / 2'),
            R(hel ? 'Normal pitch' : 'Pitch', 'pn', g.pn, 'mm', 'pn = π·mn'),
            hel && R('Transverse pitch', 'pt', g.pt, 'mm', 'pt = π·mt'),
            R('Base pitch (transverse)', 'pb', g.pb, 'mm', 'pb = π·mt·cos αt'),
            R(hel ? 'Normal tooth thickness at d' : 'Tooth thickness at d', 'sn', g.sn, 'mm', 'sn = mn·(π/2 + 2x·tan αn)'),
            hel && R('Transverse tooth thickness at d', 'st', g.st, 'mm', 'st = sn / cos β'),
            R('Tip tooth thickness (transverse)', 'sat', g.sat, 'mm', na('sat') || 'sat = da·(st/d + inv αt − inv αat)'),
            hel && R('Tip tooth thickness (normal, approx.)', 'san', g.san, 'mm', na('san') || 'san ≈ sat·cos βa, βa = atan(tan β·da/d)'),
            R('Minimum shift without undercut', 'x_min', g.xMin, '', 'x_min = 1 − zn·sin²αn / 2'),
        ] },
        { title: 'Measurement', rows: [
            R('Teeth spanned', 'k', g.k, '', na('k') || `k = round(k_th), k_th = ${fmtNum(g.kTh, 3)}`, 0),
            R('Span measurement', `W${g.k ?? 'k'}`, g.Wk, 'mm', na('Wk') || 'Wk = mn·cos αn·[π(k − 0.5) + z·inv αt] + 2x·mn·sin αn'),
            hel && R('Minimum face width for span', 'b_min', g.wkMinFace, 'mm', `b > Wk·sin βb + ${G.SPAN_FACE_MARGIN} mm`),
        ] },
    ].map((grp) => ({ ...grp, rows: grp.rows.filter(Boolean) }));
}

function identificationGroup(r, isBevel) {
    const m = isBevel ? r.mEst : r.mnEst;
    const chosen = isBevel ? r.m : r.mn;
    const rows = [
        R('Estimated module', isBevel ? 'm_est' : 'mn_est', m, 'mm', isBevel ? 'm = da1 / (z1 + 2·cos δ1)' : (r.method === 'da' ? 'mn = da / (z/cos β + 2), x = 0 assumed' : 'mn = (Wk − da·sin αn) / (A − sin αn·(z/cos β + 2))'), 4),
        !isBevel && r.method !== 'da' && R('Estimated profile shift', 'x_est', r.xEst, '', 'x = (da/mn − z/cos β − 2) / 2'),
        R(`Nearest ISO 54 module (${signed(r.nearestModule.deviationPct)} %)`, 'm', r.nearestModule.module, 'mm', 'Deviation of the estimate from this module', 4),
        R(`Nearest DP ${r.nearestDP.dp} (${signed(r.nearestDP.deviationPct)} %)`, 'm', r.nearestDP.module, 'mm', `Metric equivalent 25.4 / ${r.nearestDP.dp}`, 4),
        R('Module used below', isBevel ? 'm' : 'mn', chosen, 'mm', r.snapSource === 'dp' ? 'Imperial (DP) gear' : r.snapSource === 'module' ? 'Metric (ISO 54) gear' : 'Chosen by you', 4),
        !isBevel && R('Profile shift for this module', 'x', r.x, '', r.method === 'da' ? 'x = (da/mn − z/cos β − 2) / 2' : 'x = (Wk/mn − A) / (2·sin αn)'),
        R('Measured tip diameter', 'da', r.daMeasured, 'mm', null),
        R('Theoretical tip diameter', 'da', isBevel ? r.pair.gear1.da : r.daTheory, 'mm', 'Difference shows tip chamfer, shortening or wear'),
    ].filter(Boolean);
    return { title: 'Identification', rows };
}

function signed(v) {
    return (v >= 0 ? '+' : '') + fmtNum(v, 2);
}

function compute(v) {
    const t = state.type, mode = state.mode;
    const res = { groups: [], checks: [], details: [], draw: null, title: '', spec: [] };

    if (t === 'spur' || t === 'helical') {
        const beta = t === 'helical' ? v.beta : 0;
        let g;
        if (mode === 'a') {
            g = G.spurHelical({ z: v.z, mn: v.mn, an: v.an, beta, x: v.x, b: v.bw ?? null, hand: v.hand });
        } else {
            const r = G.identifySpurHelical({ z: v.z, da: v.da, Wk: v.wk, k: v.k, an: v.an, beta, b: v.bw ?? null, hand: v.hand, snap: snapOption(v.snap) });
            g = r.gear;
            res.groups.push(identificationGroup(r, false));
            res.spec.push(`Identified from sample: da = ${fmtNum(v.da)} mm${v.wk ? `, W${v.k} = ${fmtNum(v.wk)} mm` : ''}`);
        }
        res.groups.push(...singleGearGroups(g));
        res.checks = g.checks;
        res.draw = { kind: 'gear', g };
        res.title = `${TYPE_NAMES[t]}, z ${g.z}, mn ${fmtNum(g.mn, 4).replace(/\.?0+$/, '')}`;
        res.spec.unshift(...gearSpec(g));
    } else if (t === 'pair') {
        const p = mode === 'a'
            ? G.gearPair({ z1: v.z1, z2: v.z2, mn: v.mn, an: v.an, beta: v.beta, x1: v.x1, x2: v.x2, b: v.bw ?? null, hand: v.hand })
            : G.gearPairFromCentre({ z1: v.z1, z2: v.z2, mn: v.mn, an: v.an, beta: v.beta, a: v.a, x1: v.x1 ?? null, b: v.bw ?? null, hand: v.hand });
        const hel = p.beta > 0;
        res.groups.push({ title: 'Mesh', rows: [
            R('Gear ratio', 'u', p.u, '', 'u = z2 / z1'),
            R('Reference centre distance', 'a0', p.a0, 'mm', 'a0 = (z1 + z2)·mn / (2·cos β)'),
            R('Centre distance', 'a', p.a, 'mm', mode === 'a' ? 'a = a0·cos αt / cos αwt' : 'Given'),
            R('Sum of profile shifts', 'Σx', p.sumX, '', mode === 'a' ? 'Σx = x1 + x2' : 'Σx = (inv αwt − inv αt)·(z1 + z2) / (2·tan αn)'),
            R('Profile shift, pinion', 'x1', p.x1, '', mode === 'b' ? (p.x1Suggested ? 'Suggested: x1 = Σx/2 + (0.5 − Σx/2)·lg u / lg(zn1·zn2/100)' : `Your value (suggested ${fmtNum(p.suggestedX1, 4)})`) : null),
            R('Profile shift, wheel', 'x2', p.x2, '', mode === 'b' ? 'x2 = Σx − x1' : null),
            hel && R('Transverse pressure angle', 'αt', p.alphaTDeg, '°', 'αt = atan(tan αn / cos β)'),
            R('Operating pressure angle', 'αwt', p.alphaWtDeg, '°', mode === 'a' ? 'inv αwt = 2·tan αn·Σx / (z1 + z2) + inv αt' : 'αwt = acos(a0·cos αt / a)'),
            R('Operating pitch diameter, pinion', 'dw1', p.dw1, 'mm', 'dw1 = 2a·z1 / (z1 + z2)'),
            R('Operating pitch diameter, wheel', 'dw2', p.dw2, 'mm', 'dw2 = 2a·z2 / (z1 + z2)'),
            R('Centre distance modification', 'y', p.y, '', 'y = (a − a0) / mn'),
            R('Tip reduction', 'Δy', p.dy, '', 'Δy = Σx − y'),
            R('Transverse contact ratio', 'εα', p.epsAlpha, '', p.na.epsAlpha || 'εα = [√(ra1² − rb1²) + √(ra2² − rb2²) − a·sin αwt] / (π·mt·cos αt)', 3),
            hel && R('Overlap ratio', 'εβ', p.epsBeta, '', p.na.epsBeta || 'εβ = b·sin β / (π·mn)', 3),
            hel && R('Total contact ratio', 'εγ', p.epsGamma, '', p.na.epsGamma || 'εγ = εα + εβ', 3),
        ].filter(Boolean) });
        res.groups.push({ title: 'Diameters', rows: [
            R('Tip diameter, pinion', 'da1', p.gear1.da, 'mm', 'da1 = d1 + 2mn(1 + x1 − Δy)'),
            R('Tip diameter, wheel', 'da2', p.gear2.da, 'mm', 'da2 = d2 + 2mn(1 + x2 − Δy)'),
            R('Root diameter, pinion', 'df1', p.gear1.df, 'mm', 'df1 = d1 − 2mn(1.25 − x1)'),
            R('Root diameter, wheel', 'df2', p.gear2.df, 'mm', 'df2 = d2 − 2mn(1.25 − x2)'),
        ] });
        res.details = [
            { title: `Gear 1 (pinion) — full table`, groups: singleGearGroups(p.gear1, '1') },
            { title: `Gear 2 (wheel) — full table`, groups: singleGearGroups(p.gear2, '2') },
        ];
        res.checks = p.checks;
        res.draw = { kind: 'pair', p };
        res.title = `${hel ? 'Helical' : 'Spur'} gear pair, z ${p.z1}/${p.z2}, mn ${fmtNum(p.mn, 4).replace(/\.?0+$/, '')}`;
        res.spec.push(...pairSpec(p));
    } else if (t === 'bevel') {
        let p;
        if (mode === 'a') {
            p = G.bevelPair({ m: v.m, z1: v.z1, z2: v.z2, sigma: v.sig, alpha: v.an, b: v.bw ?? null });
        } else {
            const r = G.identifyBevel({ da1: v.da1, z1: v.z1, z2: v.z2, sigma: v.sig, alpha: v.an, b: v.bw ?? null, snap: snapOption(v.snap) });
            p = r.pair;
            res.groups.push(identificationGroup(r, true));
        }
        const g1 = p.gear1, g2 = p.gear2;
        res.groups.push({ title: 'Geometry', rows: [
            R('Outer transverse module', 'm', p.m, 'mm', null, 4),
            R('Gear ratio', 'u', p.u, '', 'u = z2 / z1'),
            R('Shaft angle', 'Σ', p.sigma, '°', null, 2),
            R('Pitch angle, pinion', 'δ1', g1.deltaDeg, '°', 'δ1 = atan(sin Σ / (z2/z1 + cos Σ))'),
            R('Pitch angle, wheel', 'δ2', g2.deltaDeg, '°', 'δ2 = Σ − δ1'),
            R('Outer cone distance', 'Re', p.Re, 'mm', 'Re = d1 / (2·sin δ1)'),
            p.Rm !== null && R('Mean cone distance', 'Rm', p.Rm, 'mm', 'Rm = Re − b/2'),
            R('Dedendum angle', 'θf', p.thetaFDeg, '°', 'θf = atan(hf / Re)'),
            R('Addendum angle', 'θa', p.thetaADeg, '°', 'θa = θf of the mating gear (constant clearance)'),
            R('Tip angle, pinion', 'δa1', g1.deltaADeg, '°', 'δa = δ + θa'),
            R('Tip angle, wheel', 'δa2', g2.deltaADeg, '°', 'δa = δ + θa'),
            R('Root angle, pinion', 'δf1', g1.deltaFDeg, '°', 'δf = δ − θf'),
            R('Root angle, wheel', 'δf2', g2.deltaFDeg, '°', 'δf = δ − θf'),
            R('Virtual teeth, pinion', 'zv1', g1.zv, '', 'zv = z / cos δ', 3),
            R('Virtual teeth, wheel', 'zv2', g2.zv, '', 'zv = z / cos δ', 3),
        ].filter(Boolean) });
        res.groups.push({ title: 'Diameters', rows: [
            R('Reference diameter, pinion', 'd1', g1.d, 'mm', 'd = z·m'),
            R('Reference diameter, wheel', 'd2', g2.d, 'mm', 'd = z·m'),
            R('Outside diameter, pinion', 'da1', g1.da, 'mm', 'da = d + 2·ha·cos δ'),
            R('Outside diameter, wheel', 'da2', g2.da, 'mm', 'da = d + 2·ha·cos δ'),
            R('Crown to pitch apex, pinion', 'X1', g1.crownToApex, 'mm', 'X = Re·cos δ − ha·sin δ'),
            R('Crown to pitch apex, wheel', 'X2', g2.crownToApex, 'mm', 'X = Re·cos δ − ha·sin δ'),
        ] });
        res.groups.push({ title: 'Tooth', rows: [
            R('Addendum (heel)', 'ha', p.ha, 'mm', 'ha = m'),
            R('Dedendum (heel)', 'hf', p.hf, 'mm', 'hf = 1.25·m'),
            R('Tooth depth (heel)', 'h', p.ha + p.hf, 'mm', 'h = 2.25·m'),
            R('Circular pitch (heel)', 'p', Math.PI * p.m, 'mm', 'p = π·m'),
        ] });
        res.checks = p.checks;
        res.draw = { kind: 'bevel', p };
        res.title = `Straight bevel gear pair, z ${p.z1}/${p.z2}, m ${fmtNum(p.m, 4).replace(/\.?0+$/, '')}`;
        res.spec.push(...bevelSpec(p));
    } else if (t === 'worm') {
        const dia = v.dmode === 'd1' ? { d1: v.d1 } : { q: v.q };
        let x2 = v.x2;
        if (mode === 'b') x2 = G.wormShiftFromCentre({ m: v.m, z2: v.z2, ...dia, a: v.a });
        const w = G.wormPair({ m: v.m, z1: v.starts, z2: v.z2, ...dia, an: v.an, x2, mu: v.mu });
        res.groups.push({ title: 'Worm', rows: [
            R('Axial module', 'm', w.m, 'mm', null, 4),
            R('Number of starts', 'z1', w.z1, '', null, 0),
            R('Diameter factor', 'q', w.q, '', v.dmode === 'd1' ? 'q = d1 / m' : null),
            R('Reference diameter', 'd1', w.d1, 'mm', 'd1 = q·m'),
            R('Tip diameter', 'da1', w.da1, 'mm', 'da1 = d1 + 2m'),
            R('Root diameter', 'df1', w.df1, 'mm', 'df1 = d1 − 2.4m'),
            R('Lead angle', 'γ', w.gammaDeg, '°', 'γ = atan(z1 / q)'),
            R('Axial pitch', 'px', w.px, 'mm', 'px = π·m'),
            R('Lead', 'pz', w.pz, 'mm', 'pz = π·m·z1'),
        ] });
        res.groups.push({ title: 'Wheel', rows: [
            R('Number of teeth', 'z2', w.z2, '', null, 0),
            R('Profile shift coefficient', 'x2', w.x2, '', mode === 'b' ? 'x2 = (a − (d1 + d2)/2) / m' : null),
            R('Reference diameter', 'd2', w.d2, 'mm', 'd2 = z2·m'),
            R('Throat (tip) diameter', 'da2', w.da2, 'mm', 'da2 = d2 + 2m(1 + x2)'),
            R('Root diameter', 'df2', w.df2, 'mm', 'df2 = d2 − 2m(1.2 − x2)'),
            R('Outside diameter (approx.)', 'de2', w.de2, 'mm', `de2 ≈ da2 + ${w.z1 === 1 ? '2' : w.z1 <= 3 ? '1.5' : '1'}·m`),
        ] });
        res.groups.push({ title: 'Mesh', rows: [
            R('Ratio', 'i', w.i, '', 'i = z2 / z1'),
            R('Centre distance', 'a', w.a, 'mm', mode === 'b' ? 'Given' : 'a = (d1 + d2)/2 + x2·m'),
            R('Friction angle', 'ρ′', w.rhoPDeg, '°', 'ρ′ = atan(μ / cos αn)'),
            R('Efficiency, worm driving (estimate)', 'η', w.eta * 100, '%', 'η = tan γ / tan(γ + ρ′)', 1),
        ] });
        res.checks = w.checks;
        res.draw = { kind: 'worm', w };
        res.title = `Worm & wheel, m ${fmtNum(w.m, 4).replace(/\.?0+$/, '')}, ${w.z1}/${w.z2}`;
        res.spec.push(...wormSpec(w));
    }
    return res;
}

const line = (label, v, unit = '', d = 3) => `${label}: ${typeof v === 'number' ? fmtNum(v, d) : v}${unit ? ` ${unit}` : ''}`;

function gearSpec(g, prefix = '') {
    const hel = g.beta > 0;
    return [
        line(`${prefix}Type`, hel ? `Helical gear, ${g.hand}` : 'Spur gear'),
        line(`${prefix}Number of teeth z`, String(g.z)),
        line(`${prefix}${hel ? 'Normal module mn' : 'Module m'}`, g.mn, 'mm', 4),
        line(`${prefix}Pressure angle αn`, g.an, '°', 1),
        hel && line(`${prefix}Helix angle β`, g.beta, '°', 4),
        line(`${prefix}Profile shift x`, g.x, '', 4),
        line(`${prefix}Reference diameter d`, g.d, 'mm'),
        line(`${prefix}Tip diameter da`, g.da, 'mm'),
        line(`${prefix}Root diameter df`, g.df, 'mm'),
        g.Wk !== null && line(`${prefix}Span W${g.k}`, g.Wk, 'mm'),
        g.b !== null && line(`${prefix}Face width b`, g.b, 'mm', 1),
    ].filter(Boolean);
}
function pairSpec(p) {
    return [
        line('Centre distance a', p.a, 'mm'),
        line('Ratio u', p.u, '', 4),
        line('Operating pressure angle αwt', p.alphaWtDeg, '°', 4),
        p.epsAlpha !== null && line('Contact ratio εα', p.epsAlpha, ''),
        '', 'Gear 1 (pinion)', ...gearSpec(p.gear1, '  '),
        '', 'Gear 2 (wheel)', ...gearSpec(p.gear2, '  '),
    ].filter((s) => s !== false);
}
function bevelSpec(p) {
    return [
        'Type: Straight bevel gear pair (equal addendum)',
        line('Module m (outer)', p.m, 'mm', 4),
        line('Teeth z1 / z2', `${p.z1} / ${p.z2}`),
        line('Shaft angle Σ', p.sigma, '°', 2),
        line('Pressure angle α', p.alpha, '°', 1),
        line('Pitch angles δ1 / δ2', `${fmtNum(p.gear1.deltaDeg, 4)} / ${fmtNum(p.gear2.deltaDeg, 4)}`, '°'),
        line('Outside diameters da1 / da2', `${fmtNum(p.gear1.da)} / ${fmtNum(p.gear2.da)}`, 'mm'),
        line('Outer cone distance Re', p.Re, 'mm'),
        p.b !== null && line('Face width b', p.b, 'mm', 1),
    ].filter(Boolean);
}
function wormSpec(w) {
    return [
        'Type: Cylindrical worm and wheel',
        line('Axial module m', w.m, 'mm', 4),
        line('Worm starts z1 / wheel teeth z2', `${w.z1} / ${w.z2}`),
        line('Diameter factor q', w.q, '', 4),
        line('Worm reference diameter d1', w.d1, 'mm'),
        line('Lead angle γ', w.gammaDeg, '°', 4),
        line('Pressure angle αn', w.an, '°', 1),
        line('Wheel profile shift x2', w.x2, '', 4),
        line('Centre distance a', w.a, 'mm'),
        line('Wheel throat diameter da2', w.da2, 'mm'),
    ];
}

/* ------------------------------------------------------------------ */
/* Rendering                                                           */
/* ------------------------------------------------------------------ */

function table(group) {
    const t = el('table', { class: 'gc-table' });
    t.append(el('caption', { text: group.title }));
    const tb = el('tbody');
    for (const r of group.rows) {
        const val = typeof r.value === 'number' && Number.isFinite(r.value) ? fmtNum(r.value, r.d) : '—';
        const name = el('th', { scope: 'row' }, el('span', { text: r.name }));
        if (r.formula) name.append(el('span', { class: 'gc-formula', text: r.formula }));
        tb.append(el('tr', {}, name, el('td', { class: 'gc-sym-c', text: r.sym }), el('td', { class: 'gc-val' }, val, r.unit && val !== '—' ? el('span', { class: 'gc-unit-c', text: ` ${r.unit}` }) : null)));
    }
    t.append(tb);
    return t;
}

function renderResults(res) {
    $('#gc-title').textContent = res.title;
    const checks = $('#gc-checks');
    checks.replaceChildren(...res.checks.map((c) => el('li', { class: `gc-check is-${c.status}` },
        el('span', { class: 'gc-pill', text: c.status === 'ok' ? 'OK' : c.status === 'warn' ? 'Check' : 'Problem' }),
        el('span', {}, el('b', { text: c.label }), ` — ${c.message}`))));
    const box = $('#gc-tables');
    box.replaceChildren(...res.groups.map(table));
    for (const d of res.details) {
        const det = el('details', { class: 'gc-details' }, el('summary', { text: d.title }));
        d.groups.forEach((g) => det.append(table(g)));
        box.append(det);
    }
    draw(res.draw, res.title);
    const url = SITE + shareUrl();
    const spec = [`${res.title}`, `Calculated with the PT Misuba Guna Indonesia gear calculator`, '', ...res.spec, '', `Link: ${url}`].join('\n');
    $('#gc-spec').textContent = spec;
    const subject = `Quote request: ${res.title}`;
    $('#gc-mail').href = `mailto:${SALES_EMAIL}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(`Please quote for the following gear.\nQuantity: \nMaterial: \n\n${spec}`)}`;
    $('#gc-wa').href = `https://wa.me/${WA_NUMBER}?text=${encodeURIComponent(`Hello Misuba, please quote for this gear.\n\n${spec}`)}`;
    $('#gc-form').href = `/request-a-quote/?item=${encodeURIComponent(res.title)}&message=${encodeURIComponent(spec)}`;
}

function showError(errors, general) {
    for (const e of document.querySelectorAll('#gc-fields .gc-err')) e.textContent = '';
    for (const i of document.querySelectorAll('#gc-fields [aria-invalid]')) i.removeAttribute('aria-invalid');
    for (const [k, msg] of Object.entries(errors)) {
        const e = $(`#gc-f-${k}-err`);
        if (e) { e.textContent = msg; $(`#gc-f-${k}`).setAttribute('aria-invalid', 'true'); }
    }
    const g = $('#gc-general');
    g.textContent = general || '';
    g.hidden = !general;
    $('#gc-results').classList.toggle('is-stale', Boolean(general || Object.keys(errors).length));
}

// Maps math-module field names to form keys.
const FIELD_ALIAS = { b: 'bw', Wk: 'wk', alpha: 'an', sigma: 'sig', z1: state.type === 'worm' ? 'starts' : 'z1' };

function update() {
    syncVisibility();
    history.replaceState(null, '', shareUrl());
    const { values, errors } = parse();
    if (Object.keys(errors).length) {
        showError(errors, 'Results show the last valid input. Correct the highlighted field.');
        return;
    }
    try {
        const res = compute(values);
        showError({}, '');
        renderResults(res);
    } catch (e) {
        if (!(e instanceof G.GearInputError)) throw e;
        let k = FIELD_ALIAS[e.field] || e.field;
        if (state.type === 'worm' && e.field === 'z1') k = 'starts';
        if (k && k in state.values && isVisible(k)) showError({ [k]: e.message }, 'Results show the last valid input. Correct the highlighted field.');
        else showError({}, e.message);
    }
}

/* ------------------------------------------------------------------ */
/* Drawings and downloads                                              */
/* ------------------------------------------------------------------ */

const FOOTER = [
    'Units: mm. Scale 1:1. Generated by the PT Misuba Guna Indonesia gear calculator, misubaguna.com/toolkit/gear-calculator/',
    'Involute flanks are exact; the root is simplified (no trochoidal fillet). Check the drawing before manufacturing.',
];
let current = null; // { views, title } of the last valid result, for the downloads

function viewsFor(d) {
    if (d.kind === 'gear') return [D.gearView(d.g, { notes: gearSpec(d.g) })];
    if (d.kind === 'pair') {
        const p = d.p;
        const asm = D.pairView(p, { title: 'Assembly', notes: pairSpec(p).slice(0, 4) });
        // same text height on every view of the exported sheet
        const single = (g, t) => ({ ...D.gearView(g, { title: t, notes: gearSpec(g), fs: asm.fs * 0.75 }), screen: false });
        return [D.pairCloseUp(p), asm, single(p.gear1, 'Gear 1 (pinion)'), single(p.gear2, 'Gear 2 (wheel)')];
    }
    if (d.kind === 'bevel') return [D.bevelView(d.p, { notes: bevelSpec(d.p) })];
    return [D.wormView(d.w, { notes: wormSpec(d.w) })];
}

function draw(d, title) {
    const legend = $('#gc-legend');
    const note = $('#gc-draw-note');
    legend.replaceChildren();
    if (!d) return;
    const views = viewsFor(d);
    current = { views, title };
    const box = $('#gc-views');
    box.innerHTML = views.filter((v) => v.screen !== false)
        .map((v, i) => `<div class="gc-svgbox">${D.toSvg(v, { label: v.title || title, clipId: `gc-clip-${i}` })}</div>`).join('');
    const leg = (cls, label) => legend.append(el('li', {}, el('span', { class: `gc-swatch ${cls}` }), label));
    const bNote = (v) => (v.faceWidthAssumed ? ` Face width not entered: side view drawn with b = ${fmtNum(v.faceWidth, 0)} mm.` : '');

    if (d.kind === 'gear') {
        const g = d.g;
        leg('s-tip', `Tip da ${fmtNum(g.da)}`);
        leg('s-ref', `Reference d ${fmtNum(g.d)}`);
        leg('s-base', `Base db ${fmtNum(g.db)}`);
        leg('s-root', `Root df ${fmtNum(g.df)}`);
        if (g.beta > 0) leg('s-helix', `Tooth traces, β ${fmtNum(g.beta, 2)}° ${g.hand}`);
        note.textContent = `Front view: true involute${g.beta > 0 ? ' in the transverse plane (mt, αt)' : ''}, simplified root. Side view: face width b${g.beta > 0 ? ' and tooth direction' : ''}.${bNote(views[0])}`;
    } else if (d.kind === 'pair') {
        const p = d.p;
        leg('s-ref', `Operating pitch circles dw1 ${fmtNum(p.dw1)}, dw2 ${fmtNum(p.dw2)}`);
        leg('s-loa', `Line of action, αwt ${fmtNum(p.alphaWtDeg, 3)}°`);
        if (p.beta > 0) leg('s-helix', `Tooth traces, gear 1 ${p.hand}, gear 2 ${p.hand2}`);
        note.textContent = `Top: close-up at the pitch point P, zero backlash. Below: both gears at centre distance a, with side views.${bNote(views[1])}`;
    } else if (d.kind === 'bevel') {
        leg('s-pitch', 'Pitch cones');
        leg('s-gear-sw', `Tooth section, b ${fmtNum(views[0].faceWidth, 2)}${views[0].faceWidthAssumed ? ' (Re/3, drawing only)' : ''}`);
        note.textContent = 'Schematic axial section: pitch cones, tip and root lines over the face width (equal addendum, constant clearance).';
    } else {
        const w = d.w;
        leg('s-ref', 'Reference diameters d1, d2');
        leg('s-dim', `Centre distance a ${fmtNum(w.a)}`);
        note.textContent = 'Schematic: worm in axial section over the wheel (throat diameter shaded).';
    }
}

function fileBase(title) {
    return title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'gear';
}

function download(name, type, content) {
    const url = URL.createObjectURL(new Blob([content], { type }));
    const a = el('a', { href: url, download: name });
    document.body.append(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
}

function downloadDrawing(format) {
    if (!current) return;
    const sheet = D.sheet(current.views, { title: current.title, footer: FOOTER });
    const base = fileBase(current.title);
    if (format === 'dxf') download(`${base}.dxf`, 'application/dxf', toDxf(sheet, { title: current.title }));
    else download(`${base}.svg`, 'image/svg+xml', D.toSvg(sheet, { standalone: true, label: current.title }));
}

/* ------------------------------------------------------------------ */
/* Events                                                              */
/* ------------------------------------------------------------------ */

function switchTo(type, mode) {
    store[key()] = { values: state.values, unit: state.unit };
    state.type = type;
    state.mode = mode;
    const saved = store[key()];
    state.values = saved ? saved.values : { ...FIELDS[key()] };
    state.unit = saved ? saved.unit : 'm';
    FIELD_ALIAS.z1 = type === 'worm' ? 'starts' : 'z1';
    for (const r of document.querySelectorAll('input[name="gc-type"]')) r.checked = r.value === type;
    renderModes();
    renderFields();
    $('#gc-note-identify').hidden = !((type === 'spur' || type === 'helical') && mode === 'b');
    $('#gc-note-bevel').hidden = type !== 'bevel';
    update();
}

function init() {
    readUrl();
    FIELD_ALIAS.z1 = state.type === 'worm' ? 'starts' : 'z1';
    document.querySelectorAll('input[name="gc-type"]').forEach((r) => r.addEventListener('change', () => switchTo(r.value, 'a')));
    $('#gc-modes').addEventListener('change', (e) => { if (e.target.name === 'gc-mode') switchTo(state.type, e.target.value); });
    $('#gc-fields').addEventListener('input', (e) => {
        if (!e.target.name) return;
        state.values[e.target.name] = e.target.value;
        update();
    });
    $('#gc-fields').addEventListener('change', (e) => {
        if (e.target.tagName === 'SELECT') { state.values[e.target.name] = e.target.value; update(); }
    });
    $('#gc-fields').addEventListener('click', (e) => {
        const b = e.target.closest('[data-unit]');
        if (!b || b.dataset.unit === state.unit) return;
        const v = Number(state.values.mn);
        state.unit = b.dataset.unit;
        if (v > 0) state.values.mn = String(Number((25.4 / v).toFixed(4)));
        document.querySelectorAll('[data-unit]').forEach((x) => x.setAttribute('aria-pressed', String(x.dataset.unit === state.unit)));
        $('#gc-f-mn').value = state.values.mn;
        $('#gc-f-mn').setAttribute('list', state.unit === 'dp' ? 'gc-dp-list' : 'gc-mod-list');
        update();
    });
    $('#gc-copy').addEventListener('click', async () => {
        const txt = $('#gc-spec').textContent;
        const status = $('#gc-copy-status');
        try {
            await navigator.clipboard.writeText(txt);
            status.textContent = 'Specification copied.';
        } catch {
            const r = document.createRange();
            r.selectNodeContents($('#gc-spec'));
            getSelection().removeAllRanges();
            getSelection().addRange(r);
            $('#gc-spec-box').open = true;
            status.textContent = 'Press Ctrl+C to copy the selected text.';
        }
        setTimeout(() => { status.textContent = ''; }, 4000);
    });
    document.querySelectorAll('[data-download]').forEach((b) => b.addEventListener('click', () => downloadDrawing(b.dataset.download)));
    $('#gc-app').hidden = false;
    // switchTo() stores the current (URL) values under this type.mode first, so they survive.
    switchTo(state.type, state.mode);
}

init();
