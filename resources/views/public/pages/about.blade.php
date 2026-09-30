@extends('public.layout')

@section('content')
<style>
    /* Hero Section */
    .about-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 90px 24px 130px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .about-hero::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(43,157,159,0.18) 0%, rgba(15,23,42,0) 70%);
        pointer-events: none;
    }
    .about-badge {
        display: inline-block;
        background: rgba(43, 157, 159, 0.2);
        color: #2b9d9f;
        border: 1px solid rgba(43, 157, 159, 0.4);
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        padding: 6px 16px;
        border-radius: 20px;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }
    .about-hero h1 {
        font-size: 2.75rem;
        font-weight: 800;
        margin-bottom: 16px;
        color: #ffffff;
        letter-spacing: -0.02em;
        position: relative;
        z-index: 2;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .about-hero .slogan-tag {
        font-size: 1.25rem;
        color: #e67e22;
        font-weight: 700;
        max-width: 700px;
        margin: 0 auto;
        letter-spacing: 0.04em;
        position: relative;
        z-index: 2;
    }

    /* Main Content Wrapper */
    .about-wrapper {
        max-width: 1280px;
        margin: -70px auto 70px;
        padding: 0 24px;
        position: relative;
        z-index: 10;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    /* Intro Card Grid */
    .intro-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 28px;
        margin-bottom: 48px;
    }
    .intro-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .intro-card h2 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }
    .intro-card h2::after {
        content: '';
        position: absolute;
        left: 0; bottom: 0;
        width: 50px; height: 3px;
        background: #2b9d9f;
        border-radius: 2px;
    }
    .intro-card p {
        font-size: 0.95rem;
        color: #475569;
        line-height: 1.75;
        margin-bottom: 16px;
    }
    .intro-card p:last-child {
        margin-bottom: 0;
    }

    .banner-side-card {
        background: linear-gradient(135deg, #1a5276 0%, #0f172a 100%);
        border-radius: 16px;
        padding: 40px;
        color: #ffffff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }
    .banner-side-card::before {
        content: '';
        position: absolute;
        right: -40px; bottom: -40px;
        width: 200px; height: 200px;
        background: rgba(230, 126, 34, 0.15);
        border-radius: 50%;
        pointer-events: none;
    }
    .banner-side-card .mgi-slogan {
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.4;
        color: #ffffff;
        margin-bottom: 24px;
        border-left: 4px solid #e67e22;
        padding-left: 16px;
    }
    .stat-list {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        border-top: 1px solid rgba(255,255,255,0.15);
        padding-top: 20px;
    }
    .stat-box h4 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #e67e22;
        margin: 0 0 4px 0;
    }
    .stat-box p {
        font-size: 0.8rem;
        color: #cbd5e1;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    /* Our Strengths Section */
    .strengths-section {
        background: #ffffff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        margin-bottom: 48px;
    }
    .strengths-header {
        text-align: center;
        margin-bottom: 36px;
    }
    .strengths-header h2 {
        font-size: 1.85rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .strengths-header p {
        font-size: 0.95rem;
        color: #64748b;
    }

    .strengths-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }
    .strength-item {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.25s ease;
    }
    .strength-item:hover {
        background: #ffffff;
        border-color: #2b9d9f;
        box-shadow: 0 8px 20px rgba(43, 157, 159, 0.12);
        transform: translateY(-2px);
    }
    .strength-icon {
        width: 44px;
        height: 44px;
        background: #f0fdfa;
        color: #2b9d9f;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .strength-item:hover .strength-icon {
        background: #2b9d9f;
        color: #ffffff;
    }
    .strength-icon svg {
        width: 22px;
        height: 22px;
    }
    .strength-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
    }

    /* Vision & Mission Grid */
    .vm-section {
        display: grid;
        grid-template-columns: 1fr 1.2fr;
        gap: 28px;
        margin-bottom: 48px;
    }
    .vision-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .vision-card .icon-wrap {
        width: 54px;
        height: 54px;
        background: rgba(230, 126, 34, 0.2);
        color: #e67e22;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    .vision-card .icon-wrap svg {
        width: 28px;
        height: 28px;
    }
    .vision-card h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #ffffff;
        margin-bottom: 14px;
    }
    .vision-card p {
        font-size: 1.05rem;
        color: #cbd5e1;
        line-height: 1.7;
        margin: 0;
    }

    .mission-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .mission-card h2 {
        font-size: 1.6rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 24px;
        position: relative;
        padding-bottom: 10px;
    }
    .mission-card h2::after {
        content: '';
        position: absolute;
        left: 0; bottom: 0;
        width: 50px; height: 3px;
        background: #e67e22;
        border-radius: 2px;
    }
    .mission-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .mission-item {
        display: flex;
        align-items: flex-start;
        gap: 16px;
    }
    .mission-icon {
        width: 42px;
        height: 42px;
        background: #fff7ed;
        color: #e67e22;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .mission-icon svg {
        width: 22px;
        height: 22px;
    }
    .mission-text {
        font-size: 0.95rem;
        color: #334155;
        line-height: 1.6;
        font-weight: 600;
    }

    /* CMS Content */
    .db-content {
        background: #ffffff;
        border-radius: 16px;
        padding: 40px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        color: #334155;
        line-height: 1.7;
        font-size: 0.95rem;
    }

    @media (max-width: 991px) {
        .intro-grid, .vm-section {
            grid-template-columns: 1fr;
        }
        .strengths-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .about-hero h1 {
            font-size: 2.1rem;
        }
    }
    @media (max-width: 576px) {
        .strengths-grid {
            grid-template-columns: 1fr;
        }
        .stat-list {
            grid-template-columns: 1fr;
        }
        .about-hero {
            padding: 60px 16px 100px;
        }
    }
</style>

<!-- Hero Section -->
<section class="about-hero">
    <div class="about-badge">PT Misuba Guna Indonesia</div>
    <h1>About Our Company</h1>
    <div class="slogan-tag">Built on Trust, Driven by Excellence</div>
</section>

<!-- Main Content Wrapper -->
<div class="about-wrapper">
    
    <!-- Introduction Section -->
    <div class="intro-grid">
        <div class="intro-card">
            <h2>Introduction</h2>
            <p>Since 2020, we have been committed to providing high-quality products and services with competitive pricing, timely delivery, and precise execution.</p>
            <p>By partnering with global manufacturers, we ensure the best value and top-tier quality. As an authorized agent for several international brands, PT Misuba Guna Indonesia delivers reliable solutions tailored to our customers' needs.</p>
            <p>Our growth is driven by the trust of our suppliers and clients, and we remain dedicated to maintaining that trust. Built on trust, driven by excellence, we will continue to be your dependable and forward-thinking supplier.</p>
        </div>

        <div class="banner-side-card">
            <div>
                <div class="mgi-slogan">"Built on Trust, Driven by Excellence"</div>
                <p style="font-size:0.9rem; color:#cbd5e1; line-height:1.6; margin-bottom: 24px;">
                    We specialize in mechanical engineering, electrical services, and industrial technical supply across power generation, palm oil, petrochemical, mining, and manufacturing industries.
                </p>
            </div>

            <div class="stat-list">
                <div class="stat-box">
                    <h4>2020</h4>
                    <p>Established</p>
                </div>
                <div class="stat-box">
                    <h4>Global</h4>
                    <p>Authorized Agent</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Strength Section -->
    <div class="strengths-section">
        <div class="strengths-header">
            <h2>Our Strength</h2>
            <p>The core values and operational principles that define our commitment to industrial excellence.</p>
        </div>

        <div class="strengths-grid">
            <!-- 1. Passion -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 16.121A3 3 0 1012.015 11L11 14H9.879z"/>
                    </svg>
                </div>
                <div class="strength-title">Passion</div>
            </div>

            <!-- 2. Persistent -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div class="strength-title">Persistent</div>
            </div>

            <!-- 3. Planning -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div class="strength-title">Planning</div>
            </div>

            <!-- 4. Control -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="strength-title">Control</div>
            </div>

            <!-- 5. Guiding -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a2 2 0 01-2 2 2 2 0 01-2-2V4zM10.707 14.707a1 1 0 010-1.414l3.586-3.586a1 1 0 011.414 1.414l-3.586 3.586a1 1 0 01-1.414 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                    </svg>
                </div>
                <div class="strength-title">Guiding</div>
            </div>

            <!-- 6. Hardwork -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 4a2 2 0 114 0v1a2 2 0 01-2 2 2 2 0 01-2-2V4zM10.707 14.707a1 1 0 010-1.414l3.586-3.586a1 1 0 011.414 1.414l-3.586 3.586a1 1 0 01-1.414 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="strength-title">Hardwork</div>
            </div>

            <!-- 7. Service -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="strength-title">Service</div>
            </div>

            <!-- 8. Positive Thinking -->
            <div class="strength-item">
                <div class="strength-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 01-2 2h-0a2 2 0 01-2-2v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <div class="strength-title">Positive Thinking</div>
            </div>
        </div>
    </div>

    <!-- Vision & Mission Grid -->
    <div class="vm-section">
        <!-- Vision -->
        <div class="vision-card">
            <div class="icon-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </div>
            <h2>Our Vision</h2>
            <p>Become the first choice and most trusted company for mechanical engineering, electrical service and supply.</p>
        </div>

        <!-- Mission -->
        <div class="mission-card">
            <h2>Our Mission</h2>
            <div class="mission-list">
                <div class="mission-item">
                    <div class="mission-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                        </svg>
                    </div>
                    <div class="mission-text">Providing high quality product and service at reasonable price</div>
                </div>

                <div class="mission-item">
                    <div class="mission-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="mission-text">Providing a fast and accurate response to our customer's request</div>
                </div>

                <div class="mission-item">
                    <div class="mission-icon">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                        </svg>
                    </div>
                    <div class="mission-text">Extend and expand our business with global manufacturers</div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
