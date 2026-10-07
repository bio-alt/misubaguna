@extends('public.layout')

@section('content')
<style>
/* Category Page Styling */
.category-wrapper {
    background-color: #f8fafc;
    min-height: 100vh;
    padding-bottom: 64px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

.category-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    padding: 40px 0 56px;
    border-bottom: 3px solid #dc2626;
    position: relative;
    overflow: hidden;
}

.category-hero::before {
    content: '';
    position: absolute;
    top: 0; right: 0; bottom: 0; left: 0;
    background: radial-gradient(circle at 80% 20%, rgba(220, 38, 38, 0.15) 0%, transparent 50%);
    pointer-events: none;
}

.category-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
    position: relative;
    z-index: 2;
}

.category-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #94a3b8;
    margin-bottom: 18px;
    font-weight: 500;
}

.category-breadcrumb a {
    color: #cbd5e1;
    text-decoration: none;
    transition: color 0.15s;
}

.category-breadcrumb a:hover {
    color: #e67e22;
}

.category-breadcrumb .sep {
    color: #64748b;
}

.category-breadcrumb .current {
    color: #ffffff;
    font-weight: 700;
}

.category-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(220, 38, 38, 0.2);
    border: 1px solid rgba(220, 38, 38, 0.4);
    color: #fca5a5;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    padding: 5px 12px;
    border-radius: 9999px;
    margin-bottom: 14px;
}

.category-hero h1 {
    font-size: 2.2rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 12px;
    letter-spacing: -0.02em;
    color: #ffffff;
}

.category-hero .category-summary {
    font-size: 1.05rem;
    color: #cbd5e1;
    max-width: 860px;
    line-height: 1.6;
    margin-bottom: 24px;
}

/* Principal Partner Banner */
.principal-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    padding: 14px 20px;
    max-width: 860px;
}

.principal-flag {
    width: 36px;
    height: 36px;
    background: #dc2626;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 14px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
}

.principal-info {
    font-size: 0.85rem;
    color: #e2e8f0;
    line-height: 1.45;
}

.principal-info strong {
    color: #ffffff;
    font-size: 0.95rem;
    display: block;
    margin-bottom: 2px;
}

/* Key Capabilities Grid */
.capabilities-section {
    margin-top: -30px;
    margin-bottom: 40px;
    position: relative;
    z-index: 3;
}

.capabilities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
}

.capability-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 18px 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.capability-card .cap-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: #fef2f2;
    color: #dc2626;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    margin-bottom: 4px;
}

.capability-card h4 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.capability-card p {
    font-size: 0.8rem;
    color: #64748b;
    line-height: 1.45;
    margin: 0;
}

/* Narrative Body */
.category-narrative {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 28px 32px;
    margin-bottom: 36px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    font-size: 0.9rem;
    line-height: 1.6;
    color: #374151;
}

.category-narrative h2 {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 12px 0;
    letter-spacing: -0.01em;
}

.category-narrative p {
    margin-bottom: 14px;
}

.category-narrative p:last-child {
    margin-bottom: 0;
}

/* Products Section */
.products-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 12px;
}

.products-header-bar h2 {
    font-size: 1.5rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}

.products-count-badge {
    font-size: 0.85rem;
    font-weight: 600;
    color: #64748b;
    background: #f1f5f9;
    padding: 4px 12px;
    border-radius: 20px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
    gap: 24px;
    margin-bottom: 48px;
}

.product-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.product-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
    border-color: #cbd5e1;
}

.product-card-thumb-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 220px;
    background: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 14px;
    position: relative;
    overflow: hidden;
    text-decoration: none;
}

.product-card-thumb-link img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.product-card:hover .product-card-thumb-link img {
    transform: scale(1.05);
}

.product-card-top {
    padding: 20px 24px 14px;
    flex: 1;
}

.product-subgroup-tag {
    display: inline-block;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: #dc2626;
    background: #fef2f2;
    padding: 3px 8px;
    border-radius: 4px;
    margin-bottom: 10px;
}

.product-card-title {
    font-size: 1.15rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.35;
    margin: 0 0 8px;
}

.product-card-title a {
    color: inherit;
    text-decoration: none;
    transition: color 0.15s;
}

.product-card-title a:hover {
    color: #dc2626;
}

.product-card-headline {
    font-size: 0.8rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 10px;
}

.product-card-summary {
    font-size: 0.85rem;
    color: #64748b;
    line-height: 1.5;
    margin-bottom: 16px;
}

/* Feature tags inside card */
.product-spec-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: auto;
}

.spec-pill {
    font-size: 0.75rem;
    font-weight: 600;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #334155;
    padding: 3px 8px;
    border-radius: 4px;
}

.product-card-actions {
    padding: 14px 24px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.btn-card-details {
    font-size: 0.825rem;
    font-weight: 700;
    color: #dc2626;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: gap 0.2s;
}

.btn-card-details:hover {
    gap: 8px;
}

.btn-card-inquire {
    font-size: 0.75rem;
    font-weight: 700;
    background: #0f172a;
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.15s;
}

.btn-card-inquire:hover {
    background: #dc2626;
}

/* Principal Deep Dive Card */
.principal-showcase-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 32px;
    margin-bottom: 40px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.03);
}

.principal-showcase-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 32px;
}

.principal-showcase-left h3 {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 12px 0;
}

.principal-showcase-left p {
    font-size: 0.875rem;
    color: #475569;
    line-height: 1.6;
    margin-bottom: 14px;
}

.principal-showcase-right {
    background: #f8fafc;
    border-radius: 8px;
    padding: 20px 24px;
    border: 1px solid #e2e8f0;
}

.principal-showcase-right h4 {
    font-size: 0.95rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 12px 0;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.principal-features-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.principal-features-list li {
    font-size: 0.85rem;
    color: #334155;
    line-height: 1.45;
    position: relative;
    padding-left: 22px;
}

.principal-features-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 0;
    color: #dc2626;
    font-weight: 900;
}

/* Engineering Consultation CTA */
.engineering-cta-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    border-radius: 12px;
    padding: 36px;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 32px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.engineering-cta-content h3 {
    font-size: 1.4rem;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 8px 0;
}

.engineering-cta-content p {
    font-size: 0.9rem;
    color: #cbd5e1;
    margin: 0;
    max-width: 680px;
    line-height: 1.55;
}

.engineering-cta-actions {
    display: flex;
    gap: 12px;
    flex-shrink: 0;
}

.btn-cta-rfq {
    background: #dc2626;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.15s;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
}

.btn-cta-rfq:hover {
    background: #b91c1c;
}

.btn-cta-wa {
    background: #25d366;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 12px 24px;
    border-radius: 6px;
    text-decoration: none;
    transition: background 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.btn-cta-wa:hover {
    background: #1eb954;
}

@media (max-width: 992px) {
    .principal-showcase-grid { grid-template-columns: 1fr; }
    .engineering-cta-card { flex-direction: column; align-items: flex-start; }
    .products-grid { grid-template-columns: 1fr; }
}
</style>

<div class="category-wrapper">
    {{-- Hero Section --}}
    <section class="category-hero">
        <div class="category-inner">
            <div class="category-breadcrumb">
                <a href="/">Home</a>
                <span class="sep">&gt;</span>
                <a href="/catalog">Products</a>
                <span class="sep">&gt;</span>
                <span class="current">{{ $category->name }}</span>
            </div>

            @if($category->slug === 'filtration-clarification')
                <span class="category-badge-pill">Industrial Fluid Filtration Systems</span>
            @else
                <span class="category-badge-pill">{{ $category->parent_group ?? 'Industrial Engineering Solutions' }}</span>
            @endif
            <h1>{{ $category->name }}</h1>

            <div class="category-summary">
                {{ $category->summary ?? ($category->name . ' engineered solutions and technical equipment from PT Misuba Guna Indonesia.') }}
            </div>

            @if($category->slug === 'filtration-clarification')
                <div class="principal-banner">
                    <div class="principal-flag">JCI</div>
                    <div class="principal-info">
                        <strong>Authorized Indonesia Partner of Shanghai JCI Technology Co., Ltd. (上海久丞工业科技有限公司)</strong>
                        Leading global manufacturer specializing in automatic filter plate presses, intelligent self-cleaning filtration, hermetic cake clarifying systems, and precision fluid separation.
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Main Content Inner --}}
    <div class="category-inner">
        {{-- 4 Core Capability Pillars --}}
        @if($category->slug === 'filtration-clarification')
            <section class="capabilities-section">
                <div class="capabilities-grid">
                    <div class="capability-card">
                        <div class="cap-icon">🛡️</div>
                        <h4>Filter Plate Press & Cake Clarification</h4>
                        <p>High-pressure automatic membrane & chamber filter plate presses with diaphragm squeeze, plus hermetic plate/leaf and candle filters.</p>
                    </div>
                    <div class="capability-card">
                        <div class="cap-icon">⚙️</div>
                        <h4>Continuous Online Self-Cleaning</h4>
                        <p>Mechanical scraping (AF Series) and multi-cartridge backwash (AR Series) operate 24/7 without consumables or manual cleaning.</p>
                    </div>
                    <div class="capability-card">
                        <div class="cap-icon">🔬</div>
                        <h4>Precision Vessel & Cartridge Systems</h4>
                        <p>Quick-opening multi-bag housings (BT Series) and sanitary cartridge vessels (CT Series) down to 0.05 micron sterile grade.</p>
                    </div>
                    <div class="capability-card">
                        <div class="cap-icon">⚡</div>
                        <h4>Centrifugal & Magnetic Separation</h4>
                        <p>Media-free hydrocyclone sand separators (CS Series) and 12,000 Gauss NdFeB magnetic iron removers (MS Series).</p>
                    </div>
                </div>
            </section>
        @endif

        {{-- Narrative Overview --}}
        @if($category->description_html)
            <div class="category-narrative">
                {!! $category->description_html !!}
            </div>
        @endif

        {{-- Products Grid --}}
        <section class="products-section">
            <div class="products-header-bar">
                <h2>Equipment & Systems ({{ $products->count() }})</h2>
                <span class="products-count-badge">Official JCI Product Range</span>
            </div>

            @if($products->count())
                <div class="products-grid">
                    @foreach($products as $product)
                        <div class="product-card">
                            @php
                                $thumbImg = $product->og_image_path ?? (is_array($product->gallery) && count($product->gallery) > 0 ? $product->gallery[0] : null);
                            @endphp
                            @if($thumbImg)
                                <a href="{{ $product->url_path }}" class="product-card-thumb-link" title="{{ $product->title ?? $product->name }}">
                                    <img src="{{ asset($thumbImg) }}" alt="{{ $product->title ?? $product->name }}" loading="lazy">
                                </a>
                            @endif
                            <div class="product-card-top">
                                @if($product->product_subgroup)
                                    <span class="product-subgroup-tag">{{ $product->product_subgroup }}</span>
                                @endif
                                <h3 class="product-card-title">
                                    <a href="{{ $product->url_path }}">{{ $product->title ?? $product->name }}</a>
                                </h3>
                                @if($product->headline)
                                    <div class="product-card-headline">{{ $product->headline }}</div>
                                @endif
                                <div class="product-card-summary">
                                    {{ Str::limit($product->summary, 160) }}
                                </div>

                                @if($product->technical_specs && is_array($product->technical_specs))
                                    <div class="product-spec-pills">
                                        @foreach(array_slice($product->technical_specs, 0, 3) as $spec)
                                            <span class="spec-pill">{{ $spec }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="product-card-actions">
                                <a href="{{ $product->url_path }}" class="btn-card-details">
                                    View Specifications &rarr;
                                </a>
                                <a href="/contact-us" class="btn-card-inquire">
                                    Request Quote
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p style="color: #64748b; font-size: 0.95rem;">No products currently listed under this category.</p>
            @endif
        </section>

        {{-- Principal Partnership Spotlight --}}
        @if($category->slug === 'filtration-clarification')
        <section class="principal-showcase-box">
            <div class="principal-showcase-grid">
                <div class="principal-showcase-left">
                    <h3>About Our Principal: Shanghai JCI Technology Co., Ltd.</h3>
                    <p>
                        Shanghai JCI Technology Co., Ltd. (上海久丞工业科技有限公司) is a premier global designer and manufacturer in the fluid filtration industry. Specializing in automated, intelligent, and self-cleaning filtration systems, JCI operates state-of-the-art production and R&D centers in Kunshan (Jiangsu) and Taiwan.
                    </p>
                    <p>
                        Through strictly controlled design, precision machining, 3D finite element magnetic circuit modeling, and automated testing, JCI delivers first-class filtration systems meeting rigorous international standards across petrochemicals, oleochemicals, palm oil mills, fine chemicals, paper & pulp, food & beverage, and environmental water treatment.
                    </p>
                    <p>
                        <strong>PT Misuba Guna Indonesia</strong> provides local engineering consultation, equipment sizing, technical drawings, commissioning, and inventory support for JCI filtration equipment across Indonesia.
                    </p>
                </div>
                <div class="principal-showcase-right">
                    <h4>Direct Engineering & Supply Benefits</h4>
                    <ul class="principal-features-list">
                        <li><strong>Zero Consumable Scraping & Backwash:</strong> Dramatic reduction in operating costs, waste disposal fees, and labor compared to disposable filters.</li>
                        <li><strong>High-Viscosity & Hazardous Media:</strong> Handles fluids up to 800,000 cP and explosive/volatile solvents with ATEX/pneumatic configurations.</li>
                        <li><strong>Hermetic Sealed Design:</strong> Total operator safety and zero vapor emissions for hot oils, harsh chemicals, and sterile pharmaceuticals.</li>
                        <li><strong>Complete Local Engineering Support:</strong> Indonesian inventory of spare baskets, screens, filter bags, cartridges, and technical field assistance from PT Misuba Guna Indonesia.</li>
                    </ul>
                </div>
            </div>

            {{-- JCI Manufacturing & Engineering Facilities --}}
            <div style="margin-top: 28px; padding-top: 24px; border-top: 1px solid #e2e8f0;">
                <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 16px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                    <span style="color: #dc2626;">🏭</span> JCI Manufacturing &amp; Engineering Facilities
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <img src="{{ asset('images/products/filtration/principal/jci-engineering-facility.webp') }}" alt="Shanghai JCI Engineering & R&D Campus" loading="lazy" style="width: 100%; height: 180px; object-fit: cover; display: block;">
                        <div style="padding: 12px 14px;">
                            <strong style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 2px;">Kunshan Advanced R&amp;D Campus</strong>
                            <span style="font-size: 0.775rem; color: #64748b; line-height: 1.4; display: block;">High-precision automated test rigs, pressure vessel engineering, and filtration simulation laboratories.</span>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <img src="{{ asset('images/products/filtration/principal/jci-manufacturing-workshop.webp') }}" alt="JCI Vessel Fabrication Workshop" loading="lazy" style="width: 100%; height: 180px; object-fit: cover; display: block;">
                        <div style="padding: 12px 14px;">
                            <strong style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 2px;">Heavy Vessel &amp; Piping Fabrication</strong>
                            <span style="font-size: 0.775rem; color: #64748b; line-height: 1.4; display: block;">CNC machining, automated welding, sanitary electro-polishing, and rigorous non-destructive testing (NDT).</span>
                        </div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <img src="{{ asset('images/products/filtration/principal/jci-global-operations.webp') }}" alt="JCI Global Distribution Network" loading="lazy" style="width: 100%; height: 180px; object-fit: cover; display: block;">
                        <div style="padding: 12px 14px;">
                            <strong style="display: block; font-size: 0.85rem; color: #0f172a; margin-bottom: 2px;">Global Sales &amp; Technical Network</strong>
                            <span style="font-size: 0.775rem; color: #64748b; line-height: 1.4; display: block;">Global industrial customer support, export certifications (CE, ASME, ISO), and direct regional engineering backing.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- Engineering Consultation CTA --}}
        <section class="engineering-cta-card">
            <div class="engineering-cta-content">
                <h3>Need Custom Sizing, P&ID Verification, or a Fast Quote?</h3>
                <p>
                    Our filtration engineers work with your exact flowrate, temperature, design pressure, fluid viscosity, and target micron rating to deliver tailored JCI filtration solutions for your plant.
                </p>
            </div>
            <div class="engineering-cta-actions">
                <a href="/contact-us" class="btn-cta-rfq">Submit RFQ</a>
                <a href="https://wa.me/6281119253388?text=Hello%20PT%20Misuba%20Guna%20Indonesia,%20I%20would%20like%20to%20inquire%20about%20JCI%20Filtration%20and%20Clarification%20Systems" target="_blank" class="btn-cta-wa">
                    <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    WhatsApp Support
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
