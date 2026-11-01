// Unit tests for public/js/gear-math.js. Run: npm run test:js
import { test } from 'node:test';
import assert from 'node:assert/strict';
import {
    inv, invInverse, rad, deg, spurHelical, spanLength, identifySpurHelical, gearPair,
    gearPairFromCentre, sumShiftFromCentre, bevelPair, identifyBevel, wormPair,
    wormShiftFromCentre, gearOutline, nearestModule, nearestDP, GearInputError,
} from '../../public/js/gear-math.js';

const near = (actual, expected, tol = 1e-3, label = '') =>
    assert.ok(Math.abs(actual - expected) <= tol, `${label} expected ${expected} ± ${tol}, got ${actual}`);

test('1. spur m=3 z=30 αn=20° x=0', () => {
    const g = spurHelical({ z: 30, mn: 3, an: 20, x: 0 });
    near(g.d, 90, 1e-3, 'd');
    near(g.da, 96, 1e-3, 'da');
    near(g.df, 82.5, 1e-3, 'df');
    near(g.db, 84.572, 1e-3, 'db');
    assert.equal(g.k, 4);
    near(g.Wk, 32.258, 1e-3, 'W4');
});

test('2. identify z=30 αn=20° β=0 da=97.2 W4=32.668 → mn 3.000, x 0.200', () => {
    const r = identifySpurHelical({ z: 30, da: 97.2, Wk: 32.668, k: 4, an: 20, beta: 0 });
    near(r.mnEst, 3.0, 0.005, 'mn_est');
    assert.equal(r.mn, 3);
    near(r.x, 0.2, 0.005, 'x (snapped, from Wk)');
});

test('2b. identify with exact span: closed form recovers mn and x directly', () => {
    const Wk = spanLength({ z: 30, mn: 3, an: 20, x: 0.2, k: 4 });
    const r = identifySpurHelical({ z: 30, da: 97.2, Wk, k: 4, an: 20 });
    near(r.mnEst, 3, 1e-9, 'mn_est');
    near(r.xEst, 0.2, 1e-9, 'x_est');
});

test('2c. identify from da only assumes x = 0', () => {
    const r = identifySpurHelical({ z: 30, da: 96, an: 20 });
    near(r.mnEst, 3, 1e-9);
    assert.equal(r.method, 'da');
    near(r.x, 0, 1e-9);
});

test('2d. nearest module / DP', () => {
    assert.equal(nearestModule(3.02).module, 3);
    assert.equal(nearestDP(3.17).dp, 8); // 25.4/8 = 3.175
    const r = identifySpurHelical({ z: 30, da: 25.4 / 8 * 32, an: 20 });
    assert.equal(r.snapSource, 'dp');
    near(r.mn, 3.175, 1e-9);
});

test('3. pair z1=20 z2=40 m=2 x1=x2=0 → a=60', () => {
    const p = gearPair({ z1: 20, z2: 40, mn: 2, an: 20, beta: 0, x1: 0, x2: 0 });
    near(p.a, 60, 1e-3, 'a');
    near(p.alphaWtDeg, 20, 1e-9, 'αwt');
    near(p.dy, 0, 1e-9, 'Δy');
});

test('4. round trip z1=17 z2=53 mn=4 β=12° Σx=0.5', () => {
    const p = gearPair({ z1: 17, z2: 53, mn: 4, an: 20, beta: 12, x1: 0.3, x2: 0.2 });
    const sumX = sumShiftFromCentre({ z1: 17, z2: 53, mn: 4, an: 20, beta: 12, a: p.a });
    near(sumX, 0.5, 1e-6, 'Σx');
    const q = gearPairFromCentre({ z1: 17, z2: 53, mn: 4, an: 20, beta: 12, a: p.a });
    near(q.x1 + q.x2, 0.5, 1e-6, 'x1 + x2');
    assert.ok(q.x1 > q.x2, 'pinion gets more shift');
});

test('5. inv⁻¹(inv α) = α for α in 5°…45°', () => {
    for (let a = 5; a <= 45; a += 0.5) {
        near(invInverse(inv(rad(a))), rad(a), 1e-9, `α=${a}°`);
    }
});

test('6. helical mn=3 z=30 αn=20° β=15° x=0', () => {
    const g = spurHelical({ z: 30, mn: 3, an: 20, beta: 15, x: 0 });
    near(g.mt, 3.10583, 1e-3, 'mt');
    near(g.alphaTDeg, 20.6469, 1e-3, 'αt');
    near(g.d, 93.175, 1e-3, 'd');
});

test('7. bevel m=5 z1=20 z2=40 Σ=90°', () => {
    const b = bevelPair({ m: 5, z1: 20, z2: 40, sigma: 90 });
    near(b.gear1.deltaDeg, 26.565, 1e-3, 'δ1');
    near(b.gear2.deltaDeg, 63.435, 1e-3, 'δ2');
    near(b.Re, 111.803, 1e-3, 'Re');
});

test('7b. bevel identify from da1 recovers m', () => {
    const da1 = bevelPair({ m: 5, z1: 20, z2: 40 }).gear1.da;
    const r = identifyBevel({ da1, z1: 20, z2: 40 });
    near(r.mEst, 5, 1e-9);
    assert.equal(r.m, 5);
});

test('8. worm m=4 z1=2 q=10 z2=40 x2=0', () => {
    const w = wormPair({ m: 4, z1: 2, z2: 40, q: 10, x2: 0 });
    near(w.d1, 40, 1e-3, 'd1');
    near(w.d2, 160, 1e-3, 'd2');
    near(w.a, 100, 1e-3, 'a');
    near(w.gammaDeg, 11.310, 1e-3, 'γ');
    near(wormShiftFromCentre({ m: 4, z2: 40, q: 10, a: 102 }), 0.5, 1e-12, 'x2 from a');
});

test('checks: undercut, pointed tip, contact ratio', () => {
    const small = spurHelical({ z: 10, mn: 2, x: 0 });
    assert.equal(small.checks.find((c) => c.id === 'undercut').status, 'warn');
    const pointed = spurHelical({ z: 10, mn: 2, x: 1.2 });
    assert.equal(pointed.checks.find((c) => c.id === 'tip').status, 'error');
    const p = gearPair({ z1: 20, z2: 40, mn: 2 });
    assert.equal(p.checks.find((c) => c.id === 'eps').status, 'ok');
});

test('invalid input throws GearInputError with the field', () => {
    assert.throws(() => spurHelical({ z: 5.5, mn: 3 }), (e) => e instanceof GearInputError && e.field === 'z');
    assert.throws(() => spurHelical({ z: 30, mn: 3, beta: 45 }), GearInputError);
    assert.throws(() => sumShiftFromCentre({ z1: 20, z2: 40, mn: 2, a: 50 }), GearInputError);
});

test('outline: every point lies between root and tip circle', () => {
    const g = spurHelical({ z: 18, mn: 2, x: 0.3 });
    const pts = gearOutline({ ...g });
    for (const [x, y] of pts) {
        const r = Math.hypot(x, y);
        assert.ok(r >= g.df / 2 - 1e-9 && r <= g.da / 2 + 1e-9, `r=${r}`);
    }
    assert.ok(deg(1) > 57);
});
