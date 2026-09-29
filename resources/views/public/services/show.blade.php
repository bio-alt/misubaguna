@extends('public.layout')

@section('content')
<style>
    /* =====================================================
       SERVICE DETAIL PAGE STYLING
    ===================================================== */
    .service-detail-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        padding: 50px 32px 60px;
        border-bottom: 4px solid #dc2626;
    }
    .service-detail-hero-inner {
        max-width: 1280px;
        margin: 0 auto;
    }
    .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #94a3b8;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .breadcrumb-nav a {
        color: #cbd5e1;
        text-decoration: none;
        transition: color 0.15s;
    }
    .breadcrumb-nav a:hover {
        color: #ef4444;
    }
    .breadcrumb-nav .sep {
        color: #64748b;
    }
    .service-badge-pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #f87171;
        background: rgba(220, 38, 38, 0.15);
        border: 1px solid rgba(239, 68, 68, 0.3);
        padding: 4px 12px;
        border-radius: 4px;
        margin-bottom: 16px;
    }
    .service-detail-hero h1 {
        font-size: 40px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 12px;
        color: #ffffff;
        letter-spacing: -0.02em;
    }
    .service-detail-hero .service-headline {
        font-size: 20px;
        font-weight: 600;
        color: #ef4444;
        margin-bottom: 16px;
        line-height: 1.4;
    }
    .service-detail-hero .service-summary {
        font-size: 16px;
        color: #cbd5e1;
        max-width: 850px;
        line-height: 1.7;
        margin-bottom: 28px;
    }
    .hero-cta-btns {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
    }
    .hero-btn-primary {
        background: #dc2626;
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 700;
        text-decoration: none;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .06em;
        transition: background 0.2s ease;
    }
    .hero-btn-primary:hover {
        background: #b91c1c;
    }
    .hero-btn-secondary {
        background: rgba(255, 255, 255, 0.1);
        color: #ffffff;
        padding: 12px 24px;
        border-radius: 6px;
        font-weight: 700;
        text-decoration: none;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .06em;
        border: 1px solid rgba(255, 255, 255, 0.2);
        transition: background 0.2s ease;
    }
    .hero-btn-secondary:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* MAIN CONTENT CONTAINER */
    .service-body-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 60px 32px;
        display: grid;
        grid-template-columns: 1fr 360px;
        gap: 48px;
    }

    /* SECTION STYLES */
    .service-section-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }
    .service-section-box h2 {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .service-section-box h2 .section-icon {
        width: 32px;
        height: 32px;
        background: #fef2f2;
        color: #dc2626;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        font-weight: bold;
    }

    /* FEATURES GRID */
    .features-list-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
    }
    .feature-item-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #dc2626;
        padding: 16px;
        border-radius: 6px;
        font-size: 14px;
        color: #334155;
        font-weight: 600;
        line-height: 1.5;
    }

    /* PROBLEMS GRID */
    .problems-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
    }
    .problem-card {
        background: #fff5f5;
        border: 1px solid #fed7d7;
        padding: 16px;
        border-radius: 8px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .problem-icon {
        color: #e53e3e;
        font-size: 18px;
        line-height: 1;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .problem-text {
        font-size: 14px;
        color: #742a2a;
        line-height: 1.5;
        font-weight: 500;
    }

    /* SCOPE OF WORK STEPS */
    .scope-steps-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .scope-step-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
    }
    .step-number {
        width: 36px;
        height: 36px;
        background: #dc2626;
        color: #ffffff;
        font-weight: 800;
        font-size: 15px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .step-content {
        font-size: 15px;
        color: #334155;
        line-height: 1.6;
        font-weight: 500;
        padding-top: 6px;
    }

    /* TAG BADGES */
    .tag-cloud {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .tag-badge {
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #cbd5e1;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 20px;
    }

    /* SIDEBAR STYLES */
    .sidebar-widget {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 28px;
        margin-bottom: 28px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .sidebar-widget h3 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f1f5f9;
    }
    .contact-widget {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border: none;
    }
    .contact-widget h3 {
        color: #ffffff;
        border-bottom-color: #334155;
    }
    .contact-widget p {
        font-size: 14px;
        color: #94a3b8;
        line-height: 1.6;
        margin-bottom: 20px;
    }
    .contact-info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 24px;
    }
    .contact-info-item {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 14px;
        color: #e2e8f0;
    }
    .contact-info-item svg {
        color: #ef4444;
        flex-shrink: 0;
    }
    .sidebar-cta-btn {
        display: block;
        width: 100%;
        text-align: center;
        background: #dc2626;
        color: #ffffff;
        padding: 14px;
        border-radius: 6px;
        font-weight: 700;
        text-decoration: none;
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: .06em;
        transition: background 0.2s ease;
    }
    .sidebar-cta-btn:hover {
        background: #b91c1c;
    }

    /* RELATED PRODUCTS CARDS */
    .related-products-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .related-product-card {
        display: block;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .related-product-card:hover {
        border-color: #dc2626;
        transform: translateX(4px);
        background: #ffffff;
    }
    .related-product-title {
        font-size: 15px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }
    .related-product-card:hover .related-product-title {
        color: #dc2626;
    }
    .related-product-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    @media (max-width: 992px) {
        .service-body-container {
            grid-template-columns: 1fr;
            padding: 40px 20px;
        }
        .service-detail-hero { padding: 40px 20px; }
        .service-detail-hero h1 { font-size: 30px; }
    }
</style>

{{-- HERO SECTION --}}
<section class="service-detail-hero">
    <div class="service-detail-hero-inner">
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

        @if($service->service_group)
            <div class="service-badge-pill">{{ $service->service_group }}</div>
        @endif

        <h1>{{ $service->name }}</h1>

        @if($service->headline)
            <div class="service-headline">{{ $service->headline }}</div>
        @endif

        <p class="service-summary">{{ $service->summary }}</p>

        <div class="hero-cta-btns">
            <a href="/contact-us/" class="hero-btn-primary">Request Service Consultation</a>
            <a href="https://wa.me/628118715671" target="_blank" rel="noopener" class="hero-btn-secondary">WhatsApp Engineering Support</a>
        </div>
    </div>
</section>

{{-- MAIN CONTENT --}}
<div class="service-body-container">
    <main>
        {{-- KEY FEATURES & CAPABILITIES --}}
        @if(!empty($service->key_features) && is_array($service->key_features))
            <div class="service-section-box">
                <h2>
                    <span class="section-icon">✓</span>
                    Key Service Capabilities & Features
                </h2>
                <div class="features-list-grid">
                    @foreach($service->key_features as $feature)
                        <div class="feature-item-card">
                            {{ $feature }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- TYPICAL INDUSTRIAL PROBLEMS SOLVED --}}
        @if(!empty($service->typical_problems) && is_array($service->typical_problems))
            <div class="service-section-box">
                <h2>
                    <span class="section-icon">!</span>
                    Industrial Challenges & Failures We Resolve
                </h2>
                <div class="problems-grid">
                    @foreach($service->typical_problems as $problem)
                        <div class="problem-card">
                            <span class="problem-icon">⚠️</span>
                            <div class="problem-text">{{ $problem }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- SCOPE OF WORK & ENGINEERING PROCESS --}}
        @if(!empty($service->scope_of_work) && is_array($service->scope_of_work))
            <div class="service-section-box">
                <h2>
                    <span class="section-icon">⚙</span>
                    Standard Scope of Work & Repair Methodology
                </h2>
                <div class="scope-steps-list">
                    @foreach($service->scope_of_work as $index => $step)
                        <div class="scope-step-item">
                            <div class="step-number">{{ $index + 1 }}</div>
                            <div class="step-content">{{ $step }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- MAIN DETAILED DESCRIPTION / CONTENT --}}
        @if($service->content)
            <div class="service-section-box">
                <h2>
                    <span class="section-icon">ℹ</span>
                    Detailed Technical Overview
                </h2>
                <div style="font-size: 15px; color: #334155; line-height: 1.8;">
                    {!! $service->content !!}
                </div>
            </div>
        @endif

        {{-- INDUSTRIES SERVED --}}
        @if(!empty($service->industries_served) && is_array($service->industries_served))
            <div class="service-section-box">
                <h2>
                    <span class="section-icon">🏭</span>
                    Target Industries & Operational Environments
                </h2>
                <div class="tag-cloud">
                    @foreach($service->industries_served as $industry)
                        <span class="tag-badge">{{ $industry }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- MATERIALS & EQUIPMENT UTILIZED --}}
        @if(!empty($service->materials_equipment) && is_array($service->materials_equipment))
            <div class="service-section-box">
                <h2>
                    <span class="section-icon">🛠</span>
                    Materials, Elastomers & Technical Equipment
                </h2>
                <div class="tag-cloud">
                    @foreach($service->materials_equipment as $mat)
                        <span class="tag-badge" style="background:#fef2f2; color:#b91c1c; border-color:#fca5a5;">{{ $mat }}</span>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    {{-- SIDEBAR --}}
    <aside>
        {{-- QUICK CONTACT WIDGET --}}
        <div class="sidebar-widget contact-widget">
            <h3>Schedule Field Service</h3>
            <p>Our experienced team of field technicians and workshop specialists are ready for emergency repairs, turnarounds, or routine maintenance.</p>
            
            <div class="contact-info-list">
                <div class="contact-info-item">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    <span>+62 21 55660700</span>
                </div>
                <div class="contact-info-item">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span>+62 811 8715 671 (WA)</span>
                </div>
                <div class="contact-info-item">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>marketing@misubaguna.com</span>
                </div>
            </div>

            <a href="/contact-us/" class="sidebar-cta-btn">Inquire for Service Quote</a>
        </div>

        {{-- RELATED PRODUCTS WIDGET --}}
        @if(isset($relatedProducts) && $relatedProducts->count() > 0)
            <div class="sidebar-widget">
                <h3>Related Industrial Products</h3>
                <div class="related-products-list">
                    @foreach($relatedProducts as $prod)
                        <a href="{{ url($prod->url_path) }}" class="related-product-card">
                            <div class="related-product-title">{{ $prod->title }}</div>
                            <div class="related-product-desc">{{ $prod->summary }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </aside>
</div>
@endsection
