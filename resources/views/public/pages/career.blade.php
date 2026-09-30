@extends('public.layout')

@section('content')
<style>
    /* Hero Section */
    .career-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 90px 20px 130px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .career-hero::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(43,157,159,0.18) 0%, rgba(15,23,42,0) 70%);
        pointer-events: none;
    }
    .career-badge {
        display: inline-block;
        background: rgba(43, 157, 159, 0.15);
        color: #2b9d9f;
        border: 1px solid rgba(43, 157, 159, 0.3);
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        margin-bottom: 20px;
        position: relative;
        z-index: 2;
    }
    .career-hero h1 {
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 20px;
        background: linear-gradient(to right, #ffffff, #cbd5e1);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        position: relative;
        z-index: 2;
    }
    .career-hero p {
        font-size: 18px;
        color: #94a3b8;
        max-width: 720px;
        margin: 0 auto 36px;
        line-height: 1.7;
        position: relative;
        z-index: 2;
    }

    /* Stats Ribbon */
    .career-stats {
        display: flex;
        justify-content: center;
        gap: 32px;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }
    .stat-pill {
        background: rgba(255, 255, 255, 0.06);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 600;
        color: #e2e8f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .stat-pill svg {
        width: 18px;
        height: 18px;
        color: #2b9d9f;
    }

    /* Main Container */
    .career-container {
        max-width: 1280px;
        margin: -60px auto 80px;
        padding: 0 32px;
        position: relative;
        z-index: 10;
    }

    /* Culture / Benefits Section */
    .benefits-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        margin-bottom: 72px;
    }
    @media (max-width: 1024px) {
        .benefits-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .benefits-grid { grid-template-columns: 1fr; }
        .career-hero h1 { font-size: 34px; }
    }
    .benefit-card {
        background: #ffffff;
        border-radius: 18px;
        padding: 32px 24px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    .benefit-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
        border-color: #2b9d9f;
    }
    .benefit-icon {
        width: 52px;
        height: 52px;
        background: #f0fdfa;
        color: #2b9d9f;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }
    .benefit-icon svg { width: 26px; height: 26px; }
    .benefit-card h3 {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 10px;
    }
    .benefit-card p {
        font-size: 14px;
        color: #64748b;
        line-height: 1.6;
        margin: 0;
    }

    /* Open Positions Header */
    .positions-section-header {
        text-align: center;
        margin-bottom: 48px;
    }
    .positions-section-header h2 {
        font-size: 36px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 12px;
    }
    .positions-section-header p {
        font-size: 16px;
        color: #64748b;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Position Cards List */
    .positions-list {
        display: flex;
        flex-direction: column;
        gap: 24px;
        margin-bottom: 72px;
    }
    .position-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .position-card:hover {
        border-color: #2b9d9f;
        box-shadow: 0 12px 35px rgba(43, 157, 159, 0.1);
    }
    .position-header {
        padding: 32px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        flex-wrap: wrap;
        background: #ffffff;
    }
    .position-main {
        flex: 1;
        min-width: 280px;
    }
    .position-badge-tag {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 20px;
        background: #f0fdfa;
        color: #0d9488;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .position-title {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }
    .position-meta {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: 14px;
        color: #64748b;
        margin-top: 8px;
    }
    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .meta-item svg { width: 16px; height: 16px; color: #2b9d9f; }
    .position-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .btn-toggle-detail {
        background: #f8fafc;
        color: #334155;
        border: 1px solid #cbd5e1;
        padding: 12px 20px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-toggle-detail:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
        color: #0f172a;
    }
    .btn-apply-now {
        background: #2b9d9f;
        color: #ffffff;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .btn-apply-now:hover {
        background: #227f81;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(43, 157, 159, 0.3);
    }

    /* Expandable Detail Panel */
    .position-details {
        display: none;
        padding: 0 32px 32px;
        border-top: 1px solid #f1f5f9;
        background: #fafafa;
    }
    .position-details.open {
        display: block;
    }
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        margin-top: 24px;
    }
    @media (max-width: 768px) {
        .details-grid { grid-template-columns: 1fr; }
        .position-header { padding: 24px; }
        .position-details { padding: 0 24px 24px; }
    }
    .detail-box h4 {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .detail-box h4 svg { width: 20px; height: 20px; color: #2b9d9f; }
    .detail-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .detail-list li {
        position: relative;
        padding-left: 24px;
        margin-bottom: 12px;
        font-size: 14px;
        color: #475569;
        line-height: 1.6;
    }
    .detail-list li::before {
        content: '✓';
        position: absolute;
        left: 0;
        top: 0;
        color: #2b9d9f;
        font-weight: 800;
        font-size: 14px;
    }

    /* Recruitment Process Section */
    .process-section {
        background: #f8fafc;
        border-radius: 24px;
        padding: 56px 40px;
        margin-bottom: 72px;
        border: 1px solid #e2e8f0;
    }
    .process-section h2 {
        text-align: center;
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 40px;
    }
    .process-steps {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 24px;
        position: relative;
    }
    @media (max-width: 991px) {
        .process-steps { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 576px) {
        .process-steps { grid-template-columns: 1fr; }
    }
    .step-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 28px 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        position: relative;
    }
    .step-number {
        width: 44px;
        height: 44px;
        background: #2b9d9f;
        color: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 18px;
        margin: 0 auto 16px;
    }
    .step-card h3 {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 8px;
    }
    .step-card p {
        font-size: 13px;
        color: #64748b;
        margin: 0;
        line-height: 1.5;
    }

    /* General Application Callout */
    .general-apply-banner {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 24px;
        padding: 48px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 32px;
        flex-wrap: wrap;
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    .banner-text h3 {
        font-size: 26px;
        font-weight: 800;
        margin-bottom: 10px;
    }
    .banner-text p {
        font-size: 15px;
        color: #94a3b8;
        margin: 0;
        max-width: 600px;
    }
    .banner-btn {
        background: #2b9d9f;
        color: #ffffff;
        padding: 16px 32px;
        border-radius: 14px;
        font-weight: 700;
        text-decoration: none;
        font-size: 15px;
        transition: all 0.3s ease;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .banner-btn:hover {
        background: #227f81;
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(43, 157, 159, 0.4);
    }

    /* Application Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(4px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .modal-backdrop.open {
        display: flex;
    }
    .modal-box {
        background: #ffffff;
        border-radius: 24px;
        width: 100%;
        max-width: 650px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        position: relative;
        animation: modalFadeIn 0.25s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modal-header {
        padding: 28px 32px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-header h3 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .modal-close-btn {
        background: none;
        border: none;
        font-size: 24px;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px;
        line-height: 1;
        transition: color 0.2s;
    }
    .modal-close-btn:hover { color: #0f172a; }
    .modal-body {
        padding: 32px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 14px;
        font-family: inherit;
        color: #0f172a;
        box-sizing: border-box;
        transition: border-color 0.2s;
    }
    .form-control:focus {
        outline: none;
        border-color: #2b9d9f;
        box-shadow: 0 0 0 3px rgba(43, 157, 159, 0.15);
    }
    .modal-footer {
        padding: 24px 32px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        border-bottom-left-radius: 24px;
        border-bottom-right-radius: 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }
</style>

<!-- Hero Section -->
<section class="career-hero">
    <span class="career-badge">Careers at PT Misuba Guna Indonesia</span>
    <h1>Join Our Team & Shape the Future of Industrial Engineering</h1>
    <p>Discover rewarding career opportunities with a trusted leader in industrial equipment, expansion joints, protective linings, and mechanical services.</p>
    
    <div class="career-stats">
        <div class="stat-pill">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>4 Active Positions</span>
        </div>
        <div class="stat-pill">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
            <span>Tangerang & Field Sites</span>
        </div>
        <div class="stat-pill">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Continuous Career Growth</span>
        </div>
    </div>
</section>

<!-- Main Content Area -->
<div class="career-container">
    
    <!-- Benefits Grid -->
    <div class="benefits-grid">
        <div class="benefit-card">
            <div class="benefit-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <h3>Growth & Advancement</h3>
            <p>Work on high-impact industrial projects and advance your skills through continuous technical training.</p>
        </div>
        
        <div class="benefit-card">
            <div class="benefit-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <h3>Safety First Culture</h3>
            <p>We prioritize health, safety, and well-being with rigorous K3 standards and comprehensive safety gear.</p>
        </div>
        
        <div class="benefit-card">
            <div class="benefit-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <h3>Collaborative Team</h3>
            <p>Join an experienced team of engineers, sales experts, and operational specialists who support each other.</p>
        </div>
        
        <div class="benefit-card">
            <div class="benefit-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3>Competitive Rewards</h3>
            <p>Enjoy competitive compensation, medical benefits, performance incentives, and clear career milestones.</p>
        </div>
    </div>

    <!-- Open Positions Header -->
    <div class="positions-section-header">
        <h2>Open Positions</h2>
        <p>We are currently recruiting for the following roles. Click on any position to view details and apply.</p>
    </div>

    <!-- Positions List -->
    <div class="positions-list">
        @foreach($openPositions as $pos)
        <div class="position-card" id="card-{{ $pos['id'] }}">
            <div class="position-header">
                <div class="position-main">
                    <span class="position-badge-tag">{{ $pos['badge'] }}</span>
                    <div class="position-title">{{ $pos['title'] }}</div>
                    <div class="position-meta">
                        <span class="meta-item">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            {{ $pos['department'] }}
                        </span>
                        <span class="meta-item">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $pos['location'] }}
                        </span>
                        <span class="meta-item">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ $pos['type'] }} ({{ $pos['experience'] }})
                        </span>
                    </div>
                </div>
                <div class="position-actions">
                    <button type="button" class="btn-toggle-detail" onclick="toggleDetails('{{ $pos['id'] }}')">
                        <span id="btn-text-{{ $pos['id'] }}">View Details</span>
                        <svg id="arrow-{{ $pos['id'] }}" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="transition: transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <button type="button" class="btn-apply-now" onclick="openApplyModal('{{ $pos['title'] }}')">
                        <span>Apply Now</span>
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
            
            <div class="position-details" id="details-{{ $pos['id'] }}">
                <p style="font-size:15px; color:#475569; margin-top:20px; line-height:1.7;">{{ $pos['summary'] }}</p>
                <div class="details-grid">
                    <div class="detail-box">
                        <h4>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            Key Responsibilities
                        </h4>
                        <ul class="detail-list">
                            @foreach($pos['responsibilities'] as $resp)
                                <li>{{ $resp }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="detail-box">
                        <h4>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                            Requirements & Qualifications
                        </h4>
                        <ul class="detail-list">
                            @foreach($pos['requirements'] as $req)
                                <li>{{ $req }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Recruitment Process -->
    <div class="process-section">
        <h2>Our Hiring Process</h2>
        <div class="process-steps">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Application</h3>
                <p>Submit your resume and cover letter via our website or direct email.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>HR Screening</h3>
                <p>Initial interview to discuss your experience, background, and expectations.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>Technical Review</h3>
                <p>In-depth session with department managers to evaluate technical skills.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h3>Job Offer</h3>
                <p>Formal proposal and onboarding setup to join PT Misuba Guna Indonesia.</p>
            </div>
        </div>
    </div>

    <!-- General Application Callout -->
    <div class="general-apply-banner">
        <div class="banner-text">
            <h3>Don't see a position that matches your profile?</h3>
            <p>We are always looking for passionate engineers, sales specialists, and operational talent. Send your open application and CV directly to our HR team.</p>
        </div>
        <a href="mailto:marketing@misubaguna.com?subject=Spontaneous%20Application%20-%20PT%20Misuba%20Guna%20Indonesia" class="banner-btn">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Send Spontaneous Application
        </a>
    </div>

</div>

<!-- Application Modal -->
<div class="modal-backdrop" id="applyModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Apply for Position</h3>
            <button type="button" class="modal-close-btn" onclick="closeApplyModal()">&times;</button>
        </div>
        <form id="careerForm" onsubmit="handleFormSubmit(event)">
            <div class="modal-body">
                <div class="form-group">
                    <label for="modalPosition">Position Applied For</label>
                    <input type="text" id="modalPosition" class="form-control" readonly style="background:#f1f5f9; font-weight:700;">
                </div>
                <div class="form-group">
                    <label for="fullName">Full Name *</label>
                    <input type="text" id="fullName" class="form-control" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label for="emailAddr">Email Address *</label>
                    <input type="email" id="emailAddr" class="form-control" placeholder="name@example.com" required>
                </div>
                <div class="form-group">
                    <label for="phoneNum">Phone / WhatsApp Number *</label>
                    <input type="tel" id="phoneNum" class="form-control" placeholder="+62 812-3456-7890" required>
                </div>
                <div class="form-group">
                    <label for="cvLink">Link to CV / Resume / LinkedIn Profile *</label>
                    <input type="url" id="cvLink" class="form-control" placeholder="https://drive.google.com/... or https://linkedin.com/in/..." required>
                </div>
                <div class="form-group">
                    <label for="coverLetter">Cover Letter / Additional Information</label>
                    <textarea id="coverLetter" class="form-control" rows="4" placeholder="Briefly describe your relevant experience and why you are interested in joining PT Misuba Guna Indonesia..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-toggle-detail" onclick="closeApplyModal()">Cancel</button>
                <button type="submit" class="btn-apply-now">
                    <span>Send Application via Email</span>
                    <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDetails(id) {
        var detailsEl = document.getElementById('details-' + id);
        var btnText = document.getElementById('btn-text-' + id);
        var arrow = document.getElementById('arrow-' + id);

        if (detailsEl.classList.contains('open')) {
            detailsEl.classList.remove('open');
            btnText.textContent = 'View Details';
            arrow.style.transform = 'rotate(0deg)';
        } else {
            detailsEl.classList.add('open');
            btnText.textContent = 'Hide Details';
            arrow.style.transform = 'rotate(180deg)';
        }
    }

    function openApplyModal(title) {
        document.getElementById('modalPosition').value = title;
        document.getElementById('applyModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeApplyModal() {
        document.getElementById('applyModal').classList.remove('open');
        document.body.style.overflow = '';
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        var position = document.getElementById('modalPosition').value;
        var name = document.getElementById('fullName').value;
        var email = document.getElementById('emailAddr').value;
        var phone = document.getElementById('phoneNum').value;
        var cv = document.getElementById('cvLink').value;
        var message = document.getElementById('coverLetter').value;

        var subject = encodeURIComponent("Job Application: " + position + " - " + name);
        var body = encodeURIComponent(
            "Dear HR Department PT Misuba Guna Indonesia,\n\n" +
            "I would like to apply for the position of " + position + ".\n\n" +
            "Candidate Details:\n" +
            "- Full Name: " + name + "\n" +
            "- Email: " + email + "\n" +
            "- Phone/WhatsApp: " + phone + "\n" +
            "- CV/Resume Link: " + cv + "\n\n" +
            "Cover Letter / Brief Summary:\n" + message + "\n\n" +
            "Best regards,\n" + name
        );

        window.location.href = "mailto:marketing@misubaguna.com?subject=" + subject + "&body=" + body;
        closeApplyModal();
    }
</script>
@endsection
