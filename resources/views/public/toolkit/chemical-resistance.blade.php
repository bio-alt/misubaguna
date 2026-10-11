@extends('public.layout')

@section('title', 'Chemical Resistance Guide | Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Chemical compatibility chart for PTFE, elastomers, stainless steel, ceramic, and FRP.')

@php
    $materials = [
        ['key' => 'ptfe', 'short' => 'PTFE', 'name' => 'PTFE', 'maxTemp' => 260, 'products' => [['PTFE Lining', 'ptfe-lining'], ['PTFE Expansion Joint', 'ptfe-expansion-joint'], ['PTFE Coating', 'ptfe-coating']]],
        ['key' => 'epdm', 'short' => 'EPDM', 'name' => 'EPDM', 'maxTemp' => 120, 'products' => [['Rubber Expansion Joint', 'rubber-expansion-joint'], ['Rubber Lining', 'rubber-lining'], ['Rubber Hose', 'rubber-hose']]],
        ['key' => 'nbr', 'short' => 'NBR', 'name' => 'NBR (Nitrile)', 'maxTemp' => 100, 'products' => [['Rubber Hose', 'rubber-hose'], ['O-Ring', 'o-ring'], ['Oil Seal', 'oil-seal']]],
        ['key' => 'fkm', 'short' => 'FKM', 'name' => 'FKM (Viton)', 'maxTemp' => 200, 'products' => [['O-Ring', 'o-ring'], ['Oil Seal', 'oil-seal']]],
        ['key' => 'cr', 'short' => 'CR', 'name' => 'CR (Neoprene)', 'maxTemp' => 100, 'products' => [['Rubber Lining', 'rubber-lining'], ['Rubber Hose', 'rubber-hose']]],
        ['key' => 'csm', 'short' => 'CSM', 'name' => 'CSM (Hypalon)', 'maxTemp' => 120, 'products' => [['Rubber Lining', 'rubber-lining'], ['Rubber Expansion Joint', 'rubber-expansion-joint']]],
        ['key' => 'nr', 'short' => 'NR', 'name' => 'Natural Rubber', 'maxTemp' => 70, 'products' => [['Rubber Lining', 'rubber-lining'], ['Rubber Hose', 'rubber-hose']]],
        ['key' => 'ss', 'short' => '316SS', 'name' => 'Stainless Steel 316', 'maxTemp' => 550, 'products' => [['Metal Expansion Joint', 'metal-expansion-joint'], ['Flexible Metal Hose', 'flexible-metal-hose']]],
        ['key' => 'cer', 'short' => 'Ceramic', 'name' => 'Ceramic (Alumina)', 'maxTemp' => 1000, 'products' => [['Ceramic Lining', 'ceramic-lining'], ['Ceramic Coating', 'ceramic-coating']]],
        ['key' => 'frp', 'short' => 'FRP', 'name' => 'FRP (Vinyl Ester)', 'maxTemp' => 100, 'products' => [['FRP Lining', 'frp-lining']]],
    ];

    // Ratings in material column order: PTFE, EPDM, NBR, FKM, CR, CSM, NR, 316SS, Ceramic, FRP.
    $chemicals = [
        ['Sulfuric Acid 10%', 'acids', 'AACABABBAA'],
        ['Sulfuric Acid 50%', 'acids', 'ABXACBXXAA'],
        ['Sulfuric Acid 98%', 'acids', 'AXXBXCXBAX'],
        ['Hydrochloric Acid 10%', 'acids', 'AACABABXAA'],
        ['Hydrochloric Acid 37%', 'acids', 'ABXACBCXAA'],
        ['Nitric Acid 10%', 'acids', 'ABXACBXAAA'],
        ['Nitric Acid 65%', 'acids', 'AXXCXCXBAX'],
        ['Phosphoric Acid 85%', 'acids', 'AACABACBBA'],
        ['Hydrofluoric Acid 40%', 'acids', 'ACXBCCXXXC'],
        ['Acetic Acid 10%', 'acids', 'AACCBBBAAA'],
        ['Acetic Acid, Glacial', 'acids', 'ABXXCCCAAX'],
        ['Formic Acid 85%', 'acids', 'ABCCBBBBAB'],
        ['Chromic Acid 50%', 'acids', 'ACXBXBXCAC'],
        ['Citric Acid', 'acids', 'AAAAAAAAAA'],
        ['Sodium Hydroxide 10%', 'alkalis', 'AABBAAAAAA'],
        ['Sodium Hydroxide 50%', 'alkalis', 'AACXAABABB'],
        ['Ammonia, Aqueous', 'alkalis', 'AABXAACAAC'],
        ['Lime Slurry (Calcium Hydroxide)', 'alkalis', 'AAAAAAAAAA'],
        ['White Liquor (Kraft)', 'alkalis', 'AACXBACBBB'],
        ['Black Liquor (Kraft)', 'alkalis', 'AABCBACAAB'],
        ['Sodium Hypochlorite 15%', 'oxidisers', 'ABXACAXXAA'],
        ['Chlorine, Wet Gas', 'oxidisers', 'ACXBXCXXAA'],
        ['Chlorine Dioxide (Bleach Plant)', 'oxidisers', 'AXXBXCXXAA'],
        ['Hydrogen Peroxide 30%', 'oxidisers', 'ABCACBXBAB'],
        ['Water, Fresh', 'water', 'AAAAAAAAAA'],
        ['Hot Water 80 °C', 'water', 'AABBBBCAAB'],
        ['Steam to 150 °C', 'water', 'AACCCCXAAX'],
        ['Seawater', 'water', 'AAAAAAABAA'],
        ['Sodium Chloride Brine', 'water', 'AAAAAAABAA'],
        ['Ferric Chloride', 'water', 'AAAAAAAXAA'],
        ['Aluminium Sulfate (Alum)', 'water', 'AAAAAAABAA'],
        ['Crude Palm Oil', 'oils', 'ACAABBXAAA'],
        ['Diesel / Fuel Oil', 'oils', 'AXAACCXAAA'],
        ['Gasoline', 'oils', 'AXBAXXXAAB'],
        ['Hydraulic / Mineral Oil', 'oils', 'AXAABBXAAA'],
        ['Toluene / Xylene', 'solvents', 'AXXBXXXAAC'],
        ['Acetone / MEK', 'solvents', 'AAXXXXCAAX'],
        ['Methanol', 'solvents', 'AAACAAAAAC'],
        ['Ethanol', 'solvents', 'AABAAAAAAB'],
        ['Ethylene Glycol', 'solvents', 'AAAAAAAAAA'],
        ['Hydrogen Sulfide, Wet', 'gases', 'AACBBBCBAA'],
        ['Sulfur Dioxide, Wet (FGD Condensate)', 'gases', 'ABCACBCBAA'],
        ['Abrasive Slurry (Sand / Ore / Ash)', 'slurries', 'CBBCBBABAC'],
        ['Pulp Stock', 'slurries', 'AAAAAAAAAA'],
    ];

    $groups = [
        'all' => 'All',
        'acids' => 'Acids',
        'alkalis' => 'Alkalis & Liquors',
        'oxidisers' => 'Oxidisers & Bleach',
        'water' => 'Water, Steam & Salts',
        'oils' => 'Oils & Fuels',
        'solvents' => 'Solvents',
        'gases' => 'Gases',
        'slurries' => 'Slurries',
    ];

    $ratingLabels = ['A' => 'Excellent', 'B' => 'Good', 'C' => 'Limited', 'X' => 'Not recommended'];
@endphp

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
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .cr-wrap { max-width: 1240px; margin: 28px auto 64px; padding: 0 24px; }

    .cr-toolbar {
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .cr-search {
        flex: 1 1 280px;
        padding: 10px 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        font-family: inherit;
        background: #fff;
        color: #0f172a;
    }
    .cr-search:focus { outline: none; border-color: #2b9d9f; box-shadow: 0 0 0 3px rgba(43, 157, 159, 0.15); }
    .cr-groups { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 14px; }
    .cr-chip {
        font-size: 12.5px;
        font-weight: 700;
        padding: 6px 12px;
        border-radius: 999px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        cursor: pointer;
        font-family: inherit;
    }
    .cr-chip:hover { border-color: #2b9d9f; color: #0f766e; }
    .cr-chip.active { background: #0f172a; border-color: #0f172a; color: #fff; }

    .cr-legend {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
        font-size: 12.5px;
        color: #475569;
        margin-left: auto;
    }
    .cr-legend span { display: inline-flex; align-items: center; gap: 6px; }

    .cr-r {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 24px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 800;
    }
    .cr-r-A { background: #0f766e; color: #fff; }
    .cr-r-B { background: #99f6e4; color: #134e4a; }
    .cr-r-C { background: #e2e8f0; color: #334155; }
    .cr-r-X { background: #1e293b; color: #fff; }

    .cr-detail {
        display: none;
        background: #fff;
        border: 2px solid #2b9d9f;
        border-radius: 12px;
        padding: 18px 20px;
        margin-bottom: 16px;
    }
    .cr-detail.open { display: block; }
    .cr-detail-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }
    .cr-detail h2 { font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; }
    .cr-detail-close {
        background: none;
        border: none;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        font-family: inherit;
    }
    .cr-detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 10px;
        margin-top: 14px;
    }
    .cr-pick {
        border: 1px solid #e2e8f0;
        border-radius: 9px;
        padding: 10px 12px;
        font-size: 13px;
        color: #475569;
    }
    .cr-pick-head { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
    .cr-pick-name { font-weight: 800; color: #0f172a; font-size: 14px; }
    .cr-pick a { color: #0f766e; font-weight: 700; text-decoration: none; }
    .cr-pick a:hover { text-decoration: underline; }
    .cr-detail-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 14px;
        font-size: 13px;
        color: #475569;
    }
    .cr-quote {
        background: #2b9d9f;
        color: #fff;
        font-weight: 800;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        white-space: nowrap;
    }
    .cr-quote:hover { background: #0f766e; }

    .cr-table-box {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .cr-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 860px; }
    .cr-table th {
        position: sticky;
        top: 0;
        background: #f8fafc;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #475569;
        padding: 10px 6px;
        border-bottom: 1px solid #e2e8f0;
        text-align: center;
        white-space: nowrap;
    }
    .cr-table th:first-child, .cr-table td:first-child {
        text-align: left;
        position: sticky;
        left: 0;
        background: inherit;
        padding-left: 14px;
        min-width: 210px;
        z-index: 1;
    }
    .cr-table th:first-child { background: #f8fafc; z-index: 2; }
    .cr-table tbody tr { background: #fff; cursor: pointer; }
    .cr-table tbody tr:hover, .cr-table tbody tr.selected { background: #f0fdfa; }
    .cr-table td { padding: 6px; border-bottom: 1px solid #f1f5f9; text-align: center; }
    .cr-table td:first-child { font-weight: 700; color: #0f172a; }
    .cr-empty { padding: 24px; text-align: center; color: #64748b; font-size: 14px; display: none; }

    .cr-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 28px;
    }
    .cr-info-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 20px;
    }
    .cr-info-card h2 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 10px; }
    .cr-info-card p, .cr-info-card li { font-size: 13.5px; color: #475569; line-height: 1.65; }
    .cr-info-card ul { padding-left: 18px; margin: 0; }
    .cr-info-card a { color: #0f766e; font-weight: 700; text-decoration: none; }
    .cr-info-card table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .cr-info-card td { padding: 6px 4px; border-bottom: 1px solid #f1f5f9; color: #475569; }
    .cr-info-card td:first-child { font-weight: 700; color: #0f172a; }

    @media (max-width: 860px) {
        .cr-info { grid-template-columns: 1fr; }
        .cr-legend { margin-left: 0; }
    }
    @media (max-width: 480px) {
        .cr-wrap { padding: 0 16px; }
        .toolkit-page-header h1 { font-size: 26px; }
        .cr-table th:first-child, .cr-table td:first-child { min-width: 150px; }
    }
</style>

<div class="toolkit-page-header">
    <div class="tk-crumb"><a href="/toolkit/">Engineering Toolkit</a></div>
    <h1>Chemical Resistance Guide</h1>
    <p>Check how PTFE, rubber elastomers, stainless steel, ceramic, and FRP hold up against {{ count($chemicals) }} common industrial chemicals before you choose a lining, hose, gasket, or expansion joint. Click any chemical to see the best materials and matching products.</p>
</div>

<div class="cr-wrap">
    <div class="cr-toolbar">
        <input id="cr-search" class="cr-search" type="search" placeholder="Search a chemical, e.g. sulfuric, caustic, palm oil..." autocomplete="off">
        <div class="cr-legend">
            @foreach ($ratingLabels as $code => $label)
                <span><span class="cr-r cr-r-{{ $code }}">{{ $code }}</span>{{ $label }}</span>
            @endforeach
        </div>
    </div>

    <div class="cr-groups" id="cr-groups">
        @foreach ($groups as $key => $label)
            <button type="button" class="cr-chip{{ $key === 'all' ? ' active' : '' }}" data-group="{{ $key }}">{{ $label }}</button>
        @endforeach
    </div>

    <div class="cr-detail" id="cr-detail" aria-live="polite"></div>

    <div class="cr-table-box">
        <table class="cr-table">
            <thead>
                <tr>
                    <th scope="col">Chemical (at ~20 °C)</th>
                    @foreach ($materials as $m)
                        <th scope="col" title="{{ $m['name'] }}">{{ $m['short'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody id="cr-body">
                @foreach ($chemicals as $i => [$chemName, $group, $ratings])
                    <tr data-index="{{ $i }}" data-group="{{ $group }}" data-name="{{ strtolower($chemName) }}">
                        <td>{{ $chemName }}</td>
                        @foreach ($materials as $j => $m)
                            @php $r = $ratings[$j]; @endphp
                            <td><span class="cr-r cr-r-{{ $r }}" title="{{ $m['name'] }}: {{ $ratingLabels[$r] }}">{{ $r }}</span></td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="cr-empty" id="cr-empty">No chemical matches your search. <a href="/contact-us/?category=Corrosion+Protection#inquiry-form" style="color:#0f766e;font-weight:700;">Ask our engineers</a> &mdash; we can check it for you.</div>
    </div>

    <div class="cr-info">
        <div class="cr-info-card">
            <h2>How to read this chart</h2>
            <ul>
                <li><strong>A, Excellent:</strong> little or no effect; suitable for continuous service.</li>
                <li><strong>B, Good:</strong> minor effect; suitable for most services.</li>
                <li><strong>C, Limited:</strong> moderate attack or swelling; short-term or intermittent use only.</li>
                <li><strong>X, Not recommended:</strong> severe attack; do not use.</li>
            </ul>
            <p style="margin-top:10px;">Ratings are for about 20 °C. Resistance drops as temperature rises, and mixtures, contaminants, and concentration changes can behave differently than the pure chemical. For abrasive slurries the rating reflects wear resistance. This guide is for preliminary selection only: send us your process data and our engineers will confirm the material.</p>
            <p>Need movement calculations too? Try the <a href="/toolkit/expansion-joint-selector/">Expansion Joint Selector</a>.</p>
        </div>
        <div class="cr-info-card">
            <h2>Material temperature limits</h2>
            <table>
                <tbody>
                    @foreach ($materials as $m)
                        <tr>
                            <td>{{ $m['name'] }}</td>
                            <td>up to ~{{ $m['maxTemp'] }} °C</td>
                            <td>
                                @foreach ($m['products'] as [$pName, $pSlug])
                                    <a href="/product/{{ $pSlug }}/">{{ $pName }}</a>@if (! $loop->last), @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    var MATERIALS = @json($materials);
    var CHEMICALS = @json($chemicals);
    var LABELS = @json($ratingLabels);

    var search = document.getElementById('cr-search');
    var rows = Array.prototype.slice.call(document.querySelectorAll('#cr-body tr'));
    var chips = Array.prototype.slice.call(document.querySelectorAll('.cr-chip'));
    var detail = document.getElementById('cr-detail');
    var empty = document.getElementById('cr-empty');
    var activeGroup = 'all';

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function filter() {
        var q = search.value.trim().toLowerCase();
        var visible = 0;
        rows.forEach(function (row) {
            var show = (activeGroup === 'all' || row.dataset.group === activeGroup)
                && (!q || row.dataset.name.indexOf(q) !== -1);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        empty.style.display = visible ? 'none' : 'block';
    }

    function pickHtml(m, rating) {
        var products = m.products.map(function (p) {
            return '<a href="/product/' + p[1] + '/">' + escapeHtml(p[0]) + '</a>';
        }).join(', ');
        return '<div class="cr-pick"><div class="cr-pick-head"><span class="cr-r cr-r-' + rating + '">' + rating + '</span>'
            + '<span class="cr-pick-name">' + escapeHtml(m.name) + '</span></div>'
            + '<div>Up to ~' + m.maxTemp + ' °C &middot; ' + products + '</div></div>';
    }

    function showDetail(index) {
        var chem = CHEMICALS[index];
        var name = chem[0], ratings = chem[2];
        var best = [], good = [];
        MATERIALS.forEach(function (m, j) {
            if (ratings[j] === 'A') best.push(m);
            if (ratings[j] === 'B') good.push(m);
        });

        var html = '<div class="cr-detail-head"><h2>' + escapeHtml(name) + '</h2>'
            + '<button type="button" class="cr-detail-close" id="cr-close">Close &times;</button></div>';
        if (best.length) {
            html += '<div class="cr-detail-grid">' + best.map(function (m) { return pickHtml(m, 'A'); }).join('') + '</div>';
        }
        if (good.length) {
            html += '<div style="margin-top:12px;font-size:13px;color:#475569;"><strong style="color:#0f172a;">Also good:</strong> '
                + good.map(function (m) { return escapeHtml(m.name); }).join(', ') + '</div>';
        }

        var message = 'Material inquiry (from Chemical Resistance Guide)\n'
            + '- Chemical: ' + name + '\n'
            + '- Materials rated Excellent: ' + (best.map(function (m) { return m.name; }).join(', ') || 'none') + '\n'
            + '- Equipment needed (lining / expansion joint / hose / gasket): \n'
            + '- Operating temperature and pressure: \n'
            + '- Size / quantity: ';
        var href = '/contact-us/?category=' + encodeURIComponent('Corrosion Protection')
            + '&message=' + encodeURIComponent(message) + '#inquiry-form';

        html += '<div class="cr-detail-actions"><span>Ratings at ~20 °C. Higher temperatures or mixtures need an engineering check.</span>'
            + '<a class="cr-quote" href="' + href + '">Ask for a Material Recommendation</a></div>';

        detail.innerHTML = html;
        detail.classList.add('open');
        document.getElementById('cr-close').addEventListener('click', closeDetail);

        rows.forEach(function (r) { r.classList.toggle('selected', r.dataset.index === String(index)); });
        var top = detail.getBoundingClientRect().top + window.pageYOffset - 110;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    function closeDetail() {
        detail.classList.remove('open');
        rows.forEach(function (r) { r.classList.remove('selected'); });
    }

    rows.forEach(function (row) {
        row.addEventListener('click', function () { showDetail(parseInt(row.dataset.index, 10)); });
    });
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            activeGroup = chip.dataset.group;
            chips.forEach(function (c) { c.classList.toggle('active', c === chip); });
            filter();
        });
    });
    search.addEventListener('input', filter);
})();
</script>
@endsection
