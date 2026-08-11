@extends('public.layout')

@section('content')
<style>
    /* Hero Section */
    .about-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 100px 20px 140px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .about-hero::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(43,157,159,0.15) 0%, rgba(15,23,42,0) 70%);
        pointer-events: none;
    }
    .about-hero h1 {
        font-size: 52px;
        font-weight: 800;
        margin-bottom: 24px;
        background: linear-gradient(to right, #ffffff, #cbd5e1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        position: relative;
        z-index: 2;
    }
    .about-hero p {
        font-size: 20px;
        color: #94a3b8;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.7;
        position: relative;
        z-index: 2;
    }

    /* Main Content Wrapper */
    .about-wrapper {
        max-width: 1280px;
        margin: -80px auto 80px;
        padding: 0 32px;
        position: relative;
        z-index: 10;
    }

    /* Intro / Vision Mission Grid */
    .vm-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        margin-bottom: 64px;
    }

    @media (max-width: 991px) {
        .vm-grid {
            grid-template-columns: 1fr;
        }
        .about-hero {
            padding: 80px 20px 100px;
        }
    }

    .vm-card {
        background: #ffffff;
        border-radius: 24px;
        padding: 48px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.06);
        border: 1px solid #f1f5f9;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .vm-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 30px 60px rgba(0,0,0,0.1);
        border-color: #e2e8f0;
    }
    .vm-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
        background: linear-gradient(to right, #2b9d9f, #1a7a7c);
    }
    .vm-card-icon {
        width: 64px;
        height: 64px;
        background: #f0fdfa;
        color: #2b9d9f;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        transition: background 0.3s ease, color 0.3s ease;
    }
    .vm-card:hover .vm-card-icon {
        background: #2b9d9f;
        color: #ffffff;
    }
    .vm-card-icon svg {
        width: 32px;
        height: 32px;
    }
    .vm-card h2 {
        font-size: 28px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 16px;
    }
    .vm-card p {
        font-size: 16px;
        color: #475569;
        line-height: 1.8;
    }

    /* Core Values Section */
    .values-section {
        margin-top: 80px;
        text-align: center;
    }
    .values-section h2 {
        font-size: 36px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 48px;
    }
    .values-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 32px;
    }
    @media (max-width: 991px) {
        .values-grid {
            grid-template-columns: 1fr;
        }
    }
    .value-item {
        background: #f8fafc;
        border-radius: 20px;
        padding: 40px 32px;
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .value-item:hover {
        background: #ffffff;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        border-color: #f1f5f9;
        transform: translateY(-4px);
    }
    .value-icon {
        width: 56px;
        height: 56px;
        background: #ffffff;
        color: #2b9d9f;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 10px 20px rgba(43,157,159,0.1);
    }
    .value-icon svg {
        width: 24px;
        height: 24px;
    }
    .value-item h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
    }
    .value-item p {
        font-size: 15px;
        color: #64748b;
        line-height: 1.6;
    }

    /* CMS Content */
    .db-content {
        background: #ffffff;
        border-radius: 24px;
        padding: 56px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.06);
        border: 1px solid #f1f5f9;
        margin-top: 64px;
        color: #334155;
        line-height: 1.8;
        font-size: 16px;
    }
    @media (max-width: 768px) {
        .db-content {
            padding: 32px;
        }
    }
    .db-content h2, .db-content h3 {
        color: #0f172a;
        font-weight: 800;
        margin-top: 32px;
        margin-bottom: 16px;
    }
    .db-content p {
        margin-bottom: 24px;
    }
    .db-content ul, .db-content ol {
        margin-bottom: 24px;
        padding-left: 24px;
    }
    .db-content li {
        margin-bottom: 8px;
    }
    .db-content img {
        max-width: 100%;
        height: auto;
        border-radius: 16px;
        margin: 24px 0;
    }
</style>

<!-- Hero Section -->
<section class="about-hero">
    <h1>{{ $page->headline ?? 'About Us' }}</h1>
    <p>We are a leading provider of premium engineering solutions, committed to excellence, innovation, and long-term partnerships.</p>
</section>

<!-- Main Wrapper -->
<div class="about-wrapper">
    <!-- Vision & Mission Grid -->
    <div class="vm-grid">
        <!-- Vision -->
        <div class="vm-card">
            <div class="vm-card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <h2>Our Vision</h2>
            <p>To be the most trusted and reliable partner in providing high-quality engineering products, setting the standard for excellence and innovation across the industry in Indonesia and beyond.</p>
        </div>

        <!-- Mission -->
        <div class="vm-card">
            <div class="vm-card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <h2>Our Mission</h2>
            <p>We strive to deliver exceptional value to our clients by supplying top-tier products, offering unparalleled technical support, and maintaining a relentless commitment to customer satisfaction and operational efficiency.</p>
        </div>
    </div>

    <!-- Core Values -->
    <div class="values-section">
        <h2>Why Choose Us</h2>
        <div class="values-grid">
            <div class="value-item">
                <div class="value-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3>Premium Quality</h3>
                <p>We exclusively partner with renowned manufacturers to ensure every product we deliver meets the highest global standards.</p>
            </div>
            <div class="value-item">
                <div class="value-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <h3>Technical Expertise</h3>
                <p>Our team of engineers brings decades of combined experience, providing you with insightful recommendations and robust solutions.</p>
            </div>
            <div class="value-item">
                <div class="value-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3>Customer Centric</h3>
                <p>Your success is our priority. We are dedicated to building lasting relationships based on trust, transparency, and reliable support.</p>
            </div>
        </div>
    </div>

    <!-- CMS Content -->
    @if(!empty(trim(strip_tags($page->content_html ?? ''))))
    <div class="db-content">
        {!! $page->content_html !!}
    </div>
    @endif
</div>
@endsection
