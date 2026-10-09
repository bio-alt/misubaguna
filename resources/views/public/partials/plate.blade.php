{{-- Drawing-style stand-in, with a caption, for products that do not have a photo yet. --}}
<div class="ds-plate">
    <svg viewBox="0 0 280 200" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
        <rect x="70" y="72" width="140" height="56" rx="2"/>
        <rect x="52" y="60" width="18" height="80" rx="2"/>
        <rect x="210" y="60" width="18" height="80" rx="2"/>
        <path d="M70 100h140" stroke-dasharray="10 5 2 5" stroke-width="1.25"/>
        <circle cx="61" cy="72" r="2.5"/><circle cx="61" cy="128" r="2.5"/>
        <circle cx="219" cy="72" r="2.5"/><circle cx="219" cy="128" r="2.5"/>
        <path d="M52 160h176M52 154v12M228 154v12" stroke-width="1.25"/>
        <path d="M52 160l8-3v6zM228 160l-8-3v6z" fill="currentColor" stroke-width="1"/>
    </svg>
    <span>{{ $label ?? 'Photo on request' }}</span>
</div>
