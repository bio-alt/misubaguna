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
        grid-template-columns: 1fr 1fr;
        gap: 64px;
        align-items: center;
    }
    
    .about-content h3 {
        font-size: 16px;
        text-transform: uppercase;
        color: #2b9d9f;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: 1px;
    }
    
    .about-content h2 {
        font-size: 42px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 24px;
        line-height: 1.2;
    }

    .about-content p {
        font-size: 18px;
        color: #4b5563;
        line-height: 1.8;
        margin-bottom: 32px;
    }

    .about-btn {
        display: inline-block;
        padding: 16px 32px;
        background-color: #111827;
        color: #fff;
        font-weight: 600;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .about-btn:hover {
        background-color: #2b9d9f;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }

    .stat-box {
        background: #fff;
        padding: 32px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        text-align: center;
        border: 1px solid #f3f4f6;
    }

    .stat-number {
        font-size: 48px;
        font-weight: 800;
        color: #2b9d9f;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 16px;
        font-weight: 600;
        color: #4b5563;
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
    .map-overlay-text {
        position: absolute;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        padding: 16px 32px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        text-align: center;
        pointer-events: none;
        backdrop-filter: blur(4px);
    }
    .map-overlay-title {
        font-size: 24px;
        font-weight: 800;
        color: #1f2937;
        margin: 0 0 4px 0;
    }
    .map-overlay-subtitle {
        font-size: 14px;
        color: #4b5563;
        margin: 0;
    }
    #interactive-map {
        width: 100%;
        margin: 0;
        border-radius: 0;
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-top: 4px solid white;
        border-bottom: 4px solid white;
        z-index: 1;
    }
    .leaflet-custom-marker {
        background-color: #0f766e;
        border-radius: 50%;
        border: 2px solid white;
        box-shadow: 0 0 10px #0f766e, 0 0 20px #0f766e;
        animation: pulse-marker 2s infinite;
    }
    @keyframes pulse-marker {
        0% { box-shadow: 0 0 0 0 rgba(15, 118, 110, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(15, 118, 110, 0); }
        100% { box-shadow: 0 0 0 0 rgba(15, 118, 110, 0); }
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
            <a href="/catalogue/" class="hero-btn">
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
            <h1 class="hero-title">Build on Trust, Driving on Excellence</h1>
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
            <h1 class="hero-title">Comprehensive Product Range</h1>
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
            <h1 class="hero-title">Advanced Engineering Toolkit</h1>
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
            <h1 class="hero-title">Industry Standard Solutions</h1>
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
            <h1 class="hero-title">Ready for Your Next Project?</h1>
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
                <h3>About Us</h3>
                <h2>Who We Are</h2>
                <p>With years of experience in providing industrial solutions, we are committed to delivering the highest quality products and services to our clients. At the heart of our mission is a dedication to reliability, innovation, and exceptional customer satisfaction. Our comprehensive approach ensures that every solution is tailored to meet the unique needs of diverse industries.</p>
                <a href="/about-us" class="about-btn">Read More</a>
            </div>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-number" data-target="15">0</div>
                    <div class="stat-label">Years of Industry Expertise</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="100">0</div>
                    <div class="stat-label">Satisfied Clients</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="500">0</div>
                    <div class="stat-label">Successful Projects Delivered</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" data-target="20">0</div>
                    <div class="stat-label">Global Partners Supporting Us</div>
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
                <div class="map-pin" style="top: 35%; left: 18%;"><span class="pin-tooltip">PT. Indah Kiat Pulp & Paper Perawang</span></div>
                <div class="map-pin" style="top: 54%; left: 22%;"><span class="pin-tooltip">PT. Pratama Nusantara Sakti</span></div>
                <div class="map-pin" style="top: 56%; left: 20%;"><span class="pin-tooltip">PT. Priamanaya Energi</span></div>
                <div class="map-pin" style="top: 57%; left: 20.5%;"><span class="pin-tooltip">PT. Dizamatra Powerindo</span></div>
                <div class="map-pin" style="top: 36%; left: 18.5%;"><span class="pin-tooltip">PT. Riau Andalan Pulp and Paper</span></div>
                
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
        <div class="map-overlay-text">
            <h2 class="map-overlay-title">Client Locations</h2>
            <p class="map-overlay-subtitle">Serving industrial partners across Indonesia.</p>
        </div>
        <div id="interactive-map" style="height: 600px;"></div>
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
        
        // Add CartoDB Positron (Light Mode) tiles
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        var customIcon = L.divIcon({
            className: 'leaflet-custom-marker',
            iconSize: [14, 14],
            iconAnchor: [7, 7]
        });

        var clients = [
            {name: "PT. Toba Pulp Lestari, Tbk", lat: 2.45, lng: 99.15},
            {name: "PT. Padang Raya Cakrawala", lat: -0.95, lng: 100.35},
            {name: "PT. Calang Sejati Indah", lat: 4.63, lng: 95.58},
            {name: "PT. Sari Dumai Sejati", lat: 1.68, lng: 101.45},
            {name: "PT. Sari Dumai Oleo", lat: 1.69, lng: 101.46},
            {name: "PT. Apical Kao Chemical", lat: 1.70, lng: 101.44},
            {name: "PT. Oki Pulp & Paper", lat: -3.05, lng: 105.20},
            {name: "PT. Indah Kiat Pulp & Paper Perawang", lat: 0.67, lng: 101.60},
            {name: "PT. Pratama Nusantara Sakti", lat: -3.20, lng: 105.30},
            {name: "PT. Priamanaya Energi", lat: -3.75, lng: 103.55},
            {name: "PT. Dizamatra Powerindo", lat: -3.76, lng: 103.56},
            {name: "PT. Riau Andalan Pulp and Paper", lat: 0.40, lng: 101.85},
            {name: "PT. Arwana Anugerah Keramik. Tbk", lat: -6.15, lng: 106.35},
            {name: "PT. Styrindo Mono Indonesia", lat: -5.95, lng: 106.00},
            {name: "PT. Indah Kiat Pulp & Paper Tbk", lat: -6.05, lng: 106.25},
            {name: "PT. Cemindo Gemilang Tbk", lat: -6.90, lng: 106.25},
            {name: "PT. Pindo Deli Pulp & Paper 1 & 2", lat: -6.35, lng: 107.30},
            {name: "PT. Lotus Lingga Pratama", lat: -6.90, lng: 107.60},
            {name: "PT. Solusi Bangun Indonesia", lat: -7.70, lng: 109.00},
            {name: "PT Pabrik Kertas Tjiwi Kimia Tbk", lat: -7.45, lng: 112.45},
            {name: "PT Excel Techno Lestari", lat: -6.30, lng: 107.15},
            {name: "PT. Indonesia Chemical Alumina", lat: -0.05, lng: 110.10},
            {name: "PT. SKS Listrik Kalimantan", lat: -1.25, lng: 113.80},
            {name: "PT. Kutai Refinery Nusantara", lat: -1.20, lng: 116.85},
            {name: "PT. Kayan LNG Nusantara", lat: 3.35, lng: 117.60},
            {name: "PT. Kayan Putra Utama Coal", lat: 4.10, lng: 117.20},
            {name: "PT. Phoenix Resource International", lat: 3.36, lng: 117.61},
            {name: "PT. Vale Indonesia Tbk", lat: -2.55, lng: 121.35}
        ];

        clients.forEach(function(client) {
            L.marker([client.lat, client.lng], {icon: customIcon})
                .addTo(map)
                .bindPopup('<b>' + client.name + '</b>');
        });
    }

    // Animate Counter Numbers on scroll
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200; // The lower the slower

    const animateCounters = () => {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText.replace('+', '');
                
                // Lower inc to slow and higher to speed up
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc) + "+";
                    setTimeout(updateCount, 20);
                } else {
                    counter.innerText = target + "+";
                }
            };

            updateCount();
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
