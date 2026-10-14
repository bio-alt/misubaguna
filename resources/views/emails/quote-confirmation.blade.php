{{-- Goes to an unverified address, so it repeats nothing the sender typed: no name, answers,
     free-text item or file names. Only our own reference, product name and question labels. --}}
<!DOCTYPE html>
<html lang="{{ $lang }}">
<body style="font-family: Arial, sans-serif; color: #10202a; font-size: 14px;">
    <p>{{ __('Hello,', [], $lang) }}</p>
    <p>{{ __('Thank you for your quote request. Your reference is :reference.', ['reference' => $inquiry->reference], $lang) }}</p>
    <p>{{ __('Our sales team will review it and reply.', [], $lang) }}</p>

    @if ($inquiry->product_name)
        <p><strong>{{ __('Product', [], $lang) }}:</strong> {{ $inquiry->product_name }}</p>
    @endif

    @if (! empty($inquiry->answers))
        <p>{{ __('You answered:', [], $lang) }}</p>
        <ul>
            @foreach ($inquiry->answers as $answer)
                <li>{{ $answer['label'] }}</li>
            @endforeach
        </ul>
    @endif

    @if (! empty($inquiry->files))
        <p>{{ trans_choice(':count file attached|:count files attached', count($inquiry->files), [], $lang) }}</p>
    @endif

    <p>{{ __('To add details, reply to this email or message us on WhatsApp:', [], $lang) }} <a href="{{ $waUrl }}">WhatsApp</a></p>
    <p>PT Misuba Guna Indonesia</p>
</body>
</html>
