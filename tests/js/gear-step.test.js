// Unit tests for public/js/gear-step.js. Run: npm run test:js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import { spurHelical, gearPair, inv, rad } from '../../public/js/gear-math.js';
import { profileSegments, gearSolid, facePoint, curvePoint, toStep } from '../../public/js/gear-step.js';

const wrap = (a) => Math.atan2(Math.sin(a), Math.cos(a));

/** Distance (mm) of a transverse point from the exact involute flank of tooth c. */
function flankError(g, [x, y], c, side) {
    const r = Math.hypot(x, y);
    const psi = g.st / g.d + inv(rad(g.alphaTDeg)) - inv(Math.acos(Math.min(1, g.db / 2 / r)));
    return r * Math.abs(wrap(Math.atan2(y, x) - (c + side * psi)));
}

test('flank B-splines stay within 0.5 µm of the exact involute', () => {
    for (const g of [spurHelical({ z: 30, mn: 3 }), spurHelical({ z: 8, mn: 1 }), spurHelical({ z: 150, mn: 4, beta: 20, x: 0.5 })]) {
        const sol = gearSolid(g, { b: 20 });
        const flanks = sol.segs.map((s, k) => [s, k]).filter(([s]) => s.kind === 'flank').slice(0, 2);
        flanks.forEach(([seg, k], side) => {
            for (let i = 0; i <= 200; i++) {
                const [x, y] = facePoint(sol, k, i / 200, 0);
                assert.ok(flankError(g, [x, y], Math.PI / 2, side ? 1 : -1) < 5e-4, `z=${g.z} flank ${side} at ${i}`);
            }
            assert.equal(seg.u.p, 3);
        });
    }
});

test('root and tip lands lie on their circles', () => {
    const g = spurHelical({ z: 18, mn: 2, x: 0.3 });
    const sol = gearSolid(g, { b: 10 });
    for (const [kind, r] of [['root', g.df / 2], ['tip', g.da / 2]]) {
        const k = sol.segs.findIndex((s) => s.kind === kind);
        for (let i = 0; i <= 50; i++) {
            const [x, y] = facePoint(sol, k, i / 50, 0);
            assert.ok(Math.abs(Math.hypot(x, y) - r) < 1e-4, `${kind} r`);
        }
    }
});

test('profile is closed and counter-clockwise, segments share end points', () => {
    const segs = profileSegments(spurHelical({ z: 8, mn: 1 }));
    assert.ok(segs.some((s) => s.kind === 'foot'), 'root inside base circle gives radial feet');
    segs.forEach((s, k) => assert.equal(s.pts[0], segs[(k + segs.length - 1) % segs.length].pts.at(-1)));
    let a = 0;
    const pts = segs.flatMap((s) => s.pts.slice(1));
    pts.forEach(([x1, y1], i) => { const [x2, y2] = pts[(i + 1) % pts.length]; a += x1 * y2 - x2 * y1; });
    assert.ok(a > 0);
});

test('pointed tooth drops the tip land', () => {
    const segs = profileSegments(spurHelical({ z: 10, mn: 2, x: 1.2 }));
    assert.ok(!segs.some((s) => s.kind === 'tip'));
});

test('helical faces follow the helix: twist sign by hand, exact on mid sections', () => {
    for (const hand of ['RH', 'LH']) {
        const g = spurHelical({ z: 20, mn: 2, beta: 25, hand, b: 50 });
        const sol = gearSolid(g, { b: 50 });
        const expect = (hand === 'RH' ? 1 : -1) * 50 * Math.tan(rad(25)) / (g.d / 2);
        assert.ok(Math.abs(sol.twist - expect) < 1e-12);
        // seam from bottom to top turns by the twist (counter-clockwise for RH)
        const col = sol.segs[0].net[0];
        const turn = wrap(Math.atan2(col.at(-1)[1], col.at(-1)[0]) - Math.atan2(col[0][1], col[0][0]));
        assert.ok(Math.abs(turn - expect) < 1e-9, `${hand} turn`);
        // a point halfway up a flank, turned back, lies on the transverse involute
        const k = sol.segs.findIndex((s) => s.kind === 'flank');
        for (const w of [0.13, 0.5, 0.77]) {
            const [x, y, zz] = facePoint(sol, k, 0.4, w);
            assert.ok(Math.abs(zz - 50 * w) < 1e-9, 'z linear');
            const back = -sol.twist * zz / 50;
            const p = [x * Math.cos(back) - y * Math.sin(back), x * Math.sin(back) + y * Math.cos(back)];
            assert.ok(flankError(g, p, Math.PI / 2, -1) < 5e-4, `${hand} w=${w}`);
        }
    }
});

test('face width is required', () => {
    assert.throws(() => gearSolid(spurHelical({ z: 20, mn: 2 }), { b: null }), /Face width/);
});

test('STEP file: Part 21 structure, mm units, one solid per gear', () => {
    const p = gearPair({ z1: 17, z2: 53, mn: 4, beta: 12, x1: 0.3, x2: 0.2, b: 40 });
    const solids = [
        gearSolid(p.gear1, { b: 40, hand: p.hand, name: 'Gear 1 z17' }),
        gearSolid(p.gear2, { b: 40, center: [0, p.a], rotation: -Math.PI / 2 + Math.PI / 53, hand: p.hand2, name: 'Gear 2 z53' }),
    ];
    const s = toStep(solids, { name: 'Helical gear pair z 17/53', description: 'β 12° · mn 4', stamp: '2026-11-02T00:00:00' });
    assert.match(s, /^ISO-10303-21;\r\nHEADER;/);
    assert.ok(s.endsWith('END-ISO-10303-21;\r\n'));
    assert.match(s, /^[\x20-\x7e\r\n]*$/, 'ASCII only');
    assert.ok(!/NaN|Infinity|undefined/.test(s));
    assert.ok(s.includes("FILE_SCHEMA(('AUTOMOTIVE_DESIGN { 1 0 10303 214 1 1 1 1 }'));"));
    assert.ok(s.includes('SI_UNIT(.MILLI.,.METRE.)'));
    assert.equal(s.match(/MANIFOLD_SOLID_BREP\(/g).length, 2);
    assert.ok(s.includes("MANIFOLD_SOLID_BREP('Gear 2 z53'"));
    assert.ok(s.includes("'beta 12 deg x mn 4'"));
    // entity ids are sequential and every reference resolves
    const ids = [...s.matchAll(/^#(\d+)=/gm)].map((m) => +m[1]);
    ids.forEach((v, i) => assert.equal(v, i + 1));
    for (const [, r] of s.matchAll(/#(\d+)(?!=)/g)) assert.ok(+r <= ids.length);
    // every edge is used once in each direction (closed, consistently oriented shells)
    const uses = new Map();
    for (const [, e, sense] of s.matchAll(/ORIENTED_EDGE\('',\*,\*,(#\d+),\.(T|F)\.\)/g)) {
        const u = uses.get(e) ?? { T: 0, F: 0 };
        u[sense]++;
        uses.set(e, u);
    }
    for (const [e, u] of uses) assert.deepEqual(u, { T: 1, F: 1 }, e);
});

test('reals are written in STEP form', () => {
    const s = toStep([gearSolid(spurHelical({ z: 12, mn: 1 }), { b: 5 })], { stamp: 'x' });
    const reals = [...s.matchAll(/CARTESIAN_POINT\('',\(([^)]*)\)\)/g)].flatMap((m) => m[1].split(','));
    for (const r of reals) assert.match(r, /^-?\d+\.\d*(E-?\d+)?$/);
    const ctrl = gearSolid(spurHelical({ z: 12, mn: 1 }), { b: 5 }).segs[0];
    assert.equal(curvePoint({ p: ctrl.u.p, U: ctrl.u.U, ctrl: ctrl.net.map((c) => c[0]) }, 0)[2], 0);
});
