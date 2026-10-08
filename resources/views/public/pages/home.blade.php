@extends('public.layout')

@section('content')
<style>
    

    /* =====================================================
       HERO SLIDER SECTION
    ===================================================== */
    .hero-slider-container {
        position: relative;
        width: 100%;
        height: 70vh;
        min-height: 450px;
        overflow: hidden;
        background-color: #111;
    }
    
    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        visibility: hidden;
        transition: opacity 1s ease-in-out, visibility 1s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .hero-slide.active {
        opacity: 1;
        visibility: visible;
    }

    .hero-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 1;
        transform: scale(1);
        transition: transform 8s linear;
    }

    .hero-slide.active .hero-bg {
        transform: scale(1.1);
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to right, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0.3) 100%);
        z-index: 2;
    }

    .hero-content {
        position: relative;
        z-index: 3;
        max-width: 1280px;
        width: 100%;
        padding: 0 32px;
        color: #fff;
        transform: translateY(30px);
        opacity: 0;
        transition: all 1s ease-out 0.3s;
    }

    .hero-slide.active .hero-content {
        transform: translateY(0);
        opacity: 1;
    }

    .hero-title {
        
        font-size: 56px;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 16px;
        max-width: 800px;
        text-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }

    .hero-subtitle {
        
        font-size: 20px;
        font-weight: 400;
        line-height: 1.6;
        color: #e5e7eb;
        max-width: 600px;
        margin-bottom: 32px;
        text-shadow: 0 2px 10px rgba(0,0,0,0.5);
    }

    .hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        background: #dc2626;
        color: #fff;
        
        font-size: 15px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        padding: 16px 32px;
        border-radius: 4px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3);
    }

    .hero-btn:hover {
        background: #ef4444;
        transform: translateY(-2px);
        box-shadow: 0 15px 40px rgba(220, 38, 38, 0.4);
    }

    /* Controls */
    .slider-controls {
        position: absolute;
        bottom: 40px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10;
        display: flex;
        gap: 12px;
    }

    .slider-dot {
        width: 48px;
        height: 4px;
        background: rgba(255,255,255,0.3);
        border: none;
        border-radius: 2px;
        cursor: pointer;
        transition: background 0.3s ease;
    }

    .slider-dot.active {
        background: #dc2626;
    }

    .slider-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        backdrop-filter: blur(4px);
        transition: all 0.3s ease;
    }

    .slider-nav:hover {
        background: #dc2626;
        border-color: #dc2626;
    }

    .slider-nav.prev {
        left: 32px;
    }

    .slider-nav.next {
        right: 32px;
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 36px; }
        .hero-subtitle { font-size: 16px; }
        .slider-nav { display: none; }
    }

    /* =====================================================
       PAGE CONTENT SECTION
    ===================================================== */
    .page-content-wrapper {
        max-width: 1280px;
        margin: 0 auto;
        padding: 60px 32px;
    }

    .section-title {
        
        font-size: 32px;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 32px;
        position: relative;
        padding-bottom: 12px;
    }

    .section-title::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 60px;
        height: 4px;
        background: #dc2626;
        border-radius: 2px;
    }

    .product-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 32px;
        margin-bottom: 60px;
    }

    .product-card {
        background: #fff;
        border: 1px solid #f3f4f6;
        border-radius: 12px;
        padding: 24px;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        flex-direction: column;
    }

    .product-card:hover {
        border-color: #e5e7eb;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        transform: translateY(-4px);
    }

    .product-card-title {
        
        font-size: 18px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
        transition: color 0.2s;
    }

    .product-card:hover .product-card-title {
        color: #dc2626;
    }

    .product-card-icon {
        width: 48px;
        height: 48px;
        background: #fef2f2;
        color: #dc2626;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    /* Overlap Guidelines Section */
    .guidelines-wrapper {
        display: flex;
        justify-content: center;
        margin-top: -120px; /* Pulls the box up to overlap the slider more */
        position: relative;
        z-index: 10;
        padding: 0 32px;
    }
    
    .guidelines-box {
        background-color: #2b9d9f; /* Teal color matching screenshot */
        width: 100%;
        max-width: 1000px;
        text-align: center;
        padding: 40px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1); /* Subtle shadow for depth */
        border-radius: 4px;
        min-height: 240px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    
    .guidelines-subtitle {
        
        font-size: 36px;
        font-weight: 600;
        color: #ffffff;
        margin-bottom: 8px;
        letter-spacing: 1px;
        transform: rotate(-2deg);
        display: inline-block;
    }
    
    .guidelines-title {
        
        font-size: 36px;
        font-weight: 800;
        color: #ffffff;
        margin: 0 auto;
        border-right: 3px solid rgba(255,255,255,0.75);
        white-space: nowrap;
        width: max-content;
        max-width: 100%;
        padding-right: 5px;
        min-height: 45px; /* Prevents box jitter when empty */
        animation: blink-caret 0.75s step-end infinite;
    }

    @keyframes blink-caret {
        from, to { border-color: transparent }
        50% { border-color: rgba(255,255,255,0.75); }
    }

    @media (max-width: 768px) {
        .guidelines-wrapper {
            margin-top: -70px;
            padding: 0 16px;
        }
        .guidelines-box {
            padding: 24px;
        }
        .guidelines-title {
            font-size: 28px;
            min-height: 35px;
        }
        .about-grid {
            grid-template-columns: 1fr;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr;
        }
        .client-locations-grid {
            grid-template-columns: 1fr;
        }
    }

    /* =====================================================
       NEW SECTIONS (ABOUT, PARTNERS, CLIENTS, CTA)
    ===================================================== */
    .home-section {
        padding: 80px 32px;
        max-width: 1280px;
        margin: 0 auto;
    }
    
    .home-section-bg {
        background-color: #f9fafb;
    }

    .section-header {
        text-align: center;
        margin-bottom: 48px;
    }
    
    .section-header h2 {
        font-size: 36px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 16px;
    }

    .section-header p {
        font-size: 18px;
        color: #4b5563;
        max-width: 600px;
        margin: 0 auto;
    }

    /* About Us */
    .about-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 56px;
        align-items: center;
    }
    
    .about-content h3 {
        font-size: 15px;
        text-transform: uppercase;
        color: #2b9d9f;
        font-weight: 800;
        margin-bottom: 8px;
        letter-spacing: 1.5px;
    }
    
    .about-content h2 {
        font-size: 38px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 20px;
        line-height: 1.25;
    }

    .about-content p {
        font-size: 16px;
        color: #4b5563;
        line-height: 1.75;
        margin-bottom: 16px;
    }

    .about-pillars {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin: 24px 0 32px 0;
    }

    .pillar-item {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        padding: 12px 14px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: all 0.25s ease;
    }

    .pillar-item:hover {
        background: #ffffff;
        border-color: #2b9d9f;
        box-shadow: 0 4px 12px rgba(43, 157, 159, 0.1);
        transform: translateY(-2px);
    }

    .pillar-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: #f0fdfa;
        color: #2b9d9f;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .pillar-icon svg {
        width: 20px;
        height: 20px;
    }

    .pillar-item strong {
        display: block;
        font-size: 13px;
        color: #0f172a;
        line-height: 1.2;
    }

    .pillar-item span {
        display: block;
        font-size: 11px;
        color: #64748b;
        margin-top: 2px;
    }

    .about-action-btns {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .about-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        background-color: #111827;
        color: #fff;
        font-weight: 600;
        font-size: 14px;
        border-radius: 8px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .about-btn:hover {
        background-color: #2b9d9f;
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(43, 157, 159, 0.25);
    }

    .about-btn-secondary {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 24px;
        background-color: #ffffff;
        color: #1e293b;
        border: 1.5px solid #cbd5e1;
        font-weight: 600;
        font-size: 14px;
        border-radius: 8px;
        transition: all 0.3s;
        text-decoration: none;
    }

    .about-btn-secondary:hover {
        background-color: #f8fafc;
        border-color: #2b9d9f;
        color: #2b9d9f;
        transform: translateY(-2px);
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .stat-box {
        background: #fff;
        padding: 28px 20px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        text-align: center;
        border: 1px solid #f3f4f6;
        transition: all 0.3s ease;
    }

    .stat-box:hover {
        border-color: #cbd5e1;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        transform: translateY(-3px);
    }

    .stat-number {
        font-size: 42px;
        font-weight: 800;
        color: #2b9d9f;
        margin-bottom: 6px;
        line-height: 1;
    }

    .stat-label {
        font-size: 14px;
        font-weight: 600;
        color: #4b5563;
        line-height: 1.3;
    }

    @media (max-width: 991px) {
        .about-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }
    }
    @media (max-width: 640px) {
        .about-pillars {
            grid-template-columns: 1fr;
        }
        .stats-grid {
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
    }

    /* Logos Grid */
    .logo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 32px;
        align-items: center;
        justify-items: center;
    }

    .logo-item {
        opacity: 0.6;
        transition: opacity 0.3s, transform 0.3s;
        filter: grayscale(100%);
    }

    .logo-item:hover {
        opacity: 1;
        filter: grayscale(0%);
        transform: scale(1.05);
    }
    
    .logo-item img {
        max-width: 120px;
        height: auto;
        display: block;
    }

    .client-locations-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-top: 48px;
    }

    .client-item {
        padding: 8px 12px;
        background: transparent;
        font-size: 13px;
        font-weight: 500;
        color: #4b5563;
        display: flex;
        align-items: center;
    }
    
    .client-item::before {
        content: "";
        display: inline-block;
        width: 6px;
        height: 6px;
        background-color: #2b9d9f;
        border-radius: 50%;
        margin-right: 8px;
        flex-shrink: 0;
    }

    /* Map with Glowing Pins */
    .map-container {
        position: relative;
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .indonesia-map {
        width: 100%;
        height: auto;
        display: block;
        opacity: 0.9;
    }
    
    .map-pin {
        position: absolute;
        width: 10px;
        height: 10px;
        background-color: #2b9d9f;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        box-shadow: 0 0 10px #2b9d9f;
        cursor: pointer;
        z-index: 5;
    }
    
    .pin-tooltip {
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(-10px);
        background: #111827;
        color: #fff;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.2s;
        pointer-events: none;
        z-index: 10;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    
    .pin-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: #111827 transparent transparent transparent;
    }
    
    .map-pin:hover {
        z-index: 20;
    }
    
    .map-pin:hover .pin-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(-5px);
    }
    
    .map-pin::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100%;
        height: 100%;
        background-color: rgba(43, 157, 159, 0.8);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        animation: ripple 2s infinite ease-in-out;
    }
    
    /* Random animation delays for a more organic feel */
    .map-pin:nth-child(even)::after { animation-delay: 0.5s; }
    .map-pin:nth-child(3n)::after { animation-delay: 1s; }
    .map-pin:nth-child(5n)::after { animation-delay: 1.5s; }
    
    @keyframes ripple {
        0% { transform: translate(-50%, -50%) scale(1); opacity: 1; }
        100% { transform: translate(-50%, -50%) scale(4); opacity: 0; }
    }

    /* CTA Section */
    .cta-fullwidth {
        background-color: #2b9d9f;
        padding: 80px 32px;
        text-align: center;
        color: #fff;
    }
    
    .cta-content {
        max-width: 800px;
        margin: 0 auto;
    }

    .cta-content h2 {
        font-size: 42px;
        font-weight: 800;
        margin-bottom: 16px;
    }

    .cta-content p {
        font-size: 20px;
        opacity: 0.9;
        margin-bottom: 32px;
    }

    .cta-btn {
        display: inline-block;
        padding: 16px 40px;
        background-color: #fff;
        color: #2b9d9f;
        font-weight: 700;
        font-size: 18px;
        border-radius: 8px;
        transition: all 0.3s;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .cta-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    
    /* Marquee for logos */
    .marquee-container {
        width: 100%;
        overflow: hidden;
        position: relative;
        padding: 40px 0;
        background: #fff;
    }
    .marquee-container::before,
    .marquee-container::after {
        content: "";
        position: absolute;
        top: 0;
        bottom: 0;
        width: 150px;
        z-index: 2;
        pointer-events: none;
    }
    .marquee-container::before {
        left: 0;
        background: linear-gradient(to right, #fff 0%, rgba(255,255,255,0) 100%);
    }
    .marquee-container::after {
        right: 0;
        background: linear-gradient(to left, #fff 0%, rgba(255,255,255,0) 100%);
    }
    .marquee-track {
        display: flex;
        align-items: center;
        width: max-content;
        animation: marquee-scroll 40s linear infinite;
        gap: 60px;
        padding-left: 60px;
    }
    .marquee-track:hover {
        animation-play-state: paused;
    }
    @keyframes marquee-scroll {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); }
    }
    .marquee-track img {
        height: 100px;
        width: auto;
        max-width: 250px;
        object-fit: contain;
        /* Initial states, overridden by JS */
        filter: grayscale(100%);
        opacity: 0.5;
        will-change: transform, filter, opacity;
        transition: filter 0.3s ease, opacity 0.3s ease;
    }
    .marquee-track img:hover,
    .marquee-track img.active {
        filter: grayscale(0%) !important;
        opacity: 1 !important;
    }
    
    @media (max-width: 768px) {
        .marquee-container::before,
        .marquee-container::after {
            width: 50px;
        }
        .marquee-track img {
            height: 40px;
        }
    }

    /* Interactive Map Styles */
    .interactive-map-section {
        padding: 0;
        background-color: #f8fafc;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        position: relative;
    }
    /* Interactive Map Layout & Floating Controls */
    .interactive-map-section {
        position: relative;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 60px 0 0 0;
        max-width: 100%;
        overflow-x: hidden;
    }

    .map-section-header {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 32px auto;
        padding: 0 20px;
    }

    .map-section-title {
        font-size: 32px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 8px 0;
        line-height: 1.2;
    }

    .map-section-subtitle {
        font-size: 16px;
        color: #4b5563;
        margin: 0;
        line-height: 1.5;
    }

    .map-container-relative {
        position: relative;
        width: 100%;
    }

    .map-floating-controls {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.92);
        padding: 6px 8px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        backdrop-filter: blur(8px);
    }

    .map-floating-select {
        padding: 7px 12px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        outline: none;
        cursor: pointer;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: border-color 0.2s ease;
    }
    .map-floating-select:hover, .map-floating-select:focus {
        border-color: #2b9d9f;
    }

    .map-floating-btn {
        padding: 7px 12px;
        border-radius: 6px;
        border: 1px solid #d1d5db;
        background: #ffffff;
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        transition: all 0.2s ease;
    }
    .map-floating-btn:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    #interactive-map {
        width: 100%;
        margin: 0;
        border-radius: 0;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        z-index: 1;
    }

    /* Industry Marker Variants & Pulsing Effects */
    .leaflet-custom-marker {
        width: 24px !important;
        height: 24px !important;
        border-radius: 50%;
        border: 2px solid white;
        display: flex !important;
        align-items: center;
        justify-content: center;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .leaflet-custom-marker svg {
        width: 12px;
        height: 12px;
        display: block;
        pointer-events: none;
    }
    .leaflet-custom-marker:hover {
        transform: scale(1.35);
        z-index: 1000 !important;
    }
    
    .leaflet-custom-marker.marker-pulp_paper {
        background-color: #10b981;
        box-shadow: 0 0 10px #10b981, 0 0 20px #10b981;
        animation: pulse-pulp 2s infinite ease-in-out;
    }
    .leaflet-custom-marker.marker-chemical {
        background-color: #06b6d4;
        box-shadow: 0 0 10px #06b6d4, 0 0 20px #06b6d4;
        animation: pulse-chemical 2s infinite ease-in-out;
    }
    .leaflet-custom-marker.marker-energy {
        background-color: #f59e0b;
        box-shadow: 0 0 10px #f59e0b, 0 0 20px #f59e0b;
        animation: pulse-energy 2s infinite ease-in-out;
    }
    .leaflet-custom-marker.marker-manufacturing {
        background-color: #8b5cf6;
        box-shadow: 0 0 10px #8b5cf6, 0 0 20px #8b5cf6;
        animation: pulse-manufacturing 2s infinite ease-in-out;
    }

    .leaflet-custom-marker.marker-agriculture {
        background-color: #84cc16;
        box-shadow: 0 0 10px #84cc16, 0 0 20px #84cc16;
        animation: pulse-agriculture 2s infinite ease-in-out;
    }

    .leaflet-custom-marker.marker-fertilizer {
        background-color: #f43f5e;
        box-shadow: 0 0 10px #f43f5e, 0 0 20px #f43f5e;
        animation: pulse-fertilizer 2s infinite ease-in-out;
    }

    @keyframes pulse-pulp {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.8); }
        70% { box-shadow: 0 0 0 14px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    @keyframes pulse-chemical {
        0% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0.8); }
        70% { box-shadow: 0 0 0 14px rgba(6, 182, 212, 0); }
        100% { box-shadow: 0 0 0 0 rgba(6, 182, 212, 0); }
    }
    @keyframes pulse-energy {
        0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.8); }
        70% { box-shadow: 0 0 0 14px rgba(245, 158, 11, 0); }
        100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }
    @keyframes pulse-agriculture {
        0% { box-shadow: 0 0 0 0 rgba(132, 204, 22, 0.8); }
        70% { box-shadow: 0 0 0 14px rgba(132, 204, 22, 0); }
        100% { box-shadow: 0 0 0 0 rgba(132, 204, 22, 0); }
    }
    @keyframes pulse-fertilizer {
        0% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0.8); }
        70% { box-shadow: 0 0 0 14px rgba(244, 63, 94, 0); }
        100% { box-shadow: 0 0 0 0 rgba(244, 63, 94, 0); }
    }
    @keyframes pulse-manufacturing {
        0% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0.8); }
        70% { box-shadow: 0 0 0 14px rgba(139, 92, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(139, 92, 246, 0); }
    }

    /* Map Legend Overlay */
    .map-legend {
        position: absolute;
        bottom: 24px;
        right: 24px;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        padding: 12px 18px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        backdrop-filter: blur(4px);
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
    }
    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .legend-dot {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .legend-dot svg {
        width: 11px;
        height: 11px;
        display: block;
    }
    .legend-dot.dot-pulp_paper { background-color: #10b981; box-shadow: 0 0 6px #10b981; }
    .legend-dot.dot-chemical { background-color: #06b6d4; box-shadow: 0 0 6px #06b6d4; }
    .legend-dot.dot-energy { background-color: #f59e0b; box-shadow: 0 0 6px #f59e0b; }
    .legend-dot.dot-manufacturing { background-color: #8b5cf6; box-shadow: 0 0 6px #8b5cf6; }
    .legend-dot.dot-agriculture { background-color: #84cc16; box-shadow: 0 0 6px #84cc16; }
    .legend-dot.dot-fertilizer { background-color: #f43f5e; box-shadow: 0 0 6px #f43f5e; }

    @media (max-width: 768px) {
        .map-overlay-text {
            top: 12px;
            left: 12px;
            right: 12px;
            max-width: none;
            padding: 10px 16px;
        }
        .map-floating-controls {
            top: 90px;
            left: 12px;
            right: 12px;
            justify-content: space-between;
        }
        .map-floating-select {
            flex: 1;
            min-width: 100px;
        }
        .map-legend {
            bottom: 12px;
            left: 12px;
            right: 12px;
            justify-content: center;
            padding: 10px;
            gap: 12px;
            font-size: 11px;
        }
    }
</style>

<div class="hero-slider-container">
    <!-- Slide 1 -->
    <div class="hero-slide active">
        <img src="/images/hero/slider1.jpg" alt="Hero Background 1" class="hero-bg" onerror="this.onerror=null; this.src='https://via.placeholder.com/1920x1080?text=Slider+1'">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">{{ $page->headline ?? 'PT Misuba Guna Indonesia' }}</h1>
            <p class="hero-subtitle">{{ $page->excerpt ?? 'Partner with PT Misuba Guna Indonesia for reliable, high-quality technical solutions.' }}</p>
            <a href="/catalog/" class="hero-btn">
                Discover Our Catalog
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Slide 2 -->
    <div class="hero-slide">
        <img src="/images/hero/slider2.jpg" alt="Hero Background 2" class="hero-bg" onerror="this.onerror=null; this.src='https://via.placeholder.com/1920x1080?text=Slider+2'">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2 class="hero-title">Build on Trust, Driving on Excellence</h2>
            <p class="hero-subtitle">We drive business forward with innovation, excellence, and trust across all our product lines.</p>
            <a href="/about-us/" class="hero-btn">
                Learn About Us
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Slide 3 -->
    <div class="hero-slide">
        <img src="/images/hero/slider3.jpg" alt="Hero Background 3" class="hero-bg" onerror="this.onerror=null; this.src='https://via.placeholder.com/1920x1080?text=Slider+3'">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2 class="hero-title">Comprehensive Product Range</h2>
            <p class="hero-subtitle">From Flexible Joints to Sealing Systems, we provide industry-leading solutions for your needs.</p>
            <a href="/project-list/" class="hero-btn">
                View Project List
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Slide 4 -->
    <div class="hero-slide">
        <img src="/images/hero/slider4.jpg" alt="Hero Background 4" class="hero-bg" onerror="this.onerror=null; this.src='https://via.placeholder.com/1920x1080?text=Slider+4'">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2 class="hero-title">Advanced Engineering Toolkit</h2>
            <p class="hero-subtitle">Access our rich library of technical data, calculators, and detailed documentation.</p>
            <a href="{{ route('public.toolkit.index') }}" class="hero-btn">
                Explore Toolkit
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Slide 5 -->
    <div class="hero-slide">
        <img src="/images/hero/slider5.jpg" alt="Hero Background 5" class="hero-bg" onerror="this.onerror=null; this.src='https://via.placeholder.com/1920x1080?text=Slider+5'">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2 class="hero-title">Industry Standard Solutions</h2>
            <p class="hero-subtitle">Serving major infrastructure and industrial projects across Indonesia since our establishment.</p>
            <a href="/project-list/" class="hero-btn">
                Our Projects
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Slide 6 -->
    <div class="hero-slide">
        <img src="/images/hero/slider6.jpg" alt="Hero Background 6" class="hero-bg" onerror="this.onerror=null; this.src='https://via.placeholder.com/1920x1080?text=Slider+6'">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2 class="hero-title">Ready for Your Next Project?</h2>
            <p class="hero-subtitle">Contact our expert team today for consultations, product sizing, and quotes.</p>
            <a href="/contact-us/" class="hero-btn">
                Contact Us
                <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Controls Removed -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    let currentSlide = 0;
    let slideInterval;

    function goToSlide(n) {
        slides[currentSlide].classList.remove('active');
        currentSlide = (n + slides.length) % slides.length;
        slides[currentSlide].classList.add('active');
    }

    function nextSlide() { goToSlide(currentSlide + 1); }
    function prevSlide() { goToSlide(currentSlide - 1); }

    function startSlider() {
        slideInterval = setInterval(nextSlide, 6000);
    }
    function resetSlider() {
        clearInterval(slideInterval);
        startSlider();
    }
    
    startSlider();

    // ==========================================
    // Typewriter Effect for Guidelines
    // ==========================================
    const guidelines = [
        "Prioritize of Quality",
        "On-Time Delivery",
        "Right Product, Right Solution",
        "Exceptional Support"
    ];
    const typingElement = document.getElementById('typing-text');
    let guidelineIndex = 0;
    let charIndex = 0;
    let isDeleting = false;
    
    function type() {
        if (!typingElement) return;
        const currentText = guidelines[guidelineIndex];
        
        if (isDeleting) {
            typingElement.textContent = currentText.substring(0, charIndex - 1);
            charIndex--;
        } else {
            typingElement.textContent = currentText.substring(0, charIndex + 1);
            charIndex++;
        }
        
        // Dynamic typing speed
        let typeSpeed = isDeleting ? 40 : 80;
        
        // If word is completely typed out
        if (!isDeleting && charIndex === currentText.length) {
            typeSpeed = 2500; // Pause to let them read it
            isDeleting = true;
        } 
        // If word is completely deleted
        else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            guidelineIndex = (guidelineIndex + 1) % guidelines.length;
            typeSpeed = 500; // Pause before starting next word
        }
        
        setTimeout(type, typeSpeed);
    }
    
    // Start typing after a short delay
    setTimeout(type, 1000);
});
</script>

<div class="page-content-wrapper">
    <div class="guidelines-wrapper">
        <div class="guidelines-box">
            <div class="guidelines-subtitle">Our Guidelines</div>
            <h2 class="guidelines-title" id="typing-text"></h2>
        </div>
    </div>

    {{-- 
    @if($page->content_html)
        <div class="mb-12">
            {!! $page->content_html !!}
        </div>
    @endif
    --}}
</div> <!-- End page-content-wrapper -->

    <!-- About Us Section -->
    <div class="home-section">
        <div class="about-grid">
            <div class="about-content">
                <h3>About PT Misuba Guna Indonesia</h3>
                <h2>Built on Trust, Driven by Excellence</h2>
                <p>Established in <strong>2020</strong>, PT Misuba Guna Indonesia is an authorized global partner and trusted supplier of industrial mechanical engineering, electrical services, thermal expansion systems, and technical components across Indonesia.</p>
                <p>We deliver high-quality products, competitive pricing, precise execution, and rapid field technical support tailored to the demanding operational needs of <strong>Pulp & Paper, Oleochemicals, Energy & Mining, and Manufacturing</strong> plants nationwide.</p>
                
                <div class="about-pillars">
                    <div class="pillar-item">
                        <div class="pillar-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <div>
                            <strong>Prioritize Quality</strong>
                            <span>Certified global brands & top-grade materials</span>
                        </div>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <strong>On-Time Delivery</strong>
                            <span>Fast response & zero unnecessary downtime</span>
                        </div>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <strong>Right Solution</strong>
                            <span>Custom engineering sizing & technical selection</span>
                        </div>
                    </div>
                    <div class="pillar-item">
                        <div class="pillar-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div>
                            <strong>Exceptional Support</strong>
                            <span>Field engineering assistance & long-term care</span>
                        </div>
                    </div>
                </div>

                <div class="about-action-btns">
                    <a href="/about-us" class="about-btn">Read Full Profile</a>
                    <a href="/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf" target="_blank" class="about-btn-secondary">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Company Profile (PDF)
                    </a>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number" data-target="2020" data-suffix="">0</div>
                    <div class="stat-label">Year Established</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="200" data-suffix="+">0</div>
                    <div class="stat-label">Satisfied Industrial Clients</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="4" data-suffix=" Heavy">0</div>
                    <div class="stat-label">Core Industry Sectors</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="100" data-suffix="%">0</div>
                    <div class="stat-label">Quality & On-Time Commitment</div>
                </div>
            </div>
        </div>
    </div>



    <!-- Our Clients Section -->
    <div class="home-section" style="max-width: 100%; padding-left: 0; padding-right: 0; overflow: hidden;">
        <div class="section-header" style="max-width: 1280px; margin: 0 auto; padding: 0 32px;">
            <h2>Our Clients</h2>
            <p>Our clients trust us to deliver exceptional service and solutions tailored to their needs. Join the growing list of satisfied customers.</p>
        </div>
        <div class="marquee-container mb-12">
            <div class="marquee-track">
                <!-- Group 1 -->
                @for ($i = 1; $i <= 13; $i++)
                    <div class="logo-item"><img src="/images/clients/cl{{ $i }}.png" alt="Client {{ $i }}" onerror="this.onerror=null; this.src='https://via.placeholder.com/150x60?text=Client+{{ $i }}'"></div>
                @endfor
                <!-- Group 2 (Duplicate for seamless loop) -->
                @for ($i = 1; $i <= 13; $i++)
                    <div class="logo-item"><img src="/images/clients/cl{{ $i }}.png" alt="Client {{ $i }}" onerror="this.onerror=null; this.src='https://via.placeholder.com/150x60?text=Client+{{ $i }}'"></div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Where Our Clients Are Section -->
    <div class="home-section-bg" style="display: none;">
        <div class="home-section">
            <div class="section-header">
                <h2>Where Our Clients Are</h2>
            </div>
            
            <div class="map-container">
                <!-- Using the map image from the old website -->
                <img src="https://misubaguna.com/wp-content/uploads/2025/01/Indonesia-Maps-1024x377.png" alt="Indonesia Map" class="indonesia-map">
                
                <!-- Sumatra -->
                <div class="map-pin" style="top: 20%; left: 10%;"><span class="pin-tooltip">PT. Toba Pulp Lestari, Tbk</span></div>
                <div class="map-pin" style="top: 40%; left: 14%;"><span class="pin-tooltip">PT. Padang Raya Cakrawala</span></div>
                <div class="map-pin" style="top: 12%; left: 5%;"><span class="pin-tooltip">PT. Calang Sejati Indah</span></div>
                <div class="map-pin" style="top: 30%; left: 16%;"><span class="pin-tooltip">PT. Sari Dumai Sejati</span></div>
                <div class="map-pin" style="top: 31%; left: 16.5%;"><span class="pin-tooltip">PT. Sari Dumai Oleo</span></div>
                <div class="map-pin" style="top: 32%; left: 16%;"><span class="pin-tooltip">PT. Apical Kao Chemical</span></div>
                <div class="map-pin" style="top: 52%; left: 21%;"><span class="pin-tooltip">PT. Oki Pulp & Paper</span></div>
                <div class="map-pin" style="top: 51.500%; left: 21.500%;"><span class="pin-tooltip">PT. Pupuk Swadaya Purimas</span></div>
                <div class="map-pin" style="top: 35%; left: 18%;"><span class="pin-tooltip">PT. Indah Kiat Pulp & Paper Perawang</span></div>
                <div class="map-pin" style="top: 54%; left: 22%;"><span class="pin-tooltip">PT. Pratama Nusantara Sakti</span></div>
                <div class="map-pin" style="top: 56%; left: 20%;"><span class="pin-tooltip">PT. Priamanaya Energi</span></div>
                <div class="map-pin" style="top: 57%; left: 20.5%;"><span class="pin-tooltip">PT. Dizamatra Powerindo</span></div>
                <div class="map-pin" style="top: 36%; left: 18.5%;"><span class="pin-tooltip">PT. Riau Andalan Pulp and Paper</span></div>
                <div class="map-pin" style="top: 44%; left: 19%;"><span class="pin-tooltip">PT. Lontar Papyrus Pulp & Paper Industry (LPPPI)</span></div>
                
                <!-- Java -->
                <div class="map-pin" style="top: 72%; left: 30.5%;"><span class="pin-tooltip">PT. Arwana Anugerah Keramik. Tbk</span></div>
                <div class="map-pin" style="top: 71%; left: 30%;"><span class="pin-tooltip">PT. Styrindo Mono Indonesia</span></div>
                <div class="map-pin" style="top: 72.5%; left: 31%;"><span class="pin-tooltip">PT. Indah Kiat Pulp & Paper Tbk</span></div>
                <div class="map-pin" style="top: 74%; left: 31.5%;"><span class="pin-tooltip">PT. Cemindo Gemilang Tbk</span></div>
                <div class="map-pin" style="top: 73%; left: 33%;"><span class="pin-tooltip">PT. Pindo Deli Pulp & Paper 1 & 2</span></div>
                <div class="map-pin" style="top: 75%; left: 34%;"><span class="pin-tooltip">PT. Lotus Lingga Pratama</span></div>
                <div class="map-pin" style="top: 76%; left: 38%;"><span class="pin-tooltip">PT. Solusi Bangun Indonesia</span></div>
                <div class="map-pin" style="top: 78%; left: 46%;"><span class="pin-tooltip">PT Pabrik Kertas Tjiwi Kimia Tbk</span></div>
                <div class="map-pin" style="top: 73%; left: 33.5%;"><span class="pin-tooltip">PT Excel Techno Lestari</span></div>
                
                <!-- Kalimantan -->
                <div class="map-pin" style="top: 47%; left: 35%;"><span class="pin-tooltip">PT. Indonesia Chemical Alumina</span></div>
                <div class="map-pin" style="top: 53%; left: 42%;"><span class="pin-tooltip">PT. SKS Listrik Kalimantan</span></div>
                <div class="map-pin" style="top: 48%; left: 48%;"><span class="pin-tooltip">PT. Kutai Refinery Nusantara</span></div>
                <div class="map-pin" style="top: 26%; left: 47%;"><span class="pin-tooltip">PT. Kayan LNG Nusantara</span></div>
                <div class="map-pin" style="top: 44%; left: 48.5%;"><span class="pin-tooltip">PT. Kayan Putra Utama Coal</span></div>
                <div class="map-pin" style="top: 27%; left: 47.5%;"><span class="pin-tooltip">PT. Phoenix Resource International</span></div>
                
                <!-- Sulawesi -->
                <div class="map-pin" style="top: 52%; left: 58%;"><span class="pin-tooltip">PT. Vale Indonesia Tbk</span></div>
            </div>
        </div>
        </div>
    </div>

    <!-- Interactive Map Section -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <div class="home-section interactive-map-section">
        <div class="map-section-header">
            <h2 class="map-section-title">Client Locations</h2>
            <p class="map-section-subtitle">Featured key plant facilities across Indonesia (part of 200+ satisfied clients nationwide).</p>
        </div>

        <div class="map-container-relative">
            <div class="map-floating-controls">
                <select id="map-client-select" class="map-floating-select">
                    <option value="">🎯 Jump to Location...</option>
                </select>

                <select id="map-style-select" class="map-floating-select style-select">
                    <option value="osm">🗺️ Open Color Map</option>
                    <option value="satellite">🛰️ Satellite View</option>
                    <option value="topo">🏔️ Topographic Map</option>
                    <option value="muted">🌫️ Muted Light</option>
                </select>

                <button id="map-reset-btn" class="map-floating-btn" title="Reset Indonesia View">
                    🇮🇩 Reset
                </button>
            </div>

            <div id="interactive-map" style="height: 600px;"></div>

            <div class="map-legend">
                <div class="legend-item"><span class="legend-dot dot-pulp_paper"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span> Pulp & Paper</div>
                <div class="legend-item"><span class="legend-dot dot-chemical"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v7.313a1 1 0 0 1-.118.474L5.14 19.24A1 1 0 0 0 6.04 20.7h11.92a1 1 0 0 0 .9-1.46L14.118 9.787A1 1 0 0 1 14 9.313V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/><line x1="7.5" y1="15" x2="16.5" y2="15"/></svg></span> Chemicals & Oleo</div>
                <div class="legend-item"><span class="legend-dot dot-energy"><svg viewBox="0 0 24 24" fill="white" stroke="none"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg></span> Energy, Mining & Gas</div>
                <div class="legend-item"><span class="legend-dot dot-manufacturing"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20h20"/><path d="M4 20V10l4 2V10l4 2V8l5 3V4h4v16H4z"/></svg></span> Cement & Manufacturing</div>
                <div class="legend-item"><span class="legend-dot dot-agriculture"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V8"/><path d="M12 8c0-3 2-5 5-6 0 3-2 5-5 6z"/><path d="M12 13c0-2-1.5-4-4.500-4.500 0 2.500 1.500 4 4.500 4.500z"/></svg></span> Cane Plantation & Sugar Factory</div>
                <div class="legend-item"><span class="legend-dot dot-fertilizer"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M10 20c5.500-2.500.800-6.400 3-10"/><path d="M9.500 9.400c1.900.700 3 2.200 3.500 4.100"/><path d="M13 10c0-3 2-5 5-6-.500 3.500-2.500 5.500-5 6z"/></svg></span> Fertilizer</div>
            </div>
        </div>
    </div>

    <!-- Contact CTA Section -->
    <div class="cta-fullwidth">
        <div class="cta-content">
            <h2>Ready to Partner with Us?</h2>
            <p>Get started today and experience why businesses trust us as their technical supplier of choice.</p>
            <a href="/contact-us" class="cta-btn">Contact Us Now</a>
        </div>
    </div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Interactive Map Initialization
    if (document.getElementById('interactive-map')) {
        var map = L.map('interactive-map', {scrollWheelZoom: false}).setView([-0.789, 113.921], 5);
        
        // Define tile layers for dynamic map style control
        var tileLayers = {
            'osm': L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                subdomains: 'abc',
                maxZoom: 19
            }),
            'satellite': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri',
                maxZoom: 18
            }),
            'topo': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri',
                maxZoom: 18
            }),
            'muted': L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Light_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri',
                maxZoom: 16
            })
        };

        var currentTileLayer = tileLayers.osm;
        currentTileLayer.addTo(map);

        setTimeout(function() {
            map.invalidateSize();
        }, 300);

        // Map style switcher listener
        var styleSelect = document.getElementById('map-style-select');
        if (styleSelect) {
            styleSelect.addEventListener('change', function(e) {
                var selectedStyle = e.target.value;
                if (tileLayers[selectedStyle]) {
                    map.removeLayer(currentTileLayer);
                    currentTileLayer = tileLayers[selectedStyle];
                    currentTileLayer.addTo(map);
                }
            });
        }

        var industryLabels = {
            'pulp_paper': 'Pulp & Paper',
            'chemical': 'Chemicals & Oleo',
            'energy': 'Energy, Mining & Gas',
            'manufacturing': 'Cement & Manufacturing',
            'agriculture': 'Cane Plantation & Sugar Factory',
            'fertilizer': 'Fertilizer'
        };

        var industryColors = {
            'pulp_paper': '#10b981',
            'chemical': '#06b6d4',
            'energy': '#f59e0b',
            'manufacturing': '#8b5cf6',
            'agriculture': '#84cc16',
            'fertilizer': '#f43f5e'
        };

        var industryIcons = {
            'pulp_paper': '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
            'chemical': '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 2v7.313a1 1 0 0 1-.118.474L5.14 19.24A1 1 0 0 0 6.04 20.7h11.92a1 1 0 0 0 .9-1.46L14.118 9.787A1 1 0 0 1 14 9.313V2"/><line x1="8.5" y1="2" x2="15.5" y2="2"/><line x1="7.5" y1="15" x2="16.5" y2="15"/></svg>',
            'energy': '<svg viewBox="0 0 24 24" fill="white" stroke="none"><polygon points="13,2 3,14 12,14 11,22 21,10 12,10"/></svg>',
            'manufacturing': '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 20h20"/><path d="M4 20V10l4 2V10l4 2V8l5 3V4h4v16H4z"/></svg>',
            'agriculture': '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V8"/><path d="M12 8c0-3 2-5 5-6 0 3-2 5-5 6z"/><path d="M12 13c0-2-1.5-4-4.500-4.500 0 2.500 1.500 4 4.500 4.500z"/></svg>',
            'fertilizer': '<svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 20h10"/><path d="M10 20c5.500-2.500.800-6.400 3-10"/><path d="M9.500 9.400c1.900.700 3 2.200 3.500 4.100"/><path d="M13 10c0-3 2-5 5-6-.500 3.500-2.500 5.500-5 6z"/></svg>'
        };

        var clients = [
            {name: "PT. Toba Pulp Lestari, Tbk", lat: 2.435, lng: 99.155, industry: "pulp_paper"},
            {name: "PT. Padang Raya Cakrawala", lat: -0.998, lng: 100.375, industry: "chemical"},
            {name: "PT. Calang Sejati Indah", lat: 4.635, lng: 95.595, industry: "manufacturing"},
            {name: "PT. Sari Dumai Sejati", lat: 1.670, lng: 101.370, industry: "chemical"},
            {name: "PT. Sari Dumai Oleo", lat: 1.672, lng: 101.378, industry: "chemical"},
            {name: "PT. Apical Kao Chemical", lat: 1.675, lng: 101.382, industry: "chemical"},
            {name: "PT. Oki Pulp & Paper", lat: -2.755, lng: 105.050, industry: "pulp_paper"},
            {name: "PT. Pupuk Swadaya Purimas", lat: -2.740, lng: 105.075, industry: "fertilizer"},
            {name: "PT. Indah Kiat Pulp & Paper Perawang", lat: 0.665, lng: 101.605, industry: "pulp_paper"},
            {name: "PT. Lontar Papyrus Pulp & Paper Industry (LPPPI)", lat: -1.145, lng: 103.115, industry: "pulp_paper"},
            {name: "PT. Pratama Nusantara Sakti", lat: -3.350, lng: 105.150, industry: "agriculture"},
            {name: "PT. Priamanaya Energi", lat: -3.790, lng: 103.530, industry: "energy"},
            {name: "PT. Dizamatra Powerindo", lat: -3.760, lng: 103.560, industry: "energy"},
            {name: "PT. Riau Andalan Pulp and Paper", lat: 0.405, lng: 101.860, industry: "pulp_paper"},
            {name: "PT. Arwana Anugerah Keramik. Tbk", lat: -6.180, lng: 106.360, industry: "manufacturing"},
            {name: "PT. Styrindo Mono Indonesia", lat: -5.990, lng: 106.015, industry: "chemical"},
            {name: "PT. Indah Kiat Pulp & Paper Tbk (Serang)", lat: -6.130, lng: 106.240, industry: "pulp_paper"},
            {name: "PT. Indah Kiat Pulp & Paper Tbk (Tangerang)", lat: -6.246, lng: 106.652, industry: "pulp_paper"},
            {name: "PT. Cemindo Gemilang Tbk", lat: -6.910, lng: 106.255, industry: "manufacturing"},
            {name: "PT. Pindo Deli Pulp & Paper 1 & 2", lat: -6.350, lng: 107.300, industry: "pulp_paper"},
            {name: "PT. Lotus Lingga Pratama", lat: -6.900, lng: 107.600, industry: "manufacturing"},
            {name: "PT. Solusi Bangun Indonesia Tbk (Cilacap)", lat: -7.715, lng: 109.005, industry: "manufacturing"},
            {name: "PT. Solusi Bangun Indonesia Tbk (Tuban)", lat: -6.814, lng: 111.886, industry: "manufacturing"},
            {name: "PT Pabrik Kertas Tjiwi Kimia Tbk", lat: -7.450, lng: 112.450, industry: "pulp_paper"},
            {name: "PT Excel Techno Lestari", lat: -6.300, lng: 107.150, industry: "pulp_paper"},
            {name: "PT. Indonesia Chemical Alumina", lat: -0.050, lng: 110.100, industry: "chemical"},
            {name: "PT. SKS Listrik Kalimantan", lat: -1.250, lng: 113.800, industry: "energy"},
            {name: "PT. Kutai Refinery Nusantara", lat: -1.185, lng: 116.825, industry: "chemical"},
            {name: "PT. Kayan LNG Nusantara", lat: 3.355, lng: 117.560, industry: "energy"},
            {name: "PT. Kayan Putra Utama Coal", lat: 4.100, lng: 117.200, industry: "energy"},
            {name: "PT. Phoenix Resource International", lat: 3.365, lng: 117.555, industry: "pulp_paper"},
            {name: "PT. Tanjungenim Lestari Pulp & Paper (TEL)", lat: -3.600, lng: 103.850, industry: "pulp_paper"},
            {name: "PT. DSSP Power Kendari", lat: -4.051, lng: 122.653, industry: "energy"},
            {name: "PT. Vale Indonesia Tbk", lat: -2.525, lng: 121.345, industry: "energy"}
        ];

        var markersByName = {};

        // Populate client dropdown
        var clientSelect = document.getElementById('map-client-select');
        var sortedClients = clients.slice().sort((a,b) => a.name.localeCompare(b.name));
        
        if (clientSelect) {
            sortedClients.forEach(function(c) {
                var opt = document.createElement('option');
                opt.value = c.name;
                opt.textContent = c.name + ' (' + (industryLabels[c.industry] || 'Client') + ')';
                clientSelect.appendChild(opt);
            });

            clientSelect.addEventListener('change', function(e) {
                var selectedName = e.target.value;
                if (!selectedName) return;
                
                var item = markersByName[selectedName];
                if (item) {
                    map.flyTo([item.lat, item.lng], 9, {
                        duration: 1.5,
                        easeLinearity: 0.25
                    });
                    setTimeout(function() {
                        item.marker.openPopup();
                    }, 1200);
                }
            });
        }

        window.zoomToMarker = function(lat, lng) {
            map.flyTo([lat, lng], 9, { duration: 1.2 });
        };

        clients.forEach(function(client, idx) {
            var iconClass = 'leaflet-custom-marker marker-' + client.industry;
            var svgContent = industryIcons[client.industry] || '';
            var customIcon = L.divIcon({
                className: iconClass,
                html: svgContent,
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            });
            var marker = L.marker([client.lat, client.lng], {icon: customIcon}).addTo(map);

            if (marker._icon) {
                marker._icon.style.animationDelay = (idx % 5 * 0.4) + 's';
            }

            var industryLabel = industryLabels[client.industry] || 'Industrial Partner';
            var industryColor = industryColors[client.industry] || '#0f766e';

            var popupContent = '<div style="padding: 4px; min-width: 180px;">' +
                '<span style="display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; padding: 3px 8px; border-radius: 12px; background: ' + industryColor + '20; color: ' + industryColor + ';">' + industryLabel + '</span>' +
                '<h4 style="margin: 6px 0 10px 0; font-size: 13px; font-weight: 700; color: #111827; line-height: 1.3;">' + client.name + '</h4>' +
                '<button onclick="zoomToMarker(' + client.lat + ', ' + client.lng + ')" style="display: block; width: 100%; text-align: center; padding: 6px 10px; font-size: 11px; font-weight: 600; color: #ffffff; background: ' + industryColor + '; border: none; border-radius: 6px; cursor: pointer; transition: opacity 0.2s;">🔍 Focus Location</button>' +
                '</div>';

            marker.bindPopup(popupContent);

            markersByName[client.name] = {
                client: client,
                lat: client.lat,
                lng: client.lng,
                marker: marker
            };
        });

        // Reset View button handler
        var resetBtn = document.getElementById('map-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', function() {
                map.setView([-0.789, 113.921], 5);
                if (clientSelect) clientSelect.value = '';
            });
        }
    }

    // Animate Counter Numbers on scroll
    const counters = document.querySelectorAll('.stat-number');

    const animateCounters = () => {
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-target');
            const suffix = counter.getAttribute('data-suffix') !== null ? counter.getAttribute('data-suffix') : '+';
            const prefix = counter.getAttribute('data-prefix') || '';
            const duration = 1200; // Total animation duration in ms
            const steps = 40;
            const stepTime = duration / steps;
            let currentStep = 0;

            if (isNaN(target)) {
                counter.innerText = prefix + counter.getAttribute('data-target') + suffix;
                return;
            }

            const timer = setInterval(() => {
                currentStep++;
                const progress = currentStep / steps;
                const currentVal = Math.round(target * progress);
                counter.innerText = prefix + currentVal + suffix;

                if (currentStep >= steps) {
                    counter.innerText = prefix + target + suffix;
                    clearInterval(timer);
                }
            }, stepTime);
        });
    };

    // Intersection Observer to trigger animation when visible
    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    const statsGrid = document.querySelector('.stats-grid');
    if (statsGrid) {
        observer.observe(statsGrid);
    }

    // Marquee Center Zoom Effect
    const marqueeItems = document.querySelectorAll('.marquee-track img');
    if (marqueeItems.length > 0) {
        function updateMarqueeScale() {
            const center = window.innerWidth / 2;
            const maxDist = window.innerWidth / 3;

            marqueeItems.forEach(img => {
                const rect = img.getBoundingClientRect();
                const imgCenter = rect.left + rect.width / 2;
                const distance = Math.abs(center - imgCenter);

                let scale = 1;
                
                if (distance < maxDist) {
                    const ratio = 1 - (distance / maxDist); // 0 at edge, 1 at center
                    scale = 1 + 0.4 * ratio;
                    
                    if (ratio > 0.7) {
                        img.classList.add('active');
                    } else {
                        img.classList.remove('active');
                    }
                } else {
                    img.classList.remove('active');
                }
                
                img.style.transform = `scale(${scale})`;
            });

            requestAnimationFrame(updateMarqueeScale);
        }
        
        // Start animation loop
        requestAnimationFrame(updateMarqueeScale);
    }
});
</script>
@endsection
