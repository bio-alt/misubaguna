@extends('public.layout')

@section('title', 'RAL Color Chart & Color Matcher | PT Misuba Guna Indonesia')
@section('meta_description', 'Search all '.count($colors).' RAL Classic colors by number, name or hex, and find the closest RAL to any color. Reference for powder coating, paint and equipment color specification.')

@section('content')
<style>
    .rc {
        --rc-ink: #10202a;
        --rc-text: #3a4a53;
        --rc-muted: #66767f;
        --rc-line: #dbe3e5;
        --rc-mist: #edf3f3;
        --rc-accent: #176b70;
        --rc-accent-dark: #114f53;
        --rc-accent-soft: #d6eaea;
        --rc-wa: #168a49;

        font-size: 15px;
        line-height: 1.55;
        color: var(--rc-text);
        background: #fff;
        padding-bottom: 64px;
    }
    .rc *, .rc *::before, .rc *::after { box-sizing: border-box; }
    .rc a { color: var(--rc-accent); text-decoration: none; }
    .rc a:hover { color: var(--rc-accent-dark); text-decoration: underline; text-underline-offset: 3px; }
    .rc :focus-visible { outline: 3px solid var(--rc-accent); outline-offset: 2px; }
    .rc-wrap { max-width: 1280px; margin: 0 auto; padding: 0 28px; }

    /* Intro */
    .rc-intro { background: var(--rc-mist); border-bottom: 1px solid var(--rc-line); padding: 36px 0 26px; }
    .rc-crumb { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--rc-accent); margin-bottom: 8px; }
    .rc-intro h1 { font-size: 30px; line-height: 1.2; font-weight: 800; color: var(--rc-ink); margin: 0 0 8px; }
    .rc-intro p { max-width: 760px; margin: 0; }
    .rc-facts { display: flex; flex-wrap: wrap; gap: 6px 24px; margin-top: 14px; font-size: 13.5px; }
    .rc-facts b { color: var(--rc-ink); }

    /* Matcher */
    .rc-match { display: grid; grid-template-columns: minmax(260px, 340px) 1fr; gap: 24px; margin: 24px 0; padding: 20px 22px; border: 1px solid var(--rc-line); border-radius: 12px; background: #fff; }
    .rc-match h2 { font-size: 18px; font-weight: 800; color: var(--rc-ink); margin: 0 0 4px; }
    .rc-match-sub { font-size: 13.5px; color: var(--rc-muted); margin: 0 0 14px; }
    .rc-pick { display: flex; gap: 10px; align-items: center; }
    .rc-pick input[type="color"] { width: 64px; height: 44px; padding: 2px; border: 1px solid #c4d0d3; border-radius: 8px; background: #fff; cursor: pointer; }
    .rc-pick input[type="text"] { width: 120px; padding: 10px 12px; font: inherit; font-size: 14px; font-variant-numeric: tabular-nums; text-transform: uppercase; color: var(--rc-ink); border: 1px solid #c4d0d3; border-radius: 8px; }
    .rc-pick input[type="text"]:focus { outline: none; border-color: var(--rc-accent); box-shadow: 0 0 0 3px var(--rc-accent-soft); }
    .rc-hint { font-size: 12.5px; color: var(--rc-muted); margin-top: 8px; min-height: 18px; }
    .rc-hint[data-err="1"] { color: #a33; }
    .rc-near { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 12px; align-content: start; }
    .rc-near-item { display: block; width: 100%; text-align: left; font: inherit; padding: 0; border: 1px solid var(--rc-line); border-radius: 10px; overflow: hidden; background: #fff; cursor: pointer; }
    .rc-near-item:hover { border-color: var(--rc-accent); }
    .rc-near-sw { display: flex; height: 54px; }
    .rc-near-sw i { flex: 1; display: block; }
    .rc-near-txt { padding: 8px 10px 10px; font-size: 12.5px; line-height: 1.4; }
    .rc-near-txt b { display: block; font-size: 14px; color: var(--rc-ink); }
    .rc-near-txt span { color: var(--rc-muted); }
    .rc-near-label { font-size: 11.5px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--rc-accent); }

    /* Toolbar */
    .rc-toolbar { display: flex; flex-wrap: wrap; gap: 10px 14px; align-items: center; margin: 22px 0 12px; }
    .rc-search { flex: 1 1 300px; }
    .rc-search input { width: 100%; padding: 10px 12px; font: inherit; font-size: 14px; color: var(--rc-ink); border: 1px solid #c4d0d3; border-radius: 8px; background: #fff; }
    .rc-search input:focus { outline: none; border-color: var(--rc-accent); box-shadow: 0 0 0 3px var(--rc-accent-soft); }
    .rc-count { font-size: 13px; color: var(--rc-muted); margin-left: auto; }
    .rc-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
    .rc-chip { font: inherit; font-size: 12.5px; font-weight: 700; padding: 6px 12px; cursor: pointer; border: 1px solid #c4d0d3; border-radius: 999px; background: #fff; color: var(--rc-text); }
    .rc-chip span { color: var(--rc-muted); font-weight: 600; margin-left: 3px; }
    .rc-chip:hover { border-color: var(--rc-accent); color: var(--rc-accent); }
    .rc-chip[aria-pressed="true"] { background: var(--rc-ink); border-color: var(--rc-ink); color: #fff; }
    .rc-chip[aria-pressed="true"] span { color: #b8c6ca; }

    /* Detail */
    .rc-detail { scroll-margin-top: 96px; display: none; border: 1px solid var(--rc-line); border-top: 3px solid var(--rc-accent); border-radius: 12px; padding: 20px 22px; margin-bottom: 18px; background: #fff; }
    .rc-detail.open { display: grid; grid-template-columns: 220px 1fr; gap: 22px; }
    .rc-d-sw { height: 100%; min-height: 150px; border-radius: 10px; border: 1px solid rgba(0,0,0,.12); }
    .rc-d-family { font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--rc-accent); }
    .rc-d-main h2 { font-size: 24px; line-height: 1.25; font-weight: 800; color: var(--rc-ink); margin: 2px 0 12px; }
    .rc-d-vals { display: flex; flex-wrap: wrap; gap: 8px 10px; margin-bottom: 14px; }
    .rc-val { display: inline-flex; align-items: center; gap: 8px; font: inherit; font-size: 13.5px; padding: 8px 12px; border: 1px solid #c4d0d3; border-radius: 8px; background: #fff; color: var(--rc-ink); cursor: pointer; }
    .rc-val small { font-size: 11px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--rc-muted); }
    .rc-val:hover { border-color: var(--rc-accent); }
    .rc-val[data-copied="1"] { border-color: var(--rc-accent); background: var(--rc-accent-soft); }
    .rc-d-sim-t { font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--rc-muted); margin: 14px 0 6px; }
    .rc-d-sim { display: flex; flex-wrap: wrap; gap: 8px; }
    .rc-sim { display: inline-flex; align-items: center; gap: 8px; font: inherit; font-size: 13px; padding: 5px 10px 5px 5px; border: 1px solid var(--rc-line); border-radius: 999px; background: #fff; color: var(--rc-ink); cursor: pointer; }
    .rc-sim i { width: 22px; height: 22px; border-radius: 50%; border: 1px solid rgba(0,0,0,.15); display: block; }
    .rc-sim:hover { border-color: var(--rc-accent); }
    .rc-d-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .rc-btn { display: inline-flex; align-items: center; gap: 6px; font: inherit; font-size: 13.5px; font-weight: 700; padding: 10px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; text-decoration: none; border: 1px solid #c4d0d3; background: #fff; color: var(--rc-ink); }
    .rc a.rc-btn-primary { background: var(--rc-accent); border-color: var(--rc-accent); color: #fff; }
    .rc a.rc-btn-primary:hover { background: var(--rc-accent-dark); color: #fff; text-decoration: none; }
    .rc a.rc-btn-wa { color: var(--rc-wa); }
    .rc a.rc-btn-wa:hover { border-color: var(--rc-wa); color: var(--rc-wa); text-decoration: none; }

    /* Grid */
    .rc-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
    .rc-sw { display: block; width: 100%; text-align: left; font: inherit; padding: 0; border: 1px solid var(--rc-line); border-radius: 10px; overflow: hidden; background: #fff; cursor: pointer; }
    .rc-sw:hover { border-color: var(--rc-accent); box-shadow: 0 4px 14px rgba(16,32,42,.10); }
    .rc-sw[aria-pressed="true"] { border-color: var(--rc-ink); box-shadow: 0 0 0 2px var(--rc-ink); }
    .rc-sw[hidden] { display: none; }
    .rc-sw-c { display: block; height: 84px; border-bottom: 1px solid rgba(0,0,0,.08); }
    .rc-sw-t { display: block; padding: 8px 10px 10px; font-size: 12.5px; line-height: 1.35; }
    .rc-sw-t b { display: block; font-size: 14px; color: var(--rc-ink); }
    .rc-sw-t span { color: var(--rc-muted); }
    .rc-empty { display: none; padding: 28px 0; color: var(--rc-muted); }
    .rc-empty.show { display: block; }

    /* Notes */
    .rc-notes { margin-top: 40px; display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 28px; }
    .rc-notes h2 { font-size: 18px; font-weight: 800; color: var(--rc-ink); margin: 0 0 8px; }
    .rc-notes ul { margin: 0; padding-left: 18px; }
    .rc-notes li { margin-bottom: 6px; }
    .rc-fine { margin-top: 28px; padding-top: 16px; border-top: 1px solid var(--rc-line); font-size: 12.5px; color: var(--rc-muted); max-width: 900px; }

    @media (max-width: 760px) {
        .rc-wrap { padding: 0 16px; }
        .rc-intro h1 { font-size: 25px; }
        .rc-match { grid-template-columns: 1fr; padding: 16px; }
        .rc-near { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .rc-detail.open { grid-template-columns: 1fr; }
        .rc-d-sw { min-height: 90px; height: 90px; }
        .rc-grid { grid-template-columns: repeat(auto-fill, minmax(135px, 1fr)); gap: 10px; }
    }
</style>

<div class="rc">
    <div class="rc-intro">
        <div class="rc-wrap">
            <div class="rc-crumb">Engineering Toolkit</div>
            <h1>RAL Color Chart &amp; Color Matcher</h1>
            <p>Look up any RAL Classic color by number, name or hex code, or pick a color and find the closest RAL. Use it to specify the color of powder coating, paint and fabricated equipment before you request a quote.</p>
            <div class="rc-facts">
                <span><b>{{ count($colors) }}</b> RAL Classic colors</span>
                <span><b>Hex &amp; RGB</b> values, tap to copy</span>
                <span><b>Closest-RAL</b> matcher for any color</span>
            </div>
        </div>
    </div>

    <div class="rc-wrap">
        <section class="rc-match" aria-labelledby="rc-match-h">
            <div>
                <h2 id="rc-match-h">Find the closest RAL</h2>
                <p class="rc-match-sub">Pick a color, or type a hex code from a logo, drawing or sample photo.</p>
                <div class="rc-pick">
                    <input type="color" id="rc-picker" value="#2271B3" aria-label="Pick a color">
                    <input type="text" id="rc-hex" value="#2271B3" maxlength="7" spellcheck="false" autocomplete="off" aria-label="Hex code">
                </div>
                <div class="rc-hint" id="rc-hint" aria-live="polite"></div>
            </div>
            <div class="rc-near" id="rc-near" aria-live="polite"></div>
        </section>

        <div class="rc-toolbar">
            <div class="rc-search"><input type="search" id="rc-search" placeholder="Search RAL number, name or hex, e.g. 5015, grey, #2271B3" autocomplete="off" aria-label="Search RAL colors"></div>
            <div class="rc-count" id="rc-count" aria-live="polite"></div>
        </div>
        <div class="rc-chips" id="rc-chips" role="group" aria-label="Filter by color family">
            <button type="button" class="rc-chip" data-fam="0" aria-pressed="true">All<span>{{ count($colors) }}</span></button>
            @foreach($families as $key => $label)
                <button type="button" class="rc-chip" data-fam="{{ $key }}" aria-pressed="false">{{ $label }}<span>{{ $familyCounts[$key] }}</span></button>
            @endforeach
        </div>

        <div class="rc-detail" id="rc-detail" aria-live="polite"></div>

        <h2 class="visually-hidden">RAL Classic colors</h2>
        <div class="rc-grid" id="rc-grid">
            @foreach($colors as $c)
                <button type="button" class="rc-sw" data-code="{{ $c['code'] }}" data-fam="{{ $c['family'] }}" aria-pressed="false" aria-label="RAL {{ $c['code'] }} {{ $c['name'] }}">
                    <span class="rc-sw-c" style="background: {{ $c['hex'] }};"></span>
                    <span class="rc-sw-t"><b>RAL {{ $c['code'] }}</b>{{ $c['name'] }}<br><span>{{ $c['hex'] }}</span></span>
                </button>
            @endforeach
        </div>
        <p class="rc-empty" id="rc-empty">No RAL color matches that search.</p>

        <div class="rc-notes">
            <section>
                <h2>Specifying a color for coating</h2>
                <ul>
                    <li>Quote the RAL number and the name together, for example RAL 5015 Sky blue, so a typing error is easy to spot.</li>
                    <li>State the gloss level (matt, semi-gloss or gloss) and the finish (smooth, fine texture or metallic). The same RAL looks different at different gloss levels.</li>
                    <li>Say which coating system the color is for: powder coating, epoxy, polyurethane or another paint. Some pigments are not available in every system.</li>
                    <li>Bright yellows, oranges and reds, and pearl or luminous shades, can need extra coats for full coverage.</li>
                </ul>
            </section>
            <section>
                <h2>How accurate is the on-screen color?</h2>
                <ul>
                    <li>RAL does not publish screen values. The hex codes here are common sRGB approximations, close enough to shortlist and compare.</li>
                    <li>Screens, brightness and color profiles change how a color looks. Confirm the final choice against the physical RAL color card or a coated sample.</li>
                    <li>A hex match is the closest RAL by color distance (CIE Lab). It is a starting point, not a guarantee of an exact match.</li>
                </ul>
            </section>
            <section>
                <h2>Need it applied to your equipment?</h2>
                <ul>
                    <li>Send the RAL number, gloss level and a drawing or photo with your request.</li>
                    <li>Include the item, material and quantity so we can quote the coating together with the work.</li>
                </ul>
                <div class="rc-d-actions">
                    <a class="rc-btn rc-btn-primary" href="/request-a-quote/">Request a quote</a>
                    <a class="rc-btn rc-btn-wa" href="https://wa.me/628118715671?text={{ rawurlencode('Hello Misuba Guna, I need a quote and want to specify a RAL color.') }}" target="_blank" rel="noopener">WhatsApp</a>
                </div>
            </section>
        </div>

        <p class="rc-fine">RAL is a registered trademark of RAL gGmbH. This page is an independent reference and is not affiliated with or endorsed by RAL. Displayed colors are approximations of the RAL Classic standard; always confirm against the physical RAL color card.</p>
    </div>
</div>

<script>
(function () {
    var COLORS = @json($colors);
    var WHATSAPP = 'https://wa.me/628118715671';
    var byCode = {};
    COLORS.forEach(function (c) {
        c.rgb = hexToRgb(c.hex);
        c.lab = rgbToLab(c.rgb);
        byCode[c.code] = c;
    });

    var grid = document.getElementById('rc-grid');
    var cards = Array.prototype.slice.call(grid.querySelectorAll('.rc-sw'));
    var search = document.getElementById('rc-search');
    var countEl = document.getElementById('rc-count');
    var emptyEl = document.getElementById('rc-empty');
    var chips = Array.prototype.slice.call(document.querySelectorAll('.rc-chip'));
    var detail = document.getElementById('rc-detail');
    var picker = document.getElementById('rc-picker');
    var hexIn = document.getElementById('rc-hex');
    var hint = document.getElementById('rc-hint');
    var near = document.getElementById('rc-near');
    var state = { fam: 0, q: '', selected: null };

    function esc(s) { return String(s).replace(/[&<>"']/g, function (ch) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch]; }); }

    function hexToRgb(h) {
        var n = parseInt(h.slice(1), 16);
        return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
    }
    function rgbToLab(rgb) {
        var lin = rgb.map(function (v) { v /= 255; return v <= 0.04045 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); });
        var x = (lin[0] * 0.4124564 + lin[1] * 0.3575761 + lin[2] * 0.1804375) / 0.95047;
        var y = lin[0] * 0.2126729 + lin[1] * 0.7151522 + lin[2] * 0.0721750;
        var z = (lin[0] * 0.0193339 + lin[1] * 0.1191920 + lin[2] * 0.9503041) / 1.08883;
        var f = function (t) { return t > 0.008856 ? Math.cbrt(t) : 7.787 * t + 16 / 116; };
        var fx = f(x), fy = f(y), fz = f(z);
        return [116 * fy - 16, 500 * (fx - fy), 200 * (fy - fz)];
    }
    function dE(a, b) {
        return Math.sqrt(Math.pow(a[0] - b[0], 2) + Math.pow(a[1] - b[1], 2) + Math.pow(a[2] - b[2], 2));
    }
    function closest(lab, n, skipCode) {
        return COLORS.filter(function (c) { return c.code !== skipCode; })
            .map(function (c) { return { c: c, d: dE(lab, c.lab) }; })
            .sort(function (a, b) { return a.d - b.d; })
            .slice(0, n);
    }
    function normHex(v) {
        v = String(v).trim().replace(/^#/, '');
        if (/^[0-9a-f]{3}$/i.test(v)) { v = v.replace(/(.)/g, '$1$1'); }
        return /^[0-9a-f]{6}$/i.test(v) ? '#' + v.toUpperCase() : null;
    }
    function matchLabel(d) {
        return d < 2.5 ? 'Very close' : d < 6 ? 'Close' : d < 12 ? 'Similar' : 'Rough match';
    }

    /* ---- Chart: search + family filter ---- */
    function applyFilter() {
        var q = state.q.toLowerCase().replace(/^ral\s*/, '');
        var shown = 0;
        cards.forEach(function (el) {
            var c = byCode[el.getAttribute('data-code')];
            var ok = (!state.fam || c.family === state.fam) &&
                (!q || String(c.code).indexOf(q) === 0 || c.name.toLowerCase().indexOf(q) !== -1 || c.hex.toLowerCase().indexOf(q) !== -1);
            el.hidden = !ok;
            if (ok) { shown++; }
        });
        countEl.textContent = shown + ' of ' + COLORS.length + ' colors';
        emptyEl.classList.toggle('show', shown === 0);
    }
    search.addEventListener('input', function () { state.q = search.value.trim(); applyFilter(); });
    chips.forEach(function (b) {
        b.addEventListener('click', function () {
            state.fam = parseInt(b.getAttribute('data-fam'), 10);
            chips.forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
            applyFilter();
        });
    });

    /* ---- Detail panel ---- */
    function copy(text, btn) {
        var done = function () {
            btn.setAttribute('data-copied', '1');
            var t = btn.querySelector('small'), old = t.textContent;
            t.textContent = 'Copied';
            setTimeout(function () { btn.removeAttribute('data-copied'); t.textContent = old; }, 1200);
        };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done, function () {});
        } else {
            var ta = document.createElement('textarea');
            ta.value = text; document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); done(); } catch (e) {}
            document.body.removeChild(ta);
        }
    }
    function select(code, scroll) {
        var c = byCode[code];
        if (!c) { return; }
        state.selected = code;
        cards.forEach(function (el) { el.setAttribute('aria-pressed', el.getAttribute('data-code') === String(code) ? 'true' : 'false'); });
        var rgbText = c.rgb.join(', ');
        var label = 'RAL ' + c.code + ' ' + c.name;
        var sims = closest(c.lab, 3, c.code).map(function (s) {
            return '<button type="button" class="rc-sim" data-code="' + s.c.code + '"><i style="background:' + s.c.hex + '"></i>RAL ' + s.c.code + ' ' + esc(s.c.name) + '</button>';
        }).join('');
        var msg = 'Color required: ' + label + ' (approx. ' + c.hex + ').';
        var quote = '/request-a-quote/?item=' + encodeURIComponent(label) + '&message=' + encodeURIComponent(msg);
        var wa = WHATSAPP + '?text=' + encodeURIComponent('Hello Misuba Guna, I need a quote and want ' + label + '.');
        detail.innerHTML =
            '<div class="rc-d-sw" style="background:' + c.hex + '"></div>' +
            '<div class="rc-d-main">' +
              '<div class="rc-d-family">' + esc(c.family_label) + '</div>' +
              '<h2>RAL ' + c.code + ' &middot; ' + esc(c.name) + '</h2>' +
              '<div class="rc-d-vals">' +
                '<button type="button" class="rc-val" data-copy="RAL ' + c.code + '"><small>RAL</small>' + c.code + '</button>' +
                '<button type="button" class="rc-val" data-copy="' + c.hex + '"><small>HEX</small>' + c.hex + '</button>' +
                '<button type="button" class="rc-val" data-copy="' + rgbText + '"><small>RGB</small>' + rgbText + '</button>' +
              '</div>' +
              '<div class="rc-d-sim-t">Similar RAL colors</div><div class="rc-d-sim">' + sims + '</div>' +
              '<div class="rc-d-actions">' +
                '<a class="rc-btn rc-btn-primary" href="' + quote + '">Request a quote in this color</a>' +
                '<a class="rc-btn rc-btn-wa" href="' + wa + '" target="_blank" rel="noopener">WhatsApp</a>' +
              '</div>' +
            '</div>';
        detail.classList.add('open');
        if (history.replaceState) { history.replaceState(null, '', '#ral-' + code); }
        if (scroll) { detail.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }
    }
    detail.addEventListener('click', function (e) {
        var v = e.target.closest('.rc-val');
        if (v) { copy(v.getAttribute('data-copy'), v); return; }
        var s = e.target.closest('.rc-sim');
        if (s) { select(parseInt(s.getAttribute('data-code'), 10), true); }
    });
    grid.addEventListener('click', function (e) {
        var el = e.target.closest('.rc-sw');
        if (el) { select(parseInt(el.getAttribute('data-code'), 10), true); }
    });

    /* ---- Closest-RAL matcher ---- */
    function renderMatch(hex) {
        var rgb = hexToRgb(hex), lab = rgbToLab(rgb);
        near.innerHTML = closest(lab, 4, null).map(function (m, i) {
            return '<button type="button" class="rc-near-item" data-code="' + m.c.code + '">' +
                '<span class="rc-near-sw"><i style="background:' + hex + '"></i><i style="background:' + m.c.hex + '"></i></span>' +
                '<span class="rc-near-txt"><span class="rc-near-label">' + (i === 0 ? 'Closest &middot; ' : '') + matchLabel(m.d) + '</span>' +
                '<b>RAL ' + m.c.code + '</b><span>' + esc(m.c.name) + ' &middot; ' + m.c.hex + '</span></span></button>';
        }).join('');
    }
    near.addEventListener('click', function (e) {
        var el = e.target.closest('.rc-near-item');
        if (el) { select(parseInt(el.getAttribute('data-code'), 10), true); }
    });
    picker.addEventListener('input', function () {
        var h = picker.value.toUpperCase();
        hexIn.value = h; hint.textContent = ''; hint.removeAttribute('data-err');
        renderMatch(h);
    });
    hexIn.addEventListener('input', function () {
        var h = normHex(hexIn.value);
        if (h) {
            picker.value = h.toLowerCase(); hint.textContent = ''; hint.removeAttribute('data-err');
            renderMatch(h);
        } else {
            hint.textContent = 'Enter a hex code like #2271B3.';
            hint.setAttribute('data-err', '1');
        }
    });

    /* ---- Init ---- */
    applyFilter();
    renderMatch('#2271B3');
    var m = /^#ral-(\d{4})$/.exec(location.hash);
    if (m && byCode[m[1]]) { select(parseInt(m[1], 10), false); }
})();
</script>
@endsection
