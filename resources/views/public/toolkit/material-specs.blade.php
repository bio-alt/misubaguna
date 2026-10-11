@extends('public.layout')

@section('title', 'Material Property Library | Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Mechanical, thermal, and chemical resistance properties for '.count($materials).' industrial steels, nickel alloys, elastomers, fluoropolymers, ceramics, and gasket materials.')

@php
    $ratingCodes = ['Excellent' => 'A', 'Good' => 'B', 'Fair' => 'C', 'Poor' => 'X'];
    $familyCounts = array_count_values(array_column($materials, 'family'));
@endphp

@section('content')
<style>
    .ms {
        --ms-ink: #10202a;
        --ms-text: #3a4a53;
        --ms-muted: #66767f;
        --ms-line: #dbe3e5;
        --ms-mist: #edf3f3;
        --ms-accent: #176b70;
        --ms-accent-dark: #114f53;
        --ms-accent-soft: #d6eaea;
        --ms-wa: #168a49;

        font-size: 15px;
        line-height: 1.55;
        color: var(--ms-text);
        background: #fff;
        padding-bottom: 64px;
    }
    .ms *, .ms *::before, .ms *::after { box-sizing: border-box; }
    .ms a { color: var(--ms-accent); text-decoration: none; }
    .ms a:hover { color: var(--ms-accent-dark); text-decoration: underline; text-underline-offset: 3px; }
    .ms :focus-visible { outline: 3px solid var(--ms-accent); outline-offset: 2px; }
    .ms-wrap { max-width: 1280px; margin: 0 auto; padding: 0 28px; }

    /* Intro */
    .ms-intro { background: var(--ms-mist); border-bottom: 1px solid var(--ms-line); padding: 36px 0 26px; }
    .ms-crumb { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--ms-accent); margin-bottom: 8px; }
    .ms-intro h1 { font-size: 30px; line-height: 1.2; font-weight: 800; color: var(--ms-ink); margin: 0 0 8px; }
    .ms-intro p { max-width: 760px; margin: 0; }
    .ms-facts { display: flex; flex-wrap: wrap; gap: 6px 24px; margin-top: 14px; font-size: 13.5px; }
    .ms-facts b { color: var(--ms-ink); }

    /* Toolbar */
    .ms-toolbar { display: flex; flex-wrap: wrap; gap: 10px 14px; align-items: center; margin: 22px 0 12px; }
    .ms-search { flex: 1 1 300px; position: relative; }
    .ms-search input {
        width: 100%; padding: 10px 12px; font: inherit; font-size: 14px; color: var(--ms-ink);
        border: 1px solid #c4d0d3; border-radius: 8px; background: #fff;
    }
    .ms-search input:focus { outline: none; border-color: var(--ms-accent); box-shadow: 0 0 0 3px var(--ms-accent-soft); }
    .ms-units { display: inline-flex; border: 1px solid #c4d0d3; border-radius: 8px; overflow: hidden; }
    .ms-units button {
        font: inherit; font-size: 13px; font-weight: 700; padding: 9px 14px; border: 0;
        background: #fff; color: var(--ms-muted); cursor: pointer;
    }
    .ms-units button[aria-pressed="true"] { background: var(--ms-ink); color: #fff; }
    .ms-count { font-size: 13px; color: var(--ms-muted); margin-left: auto; }

    .ms-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
    .ms-chip {
        font: inherit; font-size: 12.5px; font-weight: 700; padding: 6px 12px; cursor: pointer;
        border: 1px solid #c4d0d3; border-radius: 999px; background: #fff; color: var(--ms-text);
    }
    .ms-chip span { color: var(--ms-muted); font-weight: 600; margin-left: 3px; }
    .ms-chip:hover { border-color: var(--ms-accent); color: var(--ms-accent); }
    .ms-chip[aria-pressed="true"] { background: var(--ms-ink); border-color: var(--ms-ink); color: #fff; }
    .ms-chip[aria-pressed="true"] span { color: #b8c6ca; }

    /* Detail panel */
    .ms-detail { display: none; border: 1px solid var(--ms-line); border-top: 3px solid var(--ms-accent); border-radius: 12px; padding: 20px 22px; margin-bottom: 18px; background: #fff; }
    .ms-detail.open { display: block; }
    .ms-d-top { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 16px; }
    .ms-d-family { font-size: 12px; font-weight: 700; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ms-accent); }
    .ms-d-top h2 { font-size: 24px; line-height: 1.25; font-weight: 800; color: var(--ms-ink); margin: 2px 0 4px; }
    .ms-d-std { font-size: 13px; color: var(--ms-muted); }
    .ms-d-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-start; }
    .ms-btn {
        display: inline-flex; align-items: center; gap: 6px; font: inherit; font-size: 13.5px; font-weight: 700;
        padding: 10px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; text-decoration: none;
        border: 1px solid #c4d0d3; background: #fff; color: var(--ms-ink);
    }
    .ms .ms-btn:hover { border-color: var(--ms-accent); color: var(--ms-accent); text-decoration: none; }
    .ms .ms-btn-primary { background: var(--ms-accent); border-color: var(--ms-accent); color: #fff; }
    .ms .ms-btn-primary:hover { background: var(--ms-accent-dark); border-color: var(--ms-accent-dark); color: #fff; }
    .ms .ms-btn-wa { color: var(--ms-wa); }
    .ms .ms-btn-wa:hover { border-color: var(--ms-wa); color: var(--ms-wa); }
    .ms-btn-close { border: 0; padding: 10px 6px; color: var(--ms-muted); }

    .ms-d-keys { display: grid; grid-template-columns: repeat(4, 1fr); border: 1px solid var(--ms-line); border-radius: 10px; margin: 16px 0; }
    .ms-d-keys div { padding: 10px 14px; border-left: 1px solid var(--ms-line); }
    .ms-d-keys div:first-child { border-left: 0; }
    .ms-d-keys dt { font-size: 12px; color: var(--ms-muted); }
    .ms-d-keys dd { margin: 2px 0 0; font-size: 18px; font-weight: 800; color: var(--ms-ink); font-variant-numeric: tabular-nums; }
    .ms-d-keys dd small { font-size: 12.5px; font-weight: 600; color: var(--ms-muted); margin-left: 3px; }

    .ms-d-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 22px; }
    .ms-d-grid h3 { font-size: 12px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ms-ink); margin: 0 0 6px; }
    .ms-sheet { width: 100%; border-collapse: collapse; font-size: 13.5px; }
    .ms-sheet th, .ms-sheet td { padding: 6px 0; border-bottom: 1px solid #eef2f3; text-align: left; vertical-align: top; }
    .ms-sheet th { font-weight: 500; color: var(--ms-muted); padding-right: 10px; }
    .ms-sheet td { font-weight: 700; color: var(--ms-ink); text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .ms-sheet td small { font-weight: 500; color: var(--ms-muted); margin-left: 3px; }
    .ms-d-text p { margin: 0 0 10px; font-size: 13.5px; }
    .ms-d-text strong { color: var(--ms-ink); }
    .ms-d-products { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
    .ms .ms-d-products a { font-size: 13px; font-weight: 700; padding: 5px 10px; border: 1px solid var(--ms-line); border-radius: 6px; background: var(--ms-mist); }
    .ms .ms-d-products a.fab { background: #fff; color: var(--ms-ink); font-weight: 600; }
    .ms .ms-d-products a.fab:hover { border-color: var(--ms-accent); color: var(--ms-accent); text-decoration: none; }
    .ms-d-note { font-size: 12.5px; color: var(--ms-muted); margin: 14px 0 0; }

    /* Chemical rating marks (same scale as the Chemical Resistance Guide) */
    .ms-r { display: inline-block; min-width: 74px; text-align: center; padding: 2px 8px; border-radius: 5px; font-size: 12px; font-weight: 800; }
    .ms-r-A { background: var(--ms-accent); color: #fff; }
    .ms-r-B { background: #a8d8d6; color: #10393b; }
    .ms-r-C { background: #e2e8f0; color: #334155; }
    .ms-r-X { background: #1e293b; color: #fff; }

    /* Compare */
    .ms-compare { display: none; border: 1px solid var(--ms-line); border-radius: 12px; padding: 18px 20px; margin-bottom: 18px; background: #fff; }
    .ms-compare.open { display: block; }
    .ms-compare-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 10px; }
    .ms-compare-head h2 { font-size: 18px; font-weight: 800; color: var(--ms-ink); margin: 0; }
    .ms-compare-scroll { overflow-x: auto; }
    .ms-ctable { width: 100%; min-width: 560px; border-collapse: collapse; font-size: 13.5px; }
    .ms-ctable th, .ms-ctable td { padding: 7px 10px; border-bottom: 1px solid #eef2f3; text-align: left; }
    .ms-ctable thead th { font-size: 14px; font-weight: 800; color: var(--ms-ink); border-bottom: 1px solid var(--ms-line); vertical-align: bottom; }
    .ms-ctable thead th small { display: block; font-size: 12px; font-weight: 500; color: var(--ms-muted); }
    .ms-ctable tbody th { font-weight: 500; color: var(--ms-muted); white-space: nowrap; }
    .ms-ctable td { font-weight: 700; color: var(--ms-ink); font-variant-numeric: tabular-nums; }
    .ms-ctable td.best { color: var(--ms-accent); }

    .ms-tray {
        position: fixed; left: 50%; bottom: 18px; transform: translateX(-50%); z-index: 900;
        display: none; align-items: center; gap: 12px; padding: 10px 12px 10px 18px;
        background: var(--ms-ink); color: #fff; border-radius: 12px; font-size: 13.5px; max-width: calc(100% - 32px);
    }
    .ms-tray.open { display: flex; }
    .ms-tray span { white-space: nowrap; }
    .ms-tray button { font: inherit; font-size: 13px; font-weight: 700; border-radius: 8px; padding: 8px 14px; cursor: pointer; border: 0; }
    .ms-tray .go { background: #fff; color: var(--ms-ink); }
    .ms-tray .clear { background: transparent; color: #b8c6ca; padding: 8px 6px; }

    /* Main table */
    .ms-table-box { border: 1px solid var(--ms-line); border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .ms-table { width: 100%; min-width: 980px; border-collapse: collapse; font-size: 13.5px; }
    .ms-table thead th {
        background: var(--ms-mist); text-align: right; padding: 0; border-bottom: 1px solid var(--ms-line);
        font-size: 12px; font-weight: 700; color: var(--ms-text); white-space: nowrap;
    }
    .ms-table thead th.l { text-align: left; }
    .ms-sort { font: inherit; width: 100%; text-align: inherit; background: none; border: 0; padding: 10px 12px; cursor: pointer; color: inherit; }
    .ms-sort small { display: block; font-weight: 500; color: var(--ms-muted); }
    .ms-sort:hover { color: var(--ms-accent); }
    .ms-sort[aria-sort]::after { content: ''; }
    th[aria-sort="ascending"] .ms-sort b::after { content: ' \2191'; }
    th[aria-sort="descending"] .ms-sort b::after { content: ' \2193'; }
    .ms-table td { padding: 8px 12px; border-bottom: 1px solid #eef2f3; text-align: right; font-variant-numeric: tabular-nums; color: var(--ms-ink); }
    .ms-table tbody tr { cursor: pointer; }
    .ms-table tbody tr:hover, .ms-table tbody tr.selected { background: #f3f8f8; }
    .ms-table tbody tr.selected td:nth-child(2) { box-shadow: inset 3px 0 0 var(--ms-accent); }
    .ms-table td.l { text-align: left; }
    .ms-table td.pick { width: 40px; padding-right: 0; }
    .ms-table td.pick input { width: 16px; height: 16px; accent-color: var(--ms-accent); cursor: pointer; }
    .ms-name { font-weight: 700; color: var(--ms-ink); }
    .ms-std { display: block; font-size: 12px; color: var(--ms-muted); }
    .ms-fam { font-size: 12.5px; color: var(--ms-text); white-space: nowrap; }
    .ms-na { color: #9aa8ad; }
    .ms-empty { display: none; padding: 26px; text-align: center; color: var(--ms-muted); }

    /* Notes */
    .ms-notes { display: grid; grid-template-columns: 1.3fr 1fr; gap: 28px; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--ms-line); }
    .ms-notes h2 { font-size: 16px; font-weight: 800; color: var(--ms-ink); margin: 0 0 8px; }
    .ms-notes p, .ms-notes li { font-size: 13.5px; }
    .ms-notes ul { padding-left: 18px; margin: 0; }
    .ms-notes li { margin-bottom: 4px; }

    @media (max-width: 1000px) {
        .ms-d-grid { grid-template-columns: 1fr 1fr; }
        .ms-d-grid > :last-child { grid-column: 1 / -1; }
    }
    @media (max-width: 720px) {
        .ms-wrap { padding: 0 16px; }
        .ms-intro h1 { font-size: 26px; }
        .ms-d-keys { grid-template-columns: 1fr 1fr; }
        .ms-d-keys div:nth-child(3) { border-left: 0; }
        .ms-d-keys div:nth-child(n+3) { border-top: 1px solid var(--ms-line); }
        .ms-d-grid, .ms-notes { grid-template-columns: 1fr; }
        .ms-count { margin-left: 0; width: 100%; }
        .ms-detail, .ms-compare { padding: 16px; }
        .ms-tray { bottom: 72px; }
    }
</style>

<div class="ms">
    <section class="ms-intro">
        <div class="ms-wrap">
            <div class="ms-crumb"><a href="/toolkit/">Engineering Toolkit</a></div>
            <h1>Material Property Library</h1>
            <p>Typical mechanical, thermal, and chemical resistance properties for the steels, alloys, elastomers, plastics, and ceramics used in expansion joints, linings, seals, and heat exchangers. Click a material for its full sheet, or tick up to three to compare.</p>
            <div class="ms-facts">
                <span><b>{{ count($materials) }}</b> materials</span>
                <span><b>{{ count($families) }}</b> families</span>
                <span>SI and Imperial units</span>
            </div>
        </div>
    </section>

    <div class="ms-wrap">
        <div class="ms-toolbar">
            <div class="ms-search">
                <label for="ms-search" class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">Search materials</label>
                <input id="ms-search" type="search" placeholder="Search grade, standard or use: 316L, A105, Viton, slurry..." autocomplete="off">
            </div>
            <div class="ms-units" role="group" aria-label="Units">
                <button type="button" data-units="si" aria-pressed="true">SI</button>
                <button type="button" data-units="imp" aria-pressed="false">Imperial</button>
            </div>
            <span class="ms-count" id="ms-count" aria-live="polite">{{ count($materials) }} materials</span>
        </div>

        <div class="ms-chips" role="group" aria-label="Material family">
            <button type="button" class="ms-chip" data-family="" aria-pressed="true">All<span>{{ count($materials) }}</span></button>
            @foreach ($families as $family => $label)
                <button type="button" class="ms-chip" data-family="{{ $family }}" aria-pressed="false">{{ $label }}<span>{{ $familyCounts[$family] ?? 0 }}</span></button>
            @endforeach
        </div>

        <div class="ms-compare" id="ms-compare" aria-live="polite"></div>
        <div class="ms-detail" id="ms-detail" aria-live="polite"></div>

        <div class="ms-table-box">
            <table class="ms-table">
                <thead>
                    <tr>
                        <th class="l" scope="col"><span style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);">Compare</span></th>
                        <th class="l" scope="col"><button type="button" class="ms-sort" data-sort="name"><b>Material</b><small>Standard</small></button></th>
                        <th class="l" scope="col"><button type="button" class="ms-sort" data-sort="family"><b>Family</b><small>&nbsp;</small></button></th>
                        <th scope="col"><button type="button" class="ms-sort" data-sort="density"><b>Density</b><small data-unit="density">kg/m³</small></button></th>
                        <th scope="col"><button type="button" class="ms-sort" data-sort="yield"><b>Yield</b><small data-unit="stress">MPa</small></button></th>
                        <th scope="col"><button type="button" class="ms-sort" data-sort="tensile"><b>Tensile</b><small data-unit="stress">MPa</small></button></th>
                        <th scope="col"><button type="button" class="ms-sort" data-sort="modulus"><b>Modulus E</b><small data-unit="modulus">GPa</small></button></th>
                        <th scope="col"><button type="button" class="ms-sort" data-sort="maxTemp"><b>Max temp</b><small data-unit="temp">°C</small></button></th>
                        <th scope="col"><button type="button" class="ms-sort" data-sort="minTemp"><b>Min temp</b><small data-unit="temp">°C</small></button></th>
                    </tr>
                </thead>
                <tbody id="ms-body">
                    @foreach ($materials as $m)
                        <tr id="row-{{ $m['id'] }}" data-id="{{ $m['id'] }}" data-family="{{ $m['family'] }}"
                            data-search="{{ strtolower($m['name'].' '.$m['standard'].' '.$m['family'].' '.$m['uses'].' '.implode(' ', $m['fabrications'])) }}">
                            <td class="pick"><input type="checkbox" value="{{ $m['id'] }}" aria-label="Compare {{ $m['name'] }}"></td>
                            <td class="l"><span class="ms-name">{{ $m['name'] }}</span><span class="ms-std">{{ $m['standard'] }}</span></td>
                            <td class="l ms-fam">{{ $families[$m['family']] }}</td>
                            <td data-q="density">{{ $m['mech']['density'] }}</td>
                            <td data-q="stress" data-v="{{ $m['mech']['yieldStrength'] }}">@if ($m['mech']['yieldStrength'] === null)<span class="ms-na">n/a</span>@else{{ $m['mech']['yieldStrength'] }}@endif</td>
                            <td data-q="stress" data-v="{{ $m['mech']['tensileStrength'] }}">{{ $m['mech']['tensileStrength'] }}</td>
                            <td data-q="modulus" data-v="{{ $m['mech']['elasticModulus'] }}">{{ $m['mech']['elasticModulus'] }}</td>
                            <td data-q="temp" data-v="{{ $m['thermal']['maxTemp'] }}">{{ $m['thermal']['maxTemp'] }}</td>
                            <td data-q="temp" data-v="{{ $m['thermal']['minTemp'] }}">{{ $m['thermal']['minTemp'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="ms-empty" id="ms-empty">No material matches your search. <a href="/contact-us/?category=General+Inquiry#inquiry-form">Ask our engineers</a> and we will check the grade for you.</div>
        </div>

        <div class="ms-notes">
            <div>
                <h2>About these values</h2>
                <ul>
                    <li>Values are typical room-temperature figures for the annealed or standard condition. Certified minimums are set by each standard and by the mill certificate, not by this table.</li>
                    <li>Max and min temperature are general service limits. Pressure, media, and cycling usually lower the practical limit.</li>
                    <li>Chemical ratings are a broad screen at about 20 °C. For a specific chemical and concentration, use the <a href="/toolkit/chemical-resistance/">Chemical Resistance Guide</a>.</li>
                    <li>Yield strength is not given for brittle ceramics, which fail without yielding.</li>
                </ul>
            </div>
            <div>
                <h2>Need a material confirmed?</h2>
                <p>Send us the media, temperature, pressure, and size. Our engineers will recommend a material and quote the expansion joint, lining, hose, or gasket to suit.</p>
                <p><a class="ms-btn ms-btn-primary" href="/contact-us/?category=General+Inquiry#inquiry-form">Request a quote</a></p>
                <p style="font-size:13px;">Sizing a joint? Try the <a href="/toolkit/expansion-joint-selector/">Expansion Joint Selector</a> or <a href="/toolkit/flange-standards/">Flange Standards</a>.</p>
            </div>
        </div>
    </div>

    <div class="ms-tray" id="ms-tray" role="region" aria-label="Compare materials">
        <span id="ms-tray-text"></span>
        <button type="button" class="go" id="ms-tray-go">Compare</button>
        <button type="button" class="clear" id="ms-tray-clear">Clear</button>
    </div>
</div>

<script>
(function () {
    var MATERIALS = @json($materials);
    var FAMILIES = @json($families);
    var RATING = @json($ratingCodes);
    var WHATSAPP = 'https://wa.me/628118715671';

    var byId = {};
    MATERIALS.forEach(function (m) { byId[m.id] = m; });

    var state = { units: 'si', family: '', query: '', selected: null, compare: [], sortKey: null, sortDir: 1 };
    var tbody = document.getElementById('ms-body');
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
    var detail = document.getElementById('ms-detail');
    var compareBox = document.getElementById('ms-compare');
    var tray = document.getElementById('ms-tray');

    // Unit conversions from the stored SI values.
    var UNITS = {
        si: {
            density: ['kg/m³', function (v) { return fmt(v, 0); }],
            stress: ['MPa', function (v) { return fmt(v, 0); }],
            modulus: ['GPa', function (v) { return sig(v); }],
            temp: ['°C', function (v) { return fmt(v, 0); }],
            conductivity: ['W/(m·K)', function (v) { return sig(v); }],
            specificHeat: ['J/(kg·K)', function (v) { return fmt(v, 0); }],
            expansion: ['×10⁻⁶/K', function (v) { return sig(v); }]
        },
        imp: {
            density: ['lb/in³', function (v) { return fmt(v * 0.0000361273, 3); }],
            stress: ['ksi', function (v) { return sig(v * 0.145038); }],
            modulus: ['Msi', function (v) { return sig(v * 0.145038); }],
            temp: ['°F', function (v) { return fmt(v * 9 / 5 + 32, 0); }],
            conductivity: ['Btu/(h·ft·°F)', function (v) { return sig(v * 0.577789); }],
            specificHeat: ['Btu/(lb·°F)', function (v) { return fmt(v * 0.000238846, 3); }],
            expansion: ['×10⁻⁶/°F', function (v) { return sig(v * 5 / 9); }]
        }
    };

    function fmt(v, dp) {
        return Number(v).toLocaleString('en-US', { minimumFractionDigits: dp, maximumFractionDigits: dp, useGrouping: false });
    }
    function sig(v) {
        if (v === 0) return '0';
        var a = Math.abs(v);
        var dp = a >= 100 ? 0 : a >= 10 ? 1 : a >= 1 ? 2 : 3;
        return String(Number(v.toFixed(dp)));
    }
    function val(q, v) {
        if (v === null || v === undefined) return '<span class="ms-na">n/a</span>';
        return UNITS[state.units][q][1](v);
    }
    function unit(q) { return UNITS[state.units][q][0]; }
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function rating(r) { return '<span class="ms-r ms-r-' + RATING[r] + '">' + esc(r) + '</span>'; }

    function sheetRow(label, q, v) {
        return '<tr><th>' + label + '</th><td>' + val(q, v) + (v === null ? '' : '<small>' + unit(q) + '</small>') + '</td></tr>';
    }
    function plainRow(label, v, u) {
        return '<tr><th>' + label + '</th><td>' + esc(v) + (u ? '<small>' + u + '</small>' : '') + '</td></tr>';
    }

    var CHEM_LABELS = [
        ['acids', 'Acids'], ['alkalis', 'Alkalis'], ['solvents', 'Organic solvents'],
        ['oils', 'Oils & hydrocarbons'], ['steam', 'Steam'], ['seawater', 'Seawater']
    ];

    function quoteCategory(m) {
        return m.products.length ? m.products[0].category : 'General Inquiry';
    }
    function quoteHref(m, item, category) {
        return '/contact-us/?category=' + encodeURIComponent(category || quoteCategory(m))
            + '&message=' + encodeURIComponent(inquiryText(m, item)) + '#inquiry-form';
    }
    function inquiryText(m, item) {
        var options = m.products.map(function (p) { return p.name; }).concat(m.fabrications);
        return 'Material inquiry (from Material Property Library)\n'
            + '- Material: ' + m.name + ' (' + m.standard + ')\n'
            + '- Product needed: ' + (item || options.join(' / ')) + '\n'
            + '- Media, temperature and pressure: \n'
            + '- Size / quantity: ';
    }

    // ---- Detail panel -------------------------------------------------
    function renderDetail() {
        var m = byId[state.selected];
        if (!m) { detail.classList.remove('open'); detail.innerHTML = ''; return; }
        var me = m.mech, th = m.thermal;
        var quote = quoteHref(m);
        var wa = WHATSAPP + '?text=' + encodeURIComponent('Hello Misuba Guna, I need a quote for a product in ' + m.name + '.');
        var inCompare = state.compare.indexOf(m.id) !== -1;

        var html = '<div class="ms-d-top"><div>'
            + '<div class="ms-d-family">' + esc(FAMILIES[m.family]) + '</div>'
            + '<h2>' + esc(m.name) + '</h2>'
            + '<div class="ms-d-std">' + esc(m.standard) + '</div></div>'
            + '<div class="ms-d-actions">'
            + '<a class="ms-btn ms-btn-primary" href="' + quote + '">Request a quote</a>'
            + '<a class="ms-btn ms-btn-wa" href="' + wa + '" target="_blank" rel="noopener">WhatsApp</a>'
            + '<button type="button" class="ms-btn" data-act="compare">' + (inCompare ? 'Remove from compare' : 'Add to compare') + '</button>'
            + '<button type="button" class="ms-btn" data-act="copy">Copy specs</button>'
            + '<button type="button" class="ms-btn ms-btn-close" data-act="close" aria-label="Close">Close &times;</button>'
            + '</div></div>';

        html += '<dl class="ms-d-keys">'
            + key('Yield strength', 'stress', me.yieldStrength)
            + key('Tensile strength', 'stress', me.tensileStrength)
            + key('Max service temp', 'temp', th.maxTemp)
            + key('Density', 'density', me.density)
            + '</dl>';

        html += '<div class="ms-d-grid">'
            + '<div><h3>Mechanical &amp; physical</h3><table class="ms-sheet"><tbody>'
            + sheetRow('Elastic modulus E', 'modulus', me.elasticModulus)
            + sheetRow('Shear modulus G', 'modulus', me.shearModulus)
            + plainRow('Poisson’s ratio ν', me.poissonRatio)
            + sheetRow('Yield strength', 'stress', me.yieldStrength)
            + sheetRow('Tensile strength', 'stress', me.tensileStrength)
            + plainRow('Elongation at break', me.elongation, '%')
            + plainRow('Hardness', me.hardness)
            + '</tbody></table></div>'
            + '<div><h3>Thermal</h3><table class="ms-sheet"><tbody>'
            + sheetRow('Conductivity k', 'conductivity', th.conductivity)
            + sheetRow('Specific heat cp', 'specificHeat', th.specificHeat)
            + sheetRow('Thermal expansion α', 'expansion', th.expansionCoeff)
            + sheetRow('Max service temp', 'temp', th.maxTemp)
            + sheetRow('Min service temp', 'temp', th.minTemp)
            + '</tbody></table>'
            + '<h3 style="margin-top:16px;">Chemical resistance</h3><table class="ms-sheet"><tbody>'
            + CHEM_LABELS.map(function (c) { return '<tr><th>' + c[1] + '</th><td>' + rating(m.chem[c[0]]) + '</td></tr>'; }).join('')
            + '</tbody></table></div>'
            + '<div class="ms-d-text"><h3>About this material</h3>'
            + '<p>' + esc(m.desc) + '</p>';
        if (m.products.length || m.fabrications.length) {
            html += '<h3 style="margin-top:14px;">We supply it in</h3><div class="ms-d-products">'
                + m.products.map(function (p) { return '<a href="' + p.url + '">' + esc(p.name) + '</a>'; }).join('')
                + m.fabrications.map(function (f) {
                    return '<a class="fab" href="' + quoteHref(m, f, 'General Inquiry') + '" title="Request a quote">' + esc(f) + '</a>';
                }).join('')
                + '</div>';
        }
        html += '</div></div>'
            + '<p class="ms-d-note">Typical values for preliminary selection. Final material choice should be confirmed against the governing standard and your process conditions.</p>';

        detail.innerHTML = html;
        detail.classList.add('open');
    }
    function key(label, q, v) {
        return '<div><dt>' + label + '</dt><dd>' + val(q, v) + (v === null ? '' : '<small>' + unit(q) + '</small>') + '</dd></div>';
    }

    function select(id, scroll) {
        state.selected = id;
        rows.forEach(function (r) { r.classList.toggle('selected', r.dataset.id === id); });
        renderDetail();
        if (id && history.replaceState) history.replaceState(null, '', '#' + id);
        if (id && scroll) {
            var top = detail.getBoundingClientRect().top + window.pageYOffset - 100;
            window.scrollTo({ top: top, behavior: 'smooth' });
        }
    }
    function closeDetail() {
        state.selected = null;
        rows.forEach(function (r) { r.classList.remove('selected'); });
        renderDetail();
        if (history.replaceState) history.replaceState(null, '', location.pathname + location.search);
    }

    function copySpecs() {
        var m = byId[state.selected];
        if (!m) return;
        var me = m.mech, th = m.thermal;
        var lines = [
            m.name + ' (' + m.standard + ')',
            'Density: ' + val('density', me.density) + ' ' + unit('density'),
            'Yield strength: ' + (me.yieldStrength === null ? 'n/a' : val('stress', me.yieldStrength) + ' ' + unit('stress')),
            'Tensile strength: ' + val('stress', me.tensileStrength) + ' ' + unit('stress'),
            'Elastic modulus: ' + val('modulus', me.elasticModulus) + ' ' + unit('modulus'),
            'Elongation: ' + me.elongation + ' %',
            'Hardness: ' + me.hardness,
            'Thermal conductivity: ' + val('conductivity', th.conductivity) + ' ' + unit('conductivity'),
            'Thermal expansion: ' + val('expansion', th.expansionCoeff) + ' ' + unit('expansion'),
            'Service temperature: ' + val('temp', th.minTemp) + ' to ' + val('temp', th.maxTemp) + ' ' + unit('temp'),
            'Source: misubaguna.com/toolkit/material-specs/#' + m.id
        ];
        var btn = detail.querySelector('[data-act="copy"]');
        var done = function () { if (btn) { btn.textContent = 'Copied'; setTimeout(function () { btn.textContent = 'Copy specs'; }, 1800); } };
        if (navigator.clipboard) navigator.clipboard.writeText(lines.join('\n')).then(done, function () {});
    }

    detail.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-act]');
        if (!btn) return;
        var act = btn.dataset.act;
        if (act === 'close') closeDetail();
        if (act === 'copy') copySpecs();
        if (act === 'compare') toggleCompare(state.selected);
    });

    // ---- Compare ------------------------------------------------------
    function toggleCompare(id) {
        var i = state.compare.indexOf(id);
        if (i !== -1) state.compare.splice(i, 1);
        else if (state.compare.length < 3) state.compare.push(id);
        else { state.compare.shift(); state.compare.push(id); }
        syncCompare();
    }
    function syncCompare() {
        rows.forEach(function (r) { r.querySelector('input').checked = state.compare.indexOf(r.dataset.id) !== -1; });
        var n = state.compare.length;
        document.getElementById('ms-tray-text').textContent = n + ' of 3 selected';
        document.getElementById('ms-tray-go').disabled = n < 2;
        tray.classList.toggle('open', n > 0);
        if (compareBox.classList.contains('open')) renderCompare();
        if (state.selected) renderDetail();
    }
    function renderCompare() {
        var list = state.compare.map(function (id) { return byId[id]; });
        if (list.length < 2) { compareBox.classList.remove('open'); compareBox.innerHTML = ''; return; }

        function line(label, q, get, higherIsBetter) {
            var vals = list.map(get);
            var nums = vals.filter(function (v) { return v !== null; });
            var best = higherIsBetter === undefined || !nums.length ? null
                : (higherIsBetter ? Math.max.apply(null, nums) : Math.min.apply(null, nums));
            return '<tr><th>' + label + (q ? ' <span style="color:#9aa8ad;">' + unit(q) + '</span>' : '') + '</th>'
                + vals.map(function (v) {
                    var cls = best !== null && v === best && nums.length > 1 ? ' class="best"' : '';
                    return '<td' + cls + '>' + (q ? val(q, v) : esc(v)) + '</td>';
                }).join('') + '</tr>';
        }
        function chemLine(label, k) {
            return '<tr><th>' + label + '</th>' + list.map(function (m) { return '<td>' + rating(m.chem[k]) + '</td>'; }).join('') + '</tr>';
        }

        var html = '<div class="ms-compare-head"><h2>Compare materials</h2>'
            + '<button type="button" class="ms-btn ms-btn-close" data-act="close-compare">Close &times;</button></div>'
            + '<div class="ms-compare-scroll"><table class="ms-ctable"><thead><tr><th></th>'
            + list.map(function (m) { return '<th>' + esc(m.name) + '<small>' + esc(m.standard) + '</small></th>'; }).join('')
            + '</tr></thead><tbody>'
            + line('Density', 'density', function (m) { return m.mech.density; })
            + line('Yield strength', 'stress', function (m) { return m.mech.yieldStrength; }, true)
            + line('Tensile strength', 'stress', function (m) { return m.mech.tensileStrength; }, true)
            + line('Elastic modulus', 'modulus', function (m) { return m.mech.elasticModulus; })
            + line('Elongation', null, function (m) { return m.mech.elongation + ' %'; })
            + line('Hardness', null, function (m) { return m.mech.hardness; })
            + line('Thermal conductivity', 'conductivity', function (m) { return m.thermal.conductivity; })
            + line('Thermal expansion', 'expansion', function (m) { return m.thermal.expansionCoeff; })
            + line('Max service temp', 'temp', function (m) { return m.thermal.maxTemp; }, true)
            + line('Min service temp', 'temp', function (m) { return m.thermal.minTemp; }, false)
            + CHEM_LABELS.map(function (c) { return chemLine(c[1], c[0]); }).join('')
            + '</tbody></table></div>'
            + '<p class="ms-d-note">Highlighted values are the highest strength and widest temperature range among the selected materials.</p>';
        compareBox.innerHTML = html;
        compareBox.classList.add('open');
    }

    compareBox.addEventListener('click', function (e) {
        if (e.target.closest('[data-act="close-compare"]')) { compareBox.classList.remove('open'); compareBox.innerHTML = ''; }
    });
    document.getElementById('ms-tray-go').addEventListener('click', function () {
        renderCompare();
        var top = compareBox.getBoundingClientRect().top + window.pageYOffset - 100;
        window.scrollTo({ top: top, behavior: 'smooth' });
    });
    document.getElementById('ms-tray-clear').addEventListener('click', function () {
        state.compare = [];
        compareBox.classList.remove('open');
        syncCompare();
    });

    // ---- Table: filter, sort, units ----------------------------------
    function filter() {
        var q = state.query, shown = 0;
        rows.forEach(function (r) {
            var ok = (!state.family || r.dataset.family === state.family) && (!q || r.dataset.search.indexOf(q) !== -1);
            r.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        document.getElementById('ms-count').textContent = shown + (shown === 1 ? ' material' : ' materials');
        document.getElementById('ms-empty').style.display = shown ? 'none' : 'block';
    }

    var SORTERS = {
        name: function (m) { return m.name.toLowerCase(); },
        family: function (m) { return Object.keys(FAMILIES).indexOf(m.family); },
        density: function (m) { return m.mech.density; },
        yield: function (m) { return m.mech.yieldStrength; },
        tensile: function (m) { return m.mech.tensileStrength; },
        modulus: function (m) { return m.mech.elasticModulus; },
        maxTemp: function (m) { return m.thermal.maxTemp; },
        minTemp: function (m) { return m.thermal.minTemp; }
    };
    function sortBy(k) {
        state.sortDir = state.sortKey === k ? -state.sortDir : (k === 'name' || k === 'family' ? 1 : -1);
        state.sortKey = k;
        var get = SORTERS[k];
        rows.sort(function (a, b) {
            var x = get(byId[a.dataset.id]), y = get(byId[b.dataset.id]);
            if (x === null) return 1;
            if (y === null) return -1;
            return (x < y ? -1 : x > y ? 1 : 0) * state.sortDir;
        });
        rows.forEach(function (r) { tbody.appendChild(r); });
        document.querySelectorAll('.ms-table thead th').forEach(function (th) {
            var b = th.querySelector('.ms-sort');
            if (b && b.dataset.sort === k) th.setAttribute('aria-sort', state.sortDir === 1 ? 'ascending' : 'descending');
            else th.removeAttribute('aria-sort');
        });
    }

    function applyUnits() {
        document.querySelectorAll('[data-unit]').forEach(function (el) { el.textContent = unit(el.dataset.unit); });
        rows.forEach(function (r) {
            var m = byId[r.dataset.id];
            var cells = r.querySelectorAll('td[data-q]');
            var v = [m.mech.density, m.mech.yieldStrength, m.mech.tensileStrength, m.mech.elasticModulus, m.thermal.maxTemp, m.thermal.minTemp];
            cells.forEach(function (td, i) { td.innerHTML = val(td.dataset.q, v[i]); });
        });
        renderDetail();
        if (compareBox.classList.contains('open')) renderCompare();
    }

    tbody.addEventListener('click', function (e) {
        var row = e.target.closest('tr');
        if (!row) return;
        if (e.target.matches('input[type="checkbox"]')) { toggleCompare(row.dataset.id); return; }
        if (e.target.closest('td.pick')) return;
        select(row.dataset.id, true);
    });
    document.querySelectorAll('.ms-sort').forEach(function (b) {
        b.addEventListener('click', function () { sortBy(b.dataset.sort); });
    });
    document.querySelectorAll('.ms-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            state.family = chip.dataset.family;
            document.querySelectorAll('.ms-chip').forEach(function (c) { c.setAttribute('aria-pressed', String(c === chip)); });
            filter();
        });
    });
    document.querySelectorAll('.ms-units button').forEach(function (b) {
        b.addEventListener('click', function () {
            state.units = b.dataset.units;
            document.querySelectorAll('.ms-units button').forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
            applyUnits();
        });
    });
    document.getElementById('ms-search').addEventListener('input', function (e) {
        state.query = e.target.value.trim().toLowerCase();
        filter();
    });

    function openFromHash() {
        var id = location.hash.replace('#', '');
        if (byId[id] && id !== state.selected) select(id, true);
    }
    window.addEventListener('hashchange', openFromHash);
    openFromHash();
})();
</script>
@endsection
