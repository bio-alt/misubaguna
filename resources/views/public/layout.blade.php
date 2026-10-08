<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('public.partials.seo', ['seo' => $seo ?? []])
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <meta name="theme-color" content="#dc2626">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/public.css') }}">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
        }

        /* =====================================================
           MEGA MENU NAVBAR
        ===================================================== */

        /* Reset header styles from public.css */
        header {
            background: #fff !important;
            color: #374151 !important;
            padding: 0 !important;
            position: sticky !important;
            width: 100%;
            top: 0 !important;
            z-index: 1000 !important;
            box-shadow: 0 1px 0 #f3f4f6 !important;
            transition: background 0.3s ease, box-shadow 0.3s ease;
        }
        
        /* Transparent Navbar Variations */
        header.transparent-nav {
            background: transparent !important;
            position: fixed !important;
            box-shadow: none !important;
        }
        
        header.transparent-nav.scrolled {
            background: #fff !important;
            box-shadow: 0 1px 0 #f3f4f6 !important;
        }
        
        .nav-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }
        .nav-logo img {
            height: 52px;
            width: auto;
            object-fit: contain;
        }

        /* Product button and anchor tags alignment */
        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
            height: 100%;
        }
        .nav-links a, #product-btn, #services-btn, #toolkit-btn {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #374151;
            text-decoration: none;
            white-space: nowrap;
            transition: color .15s;
            display: flex;
            align-items: center;
            height: 100%;
            background: none;
            border: none;
            padding: 0;
            margin: 0;
            cursor: pointer;
            outline: none;
        }
        #product-btn, #services-btn, #toolkit-btn { gap: 6px; }
        
        /* Transparent nav text colors */
        header.transparent-nav .nav-links a, 
        header.transparent-nav #product-btn,
        header.transparent-nav #services-btn,
        header.transparent-nav #toolkit-btn,
        header.transparent-nav #hamburger-btn {
            color: #fff;
        }
        
        /* Scrolled transparent nav text colors */
        header.transparent-nav.scrolled .nav-links a, 
        header.transparent-nav.scrolled #product-btn,
        header.transparent-nav.scrolled #services-btn,
        header.transparent-nav.scrolled #toolkit-btn,
        header.transparent-nav.scrolled #hamburger-btn {
            color: #374151;
        }

        .nav-links a:hover, #product-btn:hover, #product-btn.open, #services-btn:hover, #services-btn.open, #toolkit-btn:hover, #toolkit-btn.open { color: #dc2626 !important; }
        #product-btn svg, #services-btn svg, #toolkit-btn svg {
            width: 14px; height: 14px;
            transition: transform .2s;
            flex-shrink: 0;
        }
        #product-btn.open svg, #services-btn.open svg, #toolkit-btn.open svg { transform: rotate(180deg); }

        /* Mega menu panel */
        #mega-menu, #services-mega-menu, #toolkit-mega-menu {
            display: none;
            position: fixed;
            left: 0;
            top: 80px;
            width: 100%;
            background: #fff;
            border-top: 3px solid #dc2626;
            box-shadow: 0 20px 60px rgba(0,0,0,.12);
            z-index: 9999;
        }
        #mega-menu.open, #services-mega-menu.open, #toolkit-mega-menu.open { display: block; }
        .mega-inner {
            max-width: 1440px;
            margin: 0 auto;
            padding: 32px 24px;
            display: grid;
            grid-template-columns: repeat(8, 1fr);
            gap: 16px;
        }
        @media (min-width: 1024px) and (max-width: 1279px) {
            .mega-inner {
                grid-template-columns: repeat(4, 1fr);
                gap: 24px 20px;
                max-width: 1024px;
            }
        }
        .mega-col-title {
            display: block;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .12em;
            color: #9ca3af;
            padding-bottom: 12px;
            margin-bottom: 14px;
            border-bottom: 1px solid #e5e7eb;
        }
        .mega-sub {
            display: block;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #ef4444;
            margin: 14px 0 6px;
        }
        .mega-link {
            display: block;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            text-decoration: none;
            padding: 4px 0;
            transition: color .12s, padding-left .12s;
        }
        .mega-link:hover {
            color: #dc2626;
            padding-left: 5px;
        }

        /* Mobile hamburger */
        #hamburger-btn {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            color: #374151;
        }
        #hamburger-btn svg { width: 26px; height: 26px; }

        /* Mobile drawer */
        #mob-backdrop {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 8000;
        }
        #mob-drawer {
            display: none;
            position: fixed; top: 0; left: 0;
            width: 85vw; max-width: 360px; height: 100%;
            background: #fff;
            box-shadow: 10px 0 40px rgba(0,0,0,.15);
            z-index: 9000;
            overflow-y: auto;
            flex-direction: column;
        }
        #mob-backdrop.open { display: block; }
        #mob-drawer.open   { display: flex; }
        .mob-head {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 20px; border-bottom: 1px solid #f3f4f6; flex-shrink: 0;
        }
        .mob-head img { height: 38px; width: auto; object-fit: contain; }
        .mob-close-btn {
            background: none; border: none; cursor: pointer;
            padding: 4px; color: #6b7280;
        }
        .mob-close-btn svg { width: 22px; height: 22px; }
        .mob-nav { flex: 1; padding: 0 20px; overflow-y: auto; }
        .mob-nav-link {
            display: block; padding: 13px 0;
            font-size: 14px; font-weight: 700;
            color: #1f2937; text-transform: uppercase;
            letter-spacing: .06em; text-decoration: none;
            border-bottom: 1px solid #f3f4f6;
        }
        .mob-nav-link:hover { color: #dc2626; }
        .mob-acc-trigger {
            width: 100%; display: flex; align-items: center;
            justify-content: space-between; padding: 13px 0;
            font-size: 14px; font-weight: 700;
            color: #1f2937; text-transform: uppercase;
            letter-spacing: .06em; background: none; border: none;
            cursor: pointer; border-bottom: 1px solid #f3f4f6;
            font-family: inherit;
        }
        .mob-acc-trigger svg { width: 16px; height: 16px; transition: transform .2s; flex-shrink: 0; }
        .mob-acc-trigger.open svg { transform: rotate(180deg); }
        .mob-acc-body { display: none; padding-bottom: 12px; }
        .mob-acc-body.open { display: block; }
        .mob-cat-btn {
            width: 100%; display: flex; align-items: center;
            justify-content: space-between; padding: 8px 12px;
            font-size: 12px; font-weight: 700; color: #374151;
            text-transform: uppercase; letter-spacing: .08em;
            background: #f9fafb; border: none; cursor: pointer;
            font-family: inherit; border-radius: 6px; margin-top: 8px;
        }
        .mob-cat-btn svg { width: 13px; height: 13px; transition: transform .2s; flex-shrink: 0; }
        .mob-cat-btn.open svg { transform: rotate(90deg); }
        .mob-cat-links { display: none; padding: 6px 12px 4px; }
        .mob-cat-links.open { display: block; }
        .mob-cat-link {
            display: block; font-size: 13px; color: #4b5563;
            text-decoration: none; padding: 5px 0;
        }
        .mob-cat-link:hover { color: #dc2626; }
        .mob-sub-label {
            font-size: 10px; font-weight: 700; color: #ef4444;
            text-transform: uppercase; letter-spacing: .08em;
            display: block; margin: 8px 0 4px;
        }

        @media (max-width: 1023px) {
            .nav-links { display: none !important; }
            #hamburger-btn { display: flex !important; }
        }
    </style>
</head>
<body>

    {{-- ====================================================
         MEGA MENU PANEL
    ==================================================== --}}
    <div id="mega-menu">
        <div class="mega-inner">
            <div>
                <a href="/product-category/palm-oil-kernel-machinery/" style="text-decoration:none;"><span class="mega-col-title">Palm Oil & Kernel</span></a>
                <span class="mega-sub">Extraction (PKE)</span>
                <a class="mega-link" href="/product/palm-kernel-expeller/">Palm Kernel Expeller</a>
                <span class="mega-sub">CPO Dewatering & Clarification</span>
                <a class="mega-link" href="/product/filter-plate-press/" style="font-weight:700;">Filter Plate Press</a>
                <a class="mega-link" href="/product/pressure-leaf-filter/">Pressure Leaf Filter (CFP)</a>
                <span class="mega-sub">Spares & Wear Parts</span>
                <a class="mega-link" href="/product/expeller-screws-wear-parts/">Expeller Spare Parts</a>
                <a class="mega-link" href="/product/filter-leaf-screens/">Filter Leaf Screens & Mesh</a>
            </div>
            <div>
                <a href="/product-category/filtration-clarification/" style="text-decoration:none;"><span class="mega-col-title" style="color:#ef4444; border-bottom: 2px solid #ef4444;">Filtration & Clarification</span></a>
                <span class="mega-sub">Filter Press & Cake Clarification</span>
                <a class="mega-link" href="/product/filter-plate-press/" style="font-weight:700; color:#dc2626;">Filter Plate Press</a>
                <a class="mega-link" href="/product/pressure-leaf-filter/">CFP Seal Plate & Leaf Filter</a>
                <a class="mega-link" href="/product/candle-filter/">CFC Sealed Candle Filter</a>
                <span class="mega-sub">Continuous Self-Cleaning</span>
                <a class="mega-link" href="/product/scraping-self-cleaning-filter/">AF Scraping Filter</a>
                <a class="mega-link" href="/product/automated-backwash-filter/">AR Auto Backwash Filter</a>
                <a class="mega-link" href="/product/modular-integrated-backwash-filter/">MIF Modular Filter Skid</a>
                <span class="mega-sub">Vessels & Cartridge Housings</span>
                <a class="mega-link" href="/product/bag-filter-system/">BT Bag Filter System</a>
                <a class="mega-link" href="/product/cartridge-filter-housing/">CT Cartridge Housing</a>
                <a class="mega-link" href="/product/basket-strainer-filter/">ST Basket Strainer & Filter</a>
                <span class="mega-sub">Separators & Media</span>
                <a class="mega-link" href="/product/centrifugal-solid-liquid-separator/">CS Centrifugal Separator</a>
                <a class="mega-link" href="/product/magnetic-iron-remover/">MS Magnetic Iron Remover</a>
                <a class="mega-link" href="/product/filter-cartridges-bags-consumables/">Filter Cartridges & Bags</a>
            </div>
            <div>
                <a href="/product-category/paper-pulp-machinery/" style="text-decoration:none;"><span class="mega-col-title">Paper & Pulp Machinery</span></a>
                <span class="mega-sub">Stock Preparation</span>
                <a class="mega-link" href="/product/hicon-pulper/">High-Consistency Pulper</a>
                <a class="mega-link" href="/product/disc-refiner/">Double Disc Refiner</a>
                <a class="mega-link" href="/product/screening-cleaning-system/">Screening & Cleaning System</a>
                <span class="mega-sub">Paper & Tissue</span>
                <a class="mega-link" href="/product/paper-machine-line/">Complete Paper Machine</a>
                <a class="mega-link" href="/product/tissue-machine/">Tissue Machine</a>
                <span class="mega-sub">Molded Fiber</span>
                <a class="mega-link" href="/product/molded-fiber-machine/">Molded Fiber Plant</a>
                <span class="mega-sub">Wear Parts & Spares</span>
                <a class="mega-link" href="/product/refiner-discs-screen-baskets/">Refiner Plates & Baskets</a>
            </div>
            <div>
                <a href="/product-category/thermal-systems/" style="text-decoration:none;"><span class="mega-col-title">Thermal Systems</span></a>
                <a class="mega-link" href="/product/cooling-tower/">Cooling Tower</a>
                <a class="mega-link" href="/product/heat-exchanger/">Heat Exchanger</a>
                <a class="mega-link" href="/product/plate-heat-exchanger/">Plate Heat Exchanger</a>
                <a class="mega-link" href="/product/oil-cooler/">Oil Cooler</a>
            </div>
            <div>
                <a href="/product-category/corrosion-protection/" style="text-decoration:none;"><span class="mega-col-title">Corrosion Protection</span></a>
                <span class="mega-sub">Lining</span>
                <a class="mega-link" href="/product/ptfe-lining/">PTFE Lining</a>
                <a class="mega-link" href="/product/rubber-lining/">Rubber Lining</a>
                <a class="mega-link" href="/product/ceramic-lining/">Ceramic Lining</a>
                <a class="mega-link" href="/product/frp-lining/">FRP Lining</a>
                <span class="mega-sub">Coating</span>
                <a class="mega-link" href="/product/ceramic-coating/">Ceramic Coating</a>
                <a class="mega-link" href="/product/ptfe-coating/">PTFE Coating</a>
            </div>
            <div>
                <span class="mega-col-title">Flexible Joint</span>
                <a class="mega-link" href="/product/metal-expansion-joint/">Metal Expansion Joint</a>
                <a class="mega-link" href="/product/rubber-expansion-joint/">Rubber Expansion Joint</a>
                <a class="mega-link" href="/product/ptfe-expansion-joint/">PTFE Expansion Joint</a>
                <a class="mega-link" href="/product/ptfe-lined-rubber-metal-expansion-joint/">PTFE Lined Expansion Joint</a>
                <a class="mega-link" href="/product/rubber-hose/">Rubber Hose</a>
                <a class="mega-link" href="/product/flexible-metal-hose/">Flexible Metal Hose</a>
            </div>
            <div>
                <a href="/product-category/sealing-system/" style="text-decoration:none;"><span class="mega-col-title">Sealing System</span></a>
                <a class="mega-link" href="/product/gland-packing/">Gland Packing</a>
                <a class="mega-link" href="/product/oil-seal/">Oil Seal</a>
                <a class="mega-link" href="/product/o-ring/">O-Ring</a>
                <a class="mega-link" href="/product/gasket-sheet/">Gasket Sheet</a>
                <a class="mega-link" href="/product/mechanical-seal/">Mechanical Seal</a>
            </div>
            <div>
                <a href="/product-category/power-transfer/" style="text-decoration:none;"><span class="mega-col-title">Power Transfer</span></a>
                <a class="mega-link" href="/product/coupling/">Coupling</a>
            </div>
        </div>
    </div>

    {{-- ====================================================
         SERVICES MEGA MENU PANEL
    ==================================================== --}}
    <div id="services-mega-menu">
        <div class="mega-inner" style="grid-template-columns: repeat(4, 1fr);">
            <div>
                <a href="/services/" style="text-decoration:none;"><span class="mega-col-title">Main Services</span></a>
                <a class="mega-link" href="/services/" style="font-weight:700; color:#dc2626;">View All Services →</a>
            </div>
            <div>
                <span class="mega-col-title">Thermal & Cooling</span>
                <a class="mega-link" href="/services/cooling-tower-repair/">Cooling Tower Repair & Refurbishment</a>
                <a class="mega-link" href="/services/heat-exchanger-repair/">Heat Exchanger Service & Repair</a>
            </div>
            <div>
                <span class="mega-col-title">Protective Lining & Coating</span>
                <a class="mega-link" href="/services/thermal-spray-coating/">Thermal Spray Coating (Metal Spray)</a>
                <a class="mega-link" href="/services/rubber-lining/">Rubber Lining</a>
                <a class="mega-link" href="/services/frp-lining-repair/">FRP Lining & Repair</a>
                <a class="mega-link" href="/services/ptfe-lining-bonding/">PTFE Lining & Bonding</a>
            </div>
            <div>
                <span class="mega-col-title">Mechanical & Field Services</span>
                <a class="mega-link" href="/services/valve-repair/">Valve Repair & Overhaul</a>
                <a class="mega-link" href="/services/gearbox-repair/">Gearbox Repair & Rebuilding</a>
                <a class="mega-link" href="/services/pump-repair/">Pump & Rotating Equipment Repair</a>
                <a class="mega-link" href="/services/expansion-joint-installation/">Expansion Joint Installation</a>
                <a class="mega-link" href="/services/mechanical-maintenance/">Mechanical Maintenance & Repair</a>
                <a class="mega-link" href="/services/onsite-inspection/">On-Site Inspection & Troubleshooting</a>
            </div>
        </div>
    </div>

    {{-- ====================================================
         TOOLKIT MEGA MENU PANEL
    ==================================================== --}}
    <div id="toolkit-mega-menu">
        <div class="mega-inner" style="grid-template-columns: repeat(3, 1fr);">
            <div>
                <a href="{{ route('public.toolkit.index') }}" style="text-decoration:none;"><span class="mega-col-title">Engineering Toolkit</span></a>
                <a class="mega-link" href="{{ route('public.toolkit.flange-standards') }}">Flange Standards</a>
                <a class="mega-link" href="{{ route('public.toolkit.calculator') }}">Scientific Calculator</a>
                <a class="mega-link" href="{{ route('public.toolkit.material-specs') }}">Material Specs Library</a>
            </div>
            <div>
                <span class="mega-col-title">Documentation</span>
                <a class="mega-link" href="#">User Manuals</a>
                <a class="mega-link" href="#">Safety Data Sheets</a>
            </div>
            <div>
                <span class="mega-col-title">Software</span>
                <a class="mega-link" href="#">CAD Models</a>
                <a class="mega-link" href="#">Sizing Tools</a>
            </div>
        </div>
    </div>

    {{-- ====================================================
         HEADER
    ==================================================== --}}
    <header class="{{ request()->is('/') ? 'transparent-nav' : '' }}">
        <div class="nav-container">
            <a href="/" class="nav-logo">
                <img src="/images/misuba-logo.png"
                     alt="PT Misuba Guna Indonesia">
            </a>

            <nav class="nav-links">
                <a href="/">Home</a>
                <button id="product-btn" type="button">
                    Products
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <button id="services-btn" type="button">
                    Services
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <button id="toolkit-btn" type="button">
                    Engineering Toolkit
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <a href="/catalog/">Catalog</a>
                <a href="/about-us/">About Us</a>
                <a href="/contact-us/">Contact Us</a>
                <a href="/career/">Career</a>
            </nav>

            <button id="hamburger-btn" type="button" aria-label="Open menu">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
    </header>

    {{-- ====================================================
         MOBILE BACKDROP + DRAWER
    ==================================================== --}}
    <div id="mob-backdrop"></div>
    <div id="mob-drawer">
        <div class="mob-head">
            <img src="/images/misuba-logo.png" alt="Logo">
            <button class="mob-close-btn" id="mob-close" type="button">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <nav class="mob-nav">
            <a class="mob-nav-link" href="/">Home</a>

            <button class="mob-acc-trigger" id="mob-prod-btn" type="button">
                Product
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="mob-acc-body" id="mob-prod-body">

                <button class="mob-cat-btn" data-panel="mc-palmoil" type="button">
                    Palm Oil & Kernel Machinery
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-palmoil">
                    <a class="mob-cat-link" href="/product-category/palm-oil-kernel-machinery/" style="font-weight:700; color:#dc2626;">View All Palm Oil Systems →</a>
                    <span class="mob-sub-label">Extraction (PKE)</span>
                    <a class="mob-cat-link" href="/product/palm-kernel-expeller/">Palm Kernel Expeller</a>
                    <span class="mob-sub-label">CPO Dewatering & Clarification</span>
                    <a class="mob-cat-link" href="/product/filter-plate-press/" style="font-weight:700; color:#dc2626;">Filter Plate Press</a>
                    <a class="mob-cat-link" href="/product/pressure-leaf-filter/">Pressure Leaf Filter (CFP)</a>
                    <span class="mob-sub-label">Spares & Wear Parts</span>
                    <a class="mob-cat-link" href="/product/expeller-screws-wear-parts/">Expeller Spare Parts</a>
                    <a class="mob-cat-link" href="/product/filter-leaf-screens/">Filter Leaf Screens & Mesh</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-filtration" type="button" style="color: #dc2626;">
                    Filtration & Clarification (JCI)
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-filtration">
                    <a class="mob-cat-link" href="/product-category/filtration-clarification/" style="font-weight:700; color:#dc2626;">View All Filtration Systems →</a>
                    <span class="mob-sub-label">Filter Press & Cake Clarification</span>
                    <a class="mob-cat-link" href="/product/filter-plate-press/" style="font-weight:700; color:#dc2626;">Filter Plate Press</a>
                    <a class="mob-cat-link" href="/product/pressure-leaf-filter/">CFP Seal Plate & Leaf Filter</a>
                    <a class="mob-cat-link" href="/product/candle-filter/">CFC Sealed Candle Filter</a>
                    <span class="mob-sub-label">Continuous Self-Cleaning</span>
                    <a class="mob-cat-link" href="/product/scraping-self-cleaning-filter/">AF Scraping Filter</a>
                    <a class="mob-cat-link" href="/product/automated-backwash-filter/">AR Auto Backwash Filter</a>
                    <a class="mob-cat-link" href="/product/modular-integrated-backwash-filter/">MIF Modular Filter Skid</a>
                    <span class="mob-sub-label">Vessels & Cartridge Housings</span>
                    <a class="mob-cat-link" href="/product/bag-filter-system/">BT Bag Filter System</a>
                    <a class="mob-cat-link" href="/product/cartridge-filter-housing/">CT Cartridge Housing</a>
                    <a class="mob-cat-link" href="/product/basket-strainer-filter/">ST Basket Strainer & Filter</a>
                    <span class="mob-sub-label">Separators & Media</span>
                    <a class="mob-cat-link" href="/product/centrifugal-solid-liquid-separator/">CS Centrifugal Separator</a>
                    <a class="mob-cat-link" href="/product/magnetic-iron-remover/">MS Magnetic Iron Remover</a>
                    <a class="mob-cat-link" href="/product/filter-cartridges-bags-consumables/">Filter Cartridges & Bags</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-paper" type="button">
                    Paper & Pulp Machinery
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-paper">
                    <span class="mob-sub-label">Stock Preparation</span>
                    <a class="mob-cat-link" href="/product/hicon-pulper/">High-Consistency Pulper</a>
                    <a class="mob-cat-link" href="/product/disc-refiner/">Double Disc Refiner</a>
                    <a class="mob-cat-link" href="/product/screening-cleaning-system/">Screening & Cleaning System</a>
                    <span class="mob-sub-label">Paper & Tissue</span>
                    <a class="mob-cat-link" href="/product/paper-machine-line/">Complete Paper Machine</a>
                    <a class="mob-cat-link" href="/product/tissue-machine/">Tissue Machine</a>
                    <span class="mob-sub-label">Molded Fiber</span>
                    <a class="mob-cat-link" href="/product/molded-fiber-machine/">Molded Fiber Plant</a>
                    <span class="mob-sub-label">Wear Parts & Spares</span>
                    <a class="mob-cat-link" href="/product/refiner-discs-screen-baskets/">Refiner Plates & Baskets</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-therm" type="button">
                    Thermal Systems
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-therm">
                    <a class="mob-cat-link" href="/product/cooling-tower/">Cooling Tower</a>
                    <a class="mob-cat-link" href="/product/heat-exchanger/">Heat Exchanger</a>
                    <a class="mob-cat-link" href="/product/plate-heat-exchanger/">Plate Heat Exchanger</a>
                    <a class="mob-cat-link" href="/product/oil-cooler/">Oil Cooler</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-corr" type="button">
                    Corrosion Protection
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-corr">
                    <span class="mob-sub-label">Lining</span>
                    <a class="mob-cat-link" href="/product/ptfe-lining/">PTFE Lining</a>
                    <a class="mob-cat-link" href="/product/rubber-lining/">Rubber Lining</a>
                    <a class="mob-cat-link" href="/product/ceramic-lining/">Ceramic Lining</a>
                    <a class="mob-cat-link" href="/product/frp-lining/">FRP Lining</a>
                    <span class="mob-sub-label">Coating</span>
                    <a class="mob-cat-link" href="/product/ceramic-coating/">Ceramic Coating</a>
                    <a class="mob-cat-link" href="/product/ptfe-coating/">PTFE Coating</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-flex" type="button">
                    Flexible Joint
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-flex">
                    <a class="mob-cat-link" href="/product/metal-expansion-joint/">Metal Expansion Joint</a>
                    <a class="mob-cat-link" href="/product/rubber-expansion-joint/">Rubber Expansion Joint</a>
                    <a class="mob-cat-link" href="/product/ptfe-expansion-joint/">PTFE Expansion Joint</a>
                    <a class="mob-cat-link" href="/product/ptfe-lined-rubber-metal-expansion-joint/">PTFE Lined Expansion Joint</a>
                    <a class="mob-cat-link" href="/product/rubber-hose/">Rubber Hose</a>
                    <a class="mob-cat-link" href="/product/flexible-metal-hose/">Flexible Metal Hose</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-seal" type="button">
                    Sealing System
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-seal">
                    <a class="mob-cat-link" href="/product-category/sealing-system/" style="font-weight:700; color:#dc2626;">View All Sealing Systems →</a>
                    <a class="mob-cat-link" href="/product/gland-packing/">Gland Packing</a>
                    <a class="mob-cat-link" href="/product/oil-seal/">Oil Seal</a>
                    <a class="mob-cat-link" href="/product/o-ring/">O-Ring</a>
                    <a class="mob-cat-link" href="/product/gasket-sheet/">Gasket Sheet</a>
                    <a class="mob-cat-link" href="/product/mechanical-seal/">Mechanical Seal</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-pow" type="button">
                    Power Transfer
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-pow">
                    <a class="mob-cat-link" href="/product-category/power-transfer/" style="font-weight:700; color:#dc2626;">View All Power Transfer →</a>
                    <a class="mob-cat-link" href="/product/coupling/">Coupling</a>
                </div>

            </div>

            <button class="mob-acc-trigger" id="mob-serv-btn" type="button">
                Services
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="mob-acc-body" id="mob-serv-body">
                <a class="mob-cat-link" href="/services/" style="font-weight:700; color:#dc2626; padding-left:12px; border-bottom:1px dashed #e5e7eb; margin-bottom:8px;">View All Services →</a>
                
                <button class="mob-cat-btn" data-panel="mc-serv-therm" type="button">
                    Thermal & Cooling Services
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-serv-therm">
                    <a class="mob-cat-link" href="/services/cooling-tower-repair/">Cooling Tower Repair & Refurbishment</a>
                    <a class="mob-cat-link" href="/services/heat-exchanger-repair/">Heat Exchanger Service & Repair</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-serv-coat" type="button">
                    Protective Lining & Coating
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-serv-coat">
                    <a class="mob-cat-link" href="/services/thermal-spray-coating/">Thermal Spray Coating</a>
                    <a class="mob-cat-link" href="/services/rubber-lining/">Rubber Lining</a>
                    <a class="mob-cat-link" href="/services/frp-lining-repair/">FRP Lining & Repair</a>
                    <a class="mob-cat-link" href="/services/ptfe-lining-bonding/">PTFE Lining & Bonding</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-serv-mech" type="button">
                    Mechanical & Field Services
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-serv-mech">
                    <a class="mob-cat-link" href="/services/valve-repair/">Valve Repair & Overhaul</a>
                    <a class="mob-cat-link" href="/services/gearbox-repair/">Gearbox Repair & Rebuilding</a>
                    <a class="mob-cat-link" href="/services/pump-repair/">Pump & Rotating Equipment Repair</a>
                    <a class="mob-cat-link" href="/services/expansion-joint-installation/">Expansion Joint Installation</a>
                    <a class="mob-cat-link" href="/services/mechanical-maintenance/">Mechanical Maintenance & Repair</a>
                    <a class="mob-cat-link" href="/services/onsite-inspection/">On-Site Inspection & Troubleshooting</a>
                </div>
            </div>

            <button class="mob-acc-trigger" id="mob-toolkit-btn" type="button">
                Engineering Toolkit
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div class="mob-acc-body" id="mob-toolkit-body">
                <button class="mob-cat-btn" data-panel="mc-engdata" type="button">
                    Engineering Data & Standards
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-engdata">
                    <a class="mob-cat-link" href="{{ route('public.toolkit.index') }}">Toolkit Home</a>
                    <a class="mob-cat-link" href="{{ route('public.toolkit.flange-standards') }}">Flange Standards</a>
                    <a class="mob-cat-link" href="{{ route('public.toolkit.calculator') }}">Scientific Calculator</a>
                    <a class="mob-cat-link" href="{{ route('public.toolkit.material-specs') }}">Material Specs Library</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-docs" type="button">
                    Documentation
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-docs">
                    <a class="mob-cat-link" href="#">User Manuals</a>
                    <a class="mob-cat-link" href="#">Safety Data Sheets</a>
                </div>

                <button class="mob-cat-btn" data-panel="mc-soft" type="button">
                    Software
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="mob-cat-links" id="mc-soft">
                    <a class="mob-cat-link" href="#">CAD Models</a>
                    <a class="mob-cat-link" href="#">Sizing Tools</a>
                </div>
            </div>

            <a class="mob-nav-link" href="/about-us/">About Us</a>
            <a class="mob-nav-link" href="/contact-us/">Contact Us</a>
            <a class="mob-nav-link" href="/career/">Career</a>
            <a class="mob-nav-link" href="/catalog/" style="border-bottom:none;">Catalog</a>
        </nav>
    </div>

    <main>
        @yield('content')
    </main>

    <style>
        .site-footer {
            background-color: #111827;
            color: #d1d5db;
            padding: 64px 32px 32px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            text-align: left;
        }
        .footer-grid {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 40px;
            margin-bottom: 48px;
            align-items: start;
        }
        .footer-col h3 {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 24px;
            position: relative;
            padding-bottom: 12px;
        }
        .footer-col h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 40px;
            height: 3px;
            background-color: #2b9d9f;
        }
        .footer-col p {
            line-height: 1.8;
            margin: 0;
            font-size: 15px;
        }
        .footer-col a {
            color: #2b9d9f;
            text-decoration: none;
            transition: color 0.3s;
        }
        .footer-col a:hover {
            color: #ffffff;
        }
        .footer-contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 16px;
        }
        .footer-contact-item svg {
            width: 20px;
            height: 20px;
            color: #2b9d9f;
            margin-right: 12px;
            flex-shrink: 0;
            margin-top: 4px;
        }
        .footer-bottom {
            max-width: 1280px;
            margin: 0 auto;
            border-top: 1px solid #374151;
            padding-top: 32px;
            text-align: center;
            font-size: 14px;
        }
        @media (max-width: 1100px) {
            .footer-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 32px;
            }
        }
    </style>
    <footer class="site-footer">
        <div class="footer-grid">
            <!-- Operational -->
            <div class="footer-col">
                <h3>Operational & Warehouse</h3>
                <div class="footer-contact-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <p>Ruko Mutiara Karawaci Blok C29, Bencongan Indah, Kecamatan Kelapa Dua, Tangerang, Banten. 15810</p>
                </div>
            </div>

            <!-- Billing -->
            <div class="footer-col">
                <h3>Billing Address</h3>
                <div class="footer-contact-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <p>Gedung STC Senayan Lantai 4 No 89, Jalan Asia Afrika Pintu IX, Gelora Senayan, Tanah Abang Jakarta Pusat</p>
                </div>
            </div>

            <!-- Contact Us -->
            <div class="footer-col">
                <h3>Contact Us</h3>
                <div class="footer-contact-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <p>+62 21 55660700<br>+62 811-8715-671</p>
                </div>
                <div class="footer-contact-item">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <p style="font-size: 13px; line-height: 1.6;">
                        <strong>Sales:</strong> <a href="mailto:sales@misubaguna.com">sales@misubaguna.com</a><br>
                        <strong>Procurement:</strong> <a href="mailto:procurement@misubaguna.com">procurement@misubaguna.com</a><br>
                        <strong>Finance:</strong> <a href="mailto:finance@misubaguna.com">finance@misubaguna.com</a>
                    </p>
                </div>
            </div>

            <!-- Map Location -->
            <div class="footer-col">
                <h3>Our Location</h3>
                <div class="footer-map" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.3);">
                    <iframe src="https://maps.google.com/maps?q=Gedung+STC+Senayan,+Jakarta&t=&z=15&ie=UTF8&iwloc=&output=embed" width="100%" height="200" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <p style="margin-bottom: 6px;">&copy; {{ date('Y') }} PT Misuba Guna Indonesia. All rights reserved.</p>
            <p style="font-size: 13px; color: #9ca3af; margin: 0;">
                <a href="{{ route('public.home') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">Home</a> &bull;
                <a href="{{ route('public.about') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">About Us</a> &bull;
                <a href="{{ route('public.catalog') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">Products</a> &bull;
                <a href="{{ route('public.services.index') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">Services</a> &bull;
                <a href="{{ route('public.toolkit.index') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">Engineering Toolkit</a> &bull;
                <a href="{{ route('public.contact') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">Contact</a> &bull;
                <a href="{{ route('public.sitemap') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">Sitemap (XML)</a> &bull;
                <a href="{{ route('public.llms') }}" style="color: #9ca3af; text-decoration: none; margin: 0 8px;">AI / LLM Reference</a>
            </p>
        </div>
    </footer>

    <script>
    (function() {
        function setupMegaMenu(btnId, menuId) {
            var btn  = document.getElementById(btnId);
            var menu = document.getElementById(menuId);
            if (!btn || !menu) return;
            var timer = null;
            var open  = false;

            function show() {
                clearTimeout(timer);
                document.querySelectorAll('#mega-menu, #services-mega-menu, #toolkit-mega-menu').forEach(function(m) {
                    if (m.id !== menuId) m.classList.remove('open');
                });
                document.querySelectorAll('#product-btn, #services-btn, #toolkit-btn').forEach(function(b) {
                    if (b.id !== btnId) b.classList.remove('open');
                });
                if (open) return;
                open = true;
                menu.classList.add('open');
                btn.classList.add('open');
            }
            function hide() {
                timer = setTimeout(function() {
                    open = false;
                    menu.classList.remove('open');
                    btn.classList.remove('open');
                }, 150);
            }
            function toggle(e) {
                e.preventDefault();
                e.stopPropagation();
                if (open) { clearTimeout(timer); open = false; menu.classList.remove('open'); btn.classList.remove('open'); }
                else { show(); }
            }

            btn.addEventListener('mouseenter', show);
            btn.addEventListener('mouseleave', hide);
            btn.addEventListener('click', toggle);
            menu.addEventListener('mouseenter', show);
            menu.addEventListener('mouseleave', hide);

            document.addEventListener('click', function(e) {
                if (open && !menu.contains(e.target) && !btn.contains(e.target)) {
                    clearTimeout(timer);
                    open = false;
                    menu.classList.remove('open');
                    btn.classList.remove('open');
                }
            });
        }
        setupMegaMenu('product-btn', 'mega-menu');
        setupMegaMenu('services-btn', 'services-mega-menu');
        setupMegaMenu('toolkit-btn', 'toolkit-mega-menu');

        /* Mobile drawer */
        var backdrop = document.getElementById('mob-backdrop');
        var drawer   = document.getElementById('mob-drawer');
        var mobClose = document.getElementById('mob-close');
        var ham      = document.getElementById('hamburger-btn');
        var mobProd  = document.getElementById('mob-prod-btn');
        var mobBody  = document.getElementById('mob-prod-body');
        var mobServBtn  = document.getElementById('mob-serv-btn');
        var mobServBody = document.getElementById('mob-serv-body');
        var mobToolkitBtn  = document.getElementById('mob-toolkit-btn');
        var mobToolkitBody = document.getElementById('mob-toolkit-body');

        ham.addEventListener('click', function() {
            backdrop.classList.add('open');
            drawer.classList.add('open');
            document.body.style.overflow = 'hidden';
        });
        function closeDrawer() {
            backdrop.classList.remove('open');
            drawer.classList.remove('open');
            document.body.style.overflow = '';
        }
        backdrop.addEventListener('click', closeDrawer);
        mobClose.addEventListener('click', closeDrawer);

        mobProd.addEventListener('click', function() {
            mobBody.classList.toggle('open');
            mobProd.classList.toggle('open');
        });

        if (mobServBtn) {
            mobServBtn.addEventListener('click', function() {
                mobServBody.classList.toggle('open');
                mobServBtn.classList.toggle('open');
            });
        }

        if (mobToolkitBtn) {
            mobToolkitBtn.addEventListener('click', function() {
                mobToolkitBody.classList.toggle('open');
                mobToolkitBtn.classList.toggle('open');
            });
        }

        document.querySelectorAll('.mob-cat-btn').forEach(function(b) {
            b.addEventListener('click', function() {
                var panelId = b.getAttribute('data-panel');
                var panel   = document.getElementById(panelId);
                var wasOpen = panel.classList.contains('open');
                document.querySelectorAll('.mob-cat-links').forEach(function(p) { p.classList.remove('open'); });
                document.querySelectorAll('.mob-cat-btn').forEach(function(x) { x.classList.remove('open'); });
                if (!wasOpen) { panel.classList.add('open'); b.classList.add('open'); }
            });
        });

        /* Transparent Header Scroll */
        var header = document.querySelector('header');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    })();
    </script>
    <style>
        .floating-contact {
            position: fixed;
            bottom: 32px;
            right: 32px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            z-index: 9999;
        }
        .float-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-decoration: none;
        }
        .float-btn:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
            color: #fff;
        }
        .float-btn svg {
            width: 28px;
            height: 28px;
        }
        .wa-btn { background-color: #25d366; }
        .phone-btn { background-color: #2b9d9f; }
        
        @media (max-width: 768px) {
            .floating-contact {
                bottom: 24px;
                right: 24px;
                gap: 12px;
            }
            .float-btn {
                width: 50px;
                height: 50px;
            }
            .float-btn svg {
                width: 24px;
                height: 24px;
            }
        }
    </style>

    <!-- Floating Contact -->
    <div class="floating-contact">
        <a href="tel:+622155660700" class="float-btn phone-btn" title="Call Us">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        </a>
        <a href="https://wa.me/628118715671" target="_blank" class="float-btn wa-btn" title="WhatsApp Us">
            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
        </a>
    </div>
</body>
</html>
