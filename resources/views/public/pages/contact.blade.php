@extends('public.layout')

@section('content')
<style>
    .contact-hero {
        background: linear-gradient(135deg, #111827 0%, #1f2937 100%);
        color: white;
        padding: 100px 20px 120px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .contact-hero::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(43,157,159,0.1) 0%, rgba(17,24,39,0) 70%);
        pointer-events: none;
    }
    .contact-hero h1 {
        font-size: 48px;
        font-weight: 800;
        margin-bottom: 24px;
        background: linear-gradient(to right, #fff, #9ca3af);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .contact-hero p {
        font-size: 18px;
        color: #d1d5db;
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.6;
    }
    
    .contact-section {
        max-width: 1280px;
        margin: -80px auto 80px;
        padding: 0 32px;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 48px;
        position: relative;
        z-index: 10;
    }

    @media (max-width: 991px) {
        .contact-section {
            grid-template-columns: 1fr;
            margin-top: 40px;
        }
        .contact-hero {
            padding: 80px 20px 60px;
        }
    }

    /* Contact Info Cards */
    .contact-info-wrapper {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }
    .contact-card {
        background: #fff;
        border-radius: 16px;
        padding: 32px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.06);
        display: flex;
        align-items: flex-start;
        gap: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f3f4f6;
    }
    .contact-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-color: #e5e7eb;
    }
    .contact-card-icon {
        width: 56px;
        height: 56px;
        background: #f0fdfa;
        color: #2b9d9f;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background 0.3s ease, color 0.3s ease;
    }
    .contact-card:hover .contact-card-icon {
        background: #2b9d9f;
        color: #fff;
    }
    .contact-card-icon svg {
        width: 28px;
        height: 28px;
    }
    .contact-card-content h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 8px;
    }
    .contact-card-content p, .contact-card-content a {
        font-size: 15px;
        color: #6b7280;
        line-height: 1.6;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .contact-card-content a:hover {
        color: #dc2626;
    }

    /* Contact Form */
    .contact-form-card {
        background: #fff;
        border-radius: 24px;
        padding: 48px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        border: 1px solid #f3f4f6;
    }
    .contact-form-card h2 {
        font-size: 28px;
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 32px;
    }
    .form-group {
        margin-bottom: 24px;
    }
    .form-group label {
        display: block;
        font-size: 14px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 8px;
    }
    .form-control {
        width: 100%;
        padding: 16px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 15px;
        color: #1f2937;
        font-family: inherit;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
        background: #f9fafb;
        box-sizing: border-box;
    }
    .form-control:focus {
        outline: none;
        border-color: #2b9d9f;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(43,157,159,0.1);
    }
    textarea.form-control {
        resize: vertical;
        min-height: 140px;
    }
    .submit-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 16px 32px;
        background: linear-gradient(135deg, #2b9d9f 0%, #1a7a7c 100%);
        color: #fff;
        font-size: 16px;
        font-weight: 700;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        font-family: inherit;
    }
    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(43,157,159,0.3);
    }
    .submit-btn:active {
        transform: translateY(0);
    }

    /* Page content rendered from DB */
    .db-content {
        max-width: 800px;
        margin: 0 auto 80px;
        padding: 0 32px;
        color: #4b5563;
        line-height: 1.8;
        font-size: 16px;
    }

    /* Map Section */
    .map-section {
        width: 100%;
        height: 500px;
        background: #e5e7eb;
        position: relative;
    }
    .map-section iframe {
        width: 100%;
        height: 100%;
        border: 0;
        filter: grayscale(20%) contrast(1.1);
    }
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <h1>{{ $page->headline ?? 'Contact Us' }}</h1>
    <p>Have questions about our products or services? Our team is ready to help. Reach out to us through any of the channels below.</p>
</section>

<!-- Main Contact Section -->
<section class="contact-section">
    <!-- Contact Info -->
    <div class="contact-info-wrapper">
        <!-- Phone & WA -->
        <div class="contact-card">
            <div class="contact-card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                </svg>
            </div>
            <div class="contact-card-content">
                <h3>Call Us</h3>
                <p>
                    <a href="tel:+622155660700" style="color: inherit;">+62 21 55660700</a><br>
                    <a href="https://wa.me/628118715671" style="color: inherit;">+62 811-8715-671</a> (WhatsApp)
                </p>
            </div>
        </div>

        <!-- Email -->
        <div class="contact-card">
            <div class="contact-card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="contact-card-content">
                <h3>Email Us</h3>
                <p>
                    <a href="mailto:marketing@misubaguna.com" style="color: inherit;">marketing@misubaguna.com</a><br>
                    <span style="font-size:13px; color:#9ca3af;">We'll reply within 24 hours</span>
                </p>
            </div>
        </div>

        <!-- Operational -->
        <div class="contact-card">
            <div class="contact-card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div class="contact-card-content">
                <h3>Operational & Warehouse</h3>
                <p>Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kecamatan Kelapa Dua, Tangerang, Banten. 15810</p>
            </div>
        </div>

        <!-- Billing -->
        <div class="contact-card">
            <div class="contact-card-icon">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div class="contact-card-content">
                <h3>Billing Address</h3>
                <p>Gedung STC Senayan Lantai 4 No 89, Jalan Asia Afrika Pintu IX, Gelora Senayan, Tanah Abang Jakarta Pusat</p>
            </div>
        </div>
    </div>

    <!-- Contact Form -->
    <div class="contact-form-card">
        <h2>Send us a Message</h2>
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Form submitted successfully!');">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" class="form-control" placeholder="John Doe" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com" required>
            </div>
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" class="form-control" placeholder="How can we help you?" required>
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" class="form-control" placeholder="Write your message here..." required></textarea>
            </div>
            <button type="submit" class="submit-btn">Send Message</button>
        </form>
    </div>
</section>

@if(!empty(trim(strip_tags($page->content_html ?? ''))))
<section class="db-content">
    {!! $page->content_html !!}
</section>
@endif

<!-- Full Width Map -->
<section class="map-section">
    <iframe src="https://maps.google.com/maps?q=Gedung+STC+Senayan,+Jakarta&t=&z=15&ie=UTF8&iwloc=&output=embed" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
</section>

@endsection
