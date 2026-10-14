<!DOCTYPE html>
<html lang="{{ $lang }}">
<body style="font-family: Arial, sans-serif; color: #10202a; font-size: 14px;">
    <p>{{ __('Dear :name,', ['name' => $inquiry->name], $lang) }}</p>
    <p>{{ __('Thank you for your quote request. Your reference is :reference.', ['reference' => $inquiry->reference], $lang) }}</p>
    <p>{{ __('Our sales team will review it and reply.', [], $lang) }}</p>

    <table cellpadding="6" style="border-collapse: collapse;">
        <tr><td style="color: #66767f;">{{ __('Product / model', [], $lang) }}</td><td>{{ $inquiry->product_name ?: $inquiry->item }}</td></tr>
        @foreach ($inquiry->answers ?? [] as $answer)
            <tr><td style="color: #66767f;">{{ $answer['label'] }}</td><td>{{ $answer['value'] }}</td></tr>
        @endforeach
        @if (! empty($inquiry->files))
            <tr><td style="color: #66767f;">{{ __('Files', [], $lang) }}</td><td>{{ implode(', ', array_column($inquiry->files, 'name')) }}</td></tr>
        @endif
    </table>

    <p>{{ __('To add details, reply to this email or message us on WhatsApp:', [], $lang) }} <a href="{{ $waUrl }}">WhatsApp</a></p>
    <p>PT Misuba Guna Indonesia</p>
</body>
</html>
