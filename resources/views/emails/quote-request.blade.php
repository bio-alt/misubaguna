<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #10202a; font-size: 14px;">
    <h2 style="color: #176b70;">Quote request {{ $inquiry->reference }}</h2>
    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td><strong>Product</strong></td><td>
            {{ $inquiry->product_name ?: '—' }}
            @if ($inquiry->product_slug)
                (<a href="{{ url('/product/'.$inquiry->product_slug.'/') }}">{{ url('/product/'.$inquiry->product_slug.'/') }}</a>)
            @endif
        </td></tr>
        @if ($inquiry->item && $inquiry->item !== $inquiry->product_name)
            <tr><td><strong>Product / model</strong></td><td>{{ $inquiry->item }}</td></tr>
        @endif
        <tr><td><strong>Name</strong></td><td>{{ $inquiry->name }}</td></tr>
        <tr><td><strong>Company</strong></td><td>{{ $inquiry->company }}</td></tr>
        <tr><td><strong>Email</strong></td><td><a href="mailto:{{ $inquiry->email }}">{{ $inquiry->email }}</a></td></tr>
        <tr><td><strong>Phone / WhatsApp</strong></td><td>{{ $inquiry->phone }}</td></tr>
        @if ($inquiry->location)
            <tr><td><strong>Delivery location</strong></td><td>{{ $inquiry->location }}</td></tr>
        @endif
    </table>

    @if (! empty($inquiry->answers))
        <h3>Details</h3>
        <table cellpadding="6" style="border-collapse: collapse;">
            @foreach ($inquiry->answers as $answer)
                <tr><td style="color: #66767f; border-bottom: 1px solid #dbe3e5;">{{ $answer['label'] }}</td><td style="border-bottom: 1px solid #dbe3e5;">{{ $answer['value'] }}</td></tr>
            @endforeach
        </table>
    @endif

    @if ($inquiry->message)
        <h3>Message</h3>
        <p style="white-space: pre-line;">{{ $inquiry->message }}</p>
    @endif

    @if (! empty($fileLinks))
        <h3>Files</h3>
        <ul>
            @foreach ($fileLinks as $file)
                <li><a href="{{ $file['url'] }}">{{ $file['name'] }}</a> ({{ number_format($file['size'] / 1024, 0) }} KB)</li>
            @endforeach
        </ul>
        <p style="color: #66767f; font-size: 12px;">
            {{ $attached ? 'The files are also attached.' : 'The files are too large to attach; use the links.' }}
            Links work for {{ \App\Mail\QuoteRequest::LINK_DAYS }} days.
        </p>
    @endif

    <hr>
    <p style="color: #66767f; font-size: 12px;">
        Sent from {{ url($inquiry->locale === 'id' ? '/id/request-a-quote/' : '/request-a-quote/') }}
        @if ($inquiry->source_url) (opened from {{ $inquiry->source_url }}) @endif
        in {{ $inquiry->locale === 'id' ? 'Indonesian' : 'English' }}.
        Reply to this email to respond directly to the buyer.
    </p>
</body>
</html>
