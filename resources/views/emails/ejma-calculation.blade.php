<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #0f172a; font-size: 14px;">
    <h2 style="color: #0d9488; margin-bottom: 4px;">EJMA calculation {{ $calculation->reference }}</h2>
    <p style="margin-top: 0;"><strong>{{ $verdictLabel }}</strong> &middot; {{ ucfirst($calculation->bellows_type) }} bellows{{ $assembly['isSingle'] ? '' : ', '.strtolower($assembly['type']).' assembly' }} &middot; {{ $date }}</p>

    <h3>Contact</h3>
    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td><strong>Name</strong></td><td>{{ $contact['name'] }}</td></tr>
        <tr><td><strong>Company</strong></td><td>{{ $contact['company'] }}</td></tr>
        <tr><td><strong>Email</strong></td><td><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></td></tr>
        <tr><td><strong>Phone / WhatsApp</strong></td><td>{{ $contact['phone'] }}</td></tr>
        @if (! empty($contact['notes']))
            <tr><td><strong>Notes</strong></td><td style="white-space: pre-line;">{{ $contact['notes'] }}</td></tr>
        @endif
    </table>

    <h3>Design summary</h3>
    <table cellpadding="4" style="border-collapse: collapse;">
        @foreach ($materials as $m)
            <tr><td>{{ $m['role'] }} material</td><td>{{ $m['name'] }}</td></tr>
        @endforeach
        @foreach ($inputRows as $row)
            <tr><td>{{ $row['label'] }}</td><td>{{ $row['metric'] }} ({{ $row['us'] }})</td></tr>
        @endforeach
    </table>

    @if ($design && $design['found'])
        <h3>Proposed design</h3>
        <p style="margin-top: 0;">{{ $design['best']['profile'] }}; candidates evaluated: {{ $design['evaluated'] }}. Alternatives are listed in the attached PDF.</p>
        <table cellpadding="4" style="border-collapse: collapse;">
            <tr><td>Convolution height w</td><td>{{ $design['best']['w']['metric'] }} ({{ $design['best']['w']['us'] }})</td></tr>
            <tr><td>Convolution pitch q</td><td>{{ $design['best']['q']['metric'] }} ({{ $design['best']['q']['us'] }})</td></tr>
            <tr><td>Mean radius rm</td><td>{{ $design['best']['rm']['metric'] }} ({{ $design['best']['rm']['us'] }})</td></tr>
            <tr><td>Ply thickness t</td><td>{{ $design['best']['t']['metric'] }} ({{ $design['best']['t']['us'] }})</td></tr>
            <tr><td>Plies / convolutions per bellows</td><td>{{ $design['best']['plies'] }} / {{ $design['best']['convolutions'] }}</td></tr>
            <tr><td>Tangent length Lt</td><td>{{ $design['best']['Lt']['metric'] }} ({{ $design['best']['Lt']['us'] }})</td></tr>
        </table>
    @elseif ($design)
        <h3>No passing design</h3>
        <p style="margin-top: 0;">Candidates evaluated: {{ $design['evaluated'] }}.@if ($design['closest']) Closest candidate: {{ $design['closest']['profile'] }}, t = {{ $design['closest']['t']['metric'] }}, {{ $design['closest']['plies'] }}, {{ $design['closest']['convolutions'] }}@if ($design['governing']); governing check {{ $design['governing']['symbol'] }} &ndash; {{ $design['governing']['label'] }} (EJMA {{ $design['governing']['equation'] }})@endif.@endif</p>
    @endif

    @if (count($lines))
    <h3>Checks</h3>
    <table cellpadding="5" style="border-collapse: collapse; width: 100%;" border="1" bordercolor="#cbd5e1">
        <tr style="background: #f1f5f9; text-align: left;">
            <th>Check</th><th>Value</th><th>Limit</th><th>Margin</th><th>Result</th>
        </tr>
        @foreach ($lines as $line)
            <tr>
                <td>{{ $line['symbol'] }} &ndash; {{ $line['label'] }} (EJMA {{ $line['equation'] }})</td>
                <td>{{ $line['value'] }}</td>
                <td>{{ $line['limitExpression'] }} = {{ $line['limit'] }}</td>
                <td>{{ $line['margin'] }}</td>
                <td style="{{ $line['statusKey'] === 'pass' ? 'color: #0d9488;' : 'color: #1e293b; font-weight: bold;' }}">{{ $line['status'] }}</td>
            </tr>
        @endforeach
    </table>
    @endif

    @if (count($notes))
        <h3>Assumptions &amp; notes</h3>
        <ul>
            @foreach ($notes as $note)
                <li>{{ $note }}</li>
            @endforeach
        </ul>
    @endif

    <hr>
    <p style="color: #64748b; font-size: 12px;">The full calculation is attached as {{ $calculation->reference }}.pdf. Reply to this email to respond directly to the sender.</p>
</body>
</html>
