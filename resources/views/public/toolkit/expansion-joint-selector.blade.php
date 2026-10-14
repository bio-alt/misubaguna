@extends('public.layout')

@section('title', 'Expansion Joint Selector | Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Pipe thermal expansion calculator and expansion joint type selector.')

@section('content')
<style>
    .toolkit-page-header {
        background-color: #f8fafc;
        padding: 56px 24px 32px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
    }
    .toolkit-page-header .tk-crumb {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #0f766e;
        margin-bottom: 10px;
    }
    .toolkit-page-header .tk-crumb a { color: inherit; text-decoration: none; }
    .toolkit-page-header h1 {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 10px;
    }
    .toolkit-page-header p {
        font-size: 15px;
        color: #4b5563;
        max-width: 680px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .ej-wrap {
        max-width: 1200px;
        margin: 32px auto 56px;
        padding: 0 24px;
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 24px;
        align-items: start;
    }
    .ej-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
    }
    .ej-card h2 {
        font-size: 15px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 14px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .ej-inputs { position: sticky; top: 96px; }
    .ej-field { margin-bottom: 12px; }
    .ej-field label {
        display: block;
        font-size: 12.5px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 4px;
    }
    .ej-field .ej-hint { font-weight: 500; color: #64748b; }
    .ej-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .ej-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; }
    .ej-input {
        width: 100%;
        box-sizing: border-box;
        padding: 8px 10px;
        border: 1px solid #cbd5e1;
        border-radius: 7px;
        font-size: 13.5px;
        font-family: inherit;
        color: #0f172a;
        background: #f8fafc;
    }
    .ej-input:focus {
        outline: none;
        border-color: #2b9d9f;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(43, 157, 159, 0.15);
    }
    .ej-error {
        display: none;
        font-size: 12.5px;
        color: #0f172a;
        background: #f1f5f9;
        border-left: 3px solid #0f172a;
        padding: 8px 10px;
        border-radius: 6px;
        margin-top: 6px;
    }

    .ej-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 18px;
    }
    .ej-stat {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px 14px;
    }
    .ej-stat-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .ej-stat-value {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 4px;
        font-variant-numeric: tabular-nums;
    }
    .ej-stat-value small { font-size: 12px; font-weight: 600; color: #64748b; margin-left: 2px; }
    .ej-stat.is-primary { background: #f0fdfa; border-color: #99f6e4; }
    .ej-stat.is-primary .ej-stat-value { color: #0f766e; }

    .ej-type {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 10px;
        background: #fff;
    }
    .ej-type.is-best {
        border: 2px solid #2b9d9f;
        background: #f0fdfa;
    }
    .ej-type.is-no { background: #f8fafc; }
    .ej-type.is-no .ej-type-name { color: #94a3b8; }
    .ej-type-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .ej-type-name { font-size: 15px; font-weight: 800; color: #0f172a; }
    .ej-badge {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 3px 9px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .ej-badge.ok { background: #0f766e; color: #fff; }
    .ej-badge.limited { background: #e2e8f0; color: #334155; }
    .ej-badge.no { background: #fff; color: #94a3b8; border: 1px solid #e2e8f0; }
    .ej-type ul { margin: 8px 0 0; padding-left: 18px; }
    .ej-type li { font-size: 13px; color: #475569; line-height: 1.55; }
    .ej-type-meta {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 10px;
        font-size: 12.5px;
        color: #334155;
    }
    .ej-type-meta strong { color: #0f172a; }
    .ej-type-meta a { color: #0f766e; font-weight: 700; text-decoration: none; }
    .ej-type-meta a:hover { text-decoration: underline; }
    .ej-section-label {
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        margin: 18px 0 8px;
    }

    .ej-note {
        font-size: 13px;
        color: #334155;
        background: #f1f5f9;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 12px;
        line-height: 1.55;
    }
    .ej-note a { color: #0f766e; font-weight: 700; }

    .ej-cta {
        margin-top: 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        background: #0f172a;
        color: #fff;
        border-radius: 12px;
        padding: 18px 20px;
    }
    .ej-cta h3 { margin: 0 0 4px; font-size: 16px; font-weight: 800; color: #fff; }
    .ej-cta p { margin: 0; font-size: 13px; color: #cbd5e1; }
    .ej-cta a {
        background: #2b9d9f;
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 11px 18px;
        border-radius: 8px;
        text-decoration: none;
        white-space: nowrap;
    }
    .ej-cta a:hover { background: #0f766e; }

    .ej-method {
        max-width: 1200px;
        margin: 0 auto 64px;
        padding: 0 24px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }
    .ej-method h2 { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 10px; }
    .ej-method p, .ej-method li { font-size: 13.5px; color: #475569; line-height: 1.65; }
    .ej-method code { background: #f1f5f9; padding: 1px 6px; border-radius: 4px; font-size: 13px; color: #0f172a; }
    .ej-method table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .ej-method th, .ej-method td { text-align: left; padding: 7px 8px; border-bottom: 1px solid #e2e8f0; }
    .ej-method th { color: #64748b; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.04em; }
    .ej-method td a { color: #0f766e; font-weight: 700; text-decoration: none; }

    @media (max-width: 991px) {
        .ej-wrap { grid-template-columns: 1fr; }
        .ej-inputs { position: static; }
        .ej-stats { grid-template-columns: 1fr 1fr; }
        .ej-method { grid-template-columns: 1fr; }
    }
    @media (max-width: 480px) {
        .ej-wrap, .ej-method { padding: 0 16px; }
        .toolkit-page-header h1 { font-size: 26px; }
        .ej-stat-value { font-size: 19px; }
    }
</style>

<div class="toolkit-page-header">
    <div class="tk-crumb"><a href="/toolkit/">Engineering Toolkit</a></div>
    <h1>Expansion Joint Selector</h1>
    <p>Calculate how much your pipe grows between anchors, the pressure thrust your anchors must hold, and which expansion joint type fits your temperature, pressure, and media.</p>
</div>

<div class="ej-wrap">
    <div class="ej-card ej-inputs">
        <h2>Operating Conditions</h2>

        <div class="ej-field">
            <label for="ej-material">Pipe Material</label>
            <select id="ej-material" class="ej-input">
                <option value="cs">Carbon Steel (A106 / A53)</option>
                <option value="ss">Stainless Steel 304 / 316</option>
                <option value="duplex">Duplex Stainless Steel</option>
                <option value="ci">Cast / Ductile Iron</option>
                <option value="cu">Copper</option>
                <option value="al">Aluminium</option>
                <option value="frp">FRP / GRP</option>
                <option value="pvc">PVC-U</option>
                <option value="pp">Polypropylene (PP)</option>
                <option value="hdpe">HDPE</option>
            </select>
        </div>

        <div class="ej-row">
            <div class="ej-field">
                <label for="ej-length">Anchor Spacing <span class="ej-hint">(m)</span></label>
                <input id="ej-length" class="ej-input" type="number" min="0" step="0.5" value="40">
            </div>
            <div class="ej-field">
                <label for="ej-dn">Nominal Size</label>
                <select id="ej-dn" class="ej-input"></select>
            </div>
        </div>

        <div class="ej-field">
            <label>Temperature <span class="ej-hint">(°C)</span></label>
            <div class="ej-row-3">
                <div>
                    <label for="ej-tinst" class="ej-hint" style="font-size:11.5px;">Installed</label>
                    <input id="ej-tinst" class="ej-input" type="number" step="1" value="25">
                </div>
                <div>
                    <label for="ej-tmin" class="ej-hint" style="font-size:11.5px;">Min. operating</label>
                    <input id="ej-tmin" class="ej-input" type="number" step="1" value="25">
                </div>
                <div>
                    <label for="ej-tmax" class="ej-hint" style="font-size:11.5px;">Max. operating</label>
                    <input id="ej-tmax" class="ej-input" type="number" step="1" value="90">
                </div>
            </div>
        </div>

        <div class="ej-row">
            <div class="ej-field">
                <label for="ej-pressure">Design Pressure <span class="ej-hint">(bar g)</span></label>
                <input id="ej-pressure" class="ej-input" type="number" min="0" step="0.5" value="10">
            </div>
            <div class="ej-field">
                <label for="ej-media">Media</label>
                <select id="ej-media" class="ej-input">
                    <option value="water">Water / cooling water</option>
                    <option value="steam">Steam / condensate</option>
                    <option value="chemical">Chemicals / acids / alkalis</option>
                    <option value="slurry">Abrasive slurry / pulp</option>
                    <option value="oil">Oil / hydrocarbons</option>
                    <option value="gas">Compressed air / gas</option>
                    <option value="flue">Flue gas / ducting (low pressure)</option>
                </select>
            </div>
        </div>

        <div class="ej-error" id="ej-error"></div>
    </div>

    <div id="ej-results">
        <div class="ej-stats">
            <div class="ej-stat">
                <div class="ej-stat-label">Expansion</div>
                <div class="ej-stat-value" id="ej-out-exp">–<small>mm</small></div>
            </div>
            <div class="ej-stat">
                <div class="ej-stat-label">Contraction</div>
                <div class="ej-stat-value" id="ej-out-con">–<small>mm</small></div>
            </div>
            <div class="ej-stat is-primary">
                <div class="ej-stat-label">Total Axial Movement</div>
                <div class="ej-stat-value" id="ej-out-total">–<small>mm</small></div>
            </div>
            <div class="ej-stat">
                <div class="ej-stat-label">Anchor Pressure Thrust</div>
                <div class="ej-stat-value" id="ej-out-thrust">–<small>kN</small></div>
            </div>
        </div>

        <div id="ej-notes"></div>
        <div id="ej-types"></div>

        <div class="ej-cta">
            <div>
                <h3>Get a sized quotation for this line</h3>
                <p>Our engineers confirm movement, pressure rating, and face-to-face length, then quote the joint.</p>
            </div>
            <a id="ej-quote" href="/request-a-quote/?item=Expansion+joint+%28selector+result%29">Request a Quote</a>
        </div>
    </div>
</div>

<div class="ej-method">
    <div class="ej-card">
        <h2>How it is calculated</h2>
        <p>Axial growth between two fixed anchors follows <code>ΔL = α × L × ΔT</code>, where α is the pipe's mean coefficient of thermal expansion, L the anchored length, and ΔT the temperature swing from installation. Expansion uses the rise to max. operating temperature; contraction uses the drop to min. operating temperature. The joint must absorb both.</p>
        <p>Anchor pressure thrust is estimated as <code>F = P × π/4 × D²</code> using the nominal bore. The real effective area of a bellows or arch is larger, so treat this as a minimum and use tie rods or main anchors sized by your piping engineer.</p>
        <p>Joint ratings and movement capacities are typical values for standard designs. Final selection depends on size, face-to-face length, cycle life, and the exact media. For chemical service, check the elastomer in our <a href="/toolkit/chemical-resistance/" style="color:#0f766e;font-weight:700;">Chemical Resistance Guide</a>.</p>
    </div>
    <div class="ej-card">
        <h2>Typical joint limits</h2>
        <table>
            <thead><tr><th>Type</th><th>Temp. range</th><th>Pressure</th><th>Best for</th></tr></thead>
            <tbody>
                <tr><td><a href="/product/rubber-expansion-joint/">Rubber</a></td><td>-20 to 110 °C</td><td>to 16 bar</td><td>Water, slurry, pump vibration</td></tr>
                <tr><td><a href="/product/ptfe-expansion-joint/">PTFE bellows</a></td><td>-50 to 200 °C</td><td>to 10 bar (derated hot)</td><td>Acids, alkalis, solvents</td></tr>
                <tr><td><a href="/product/ptfe-lined-rubber-metal-expansion-joint/">PTFE-lined rubber/metal</a></td><td>-20 to 180 °C</td><td>to 16 bar</td><td>Chemicals with higher pressure or movement</td></tr>
                <tr><td><a href="/product/metal-expansion-joint/">Metal bellows</a></td><td>-196 to 600 °C</td><td>to 40 bar+</td><td>Steam, hot oil, gas, high temperature</td></tr>
                <tr><td><a href="/product/fabric-expansion-joint/">Fabric</a></td><td>to 1000 °C</td><td>to 0.35 bar</td><td>Flue gas ducts, large movement</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
(function () {
    // Mean thermal expansion coefficients, x10^-6 per °C (typical 20-200 °C).
    var MATERIALS = {
        cs: { name: 'Carbon Steel', alpha: 12.0 },
        ss: { name: 'Stainless Steel 304/316', alpha: 17.3 },
        duplex: { name: 'Duplex Stainless Steel', alpha: 13.0 },
        ci: { name: 'Cast / Ductile Iron', alpha: 10.5 },
        cu: { name: 'Copper', alpha: 17.0 },
        al: { name: 'Aluminium', alpha: 23.0 },
        frp: { name: 'FRP / GRP', alpha: 20.0 },
        pvc: { name: 'PVC-U', alpha: 52.0 },
        pp: { name: 'Polypropylene', alpha: 150.0 },
        hdpe: { name: 'HDPE', alpha: 180.0 }
    };

    var MEDIA_LABELS = {
        water: 'Water', steam: 'Steam', chemical: 'Chemicals', slurry: 'Abrasive slurry',
        oil: 'Oil / hydrocarbons', gas: 'Compressed air / gas', flue: 'Flue gas / ducting'
    };

    var DN_SIZES = [25, 32, 40, 50, 65, 80, 100, 125, 150, 200, 250, 300, 350, 400, 450, 500, 600, 700, 800, 900, 1000, 1200];

    function bucket(dn, steps) {
        for (var i = 0; i < steps.length; i++) {
            if (dn <= steps[i][0]) return steps[i][1];
        }
        return steps[steps.length - 1][1];
    }

    // pref: 1 = first choice for this media, 2 = suitable, 3 = possible with care, 0 = not suitable.
    var TYPES = [
        {
            key: 'rubber', name: 'Rubber Expansion Joint', slug: 'rubber-expansion-joint',
            tmin: -20, tmax: 110,
            pmax: function () { return 16; },
            capacity: function (dn) { return bucket(dn, [[50, 8], [150, 12], [300, 16], [600, 20], [99999, 25]]); },
            pref: { water: 1, slurry: 1, gas: 2, oil: 3, chemical: 3, steam: 3, flue: 3 },
            mediaNote: {
                oil: 'Specify an NBR inner tube for oils and hydrocarbons.',
                chemical: 'Elastomer must be checked against the exact chemical and concentration.',
                steam: 'Only low-pressure steam with a steam-grade EPDM tube.',
                flue: 'Only for cool, low-pressure gas; fabric is usually better for ducting.',
                slurry: 'Natural rubber or abrasion-resistant liner recommended.'
            },
            strengths: 'Absorbs vibration and noise, handles lateral and angular misalignment, low cost.'
        },
        {
            key: 'ptfe', name: 'PTFE Expansion Joint', slug: 'ptfe-expansion-joint',
            tmin: -50, tmax: 200,
            pmax: function (t) { return t <= 50 ? 10 : t <= 100 ? 7 : t <= 150 ? 5 : 3.5; },
            capacity: function (dn) { return bucket(dn, [[50, 6], [150, 10], [300, 13], [99999, 16]]); },
            maxDn: 600,
            pref: { chemical: 1, oil: 2, gas: 2, water: 3, steam: 3, flue: 3, slurry: 0 },
            mediaNote: {
                water: 'Works, but rubber is usually the more economical choice for water.',
                steam: 'Pressure rating drops sharply above 100 °C.',
                flue: 'Possible for corrosive condensate, check pressure and size.',
                slurry: 'PTFE bellows wear quickly with abrasive solids.'
            },
            strengths: 'Inert to almost all acids, alkalis, and solvents.'
        },
        {
            key: 'ptfeLined', name: 'PTFE Lined Expansion Joint (Rubber / Metal)', slug: 'ptfe-lined-rubber-metal-expansion-joint',
            tmin: -20, tmax: 180,
            pmax: function () { return 16; },
            capacity: function (dn) { return bucket(dn, [[50, 8], [150, 12], [300, 16], [600, 20], [99999, 25]]); },
            pref: { chemical: 1, oil: 3, gas: 3, water: 3, steam: 3, flue: 3, slurry: 3 },
            mediaNote: {
                water: 'Only needed when the water carries corrosive chemicals.',
                slurry: 'PTFE liner has limited abrasion resistance.'
            },
            strengths: 'PTFE chemical resistance with the pressure rating and movement of a rubber or metal body.'
        },
        {
            key: 'metal', name: 'Metal Expansion Joint', slug: 'metal-expansion-joint',
            tmin: -196, tmax: 600,
            pmax: function () { return 40; },
            capacity: function (dn) { return bucket(dn, [[150, 30], [300, 40], [99999, 50]]); },
            pref: { steam: 1, oil: 1, gas: 1, water: 2, flue: 2, chemical: 3, slurry: 3 },
            mediaNote: {
                chemical: 'Bellows alloy (316L, Inconel, Hastelloy) must suit the chemical; consider PTFE-lined.',
                slurry: 'Needs an internal liner to protect the thin bellows from erosion.',
                water: 'Watch chlorides in cooling water for stainless bellows.'
            },
            strengths: 'Highest temperature and pressure ratings; long cycle life when correctly designed.'
        },
        {
            key: 'fabric', name: 'Fabric Expansion Joint', slug: 'fabric-expansion-joint',
            tmin: -40, tmax: 1000,
            pmax: function () { return 0.35; },
            capacity: function (dn) { return dn <= 600 ? 60 : 100; },
            liquidsNotAllowed: true,
            pref: { flue: 1, gas: 2, water: 0, steam: 0, chemical: 0, slurry: 0, oil: 0 },
            mediaNote: {
                gas: 'Only for low-pressure air or gas ducting.'
            },
            strengths: 'Very large movement in all directions, high temperature, light weight for big ducts.'
        }
    ];

    var LIQUIDS = { water: 1, steam: 1, chemical: 1, slurry: 1, oil: 1 };

    var el = function (id) { return document.getElementById(id); };
    var dnSelect = el('ej-dn');
    DN_SIZES.forEach(function (dn) {
        var o = document.createElement('option');
        o.value = dn;
        o.textContent = 'DN ' + dn;
        if (dn === 200) o.selected = true;
        dnSelect.appendChild(o);
    });

    function fmt(n, d) {
        return n.toLocaleString('en-US', { minimumFractionDigits: d, maximumFractionDigits: d });
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function evaluate(type, c) {
        var reasons = [];
        var status = 'ok';
        var pref = type.pref[c.media];

        if (type.liquidsNotAllowed && LIQUIDS[c.media]) {
            return { status: 'no', reasons: ['Not for liquids or steam; fabric joints are for gas ducting only.'], pref: 9 };
        }
        if (c.tmax > type.tmax) {
            status = 'no';
            reasons.push('Max. temperature ' + c.tmax + ' °C exceeds the typical ' + type.tmax + ' °C limit.');
        }
        if (c.tmin < type.tmin) {
            status = 'no';
            reasons.push('Min. temperature ' + c.tmin + ' °C is below the typical ' + type.tmin + ' °C limit.');
        }
        var pAllow = type.pmax(c.tmax);
        if (c.pressure > pAllow) {
            status = 'no';
            reasons.push('Pressure ' + c.pressure + ' bar exceeds the typical ' + pAllow + ' bar rating at ' + c.tmax + ' °C.');
        }
        if (type.maxDn && c.dn > type.maxDn) {
            status = 'no';
            reasons.push('Standard range goes up to DN ' + type.maxDn + '.');
        }
        if (pref === 0) {
            status = 'no';
            reasons.push(type.mediaNote[c.media] || 'Not suited to this media.');
        }

        if (status !== 'no') {
            if (c.tmax > type.tmax * 0.9 || c.pressure > pAllow * 0.8) {
                status = 'limited';
                reasons.push('Operating close to the rating limit; confirm the design margin with our engineers.');
            }
            if (pref === 3) {
                status = 'limited';
            }
            if (type.mediaNote[c.media]) reasons.push(type.mediaNote[c.media]);
            reasons.unshift(type.strengths);
        }

        return { status: status, reasons: reasons, pref: pref === 0 ? 9 : pref };
    }

    function read() {
        return {
            material: el('ej-material').value,
            length: parseFloat(el('ej-length').value),
            dn: parseInt(el('ej-dn').value, 10),
            tinst: parseFloat(el('ej-tinst').value),
            tmin: parseFloat(el('ej-tmin').value),
            tmax: parseFloat(el('ej-tmax').value),
            pressure: parseFloat(el('ej-pressure').value),
            media: el('ej-media').value
        };
    }

    function validate(c) {
        if (!(c.length > 0)) return 'Enter the pipe length between anchors.';
        if ([c.tinst, c.tmin, c.tmax].some(isNaN)) return 'Enter installed, minimum, and maximum temperatures.';
        if (c.tmin > c.tmax) return 'Min. operating temperature is higher than max.';
        if (!(c.pressure >= 0)) return 'Enter the design pressure (0 for atmospheric).';
        return null;
    }

    function render() {
        var c = read();
        var err = validate(c);
        var errBox = el('ej-error');
        if (err) {
            errBox.textContent = err;
            errBox.style.display = 'block';
            return;
        }
        errBox.style.display = 'none';

        var alpha = MATERIALS[c.material].alpha * 1e-6;
        var lengthMm = c.length * 1000;
        var expansion = alpha * lengthMm * Math.max(0, c.tmax - c.tinst);
        var contraction = alpha * lengthMm * Math.max(0, c.tinst - c.tmin);
        var total = expansion + contraction;
        var areaM2 = Math.PI / 4 * Math.pow(c.dn / 1000, 2);
        var thrustKn = c.pressure * 1e5 * areaM2 / 1000;

        el('ej-out-exp').innerHTML = fmt(expansion, 1) + '<small>mm</small>';
        el('ej-out-con').innerHTML = fmt(contraction, 1) + '<small>mm</small>';
        el('ej-out-total').innerHTML = fmt(total, 1) + '<small>mm</small>';
        el('ej-out-thrust').innerHTML = fmt(thrustKn, 1) + '<small>kN</small>';

        var notes = [];
        if (total < 0.5) {
            notes.push('Almost no thermal movement. If you need the joint for vibration isolation or misalignment, a rubber joint is usually the right choice.');
        }
        if (c.material === 'pp' || c.material === 'hdpe' || c.material === 'pvc') {
            notes.push('Plastic pipe grows 4 to 15 times more than steel. Check the pipe supplier\'s own temperature and pressure limits too.');
        }
        if (c.media === 'chemical') {
            notes.push('Check the joint and lining material against your exact chemical in the <a href="/toolkit/chemical-resistance/">Chemical Resistance Guide</a>.');
        }
        el('ej-notes').innerHTML = notes.map(function (n) { return '<div class="ej-note">' + n + '</div>'; }).join('');

        var results = TYPES.map(function (t) {
            var r = evaluate(t, c);
            r.type = t;
            r.capacity = t.capacity(c.dn);
            r.count = total > 0 ? Math.max(1, Math.ceil(total / r.capacity)) : 1;
            return r;
        });
        var rank = { ok: 0, limited: 1, no: 2 };
        results.sort(function (a, b) {
            return rank[a.status] - rank[b.status] || a.pref - b.pref;
        });

        var html = '';
        var best = results[0].status !== 'no' ? results[0] : null;
        if (!best) {
            html += '<div class="ej-note">No standard joint type fits these conditions. Our engineers can design a custom joint &mdash; send us the details below.</div>';
        }
        var shownLabel = {};
        results.forEach(function (r, i) {
            var label = (r === best) ? 'Recommended' : r.status === 'no' ? 'Not suitable' : 'Alternatives';
            if (!shownLabel[label]) {
                html += '<div class="ej-section-label">' + label + '</div>';
                shownLabel[label] = true;
            }
            var badge = r.status === 'ok' ? 'Suitable' : r.status === 'limited' ? 'Use with care' : 'Not suitable';
            html += '<div class="ej-type' + (r === best ? ' is-best' : '') + (r.status === 'no' ? ' is-no' : '') + '">';
            html += '<div class="ej-type-head"><span class="ej-type-name">' + escapeHtml(r.type.name) + '</span>';
            html += '<span class="ej-badge ' + r.status + '">' + badge + '</span></div>';
            html += '<ul>' + r.reasons.map(function (x) { return '<li>' + escapeHtml(x) + '</li>'; }).join('') + '</ul>';
            if (r.status !== 'no') {
                var countText = r.count === 1
                    ? '1 joint (≈' + r.capacity + ' mm axial capacity)'
                    : r.count + ' joints (≈' + r.capacity + ' mm each) &mdash; split the run with intermediate anchors, or ask for a multi-arch / multi-ply design';
                html += '<div class="ej-type-meta"><span><strong>Joints needed:</strong> ' + countText + '</span>';
                html += '<a href="/product/' + r.type.slug + '/">View product &rarr;</a></div>';
            }
            html += '</div>';
        });
        el('ej-types').innerHTML = html;

        var message = 'Expansion joint inquiry (from Expansion Joint Selector)\n'
            + '- Pipe: ' + MATERIALS[c.material].name + ', DN ' + c.dn + ', ' + c.length + ' m between anchors\n'
            + '- Temperature: installed ' + c.tinst + ' °C, operating ' + c.tmin + ' to ' + c.tmax + ' °C\n'
            + '- Design pressure: ' + c.pressure + ' bar g\n'
            + '- Media: ' + MEDIA_LABELS[c.media] + '\n'
            + '- Calculated axial movement: ' + fmt(total, 1) + ' mm (expansion ' + fmt(expansion, 1) + ' / contraction ' + fmt(contraction, 1) + ')\n'
            + (best ? '- Suggested type: ' + best.type.name + ', qty ' + best.count + '\n' : '')
            + '\nPlease advise and quote. Media details / other requirements: ';
        el('ej-quote').href = '/request-a-quote/?item=' + encodeURIComponent('Expansion joint (selector result)')
            + '&message=' + encodeURIComponent(message);
    }

    ['ej-material', 'ej-length', 'ej-dn', 'ej-tinst', 'ej-tmin', 'ej-tmax', 'ej-pressure', 'ej-media'].forEach(function (id) {
        el(id).addEventListener('input', render);
        el(id).addEventListener('change', render);
    });
    render();
})();
</script>
@endsection
