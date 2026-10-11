<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #0f172a; font-size: 14px;">
    <h2 style="color: #0d9488; margin-bottom: 4px;">EJMA calculation {{ $calculation->reference }}</h2>
    <p style="margin-top: 0;"><strong>{{ $verdictLabel }}</strong> &middot; {{ ucfirst($calculation->bellows_type) }} bellows{{ $assembly['type'] !== 'Single' ? ', '.strtolower($assembly['type']).' assembly' : '' }} &middot; {{ $date }}</p>

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
