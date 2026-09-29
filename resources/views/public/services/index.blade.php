@extends('public.layout')

@section('content')
<style>
    /* =====================================================
       SERVICES LANDING HERO & STYLING
    ===================================================== */
    .services-hero {
        background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        color: #ffffff;
        padding: 60px 32px;
        border-bottom: 3px solid #dc2626;
    }
    .services-hero-inner {
        max-width: 1280px;
        margin: 0 auto;
    }
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #9ca3af;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }
    .breadcrumb-nav a {
        color: #d1d5db;
        text-decoration: none;
        transition: color 0.15s;
    }
    .breadcrumb-nav a:hover {
        color: #ef4444;
    }
    .breadcrumb-nav .sep {
        color: #6b7280;
    }
    .services-hero h1 {
        font-size: 42px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 16px;
        color: #ffffff;
    }
    .services-hero p {
        font-size: 18px;
        color: #d1d5db;
        max-width: 800px;
        line-height: 1.7;
    }

    /* SERVICES GRID SECTION */
    .services-wrapper {
        max-width: 1280px;
        margin: 0 auto;
        padding: 60px 32px;
    }
    .section-intro {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 50px;
    }
    .section-intro h2 {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 12px;
    }
    .section-intro p {
        font-size: 16px;
        color: #4b5563;
        line-height: 1.6;
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
        gap: 32px;
    }
    .service-card {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 32px;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
    }
    .service-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: #dc2626;
        opacity: 0;
        transition: opacity 0.25s ease;
    }
    .service-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        border-color: #cbd5e1;
    }
    .service-card:hover::before {
        opacity: 1;
    }
    .service-badge {
        display: inline-block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #dc2626;
        background: #fef2f2;
        padding: 4px 10px;
        border-radius: 4px;
        margin-bottom: 16px;
        width: fit-content;
    }
    .service-card-title {
        font-size: 22px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 12px;
        line-height: 1.3;
    }
    .service-card-title a {
        color: #111827;
        text-decoration: none;
        transition: color 0.15s;
    }
    .service-card-title a:hover {
        color: #dc2626;
    }
    .service-card-desc {
        font-size: 14px;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 20px;
        flex-grow: 1;
    }
    .service-features-list {
        list-style: none;
        padding: 0;
        margin: 0 0 24px;
        border-top: 1px dashed #e5e7eb;
        padding-top: 16px;
    }
    .service-features-list li {
        font-size: 13px;
        color: #374151;
        padding-left: 20px;
        position: relative;
        margin-bottom: 8px;
        line-height: 1.4;
    }
    .service-features-list li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: #dc2626;
        font-weight: bold;
    }
    .service-card-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #dc2626;
        text-decoration: none;
        transition: gap 0.2s ease;
    }
    .service-card-btn:hover {
        gap: 12px;
        color: #b91c1c;
    }

    /* CTA BANNER */
    .services-cta {
        background: #111827;
        color: #ffffff;
        border-radius: 16px;
        padding: 48px;
        text-align: center;
        margin-top: 60px;
        border: 1px solid #1f2937;
    }
    .services-cta h3 {
        font-size: 28px;
        font-weight: 800;
        margin-bottom: 12px;
    }
    .services-cta p {
        font-size: 16px;
        color: #9ca3af;
        max-width: 650px;
        margin: 0 auto 28px;
    }
    .cta-btn-group {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .cta-btn-primary {
        background: #dc2626;
        color: #ffffff;
        padding: 14px 28px;
        border-radius: 6px;
        font-weight: 700;
        text-decoration: none;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: .06em;
        transition: background 0.2s;
    }
    .cta-btn-primary:hover {
        background: #b91c1c;
    }
    .cta-btn-secondary {
        background: rgba(255,255,255,0.1);
        color: #ffffff;
        padding: 14px 28px;
        border-radius: 6px;
        font-weight: 700;
        text-decoration: none;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: .06em;
        transition: background 0.2s;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .cta-btn-secondary:hover {
        background: rgba(255,255,255,0.2);
    }

    @media (max-width: 768px) {
        .services-hero { padding: 40px 20px; }
        .services-hero h1 { font-size: 30px; }
        .services-wrapper { padding: 40px 20px; }
        .services-grid { grid-template-columns: 1fr; }
        .services-cta { padding: 32px 20px; }
    }
</style>

{{-- HERO SECTION --}}
<section class="services-hero">
    <div class="services-hero-inner">
        <nav class="breadcrumb-nav" aria-label="Breadcrumb">
            @foreach($breadcrumbs as $crumb)
                @if(!$loop->last)
                    <a href="{{ $crumb['url'] }}">{{ $crumb['name'] }}</a>
                    <span class="sep">/</span>
                @else
                    <span style="color:#ffffff;">{{ $crumb['name'] }}</span>
                @endif
            @endforeach
        </nav>
        <h1>{{ $page->headline ?? 'Industrial Engineering & Field Services' }}</h1>
        <p>{{ $page->summary ?? 'PT Misuba Guna Indonesia provides comprehensive field engineering, equipment repair, thermal overhaul, anti-corrosion lining, and mechanical maintenance services across Indonesia.' }}</p>
    </div>
</section>

{{-- SERVICES GRID SECTION --}}
<div class="services-wrapper">
    <div class="section-intro">
        <h2>Engineering Solutions Built on Expertise & Reliability</h2>
        <p>Our specialized field service engineers and workshop technicians assist industrial plants with turnkey maintenance, equipment refurbishments, protective coatings, and emergency on-site troubleshooting.</p>
    </div>

    <div class="services-grid">
        @foreach($services as $service)
            <div class="service-card">
                <div>
                    @if($service->service_group)
                        <span class="service-badge">{{ $service->service_group }}</span>
                    @endif
                    <h3 class="service-card-title">
                        <a href="{{ url($service->url_path) }}">{{ $service->name }}</a>
                    </h3>
                    <p class="service-card-desc">{{ $service->summary }}</p>

                    @if(!empty($service->key_features) && is_array($service->key_features))
                        <ul class="service-features-list">
                            @foreach(array_slice($service->key_features, 0, 3) as $feature)
                                <li>{{ $feature }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div>
                    <a href="{{ url($service->url_path) }}" class="service-card-btn">
                        View Service Details
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7-7 7m7-7H3"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    {{-- CTA BANNER --}}
    <div class="services-cta">
        <h3>Require On-Site Technical Assistance or Equipment Survey?</h3>
        <p>Contact our engineering specialists to schedule a field survey, request a quotation, or consult on specialized maintenance requirements for your plant.</p>
        <div class="cta-btn-group">
            <a href="/contact-us/" class="cta-btn-primary">Request Technical Consultation</a>
            <a href="tel:+622155660700" class="cta-btn-secondary">Call +62 21 55660700</a>
        </div>
    </div>
</div>
@endsection
