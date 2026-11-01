// Unit tests for public/js/gear-drawing.js and gear-dxf.js. Run: npm run test:js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spurHelical, gearPair, bevelPair, wormPair } from '../../public/js/gear-math.js';
import { gearView, pairView, pairCloseUp, bevelView, wormView, sheet, toSvg, LAYERS } from '../../public/js/gear-drawing.js';
import { toDxf, dxfText } from '../../public/js/gear-dxf.js';

const near = (actual, expected, tol = 1e-6, label = '') =>
    assert.ok(Math.abs(actual - expected) <= tol, `${label} expected ${expected} ± ${tol}, got ${actual}`);

const sideRect = (v, layer = 'GEAR') => v.ents.find((e) => e.t === 'poly' && e.layer === layer && e.pts.length === 4);
const helix = (v) => v.ents.filter((e) => e.layer === 'HELIX');

test('side view: rectangle is face width × tip diameter', () => {
    const g = spurHelical({ z: 30, mn: 3, x: 0, b: 30 });
    const v = gearView(g);
    const [p0, p1, p2] = sideRect(v).pts;
    near(p1[0] - p0[0], 30, 1e-9, 'b');
    near(p2[1] - p1[1], g.da, 1e-9, 'da');
    assert.equal(helix(v).length, 0, 'spur: no tooth traces');
    assert.equal(v.faceWidthAssumed, false);
});

test('side view: helical tooth traces follow β and hand', () => {
    const slopes = (hand) => {
        const g = spurHelical({ z: 30, mn: 3, beta: 15, hand, b: 40 });
        const v = gearView(g);
        const lines = helix(v);
        assert.ok(lines.length > 10, 'traces drawn');
        // trace crossing the axis: slope there equals tan β
        const r = g.d / 2;
        let best = null;
        for (const l of lines) for (let i = 1; i < l.pts.length; i++) {
            const [a, b] = [l.pts[i - 1], l.pts[i]];
            if (!best || Math.abs(a[1]) < Math.abs(best[0][1])) best = [a, b];
            for (const [, y] of l.pts) assert.ok(Math.abs(y) <= r + 1e-9);
        }
        return (best[1][1] - best[0][1]) / (best[1][0] - best[0][0]);
    };
    const rh = slopes('RH'), lh = slopes('LH');
    near(rh, -Math.tan(15 * Math.PI / 180), 0.01, 'RH slope ("\\")');
    near(lh, Math.tan(15 * Math.PI / 180), 0.01, 'LH slope ("/")');
});

test('blank face width is drawn with an assumed value', () => {
    const v = gearView(spurHelical({ z: 30, mn: 3 }));
    assert.equal(v.faceWidthAssumed, true);
    assert.equal(v.faceWidth, 30);
});

test('pair: assembly at centre distance, close-up stays on screen only', () => {
    const p = gearPair({ z1: 17, z2: 53, mn: 4, beta: 12, x1: 0.3, x2: 0.2, b: 40 });
    const asm = pairView(p);
    const r2 = sideRect(asm, 'GEAR2');
    near((r2.pts[0][1] + r2.pts[2][1]) / 2, p.a, 1e-9, 'gear 2 axis at a');
    const close = pairCloseUp(p);
    const s = sheet([close, asm]);
    assert.ok(!s.ents.some((e) => e.layer === 'LOA'), 'close-up not exported');
});

test('DXF: valid R12 group structure, ASCII only, all layers declared', () => {
    const g = spurHelical({ z: 30, mn: 3, beta: 15, b: 40 });
    const dxf = toDxf(sheet([gearView(g, { notes: ['Helix angle β: 15°', 'Tip diameter ⌀da'] })], { title: 'Helical gear', footer: ['Units: mm'] }));
    const lines = dxf.split('\r\n');
    assert.equal(lines.pop(), '');
    assert.equal(lines.length % 2, 0, 'code/value pairs');
    for (let i = 0; i < lines.length; i += 2) assert.match(lines[i], /^\d+$/, `group code at line ${i + 1}`);
    assert.match(dxf, /^[\x20-\x7e\r\n]*$/, 'ASCII only');
    assert.ok(!dxf.includes('NaN') && !dxf.includes('Infinity'));
    assert.deepEqual(lines.slice(-2), ['0', 'EOF']);
    assert.ok(dxf.includes('\r\n$ACADVER\r\n1\r\nAC1009\r\n'));
    const used = new Set();
    for (let i = 0; i < lines.length; i += 2) if (lines[i] === '8') used.add(lines[i + 1]);
    for (const l of used) assert.ok(l in LAYERS, `layer ${l} declared`);
    assert.ok(dxf.includes('Helix angle beta: 15%%d'));
});

test('DXF: gear outline is closed and lies between root and tip circle', () => {
    const g = spurHelical({ z: 18, mn: 2, x: 0.3, b: 20 });
    const s = sheet([gearView(g)]);
    const dxf = toDxf(s).split('\r\n');
    const i = dxf.indexOf('POLYLINE');
    assert.equal(dxf[i + 2], 'GEAR');
    const flag = dxf.indexOf('70', i);
    assert.equal(dxf[flag + 1], '1', 'closed');
    const outline = s.ents.find((e) => e.t === 'poly' && e.layer === 'GEAR' && e.pts.length > 100);
    // the first view is placed with its left edge at x = 0, so find the gear centre from the reference circle
    const ref = s.ents.find((e) => e.t === 'circle' && e.layer === 'REF');
    for (const [x, y] of outline.pts) {
        const r = Math.hypot(x - ref.c[0], y - ref.c[1]);
        assert.ok(r >= g.df / 2 - 1e-6 && r <= g.da / 2 + 1e-6, `r=${r}`);
    }
});

test('dxfText maps symbols to ASCII', () => {
    assert.equal(dxfText('⌀96 · 20° ±0.1 αn β'), '%%c96 * 20%%d %%p0.1 alphan beta');
    assert.equal(dxfText('a — b → c'), 'a - b -> c');
});

test('every type exports SVG and DXF without NaN', () => {
    const views = [
        gearView(spurHelical({ z: 12, mn: 1.5, x: 0.4 })),
        pairView(gearPair({ z1: 20, z2: 40, mn: 2 })),
        bevelView(bevelPair({ m: 5, z1: 20, z2: 40, b: 30 })),
        wormView(wormPair({ m: 4, z1: 2, z2: 40, q: 10 })),
    ];
    for (const v of views) {
        const s = sheet([v], { title: 'T' });
        const svg = toSvg(s, { standalone: true });
        assert.match(svg, /width="[\d.]+mm"/);
        assert.ok(!svg.includes('NaN'), 'svg');
        assert.ok(!toDxf(s).includes('NaN'), 'dxf');
    }
});
