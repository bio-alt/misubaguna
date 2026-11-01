@extends('public.layout')

@section('title', 'Gear Calculator: Spur, Helical, Bevel & Worm | PT Misuba Guna Indonesia')
@section('meta_description', 'Free involute gear calculator per ISO 21771: spur, helical, gear pair, straight bevel and worm gear dimensions, span measurement, contact ratio, and module identification from a sample.')

@section('content')
<style>
    .gc {
        --gc-ink: #10202a;
        --gc-text: #3a4a53;
        --gc-muted: #66767f;
        --gc-line: #dbe3e5;
        --gc-mist: #edf3f3;
        --gc-accent: #176b70;
        --gc-accent-dark: #114f53;
        --gc-accent-soft: #d6eaea;
        --gc-wa: #168a49;
        --gc-ok: #1d7a46;
        --gc-ok-bg: #e3f3e9;
        --gc-warn: #8a5a00;
        --gc-warn-bg: #fdf1d6;
        --gc-err: #c0392b;
        --gc-err-bg: #fbe7e5;

        font-size: 15px;
        line-height: 1.55;
        color: var(--gc-text);
        background: #fff;
        padding-bottom: 64px;
    }
    .gc *, .gc *::before, .gc *::after { box-sizing: border-box; }
    .gc a { color: var(--gc-accent); text-decoration: none; }
    .gc a:hover { color: var(--gc-accent-dark); text-decoration: underline; text-underline-offset: 3px; }
    .gc :focus-visible { outline: 3px solid var(--gc-accent); outline-offset: 2px; }
    .gc-wrap { max-width: 1280px; margin: 0 auto; padding: 0 28px; }
    .gc [hidden] { display: none !important; }

    /* Intro */
    .gc-intro { background: var(--gc-mist); border-bottom: 1px solid var(--gc-line); padding: 36px 0 26px; }
    .gc-crumb { font-size: 12px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--gc-accent); margin-bottom: 8px; }
    .gc-intro h1 { font-size: 30px; line-height: 1.2; font-weight: 800; color: var(--gc-ink); margin: 0 0 8px; }
    .gc-intro p { max-width: 780px; margin: 0; }
    .gc-facts { display: flex; flex-wrap: wrap; gap: 6px 24px; margin-top: 14px; font-size: 13.5px; }
    .gc-facts b { color: var(--gc-ink); }

    /* Steps */
    .gc-step { margin: 24px 0 0; padding: 0; border: 0; min-width: 0; }
    .gc-step > legend, .gc-h { font-size: 13px; font-weight: 800; letter-spacing: 0.06em; text-transform: uppercase; color: var(--gc-ink); margin: 0 0 10px; padding: 0; }
    .gc-step > legend span, .gc-h span { color: var(--gc-accent); margin-right: 6px; }
    .gc-types { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 10px; }
    .gc-type { position: relative; display: flex; gap: 10px; align-items: flex-start; padding: 12px; border: 1px solid var(--gc-line); border-radius: 10px; background: #fff; cursor: pointer; }
    .gc-type:hover { border-color: var(--gc-accent); }
    .gc-type input { position: absolute; opacity: 0; pointer-events: none; }
    .gc-type:has(input:checked) { border-color: var(--gc-accent); box-shadow: inset 0 0 0 1px var(--gc-accent); background: #f7fbfb; }
    .gc-type:has(input:focus-visible) { outline: 3px solid var(--gc-accent); outline-offset: 2px; }
    .gc-type svg { flex: 0 0 40px; width: 40px; height: 40px; color: var(--gc-accent); }
    .gc-type b { display: block; font-size: 14px; color: var(--gc-ink); line-height: 1.3; }
    .gc-type small { display: block; font-size: 12.5px; color: var(--gc-muted); line-height: 1.4; margin-top: 2px; }

    .gc-modes { display: flex; flex-wrap: wrap; gap: 6px; }
    .gc-chip { position: relative; display: inline-flex; font-size: 13px; font-weight: 700; padding: 7px 14px; cursor: pointer; border: 1px solid #c4d0d3; border-radius: 999px; background: #fff; color: var(--gc-text); }
    .gc-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .gc-chip:hover { border-color: var(--gc-accent); color: var(--gc-accent); }
    .gc-chip:has(input:checked) { background: var(--gc-ink); border-color: var(--gc-ink); color: #fff; }
    .gc-chip:has(input:focus-visible) { outline: 3px solid var(--gc-accent); outline-offset: 2px; }

    /* Layout */
    .gc-main { display: grid; grid-template-columns: minmax(280px, 360px) minmax(0, 1fr); gap: 28px; margin-top: 22px; align-items: start; }
    .gc-side { position: sticky; top: 96px; }
    .gc-fields { display: grid; gap: 12px; }
    .gc-field label { display: block; font-size: 13px; font-weight: 700; color: var(--gc-ink); margin-bottom: 4px; }
    .gc-sym { font-weight: 600; font-style: italic; color: var(--gc-muted); }
    .gc-opt { font-weight: 500; color: var(--gc-muted); }
    .gc-input { display: flex; align-items: stretch; border: 1px solid #c4d0d3; border-radius: 8px; background: #fff; overflow: hidden; }
    .gc-input:focus-within { border-color: var(--gc-accent); box-shadow: 0 0 0 3px var(--gc-accent-soft); }
    .gc-input input, .gc-input select { flex: 1; min-width: 0; padding: 9px 11px; font: inherit; font-size: 14px; font-variant-numeric: tabular-nums; color: var(--gc-ink); border: 0; background: transparent; outline: none; }
    .gc-input input[aria-invalid="true"] { background: var(--gc-err-bg); }
    .gc-u { display: flex; align-items: center; padding: 0 11px; font-size: 13px; color: var(--gc-muted); background: var(--gc-mist); border-left: 1px solid var(--gc-line); }
    .gc-unit { display: flex; border-left: 1px solid var(--gc-line); }
    .gc-unit button { font: inherit; font-size: 12px; font-weight: 700; padding: 0 10px; border: 0; background: var(--gc-mist); color: var(--gc-muted); cursor: pointer; }
    .gc-unit button[aria-pressed="true"] { background: var(--gc-ink); color: #fff; }
    .gc-help { font-size: 12px; color: var(--gc-muted); margin-top: 3px; }
    .gc-err { font-size: 12.5px; color: var(--gc-err); min-height: 0; }
    .gc-err:not(:empty) { margin-top: 3px; }
    .gc-general { margin-top: 12px; padding: 10px 12px; border-radius: 8px; background: var(--gc-err-bg); color: var(--gc-err); font-size: 13px; }
    .gc-callout { margin-top: 14px; padding: 10px 12px; border-left: 3px solid var(--gc-accent); background: var(--gc-mist); font-size: 13px; border-radius: 0 8px 8px 0; }

    /* Results */
    .gc-results { min-height: 900px; transition: opacity .15s; }
    .gc-results.is-stale { opacity: .55; }
    .gc-res-head { display: flex; flex-wrap: wrap; gap: 8px 16px; align-items: baseline; justify-content: space-between; margin-bottom: 12px; }
    .gc-res-head h2 { font-size: 20px; font-weight: 800; color: var(--gc-ink); margin: 0; }
    .gc-checks { list-style: none; margin: 0 0 16px; padding: 0; display: grid; gap: 6px; }
    .gc-check { display: flex; gap: 10px; align-items: flex-start; font-size: 13.5px; }
    .gc-pill { flex: 0 0 auto; min-width: 64px; text-align: center; font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; padding: 3px 8px; border-radius: 999px; }
    .is-ok .gc-pill { background: var(--gc-ok-bg); color: var(--gc-ok); }
    .is-warn .gc-pill { background: var(--gc-warn-bg); color: var(--gc-warn); }
    .is-error .gc-pill { background: var(--gc-err-bg); color: var(--gc-err); }
    .gc-check b { color: var(--gc-ink); }

    .gc-figure { margin: 0 0 18px; border: 1px solid var(--gc-line); border-radius: 12px; padding: 12px; background: #fff; }
    .gc-svgbox { aspect-ratio: 4 / 3; max-height: 460px; width: 100%; }
    .gc-svgbox svg { width: 100%; height: 100%; display: block; }
    #gc-svg .s-gear { fill: var(--gc-accent-soft); stroke: var(--gc-accent); stroke-width: 1.3; vector-effect: non-scaling-stroke; stroke-linejoin: round; }
    #gc-svg .s-gear2 { fill: #e4ebed; stroke: var(--gc-ink); }
    #gc-svg .s-c { fill: none; stroke-width: 1; vector-effect: non-scaling-stroke; }
    #gc-svg .s-tip { stroke: var(--gc-ink); stroke-dasharray: 2 3; }
    #gc-svg .s-ref { fill: none; stroke: var(--gc-accent); stroke-width: 1; stroke-dasharray: 8 4; vector-effect: non-scaling-stroke; }
    #gc-svg .s-base { stroke: var(--gc-muted); stroke-dasharray: 8 3 2 3; }
    #gc-svg .s-root { stroke: #9aa8ae; stroke-dasharray: 1 3; }
    #gc-svg .s-axis { stroke: var(--gc-muted); stroke-width: 1; stroke-dasharray: 10 3 2 3; vector-effect: non-scaling-stroke; fill: none; }
    #gc-svg .s-loa, #gc-svg .s-dim { stroke: var(--gc-ink); stroke-width: 1.4; vector-effect: non-scaling-stroke; }
    #gc-svg .s-pitch { stroke: var(--gc-accent); stroke-width: 1; stroke-dasharray: 8 4; vector-effect: non-scaling-stroke; }
    #gc-svg .s-ref-l { stroke: var(--gc-accent); stroke-width: 1; stroke-dasharray: 8 4; vector-effect: non-scaling-stroke; }
    #gc-svg .s-dot { fill: var(--gc-ink); }
    #gc-svg .s-text { fill: var(--gc-ink); font-family: inherit; font-weight: 600; }
    #gc-svg .s-muted { fill: var(--gc-muted); }
    .gc-legend { list-style: none; margin: 8px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 4px 16px; font-size: 12.5px; font-variant-numeric: tabular-nums; }
    .gc-legend li { display: inline-flex; align-items: center; gap: 6px; }
    .gc-swatch { display: inline-block; width: 22px; height: 0; border-top: 2px solid var(--gc-ink); }
    .gc-swatch.s-tip { border-top-style: dotted; }
    .gc-swatch.s-ref, .gc-swatch.s-pitch { border-top: 2px dashed var(--gc-accent); }
    .gc-swatch.s-base { border-top: 2px dashed var(--gc-muted); }
    .gc-swatch.s-root { border-top: 2px dotted #9aa8ae; }
    .gc-swatch.s-gear-sw { height: 10px; border: 1px solid var(--gc-accent); background: var(--gc-accent-soft); }
    .gc-draw-note { font-size: 12px; color: var(--gc-muted); margin: 4px 0 0; }

    .gc-tables { display: grid; grid-template-columns: repeat(auto-fit, minmax(330px, 1fr)); gap: 16px; align-items: start; }
    .gc-table { width: 100%; border-collapse: collapse; font-size: 13.5px; border: 1px solid var(--gc-line); border-radius: 10px; overflow: hidden; }
    .gc-table caption { text-align: left; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: var(--gc-ink); padding: 0 0 6px; }
    .gc-table th, .gc-table td { padding: 6px 10px; border-top: 1px solid var(--gc-line); vertical-align: top; text-align: left; }
    .gc-table tr:first-child th, .gc-table tr:first-child td { border-top: 0; }
    .gc-table th { font-weight: 600; color: var(--gc-ink); }
    .gc-formula { display: block; font-weight: 400; font-size: 11.5px; color: var(--gc-muted); font-family: ui-monospace, Consolas, monospace; margin-top: 1px; }
    .gc-sym-c { font-style: italic; color: var(--gc-muted); white-space: nowrap; }
    .gc-val { text-align: right !important; white-space: nowrap; font-variant-numeric: tabular-nums; color: var(--gc-ink); font-weight: 700; }
    .gc-unit-c { font-weight: 500; color: var(--gc-muted); }
    .gc-details { grid-column: 1 / -1; border: 1px solid var(--gc-line); border-radius: 10px; padding: 10px 12px; }
    .gc-details summary { cursor: pointer; font-weight: 700; color: var(--gc-ink); }
    .gc-details[open] { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 14px; }
    .gc-details[open] summary { grid-column: 1 / -1; }

    /* Actions */
    .gc-actions { margin-top: 22px; padding: 18px 20px; border: 1px solid var(--gc-line); border-top: 3px solid var(--gc-accent); border-radius: 12px; }
    .gc-actions h2 { font-size: 18px; font-weight: 800; color: var(--gc-ink); margin: 0 0 4px; }
    .gc-actions p { margin: 0 0 12px; font-size: 13.5px; }
    .gc-contact { display: flex; flex-wrap: wrap; gap: 6px 22px; font-size: 14px; margin-bottom: 12px; }
    .gc-contact b { color: var(--gc-ink); user-select: all; }
    .gc-btns { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .gc-btn { display: inline-flex; align-items: center; gap: 6px; font: inherit; font-size: 13.5px; font-weight: 700; padding: 10px 16px; border-radius: 8px; cursor: pointer; white-space: nowrap; border: 1px solid #c4d0d3; background: #fff; color: var(--gc-ink); }
    .gc-btn:hover { border-color: var(--gc-accent); }
    .gc a.gc-btn-primary { background: var(--gc-accent); border-color: var(--gc-accent); color: #fff; }
    .gc a.gc-btn-primary:hover { background: var(--gc-accent-dark); color: #fff; text-decoration: none; }
    .gc a.gc-btn-wa { color: var(--gc-wa); }
    .gc a.gc-btn:hover { text-decoration: none; }
    .gc-copy-status { font-size: 13px; color: var(--gc-ok); }
    .gc-spec { margin-top: 12px; }
    .gc-spec summary { cursor: pointer; font-size: 13px; font-weight: 700; color: var(--gc-ink); }
    .gc-spec pre { margin: 8px 0 0; padding: 12px; background: var(--gc-mist); border-radius: 8px; font-size: 12.5px; white-space: pre-wrap; word-break: break-word; font-variant-numeric: tabular-nums; color: var(--gc-ink); }

    /* Reference */
    .gc-ref { margin-top: 44px; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px; }
    .gc-ref h2 { font-size: 18px; font-weight: 800; color: var(--gc-ink); margin: 0 0 8px; }
    .gc-ref h3 { font-size: 15px; font-weight: 800; color: var(--gc-ink); margin: 14px 0 4px; }
    .gc-ref ul { margin: 0; padding-left: 18px; }
    .gc-ref li { margin-bottom: 5px; }
    .gc-ref code { font-family: ui-monospace, Consolas, monospace; font-size: 12.5px; color: var(--gc-ink); }
    .gc-ref dl { margin: 0; }
    .gc-ref dt { font-weight: 700; color: var(--gc-ink); margin-top: 10px; }
    .gc-ref dd { margin: 2px 0 0; }
    .gc-fine { margin-top: 28px; padding-top: 16px; border-top: 1px solid var(--gc-line); font-size: 12.5px; color: var(--gc-muted); }

    @media (max-width: 1100px) {
        .gc-types { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 900px) {
        .gc-main { grid-template-columns: 1fr; }
        .gc-side { position: static; }
        .gc-results { min-height: 0; }
    }
    @media (max-width: 760px) {
        .gc-wrap { padding: 0 16px; }
        .gc-intro h1 { font-size: 25px; }
        .gc-types { grid-template-columns: 1fr 1fr; }
        .gc-type { padding: 10px; }
        .gc-type svg { flex-basis: 32px; width: 32px; height: 32px; }
        .gc-tables { grid-template-columns: 1fr; }
        .gc-details[open] { grid-template-columns: 1fr; }
        .gc-table { font-size: 13px; }
        .gc-table th, .gc-table td { padding: 6px 7px; }
    }
</style>

<div class="gc">
    <div class="gc-intro">
        <div class="gc-wrap">
            <div class="gc-crumb">Engineering Toolkit</div>
            <h1>Gear Calculator</h1>
            <p>Calculate the geometry of spur, helical, bevel and worm gears, check a gear pair, or identify the module and profile shift of a worn sample gear for repair and replacement. Results update as you type, and the link reproduces the calculation.</p>
            <div class="gc-facts">
                <span><b>ISO 21771</b> involute geometry, ISO 53 basic rack</span>
                <span><b>Module or DP</b> (m = 25.4 / DP)</span>
                <span><b>Reverse engineering</b> from tip diameter and span</span>
            </div>
        </div>
    </div>

    <div class="gc-wrap">
        <noscript><p class="gc-callout">The calculator needs JavaScript. The formulas it uses are listed below.</p></noscript>

        <form id="gc-app" hidden onsubmit="return false" novalidate>
            <fieldset class="gc-step">
                <legend><span>1</span>Gear type</legend>
                <div class="gc-types">
                    @php
                        $types = [
                            'spur' => ['Spur gear', 'Straight teeth parallel to the axis.', '<circle cx="20" cy="20" r="13" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 3v5M20 32v5M3 20h5M32 20h5M8 8l3.5 3.5M28.5 28.5L32 32M8 32l3.5-3.5M28.5 11.5L32 8" stroke="currentColor" stroke-width="3"/><circle cx="20" cy="20" r="4" fill="currentColor"/>'],
                            'helical' => ['Helical gear', 'Teeth at a helix angle β, normal module.', '<rect x="6" y="8" width="28" height="24" rx="3" fill="none" stroke="currentColor" stroke-width="2"/><path d="M10 32l8-24M17 32l8-24M24 32l8-24" stroke="currentColor" stroke-width="2"/>'],
                            'pair' => ['Gear pair', 'Spur or helical mesh, centre distance and Σx.', '<circle cx="14" cy="22" r="10" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="29" cy="14" r="7" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="14" cy="22" r="2.5" fill="currentColor"/><circle cx="29" cy="14" r="2" fill="currentColor"/>'],
                            'bevel' => ['Straight bevel pair', 'Intersecting shafts, equal addendum.', '<path d="M6 34L20 20L6 6M20 20L34 34" fill="none" stroke="currentColor" stroke-width="2"/><path d="M8 30l6-6M8 10l6 6M30 28l-4-4" stroke="currentColor" stroke-width="4"/>'],
                            'worm' => ['Worm & wheel', 'Crossed shafts, axial module, high ratio.', '<rect x="5" y="5" width="30" height="10" rx="2" fill="none" stroke="currentColor" stroke-width="2"/><path d="M10 5l3 10M16 5l3 10M22 5l3 10M28 5l3 10" stroke="currentColor" stroke-width="1.5"/><circle cx="20" cy="28" r="9" fill="none" stroke="currentColor" stroke-width="2"/>'],
                        ];
                    @endphp
                    @foreach ($types as $value => [$name, $desc, $icon])
                        <label class="gc-type">
                            <input type="radio" name="gc-type" value="{{ $value }}" @checked($loop->first)>
                            <svg viewBox="0 0 40 40" aria-hidden="true">{!! $icon !!}</svg>
                            <span><b>{{ $name }}</b><small>{{ $desc }}</small></span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="gc-step">
                <legend><span>2</span>What do you know?</legend>
                <div class="gc-modes" id="gc-modes"></div>
            </fieldset>

            <div class="gc-main">
                <div class="gc-side">
                    <div class="gc-h" id="gc-inputs-h"><span>3</span>Inputs</div>
                    <div class="gc-fields" id="gc-fields" role="group" aria-labelledby="gc-inputs-h"></div>
                    <div class="gc-general" id="gc-general" role="alert" hidden></div>
                    <p class="gc-callout" id="gc-note-identify" hidden>Tip diameters are often reduced by tip chamfer, tip shortening or wear, so the span measurement is the more reliable input. Measure over unworn teeth, and choose k so the caliper jaws touch the flanks near mid-height.</p>
                    <p class="gc-callout" id="gc-note-bevel" hidden>Gleason designs use unequal addenda. This tool gives standard equal-addendum proportions at the outer (heel) end.</p>
                    <datalist id="gc-mod-list">@foreach ([0.5, 0.6, 0.7, 0.8, 0.9, 1, 1.125, 1.25, 1.375, 1.5, 1.75, 2, 2.25, 2.5, 2.75, 3, 3.5, 4, 4.5, 5, 5.5, 6, 7, 8, 9, 10, 11, 12, 14, 16, 18, 20, 22, 25, 28, 32, 36, 40, 45, 50] as $m)<option value="{{ $m }}"></option>@endforeach</datalist>
                    <datalist id="gc-dp-list">@foreach ([1, 1.25, 1.5, 1.75, 2, 2.5, 3, 4, 5, 6, 7, 8, 10, 12, 14, 16, 18, 20, 24, 32] as $dp)<option value="{{ $dp }}"></option>@endforeach</datalist>
                </div>

                <section class="gc-results" id="gc-results" aria-labelledby="gc-title" aria-live="polite">
                    <div class="gc-res-head">
                        <h2 id="gc-title">Results</h2>
                    </div>
                    <ul class="gc-checks" id="gc-checks" aria-label="Checks"></ul>

                    <figure class="gc-figure">
                        <div class="gc-svgbox"><svg id="gc-svg" role="img" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid meet"></svg></div>
                        <ul class="gc-legend" id="gc-legend"></ul>
                        <figcaption class="gc-draw-note" id="gc-draw-note"></figcaption>
                    </figure>

                    <div class="gc-tables" id="gc-tables"></div>

                    <div class="gc-actions">
                        <h2>Request a quote for this gear</h2>
                        <p>Send the specification with the quantity, material and heat treatment. Our engineers check the geometry before quoting.</p>
                        <div class="gc-contact">
                            <span>Email: <b>sales@misubaguna.com</b></span>
                            <span>WhatsApp: <b>+62 811-8715-671</b></span>
                        </div>
                        <div class="gc-btns">
                            <a class="gc-btn gc-btn-primary" id="gc-mail" href="mailto:sales@misubaguna.com">Email the specification</a>
                            <a class="gc-btn gc-btn-wa" id="gc-wa" href="https://wa.me/628118715671" target="_blank" rel="noopener">WhatsApp</a>
                            <a class="gc-btn" id="gc-form" href="/request-a-quote/">Quote form</a>
                            <button class="gc-btn" type="button" id="gc-copy">Copy specification</button>
                            <span class="gc-copy-status" id="gc-copy-status" role="status"></span>
                        </div>
                        <details class="gc-spec" id="gc-spec-box">
                            <summary>Show specification text</summary>
                            <pre id="gc-spec"></pre>
                        </details>
                    </div>
                </section>
            </div>
        </form>

        <div class="gc-ref">
            <section>
                <h2>Standards and conventions</h2>
                <ul>
                    <li>Involute geometry per ISO 21771 with the ISO 53 basic rack: addendum 1.0·m, dedendum 1.25·m, pressure angle 20° (14.5° and 25° also available).</li>
                    <li>Helical gears use the normal system: normal module mn and normal pressure angle αn.</li>
                    <li>Standard modules per ISO 54; diametral pitch converts with m = 25.4 / DP.</li>
                    <li>Worm gears per ISO/DIN 3975 with axial module m, diameter factor q = d1 / m.</li>
                    <li>Straight bevel gears: equal addendum, constant clearance, outer (heel) dimensions.</li>
                </ul>
                <h3>Key formulas</h3>
                <ul>
                    <li><code>mt = mn / cos β</code>, <code>αt = atan(tan αn / cos β)</code>, <code>d = z·mt</code>, <code>db = d·cos αt</code></li>
                    <li><code>da = d + 2·mn·(1 + x)</code>, <code>df = d − 2·mn·(1.25 − x)</code></li>
                    <li><code>inv α = tan α − α</code>; the inverse is solved by Newton–Raphson to 1e-12.</li>
                    <li>Span: <code>Wk = mn·cos αn·[π(k − 0.5) + z·inv αt] + 2x·mn·sin αn</code></li>
                    <li>Pair: <code>inv αwt = 2·tan αn·(x1 + x2)/(z1 + z2) + inv αt</code>, <code>a = a0·cos αt / cos αwt</code></li>
                    <li>Bevel: <code>δ1 = atan(sin Σ / (z2/z1 + cos Σ))</code>, <code>Re = d1 / (2·sin δ1)</code></li>
                    <li>Worm: <code>γ = atan(z1 / q)</code>, <code>η = tan γ / tan(γ + ρ′)</code></li>
                </ul>
            </section>
            <section>
                <h2>Frequently asked questions</h2>
                <dl>
                    @foreach ($faq as $item)
                        <dt>{{ $item['q'] }}</dt>
                        <dd>{{ $item['a'] }}</dd>
                    @endforeach
                </dl>
            </section>
        </div>

        <p class="gc-fine">Geometry per ISO 21771 basic rack; strength and load rating (ISO 6336, AGMA) is not covered. Verify critical parts with Misuba engineering.</p>
    </div>
</div>

<script type="module" src="{{ asset('js/gear-calculator.js') }}?v={{ @filemtime(public_path('js/gear-calculator.js')) }}"></script>
@endsection
