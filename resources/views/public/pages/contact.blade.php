@extends('public.layout')

@section('content')
<style>
    /* Contact Hero */
    .contact-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: white;
        padding: 80px 24px 120px;
        text-align: center;
        position: relative;
        overflow: hidden;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .contact-hero::before {
        content: '';
        position: absolute;
        top: -50%; left: -50%; width: 200%; height: 200%;
        background: radial-gradient(circle, rgba(43,157,159,0.18) 0%, rgba(15,23,42,0) 70%);
        pointer-events: none;
    }
    .contact-badge {
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
        margin-bottom: 16px;
        position: relative;
        z-index: 2;
    }
    .contact-hero h1 {
        font-size: 2.75rem;
        font-weight: 800;
        margin-bottom: 14px;
        color: #ffffff;
        letter-spacing: -0.02em;
        position: relative;
        z-index: 2;
    }
    .contact-hero p {
        font-size: 1.05rem;
        color: #94a3b8;
        max-width: 650px;
        margin: 0 auto;
        line-height: 1.6;
        position: relative;
        z-index: 2;
    }

    /* Main Grid Wrapper */
    .contact-wrapper {
        max-width: 1280px;
        margin: -65px auto 60px;
        padding: 0 24px;
        position: relative;
        z-index: 10;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 1fr 1.15fr;
        gap: 32px;
        margin-bottom: 48px;
    }

    /* Contact Cards Column */
    .contact-cards-col {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .c-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 24px 28px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.04);
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: flex-start;
        gap: 18px;
        transition: all 0.25s ease;
    }
    .c-card:hover {
        border-color: #2b9d9f;
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.08);
    }
    .c-card-icon {
        width: 48px;
        height: 48px;
        background: #f0fdfa;
        color: #2b9d9f;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .c-card:hover .c-card-icon {
        background: #2b9d9f;
        color: #ffffff;
    }
    .c-card-icon svg {
        width: 24px;
        height: 24px;
    }
    .c-card-content h3 {
        font-size: 1.05rem;
        font-weight: 700;
        color: #0f172a;
        margin: 0 0 6px 0;
    }
    .c-card-content p, .c-card-content a {
        font-size: 0.9rem;
        color: #475569;
        line-height: 1.5;
        text-decoration: none;
    }
    .c-card-content a:hover {
        color: #e67e22;
    }

    /* Quick WA Highlight Box */
    .wa-direct-box {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 14px;
        padding: 24px 28px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.25);
    }
    .wa-direct-box h4 {
        font-size: 1.1rem;
        font-weight: 800;
        margin: 0 0 4px 0;
    }
    .wa-direct-box p {
        font-size: 0.85rem;
        color: #ecfdf5;
        margin: 0;
    }
    .wa-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ffffff;
        color: #059669;
        font-size: 0.85rem;
        font-weight: 800;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        white-space: nowrap;
        transition: all 0.2s ease;
    }
    .wa-btn:hover {
        background: #f0fdf4;
        transform: translateY(-1px);
    }

    /* Form Column */
    .contact-form-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 36px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }
    .contact-form-card h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 6px;
    }
    .contact-form-card .form-subtitle {
        font-size: 0.875rem;
        color: #64748b;
        margin-bottom: 24px;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-group {
        margin-bottom: 18px;
    }
    .form-group label {
        display: block;
        font-size: 0.825rem;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .form-control {
        width: 100%;
        padding: 12px 14px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #0f172a;
        font-family: inherit;
        background: #f8fafc;
        box-sizing: border-box;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .form-control:focus {
        outline: none;
        border-color: #2b9d9f;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(43, 157, 159, 0.15);
    }
    textarea.form-control {
        resize: vertical;
        min-height: 110px;
    }

    .submit-btn {
        width: 100%;
        padding: 12px 24px;
        background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
        color: #ffffff;
        font-size: 0.9rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(230, 126, 34, 0.25);
    }
    .submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(230, 126, 34, 0.35);
    }

    /* Map Switcher Header & Full Width Map */
    .map-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .map-header h3 {
        font-size: 1.35rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .map-tabs {
        display: flex;
        gap: 8px;
    }
    .map-tab-btn {
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .map-tab-btn.active, .map-tab-btn:hover {
        background: #0f172a;
        color: #ffffff;
        border-color: #0f172a;
    }

    .map-container-box {
        width: 100%;
        height: 460px;
        background: #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        border: 1px solid #cbd5e1;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        position: relative;
    }
    .map-container-box iframe {
        width: 100%;
        height: 100%;
        border: 0;
    }

    @media (max-width: 991px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
        .wa-direct-box {
            flex-direction: column;
            align-items: flex-start;
        }
        .wa-btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<!-- Hero Section -->
<section class="contact-hero">
    <div class="contact-badge">PT Misuba Guna Indonesia</div>
    <h1>Contact Our Team</h1>
    <p>Reach out to our Sales, Procurement, or Finance department for product inquiries, quotations, procurement support, and billing assistance.</p>
</section>

<!-- Main Grid Wrapper -->
<div class="contact-wrapper">
    <div class="contact-grid">
        
        <!-- Contact Cards Column -->
        <div class="contact-cards-col">
            <!-- Direct Phone & WhatsApp -->
            <div class="c-card">
                <div class="c-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                </div>
                <div class="c-card-content">
                    <h3>Phone & Direct WhatsApp</h3>
                    <p>
                        <strong>Phone:</strong> <a href="tel:+622155660700">+62 21 55660700</a><br>
                        <strong>WhatsApp:</strong> <a href="https://wa.me/6281119253388" target="_blank">+62 811-1925-3388</a> / <a href="https://wa.me/628118715671" target="_blank">+62 811-8715-671</a>
                    </p>
                </div>
            </div>

            <!-- Email Address -->
            <div class="c-card">
                <div class="c-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="c-card-content">
                    <h3>Email Support</h3>
                    <p style="line-height: 1.6;">
                        <strong>Sales:</strong> <a href="mailto:sales@misubaguna.com">sales@misubaguna.com</a><br>
                        <strong>Procurement:</strong> <a href="mailto:procurement@misubaguna.com">procurement@misubaguna.com</a><br>
                        <strong>Finance:</strong> <a href="mailto:finance@misubaguna.com">finance@misubaguna.com</a>
                    </p>
                </div>
            </div>

            <!-- Operational & Warehouse -->
            <div class="c-card">
                <div class="c-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="c-card-content">
                    <h3>Operational & Warehouse</h3>
                    <p>Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kec. Kelapa Dua, Tangerang, Banten 15810</p>
                </div>
            </div>

            <!-- Billing Office -->
            <div class="c-card">
                <div class="c-card-icon">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div class="c-card-content">
                    <h3>Billing Office</h3>
                    <p>Gedung STC Senayan Lantai 4 No 89, Jl. Asia Afrika Pintu IX, Gelora Senayan, Tanah Abang, Jakarta Pusat</p>
                </div>
            </div>

            <!-- Quick WhatsApp Action Box -->
            <div class="wa-direct-box">
                <div>
                    <h4>Need Instant Assistance?</h4>
                    <p>Connect directly with our sales team on WhatsApp for fast response.</p>
                </div>
                <a href="https://wa.me/6281119253388?text=Hello%20PT%20Misuba%20Guna%20Indonesia,%20I%20have%20an%20inquiry." target="_blank" class="wa-btn">
                    <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    WhatsApp Support
                </a>
            </div>
        </div>

        <!-- Technical Quote Request Form -->
        <div class="contact-form-card">
            <h2>Request a Quote</h2>
            <div class="form-subtitle">Fill out your inquiry details below and our sales team will get back to you promptly.</div>

            <form action="#" method="POST" onsubmit="event.preventDefault(); handleContactSubmit();">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="c_name">Full Name *</label>
                        <input type="text" id="c_name" class="form-control" placeholder="e.g. Budi Santoso" required>
                    </div>
                    <div class="form-group">
                        <label for="c_company">Company / Organization *</label>
                        <input type="text" id="c_company" class="form-control" placeholder="e.g. PT Industri Nusantara" required>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="c_email">Work Email Address *</label>
                        <input type="email" id="c_email" class="form-control" placeholder="budi@company.co.id" required>
                    </div>
                    <div class="form-group">
                        <label for="c_phone">Phone / WhatsApp *</label>
                        <input type="tel" id="c_phone" class="form-control" placeholder="0812-3456-7890" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="c_category">Product or Service Interest</label>
                    <select id="c_category" class="form-control">
                        <option value="Flexible Joint">Flexible Joint (Expansion Joints, Rubber Hoses, Metal Hoses)</option>
                        <option value="Corrosion Protection">Corrosion Protection (PTFE/Rubber/Ceramic/FRP Linings & Coatings)</option>
                        <option value="Sealing System">Sealing System (Gland Packing, Seals, Gaskets)</option>
                        <option value="Engineering Service">Technical & Field Services (Cooling Tower, Valve, Pump Repair, Metal Spray)</option>
                        <option value="General Inquiry">General Industrial Supply & Inquiry</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="c_message">Operating Specifications & Message *</label>
                    <textarea id="c_message" class="form-control" placeholder="Include line size (DN/NB), operating pressure/temperature, media type, and specific product requirements..." required></textarea>
                </div>

                <button type="submit" class="submit-btn">Submit Inquiry</button>
            </form>
        </div>
    </div>

    <!-- Location Maps Header & Interactive Map Container -->
    <div class="map-header">
        <h3>Our Office & Warehouse Locations</h3>
        <div class="map-tabs">
            <button type="button" class="map-tab-btn active" onclick="switchMap('senayan', this)">Billing (STC Senayan, Jakarta)</button>
            <button type="button" class="map-tab-btn" onclick="switchMap('karawaci', this)">Operational & Warehouse (Karawaci, Tangerang)</button>
        </div>
    </div>

    <div class="map-container-box">
        <iframe id="contactMapIframe" src="https://maps.google.com/maps?q=Gedung+STC+Senayan,+Jakarta&t=&z=15&ie=UTF8&iwloc=&output=embed" allowfullscreen="" loading="lazy"></iframe>
    </div>
</div>

<script>
function switchMap(loc, btn) {
    document.querySelectorAll('.map-tab-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
    var iframe = document.getElementById('contactMapIframe');
    if (loc === 'senayan') {
        iframe.src = 'https://maps.google.com/maps?q=Gedung+STC+Senayan,+Jakarta&t=&z=15&ie=UTF8&iwloc=&output=embed';
    } else {
        iframe.src = 'https://maps.google.com/maps?q=Ruko+Mutiara+Karawaci+Tangerang&t=&z=15&ie=UTF8&iwloc=&output=embed';
    }
}

function handleContactSubmit() {
    var name = document.getElementById('c_name').value;
    var company = document.getElementById('c_company').value;
    var category = document.getElementById('c_category').value;
    var message = document.getElementById('c_message').value;

    alert('Thank you ' + name + '! Your inquiry regarding ' + category + ' has been received. Our sales team will contact you shortly.');

    var text = 'Hello PT Misuba Guna Indonesia, my name is ' + name + ' from ' + company + '. Inquiry regarding ' + category + ': ' + message;
    var waUrl = 'https://wa.me/6281119253388?text=' + encodeURIComponent(text);
    window.open(waUrl, '_blank');
}
</script>
@endsection
