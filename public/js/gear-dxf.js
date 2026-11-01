// ASCII DXF (AutoCAD R12, AC1009) writer for gear-drawing.js sheets.
// R12 is the version every CAD and CAM program still reads: AutoCAD,
// LibreCAD, DraftSight, SolidWorks, Fusion, wire-cut and laser software.
// Units are millimetres, 1:1. Text is ASCII with AutoCAD %% codes.
import { LAYERS } from './gear-drawing.js';

const LTYPES = {
    CONTINUOUS: { desc: 'Solid line', dashes: [] },
    DASHED: { desc: '__ __ __', dashes: [3, -1.5] },
    HIDDEN: { desc: '_ _ _ _', dashes: [2, -1] },
    CENTER: { desc: '____ _ ____', dashes: [12, -2, 2, -2] },
    PHANTOM: { desc: '____ _ _ ____', dashes: [12, -2, 2, -2, 2, -2] },
};

const GREEK = { 'α': 'alpha', 'β': 'beta', 'γ': 'gamma', 'δ': 'delta', 'ε': 'eps', 'θ': 'theta', 'μ': 'mu', 'ρ': 'rho', 'η': 'eta', 'Σ': 'Sum ', 'Δ': 'delta ', 'π': 'pi' };

/** Text for DXF R12: ASCII only, degree/diameter/plus-minus as %% codes. */
export function dxfText(s) {
    return String(s)
        .replace(/°/g, '%%d').replace(/[⌀Ø]/g, '%%c').replace(/±/g, '%%p')
        .replace(/[αβγδεθμρηΣΔπ]/g, (c) => GREEK[c])
        .replace(/[·×]/g, '*').replace(/[−–—]/g, '-').replace(/→/g, '->').replace(/′/g, "'").replace(/≈/g, '~')
        .replace(/[^\x20-\x7e]/g, '?');
}

const num = (v) => {
    const r = Math.round(v * 1e6) / 1e6;
    return Object.is(r, -0) ? '0' : String(r);
};

export function toDxf(sheet, { title = '' } = {}) {
    const out = [];
    const g = (code, value) => out.push(String(code), typeof value === 'number' ? num(value) : String(value));
    const xy = ([x, y], c = 10) => { g(c, x); g(c + 10, y); g(c + 20, 0); };
    const [x0, y0, x1, y1] = sheet.bounds;

    g(999, dxfText(title ? `${title} - misubaguna.com gear calculator` : 'misubaguna.com gear calculator'));
    g(0, 'SECTION'); g(2, 'HEADER');
    g(9, '$ACADVER'); g(1, 'AC1009');
    g(9, '$INSBASE'); xy([0, 0]);
    g(9, '$EXTMIN'); xy([x0, y0]);
    g(9, '$EXTMAX'); xy([x1, y1]);
    g(9, '$LIMMIN'); g(10, x0); g(20, y0);
    g(9, '$LIMMAX'); g(10, x1); g(20, y1);
    g(9, '$LTSCALE'); g(40, 1);
    g(9, '$MEASUREMENT'); g(70, 1);
    g(9, '$INSUNITS'); g(70, 4);
    g(0, 'ENDSEC');

    g(0, 'SECTION'); g(2, 'TABLES');
    g(0, 'TABLE'); g(2, 'LTYPE'); g(70, Object.keys(LTYPES).length);
    for (const [name, lt] of Object.entries(LTYPES)) {
        g(0, 'LTYPE'); g(2, name); g(70, 0); g(3, lt.desc); g(72, 65); g(73, lt.dashes.length);
        g(40, lt.dashes.reduce((s, d) => s + Math.abs(d), 0));
        for (const d of lt.dashes) g(49, d);
    }
    g(0, 'ENDTAB');
    g(0, 'TABLE'); g(2, 'LAYER'); g(70, Object.keys(LAYERS).length + 1);
    g(0, 'LAYER'); g(2, '0'); g(70, 0); g(62, 7); g(6, 'CONTINUOUS');
    for (const [name, l] of Object.entries(LAYERS)) {
        g(0, 'LAYER'); g(2, name); g(70, 0); g(62, l.color); g(6, l.lt);
    }
    g(0, 'ENDTAB');
    g(0, 'TABLE'); g(2, 'STYLE'); g(70, 1);
    g(0, 'STYLE'); g(2, 'STANDARD'); g(70, 0); g(40, 0); g(41, 1); g(50, 0); g(71, 0); g(42, 2.5); g(3, 'txt'); g(4, '');
    g(0, 'ENDTAB');
    g(0, 'ENDSEC');

    g(0, 'SECTION'); g(2, 'ENTITIES');
    for (const e of sheet.ents) {
        if (e.dxf === false) continue;
        const layer = e.layer;
        switch (e.t) {
            case 'line':
                g(0, 'LINE'); g(8, layer); xy(e.a); xy(e.b, 11);
                break;
            case 'circle':
                g(0, 'CIRCLE'); g(8, layer); xy(e.c); g(40, e.r);
                break;
            case 'arc':
                g(0, 'ARC'); g(8, layer); xy(e.c); g(40, e.r); g(50, e.a0); g(51, e.a1);
                break;
            case 'solid': {
                const [a, b, c] = e.pts;
                g(0, 'SOLID'); g(8, layer); xy(a); xy(b, 11); xy(c, 12); xy(c, 13);
                break;
            }
            case 'poly':
                g(0, 'POLYLINE'); g(8, layer); g(66, 1); xy([0, 0]); g(70, e.closed ? 1 : 0);
                for (const p of e.pts) { g(0, 'VERTEX'); g(8, layer); xy(p); }
                g(0, 'SEQEND'); g(8, layer);
                break;
            case 'text': {
                const align = { start: 0, middle: 1, end: 2 }[e.anchor] ?? 0;
                g(0, 'TEXT'); g(8, layer); xy(e.at); g(40, e.h); g(1, dxfText(e.s));
                if (e.rot) g(50, e.rot);
                if (align) { g(72, align); xy(e.at, 11); }
                break;
            }
            default:
        }
    }
    g(0, 'ENDSEC');
    g(0, 'EOF');
    return out.join('\r\n') + '\r\n';
}
