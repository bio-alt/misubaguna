@extends('public.layout')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/catalog-flipbook.css') }}?v={{ @filemtime(public_path('css/catalog-flipbook.css')) }}">
@endpush

@section('content')
@php
    $pdfUrl = $catalog['pdf'];
    $groups = collect($catalog['sections'])->groupBy('group');
@endphp
<div class="fb-page">
    {{-- Full-height reader: fills the window under the site header --}}
    <section class="fb" id="fb" aria-label="Catalog reader">
        <div class="fb-bar">
            <div class="fb-bar__start">
                <button type="button" class="fb-btn fb-btn--label" id="fb-contents-btn" aria-controls="fb-drawer" aria-expanded="false" title="Contents and page overview (C)">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                    <span>Contents</span>
                </button>
                <div class="fb-title">
                    <h1>{{ $catalog['title'] }}</h1>
                    <span class="fb-title__section" id="fb-section" aria-live="polite">{{ $catalog['sections'][0]['title'] ?? '' }}</span>
                </div>
            </div>

            <div class="fb-bar__mid">
                <button type="button" class="fb-btn" id="fb-prev" title="Previous page (←)" aria-label="Previous page" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <span class="fb-counter" id="fb-counter" aria-live="polite">Loading…</span>
                <button type="button" class="fb-btn" id="fb-next" title="Next page (→)" aria-label="Next page" disabled>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </button>
            </div>

            <div class="fb-bar__end">
                <div class="fb-seg" role="group" aria-label="Page layout">
                    <button type="button" class="fb-seg__btn" id="fb-mode-spread" aria-pressed="true" title="Two-page spread">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="8" height="14" rx="1"/><rect x="13" y="5" width="8" height="14" rx="1"/></svg>
                        <span class="fb-sr">Two-page spread</span>
                    </button>
                    <button type="button" class="fb-seg__btn" id="fb-mode-single" aria-pressed="false" title="Single page">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="4" width="10" height="16" rx="1"/></svg>
                        <span class="fb-sr">Single page</span>
                    </button>
                </div>

                <div class="fb-zoom" role="group" aria-label="Zoom">
                    <button type="button" class="fb-btn" id="fb-zoom-out" title="Zoom out (−)" aria-label="Zoom out" disabled>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14"/></svg>
                    </button>
                    <button type="button" class="fb-zoom__value" id="fb-zoom-reset" title="Fit to screen (0)">Fit</button>
                    <button type="button" class="fb-btn" id="fb-zoom-in" title="Zoom in (+)" aria-label="Zoom in">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                </div>

                <button type="button" class="fb-btn" id="fb-fullscreen" title="Full screen (F)" aria-label="Full screen">
                    <svg class="fb-ico-enter" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
                    <svg class="fb-ico-exit" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 3v3a2 2 0 01-2 2H3m18 0h-3a2 2 0 01-2-2V3m0 18v-3a2 2 0 012-2h3M3 16h3a2 2 0 012 2v3"/></svg>
                </button>

                <a class="fb-btn fb-btn--accent" href="{{ $pdfUrl }}" download title="Download PDF" aria-label="Download PDF">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    <span class="fb-btn__text">PDF</span>
                </a>
            </div>
        </div>

        <div class="fb-stage" id="fb-stage">
            <div class="fb-progress" id="fb-progress" aria-hidden="true"><span id="fb-progress-bar"></span></div>

            <div class="fb-scroller" id="fb-scroller">
                <div class="fb-slot" id="fb-slot"></div>
            </div>

            <button type="button" class="fb-edge fb-edge--prev" id="fb-edge-prev" aria-label="Previous page" tabindex="-1">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" class="fb-edge fb-edge--next" id="fb-edge-next" aria-label="Next page" tabindex="-1">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            </button>

            <div class="fb-loading" id="fb-loading" role="status">
                <div class="fb-loading__book" aria-hidden="true"><span></span><span></span></div>
                <p id="fb-loading-text">Opening the catalog…</p>
            </div>

            <div class="fb-scrim" id="fb-scrim" hidden></div>
            <aside class="fb-drawer" id="fb-drawer" aria-label="Contents" inert>
                <div class="fb-drawer__head">
                    <span class="fb-drawer__title">Contents</span>
                    <button type="button" class="fb-btn" id="fb-drawer-close" aria-label="Close contents">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <div class="fb-drawer__body">
                    <ol class="fb-toc">
                        @foreach ($groups as $group => $items)
                            <li class="fb-toc__group">
                                <span class="fb-toc__group-name">{{ $group }}</span>
                                <ol>
                                    @foreach ($items as $item)
                                        <li>
                                            <a class="fb-toc__link" href="#page={{ $item['page'] }}" data-page="{{ $item['page'] }}">
                                                <span>{{ $item['title'] }}</span>
                                                <span class="fb-toc__page">{{ $item['page'] }}</span>
                                            </a>
                                        </li>
                                    @endforeach
                                </ol>
                            </li>
                        @endforeach
                    </ol>
                    <p class="fb-drawer__label">All pages</p>
                    <div class="fb-thumbs" id="fb-thumbs"></div>
                </div>
            </aside>

            <noscript>
                <div class="fb-fallback">
                    <p>The page-turning reader needs JavaScript.</p>
                    <a class="fb-cta fb-cta--solid" href="{{ $pdfUrl }}">Open the PDF catalog</a>
                </div>
            </noscript>
        </div>

        <div class="fb-foot">
            <div class="fb-scrub">
                <div class="fb-scrub__ticks" id="fb-ticks" aria-hidden="true"></div>
                <input type="range" id="fb-scrub" min="1" max="1" value="1" step="1" aria-label="Go to page" disabled>
                <output class="fb-scrub__tip" id="fb-scrub-tip" for="fb-scrub"></output>
            </div>
            <span class="fb-foot__hint">
                <kbd>←</kbd><kbd>→</kbd> turn pages &nbsp;·&nbsp; double-click to zoom
            </span>
        </div>
    </section>

    {{-- Below the fold: what is inside, and the sales hand-off --}}
    <section class="fb-about" aria-labelledby="fb-about-title">
        <div class="fb-about__inner">
            <div class="fb-about__intro">
                <p class="fb-eyebrow">PT Misuba Guna Indonesia · {{ $catalog['edition'] }}</p>
                <h2 id="fb-about-title">What the catalog covers</h2>
                <p>Our company profile, the product lines we represent as official agent and distributor, and a selection of recent engineering projects for pulp &amp; paper, refinery, petrochemical and mining plants.</p>
            </div>
            <div class="fb-about__grid">
                <div class="fb-about__card">
                    <span class="fb-about__num">01</span>
                    <h3>Introduction</h3>
                    <ul>
                        <li>Company background since 2020</li>
                        <li>Our strengths: passion, persistence, service, guiding</li>
                        <li>Mission &amp; vision for engineering service and supply</li>
                    </ul>
                    <button type="button" class="fb-about__jump" data-page="2">Go to page 2</button>
                </div>
                <div class="fb-about__card">
                    <span class="fb-about__num">02</span>
                    <h3>Products &amp; brands</h3>
                    <ul>
                        <li><strong>Parason</strong>: pulping, refiner plates, head box</li>
                        <li><strong>RUD India</strong>: drag chain, sprockets, lifting beams</li>
                        <li><strong>Sampumps &amp; Kakati</strong>: slurry, vacuum &amp; chemical pumps</li>
                        <li><strong>Champion</strong>: gaskets, seals &amp; metal expansion joints</li>
                    </ul>
                    <button type="button" class="fb-about__jump" data-page="4">Go to page 4</button>
                </div>
                <div class="fb-about__card">
                    <span class="fb-about__num">03–04</span>
                    <h3>Projects &amp; clients</h3>
                    <ul>
                        <li>Corrosion protection, PTFE and rubber lining</li>
                        <li>Fabrication, material handling and power transmission</li>
                        <li>Pulp &amp; paper, refinery, petrochemical and mining clients</li>
                    </ul>
                    <button type="button" class="fb-about__jump" data-page="15">Go to page 15</button>
                </div>
            </div>
            <div class="fb-about__cta">
                <div>
                    <h3>Need specifications or a quote?</h3>
                    <p>Our sales and technical team can help with equipment sizing, material selection and site visits.</p>
                </div>
                <div class="fb-about__cta-actions">
                    <a href="/contact-us/" class="fb-cta fb-cta--solid">Contact sales</a>
                    <a href="{{ $pdfUrl }}" download class="fb-cta fb-cta--ghost">Download PDF</a>
                </div>
            </div>
        </div>
    </section>
</div>

@php
    // Built here: a multi-line @json([...]) containing a function call is mis-parsed by Blade.
    $fbConfig = [
        'pdf' => $pdfUrl,
        'sections' => $catalog['sections'],
        'worker' => asset('vendor/pdfjs-3.11.174/pdf.worker.min.js'),
    ];
@endphp
<script type="application/json" id="fb-config">@json($fbConfig)</script>
<script src="{{ asset('vendor/pdfjs-3.11.174/pdf.min.js') }}" defer></script>
<script src="{{ asset('vendor/page-flip-2.0.7/page-flip.browser.js') }}" defer></script>
<script src="{{ asset('js/catalog-flipbook.js') }}?v={{ @filemtime(public_path('js/catalog-flipbook.js')) }}" defer></script>
@endsection
