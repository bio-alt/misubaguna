<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $calculation->reference }}</title>
    <style>
        @page { margin: 22mm 14mm 18mm 14mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5pt; color: #0f172a; }
        h1 { font-size: 15pt; color: #0d9488; margin: 0 0 2pt 0; }
        h2 { font-size: 10pt; color: #0d9488; margin: 14pt 0 4pt 0; border-bottom: 1pt solid #0d9488; padding-bottom: 2pt; }
        .sub { color: #475569; margin: 0 0 6pt 0; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f1f5f9; text-align: left; font-size: 7.5pt; color: #334155; }
        th, td { border: 0.5pt solid #cbd5e1; padding: 2pt 4pt; vertical-align: top; }
        h2 { page-break-after: avoid; }
        td.nw { white-space: nowrap; }
        td.r, th.r { text-align: right; }
        .pass { color: #0d9488; font-weight: bold; }
        .fail { color: #1e293b; font-weight: bold; background: #e2e8f0; }
        .review { color: #475569; font-weight: bold; }
        .verdict { margin: 8pt 0; padding: 5pt 7pt; border: 1pt solid #0d9488; background: #f0fdfa; font-size: 10pt; font-weight: bold; }
        .meta td { border: 0; padding: 1pt 4pt 1pt 0; }
        .small { font-size: 7.5pt; color: #475569; }
        .disclaimer { margin-top: 14pt; padding: 5pt 7pt; border: 0.5pt solid #94a3b8; background: #f8fafc; font-size: 8pt; }
        ul { margin: 2pt 0 0 14pt; padding: 0; }
    </style>
</head>
<body>
    <h1>EJMA Bellows Calculation {{ $calculation->reference }}</h1>
    <p class="sub">Calculated per EJMA Standards, 9th Edition</p>

    <table class="meta">
        <tr><td width="18%"><strong>Reference</strong></td><td width="32%">{{ $calculation->reference }}</td><td width="18%"><strong>Date</strong></td><td>{{ $date }}</td></tr>
        <tr><td><strong>Company</strong></td><td>{{ $contact['company'] }}</td><td><strong>Contact</strong></td><td>{{ $contact['name'] }}, {{ $contact['email'] }}</td></tr>
        <tr><td><strong>Bellows type</strong></td><td>{{ ucfirst($calculation->bellows_type) }}{{ $assembly['type'] !== 'Single' ? ', '.strtolower($assembly['type']) : '' }}</td><td><strong>Phone</strong></td><td>{{ $contact['phone'] }}</td></tr>
    </table>
    <div class="verdict">{{ $verdict }} &ndash; {{ $verdictLabel }}</div>

    <h2>1. Inputs and materials</h2>
    <table>
        <tr><th>Parameter</th><th class="r">Metric</th><th class="r">US customary</th></tr>
        @foreach ($inputRows as $row)
            <tr><td>{{ $row['label'] }}</td><td class="r">{{ $row['metric'] }}</td><td class="r">{{ $row['us'] }}</td></tr>
        @endforeach
    </table>
    <table style="margin-top: 6pt;">
        <tr><th colspan="2">Materials and forming</th></tr>
        @foreach ($materials as $m)
            <tr><td width="30%">{{ $m['role'] }}</td><td>{{ $m['name'] }}</td></tr>
        @endforeach
        @if ($formingCondition)
            <tr><td>Forming condition</td><td>{{ str_replace('_', ' ', $formingCondition) }}</td></tr>
        @endif
        @if ($formingMethod)
            <tr><td>Forming method</td><td>{{ str_replace('_', ' ', $formingMethod) }}</td></tr>
        @endif
        @forelse ($overrides as $o)
            <tr><td>{{ $o }}</td><td>(override) user-supplied value used in place of the material table</td></tr>
        @empty
            <tr><td>Property overrides</td><td>None; material properties taken from the material data tables.</td></tr>
        @endforelse
    </table>
    @if (count($properties))
        <table style="margin-top: 6pt;">
            <tr><th>Material properties used</th><th class="r">Metric</th><th class="r">US customary</th></tr>
            @foreach ($properties as $p)
                <tr><td>{{ $p['label'] }}</td><td class="r">{{ $p['metric'] }}</td><td class="r">{{ $p['us'] }}</td></tr>
            @endforeach
        </table>
    @endif

    <h2>2. Derived geometry and coefficients</h2>
    <table>
        <tr><th>Quantity</th><th class="r">Value</th></tr>
        @foreach ($coefficients as $c)
            <tr><td>{{ $c['label'] }}</td><td class="r">{{ $c['value'] }}</td></tr>
        @endforeach
    </table>

    <h2>3. Assembly</h2>
    <p class="small" style="margin: 0 0 3pt 0;"><strong>Assembly type: {{ $assembly['type'] }}</strong>@if ($assembly['statement']) &ndash; {{ $assembly['statement'] }}@endif</p>
    <table>
        <tr><th>Quantity</th><th class="r">US customary</th><th class="r">Metric</th></tr>
        @foreach ($assembly['rows'] as $r)
            <tr><td>{{ $r['label'] }}</td><td class="r">{{ $r['us'] }}</td><td class="r">{{ $r['metric'] }}</td></tr>
        @endforeach
    </table>

    <h2>4. EJMA checks</h2>
    <table>
        <tr>
            <th>Symbol</th><th>Check</th><th class="r">Value</th><th>Limit</th><th class="r">Limit value</th>
            <th class="r">Margin</th><th>Status</th><th>EJMA eq.</th>
        </tr>
        @foreach ($lines as $l)
            <tr>
                <td>{{ $l['symbol'] }}</td>
                <td>{{ $l['label'] }}</td>
                <td class="r">{{ $l['value'] }}</td>
                <td>{{ $l['limitExpression'] }}</td>
                <td class="r">{{ $l['limit'] }}</td>
                <td class="r nw">{{ $l['margin'] }}</td>
                <td class="{{ $l['statusKey'] === 'pass' ? 'pass' : ($l['statusKey'] === 'fail' ? 'fail' : 'review') }}">{{ $l['status'] }}</td>
                <td>{{ $l['equation'] }}</td>
            </tr>
        @endforeach
    </table>

    <h2>5. Stresses, loads and spring rate</h2>
    <table>
        <tr><th>Stress</th><th class="r">Value</th></tr>
        @foreach ($stresses as $s)
            <tr><td>{{ $s['label'] }}</td><td class="r">{{ $s['value'] }}</td></tr>
        @endforeach
    </table>
    <table style="margin-top: 6pt;">
        <tr><th>Load</th><th class="r">US customary</th><th class="r">Metric</th></tr>
        @foreach ($loads as $l)
            <tr><td>{{ $l['label'] }}</td><td class="r">{{ $l['us'] }}</td><td class="r">{{ $l['metric'] }}</td></tr>
        @endforeach
    </table>

    <h2>Assumptions &amp; notes</h2>
    <ul>
        @foreach ($notes as $note)
            <li>{{ $note }}</li>
        @endforeach
        <li>Stresses and pressures are in psi; movement limit lines (ec, ee, ex) are in inches; cycles are counts. Margin is the relative distance to the limit.</li>
    </ul>

    <div class="disclaimer">{{ $disclaimer }}</div>
</body>
</html>
