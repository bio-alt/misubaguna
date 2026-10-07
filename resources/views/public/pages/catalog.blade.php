@extends('public.layout')

@section('content')
<style>
/* ==========================================================================
   CATALOG PAGE & INTERACTIVE PDF FLIPBOOK VIEWER STYLES
   ========================================================================== */

.catalog-page-wrapper {
    background-color: #f8fafc;
    min-height: 100vh;
    padding-bottom: 64px;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

/* Header Section */
.catalog-header-section {
    background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    color: #ffffff;
    padding: 36px 0 54px;
    border-bottom: 3px solid #dc2626;
}

.catalog-header-inner {
    max-width: 1560px;
    margin: 0 auto;
    padding: 0 24px;
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #94a3b8;
    margin-bottom: 16px;
    font-weight: 500;
}

.breadcrumb a {
    color: #cbd5e1;
    text-decoration: none;
    transition: color 0.15s;
}

.breadcrumb a:hover {
    color: #38bdf8;
}

.breadcrumb .sep {
    color: #64748b;
}

.breadcrumb .current {
    color: #ffffff;
    font-weight: 700;
}

.header-main {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 32px;
    flex-wrap: wrap;
}

.page-title {
    font-size: 34px;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 10px;
    letter-spacing: -0.02em;
    color: #ffffff;
}

.page-subtitle {
    font-size: 15px;
    color: #94a3b8;
    max-width: 800px;
    line-height: 1.6;
}

.btn-download-primary {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #dc2626;
    color: #ffffff;
    font-weight: 700;
    font-size: 14px;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(220, 38, 38, 0.4);
    transition: all 0.2s ease;
    white-space: nowrap;
}

.btn-download-primary:hover {
    background: #b91c1c;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 38, 38, 0.6);
    color: #ffffff;
}

.btn-download-primary svg {
    width: 20px;
    height: 20px;
}

/* Viewer Container Section */
.catalog-viewer-section {
    max-width: 1560px;
    margin: -30px auto 48px;
    padding: 0 16px;
}

.viewer-card-container {
    background: #0f172a;
    border-radius: 16px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.45);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    border: 1px solid #334155;
}

.viewer-card-container.is-fullscreen {
    border-radius: 0;
    border: none;
}

/* Top Toolbar */
.viewer-toolbar {
    background: #1e293b;
    border-bottom: 1px solid #334155;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    z-index: 20;
}

.toolbar-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.toc-dropdown {
    background: #0f172a;
    color: #f1f5f9;
    border: 1px solid #475569;
    border-radius: 6px;
    padding: 8px 14px;
    font-size: 13px;
    font-weight: 600;
    outline: none;
    cursor: pointer;
    font-family: inherit;
    transition: border-color 0.15s;
    max-width: 340px;
}

.toc-dropdown:hover, .toc-dropdown:focus {
    border-color: #38bdf8;
}

.tool-btn {
    background: #334155;
    color: #f8fafc;
    border: 1px solid #475569;
    border-radius: 6px;
    height: 38px;
    padding: 0 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.15s ease;
    font-family: inherit;
    font-size: 13px;
    font-weight: 600;
}

.tool-btn:hover {
    background: #475569;
    color: #ffffff;
    border-color: #64748b;
}

.tool-btn svg {
    width: 18px;
    height: 18px;
}

.tool-btn.icon-only {
    width: 38px;
    padding: 0;
}

.tool-btn.text-btn {
    padding: 0 14px;
    font-size: 12px;
    font-weight: 700;
}

.tool-btn.download-btn {
    background: #2563eb;
    border-color: #3b82f6;
    color: #ffffff;
}

.tool-btn.download-btn:hover {
    background: #1d4ed8;
}

.page-indicator {
    background: #0f172a;
    border: 1px solid #475569;
    padding: 7px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 700;
    color: #38bdf8;
    white-space: nowrap;
    min-width: 160px;
    text-align: center;
}

.zoom-badge {
    font-size: 13px;
    font-weight: 700;
    color: #94a3b8;
    min-width: 48px;
    text-align: center;
}

/* Loading Overlay */
.loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.96);
    z-index: 50;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 16px;
    transition: opacity 0.3s ease;
}

.spinner {
    width: 52px;
    height: 52px;
    border: 4px solid #334155;
    border-top-color: #38bdf8;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-text {
    color: #f8fafc;
    font-size: 16px;
    font-weight: 600;
}

.loading-bar-bg {
    width: 280px;
    height: 8px;
    background: #334155;
    border-radius: 4px;
    overflow: hidden;
}

.loading-bar-fill {
    width: 0%;
    height: 100%;
    background: #38bdf8;
    transition: width 0.2s ease;
}

/* Flipbook Viewport & Stage */
.flipbook-viewport {
    position: relative;
    width: 100%;
    height: calc(88vh - 120px);
    min-height: 820px;
    background: #090d16;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    user-select: none;
    padding: 20px 0;
}

.flipbook-viewport.zoomed {
    overflow: auto;
    cursor: grab;
}

.flipbook-zoom-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s cubic-bezier(0.2, 0, 0, 1);
    transform-origin: center center;
}

.flipbook {
    box-shadow: 0 30px 60px rgba(0, 0, 0, 0.8);
    cursor: pointer;
}

.page {
    background-color: #ffffff;
    overflow: hidden;
}

.page-content {
    width: 100%;
    height: 100%;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.05);
}

.page-content canvas {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.page-number {
    position: absolute;
    bottom: 10px;
    font-size: 11px;
    font-weight: 700;
    color: #475569;
    background: rgba(255, 255, 255, 0.92);
    padding: 3px 10px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    z-index: 10;
}

.page-number.left { left: 14px; }
.page-number.right { right: 14px; }

/* Stage Overlay Navigation Arrows */
.stage-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 56px;
    height: 96px;
    background: rgba(30, 41, 59, 0.85);
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 15;
    transition: all 0.2s ease;
    backdrop-filter: blur(6px);
}

.stage-arrow:hover {
    background: rgba(220, 38, 38, 0.95);
    width: 68px;
}

.stage-arrow svg {
    width: 32px;
    height: 32px;
}

.stage-arrow-left {
    left: 0;
    border-radius: 0 16px 16px 0;
}

.stage-arrow-right {
    right: 0;
    border-radius: 16px 0 0 16px;
}

/* Bottom Bar Quick Jump Pills */
.viewer-bottom-bar {
    background: #1e293b;
    border-top: 1px solid #334155;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    overflow-x: auto;
    white-space: nowrap;
}

.bar-label {
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #94a3b8;
    flex-shrink: 0;
}

.quick-pills {
    display: flex;
    gap: 8px;
}

.pill-btn {
    background: #0f172a;
    color: #cbd5e1;
    border: 1px solid #334155;
    border-radius: 20px;
    padding: 5px 16px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s ease;
    font-family: inherit;
}

.pill-btn:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
}

/* Below Viewer Options & Catalog Highlights */
.catalog-details-container {
    max-width: 1560px;
    margin: 0 auto;
    padding: 0 24px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
    gap: 24px;
    margin-bottom: 48px;
}

.detail-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 28px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid #e2e8f0;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.detail-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
}

.card-num {
    font-size: 12px;
    font-weight: 800;
    color: #dc2626;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-bottom: 8px;
}

.card-title {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 12px;
}

.card-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.card-list li {
    font-size: 14px;
    color: #475569;
    padding: 7px 0;
    border-bottom: 1px dashed #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-list li:last-child {
    border-bottom: none;
}

.card-list li::before {
    content: '•';
    color: #dc2626;
    font-weight: bold;
    font-size: 16px;
}

/* Call to Action Banner */
.cta-banner {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-radius: 16px;
    padding: 40px;
    color: #ffffff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
    flex-wrap: wrap;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.2);
    border: 1px solid #334155;
}

.cta-title {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 8px;
}

.cta-desc {
    font-size: 15px;
    color: #94a3b8;
}

.cta-buttons {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
}

.btn-contact {
    background: #dc2626;
    color: #ffffff;
    font-weight: 700;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-contact:hover {
    background: #b91c1c;
    color: #ffffff;
}

.btn-outline {
    background: transparent;
    color: #ffffff;
    border: 2px solid #475569;
    font-weight: 700;
    padding: 10px 22px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-outline:hover {
    border-color: #ffffff;
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

/* Mobile Responsiveness */
@media (max-width: 768px) {
    .page-title { font-size: 26px; }
    .catalog-header-section { padding: 30px 0 45px; }
    .viewer-toolbar { padding: 10px 14px; justify-content: center; }
    .toc-dropdown { max-width: 100%; width: 100%; order: 1; }
    .nav-controls { order: 2; }
    .view-controls { order: 3; }
    .flipbook-viewport { height: 600px; min-height: 520px; }
    .stage-arrow { width: 40px; height: 70px; }
    .stage-arrow svg { width: 22px; height: 22px; }
    .cta-banner { padding: 24px; flex-direction: column; align-items: flex-start; }
}
</style>

<div class="catalog-page-wrapper">
    <!-- Header Section -->
    <div class="catalog-header-section">
        <div class="catalog-header-inner">
            <nav class="breadcrumb">
                <a href="/">Home</a>
                <span class="sep">/</span>
                <span class="current">Catalog</span>
            </nav>
            <div class="header-main">
                <div>
                    <h1 class="page-title">Company Profile & Product Catalog</h1>
                    <p class="page-subtitle">PT Misuba Guna Indonesia - Interactive Flipbook Catalog. Explore our range of pulping solutions, conveyor & lifting equipment, pumps, flexible joints, sealing systems, and latest engineering projects.</p>
                </div>
                <div class="header-actions">
                    <a href="/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf" download class="btn-download-primary">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download PDF Catalog
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Interactive PDF Viewer Section -->
    <div class="catalog-viewer-section">
        <div class="viewer-card-container" id="viewer-container">
            
            <!-- Top Controls Toolbar -->
            <div class="viewer-toolbar">
                <!-- TOC Select -->
                <div class="toolbar-group">
                    <select id="toc-select" class="toc-dropdown" aria-label="Table of Contents">
                        <option value="0">📖 Cover Page (Page 1)</option>
                        <option value="1">01 - Introduction & Strengths (Pg 2-3)</option>
                        <option value="3">02 - Pulp & Paper Solutions (Pg 4-5)</option>
                        <option value="5">02 - Conveyor & Lifting Products (Pg 6-7)</option>
                        <option value="7">02 - Pumps & Vacuum Pumps (Pg 8-9)</option>
                        <option value="9">02 - Flexible Joints & Hoses (Pg 10-11)</option>
                        <option value="11">02 - Sealing & Protection (Pg 12-13)</option>
                        <option value="13">02 - Heat Transfer & Electrical (Pg 14-15)</option>
                        <option value="15">03 - Latest Projects: Absorbers (Pg 16-17)</option>
                        <option value="17">03 - Latest Projects: Fabrication (Pg 18-19)</option>
                        <option value="19">03 - Latest Projects: Transmission (Pg 20-21)</option>
                        <option value="21">03 - Latest Projects: Field Operations (Pg 22-27)</option>
                        <option value="27">04 - Client List (Pg 28-29)</option>
                        <option value="29">04 - Contact & Location (Pg 30)</option>
                    </select>
                </div>

                <!-- Page Navigation Controls -->
                <div class="toolbar-group nav-controls">
                    <button type="button" id="btn-first" class="tool-btn icon-only" title="First Page">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 17l-5-5 5-5M18 17l-5-5 5-5"/></svg>
                    </button>
                    <button type="button" id="btn-prev" class="tool-btn icon-only" title="Previous Page">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <div class="page-indicator">
                        <span id="page-num-text">Loading...</span>
                    </div>
                    <button type="button" id="btn-next" class="tool-btn icon-only" title="Next Page">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                    <button type="button" id="btn-last" class="tool-btn icon-only" title="Last Page">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 17l5-5-5-5M6 17l5-5-5-5"/></svg>
                    </button>
                </div>

                <!-- View & Zoom Controls -->
                <div class="toolbar-group view-controls">
                    <!-- 2-Page Spread vs Single Page View Mode Toggle -->
                    <button type="button" id="btn-view-mode" class="tool-btn text-btn" title="Toggle 2-Page Spread / Single Page View">
                        <span id="view-mode-label">📖 2-Page Spread</span>
                    </button>

                    <button type="button" id="btn-zoom-out" class="tool-btn icon-only" title="Zoom Out">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </button>
                    <span id="zoom-badge" class="zoom-badge">100%</span>
                    <button type="button" id="btn-zoom-in" class="tool-btn icon-only" title="Zoom In">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </button>
                    <button type="button" id="btn-zoom-reset" class="tool-btn text-btn" title="Reset Zoom to Fit">Fit</button>
                    
                    <button type="button" id="btn-fullscreen" class="tool-btn icon-only" title="Toggle Fullscreen">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 3H5a2 2 0 00-2 2v3m18 0V5a2 2 0 00-2-2h-3m0 18h3a2 2 0 002-2v-3M3 16v3a2 2 0 002 2h3"/></svg>
                    </button>

                    <a href="/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf" download class="tool-btn download-btn icon-only" title="Download PDF File">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                    </a>
                </div>
            </div>

            <!-- Loading Spinner Overlay -->
            <div id="loading-overlay" class="loading-overlay">
                <div class="spinner"></div>
                <div class="loading-text" id="loading-text">Loading high-resolution catalog viewer...</div>
                <div class="loading-bar-bg">
                    <div class="loading-bar-fill" id="loading-bar"></div>
                </div>
            </div>

            <!-- Stage & Flipbook Container -->
            <div class="flipbook-viewport" id="flipbook-viewport">
                <!-- Navigation Arrows -->
                <button type="button" id="stage-prev-btn" class="stage-arrow stage-arrow-left" aria-label="Previous Page">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
                </button>
                <button type="button" id="stage-next-btn" class="stage-arrow stage-arrow-right" aria-label="Next Page">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
                </button>

                <!-- Zoomable Wrapper -->
                <div id="flipbook-zoom-wrapper" class="flipbook-zoom-wrapper">
                    <div id="flipbook" class="flipbook" title="Double click to Zoom In/Out">
                        <!-- Individual portrait pages dynamically injected by JS -->
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation Bar & Quick Jump Pills -->
            <div class="viewer-bottom-bar">
                <span class="bar-label">Quick Jump:</span>
                <div class="quick-pills">
                    <button type="button" class="pill-btn" data-page="0">Cover</button>
                    <button type="button" class="pill-btn" data-page="1">Intro & Vision</button>
                    <button type="button" class="pill-btn" data-page="3">Pulp & Paper</button>
                    <button type="button" class="pill-btn" data-page="5">Conveyors & Lifting</button>
                    <button type="button" class="pill-btn" data-page="7">Pumps & Vacuum</button>
                    <button type="button" class="pill-btn" data-page="9">Flexible Joints</button>
                    <button type="button" class="pill-btn" data-page="11">Sealing & Protection</button>
                    <button type="button" class="pill-btn" data-page="15">Projects</button>
                    <button type="button" class="pill-btn" data-page="27">Clients</button>
                    <button type="button" class="pill-btn" data-page="29">Contact</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Below Viewer Content: Catalog Overview & CTA -->
    <div class="catalog-details-container">
        <div class="details-grid">
            <div class="detail-card">
                <div class="card-num">SECTION 01</div>
                <h3 class="card-title">Introduction & Company Strengths</h3>
                <ul class="card-list">
                    <li>Company background & history since 2020</li>
                    <li>Core Strengths: Passion, Persistence, Service, Guiding</li>
                    <li>Mission & Vision for engineering service & supply</li>
                </ul>
            </div>

            <div class="detail-card">
                <div class="card-num">SECTION 02</div>
                <h3 class="card-title">Products & Authorized Brands</h3>
                <ul class="card-list">
                    <li><strong>Parason</strong>: Pulping, Refiner Plates, Head Box</li>
                    <li><strong>RUD India</strong>: Drag Chain, Sprockets, Lifting Beams</li>
                    <li><strong>Sampumps & Kakati</strong>: Slurry, Vacuum & Chemical Pumps</li>
                    <li><strong>Champion</strong>: Gaskets, Seals & Metal Expansion Joints</li>
                </ul>
            </div>

            <div class="detail-card">
                <div class="card-num">SECTION 03 & 04</div>
                <h3 class="card-title">Projects & Major Client List</h3>
                <ul class="card-list">
                    <li>Vibration Absorber & Large Metal Expansion Joints</li>
                    <li>PTFE & Rubber Lining for Tanks and Pipelines</li>
                    <li>Pulp & Paper, Refinery, Petrochemical & Mining Clients</li>
                </ul>
            </div>
        </div>

        <!-- CTA Banner -->
        <div class="cta-banner">
            <div>
                <h3 class="cta-title">Need Technical Specifications or Custom Product Quotes?</h3>
                <p class="cta-desc">Our sales and technical support team is ready to assist you with equipment sizing, material selection, and site visits.</p>
            </div>
            <div class="cta-buttons">
                <a href="/contact-us/" class="btn-contact">Contact Sales Team</a>
                <a href="/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf" download class="btn-outline">Download Full PDF</a>
            </div>
        </div>
    </div>
</div>

<!-- Scripts for PDF.js and StPageFlip -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>

<script>
document.addEventListener('DOMContentLoaded', async function () {
    const pdfUrl = '/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf';
    
    // Set PDF.js worker URL
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const flipbookContainer = document.getElementById('flipbook');
    const loadingOverlay = document.getElementById('loading-overlay');
    const loadingText = document.getElementById('loading-text');
    const loadingBar = document.getElementById('loading-bar');
    const pageNumText = document.getElementById('page-num-text');
    const tocSelect = document.getElementById('toc-select');
    const zoomBadge = document.getElementById('zoom-badge');
    const zoomWrapper = document.getElementById('flipbook-zoom-wrapper');
    const viewModeLabel = document.getElementById('view-mode-label');

    let pdfDoc = null;
    let pageFlip = null;
    let currentZoom = 1.0;
    const zoomLevels = [0.8, 1.0, 1.25, 1.5, 1.8, 2.2];
    let zoomIndex = 1;
    let isSinglePageMode = false;
    let totalFlipPages = 0;

    // Calculate optimal single portrait page dimensions so 2 pages side-by-side fit comfortably
    function getSinglePageDimensions() {
        const isMobile = window.innerWidth < 768;
        if (isMobile) {
            return { width: 360, height: 509 };
        }
        
        const viewportWidth = document.getElementById('flipbook-viewport').clientWidth || (window.innerWidth * 0.95);
        const viewportHeight = document.getElementById('flipbook-viewport').clientHeight || 840;
        
        const availW = viewportWidth - 80;  // Stage width minus padding & arrows
        const availH = viewportHeight - 40; // Stage height minus padding
        
        // Single page A4 aspect ratio 1 : 1.414 (0.707)
        let h = Math.min(availH, 880);
        let w = h * 0.707;
        
        // Ensure 2-page spread (2 * w) fits within available width
        if (w * 2 > availW) {
            w = availW / 2;
            h = w / 0.707;
        }

        return { width: Math.round(w), height: Math.round(h) };
    }

    try {
        // Load PDF document
        pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;

        // 1. Inspect all PDF pages and construct DOM for single portrait pages
        for (let p = 1; p <= pdfDoc.numPages; p++) {
            const pdfPage = await pdfDoc.getPage(p);
            const vp = pdfPage.getViewport({ scale: 1.0 });

            if (vp.width > vp.height * 1.1) {
                // Landscape 2-page spread: split into 2 individual portrait flipbook pages (Left & Right)
                totalFlipPages++;
                createPageDiv(`flipbook-canvas-${p}-left`, totalFlipPages, 'soft');
                
                totalFlipPages++;
                createPageDiv(`flipbook-canvas-${p}-right`, totalFlipPages, 'soft');
            } else {
                // Single portrait page (Cover or Back cover)
                totalFlipPages++;
                const density = (p === 1 || p === pdfDoc.numPages) ? 'hard' : 'soft';
                createPageDiv(`flipbook-canvas-${p}-single`, totalFlipPages, density);
            }
        }

        function createPageDiv(canvasId, pageNumLabel, density) {
            const pageDiv = document.createElement('div');
            pageDiv.className = 'page';
            pageDiv.setAttribute('data-density', density);

            const pageContent = document.createElement('div');
            pageContent.className = 'page-content';

            const canvas = document.createElement('canvas');
            canvas.id = canvasId;

            const pageNumTag = document.createElement('div');
            pageNumTag.className = `page-number ${pageNumLabel % 2 === 0 ? 'left' : 'right'}`;
            pageNumTag.textContent = pageNumLabel;

            pageContent.appendChild(canvas);
            pageContent.appendChild(pageNumTag);
            pageDiv.appendChild(pageContent);
            flipbookContainer.appendChild(pageDiv);
        }

        // 2. Initialize St.PageFlip with portrait dimensions & usePortrait: false for 2-page spread
        const isMobileInit = window.innerWidth < 768;
        const dims = getSinglePageDimensions();
        
        pageFlip = new St.PageFlip(flipbookContainer, {
            width: dims.width,
            height: dims.height,
            size: 'stretch',
            minWidth: 280,
            maxWidth: 1200,
            minHeight: 400,
            maxHeight: 1600,
            maxShadowOpacity: 0.4,
            showCover: true,
            usePortrait: isMobileInit ? true : false, // FORCE 2-PAGE SPREAD ON DESKTOP!
            mobileScrollSupport: false
        });

        const pageElements = flipbookContainer.querySelectorAll('.page');
        pageFlip.loadFromHTML(pageElements);

        // Update Page Indicator Display
        function updatePageDisplay(pageIdx) {
            const isMobile = window.innerWidth < 768;
            if (pageIdx === 0) {
                pageNumText.textContent = `Page 1 of ${totalFlipPages} (Cover)`;
            } else if (pageIdx === totalFlipPages - 1) {
                pageNumText.textContent = `Page ${totalFlipPages} of ${totalFlipPages} (Back)`;
            } else if (isMobile || isSinglePageMode) {
                pageNumText.textContent = `Page ${pageIdx + 1} of ${totalFlipPages}`;
            } else {
                const p1 = pageIdx % 2 === 1 ? pageIdx + 1 : pageIdx;
                const p2 = p1 + 1;
                if (p2 <= totalFlipPages) {
                    pageNumText.textContent = `Pages ${p1}-${p2} of ${totalFlipPages}`;
                } else {
                    pageNumText.textContent = `Page ${p1} of ${totalFlipPages}`;
                }
            }

            // Sync TOC Select
            for (let i = tocSelect.options.length - 1; i >= 0; i--) {
                const optVal = parseInt(tocSelect.options[i].value);
                if (optVal <= pageIdx) {
                    tocSelect.selectedIndex = i;
                    break;
                }
            }
        }

        pageFlip.on('flip', (e) => {
            updatePageDisplay(e.data);
        });

        updatePageDisplay(0);

        // Synchronously render PDF Page 1 (Cover) and Page 2 (Left & Right halves) for immediate load
        await renderAndSplitPdfPage(1);
        if (pdfDoc.numPages > 1) await renderAndSplitPdfPage(2);

        // Hide Loading Overlay
        loadingOverlay.style.opacity = '0';
        setTimeout(() => { loadingOverlay.style.display = 'none'; }, 300);

        // Asynchronously render remaining PDF pages (3 to 16) in background
        for (let p = 3; p <= pdfDoc.numPages; p++) {
            renderAndSplitPdfPage(p).then(() => {
                const percent = Math.round((p / pdfDoc.numPages) * 100);
                loadingBar.style.width = percent + '%';
            });
        }

    } catch (err) {
        console.error('Error loading PDF flipbook:', err);
        loadingText.innerHTML = '<span style="color:#ef4444">Failed to load interactive flipbook. <a href="/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf" download style="color:#38bdf8; text-decoration:underline;">Click here to download PDF</a></span>';
    }

    // Helper to render PDF page and split landscape spreads into 2 portrait canvases
    async function renderAndSplitPdfPage(pdfPageNum) {
        if (!pdfDoc) return;
        try {
            const pdfPage = await pdfDoc.getPage(pdfPageNum);
            const scale = 2.5; // High definition crisp scaling
            const viewport = pdfPage.getViewport({ scale: scale });

            if (viewport.width > viewport.height * 1.1) {
                // Landscape Spread: render to offscreen canvas then crop left & right halves
                const offscreen = document.createElement('canvas');
                offscreen.width = Math.round(viewport.width);
                offscreen.height = Math.round(viewport.height);
                const offCtx = offscreen.getContext('2d');

                await pdfPage.render({
                    canvasContext: offCtx,
                    viewport: viewport
                }).promise;

                const halfWidth = Math.round(viewport.width / 2);
                const fullHeight = Math.round(viewport.height);

                // Draw Left Half
                const leftCanvas = document.getElementById(`flipbook-canvas-${pdfPageNum}-left`);
                if (leftCanvas) {
                    leftCanvas.width = halfWidth;
                    leftCanvas.height = fullHeight;
                    const leftCtx = leftCanvas.getContext('2d');
                    leftCtx.drawImage(offscreen, 0, 0, halfWidth, fullHeight, 0, 0, halfWidth, fullHeight);
                }

                // Draw Right Half
                const rightCanvas = document.getElementById(`flipbook-canvas-${pdfPageNum}-right`);
                if (rightCanvas) {
                    rightCanvas.width = halfWidth;
                    rightCanvas.height = fullHeight;
                    const rightCtx = rightCanvas.getContext('2d');
                    rightCtx.drawImage(offscreen, halfWidth, 0, halfWidth, fullHeight, 0, 0, halfWidth, fullHeight);
                }
            } else {
                // Portrait Single Page (Cover or Back Cover)
                const canvas = document.getElementById(`flipbook-canvas-${pdfPageNum}-single`);
                if (canvas) {
                    canvas.width = Math.round(viewport.width);
                    canvas.height = Math.round(viewport.height);
                    const ctx = canvas.getContext('2d');
                    await pdfPage.render({
                        canvasContext: ctx,
                        viewport: viewport
                    }).promise;
                }
            }
        } catch (e) {
            console.error(`Error rendering PDF page ${pdfPageNum}:`, e);
        }
    }

    // Controls Event Handlers
    document.getElementById('btn-first').addEventListener('click', () => pageFlip && pageFlip.turnToPage(0));
    document.getElementById('btn-prev').addEventListener('click', () => pageFlip && pageFlip.flipPrev());
    document.getElementById('btn-next').addEventListener('click', () => pageFlip && pageFlip.flipNext());
    document.getElementById('btn-last').addEventListener('click', () => pageFlip && pageFlip.turnToPage(totalFlipPages > 0 ? totalFlipPages - 1 : 29));

    document.getElementById('stage-prev-btn').addEventListener('click', () => pageFlip && pageFlip.flipPrev());
    document.getElementById('stage-next-btn').addEventListener('click', () => pageFlip && pageFlip.flipNext());

    // Single Page vs 2-Page Spread View Mode Toggle
    const btnViewMode = document.getElementById('btn-view-mode');
    btnViewMode.addEventListener('click', () => {
        if (!pageFlip) return;
        isSinglePageMode = !isSinglePageMode;
        if (isSinglePageMode) {
            viewModeLabel.textContent = '📖 2-Page Spread';
            // Enable portrait mode & zoom to focus on 1 page
            pageFlip.update({ usePortrait: true });
            zoomIndex = 2; // 1.25x zoom scale
        } else {
            viewModeLabel.textContent = '📄 Single Page Focus Mode';
            // Re-enable 2-page spread mode
            pageFlip.update({ usePortrait: false });
            zoomIndex = 1; // 1.0x standard fit
        }
        applyZoom();
        updatePageDisplay(pageFlip.getCurrentPageIndex());
    });

    // TOC Selection
    tocSelect.addEventListener('change', (e) => {
        const pageIndex = parseInt(e.target.value);
        if (pageFlip) pageFlip.flip(pageIndex);
    });

    // Quick Jump Pills
    document.querySelectorAll('.pill-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const p = parseInt(btn.getAttribute('data-page'));
            if (pageFlip) pageFlip.flip(p);
        });
    });

    // Zoom Handlers
    function applyZoom() {
        currentZoom = zoomLevels[zoomIndex];
        zoomWrapper.style.transform = `scale(${currentZoom})`;
        zoomBadge.textContent = `${Math.round(currentZoom * 100)}%`;
        if (currentZoom > 1.0) {
            document.getElementById('flipbook-viewport').classList.add('zoomed');
        } else {
            document.getElementById('flipbook-viewport').classList.remove('zoomed');
        }
    }

    document.getElementById('btn-zoom-in').addEventListener('click', () => {
        if (zoomIndex < zoomLevels.length - 1) {
            zoomIndex++;
            applyZoom();
        }
    });

    document.getElementById('btn-zoom-out').addEventListener('click', () => {
        if (zoomIndex > 0) {
            zoomIndex--;
            applyZoom();
        }
    });

    document.getElementById('btn-zoom-reset').addEventListener('click', () => {
        zoomIndex = 1;
        isSinglePageMode = false;
        viewModeLabel.textContent = '📄 Single Page Focus Mode';
        if (pageFlip) pageFlip.update({ usePortrait: false });
        applyZoom();
    });

    // Double Click to Toggle Zoom In/Out
    flipbookContainer.addEventListener('dblclick', (e) => {
        e.preventDefault();
        if (zoomIndex === 1) {
            zoomIndex = 3; // Zoom in to 150%
        } else {
            zoomIndex = 1; // Reset zoom to 100%
        }
        applyZoom();
    });

    // Fullscreen Toggle
    const container = document.getElementById('viewer-container');
    const fullscreenBtn = document.getElementById('btn-fullscreen');

    fullscreenBtn.addEventListener('click', () => {
        if (!document.fullscreenElement) {
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen();
            }
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            }
        }
    });

    document.addEventListener('fullscreenchange', () => {
        if (document.fullscreenElement) {
            container.classList.add('is-fullscreen');
        } else {
            container.classList.remove('is-fullscreen');
        }
        if (pageFlip) pageFlip.update();
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', (e) => {
        if (['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;
        if (e.key === 'ArrowRight' || e.key === ' ') {
            if (pageFlip) pageFlip.flipNext();
        } else if (e.key === 'ArrowLeft') {
            if (pageFlip) pageFlip.flipPrev();
        } else if (e.key === 'Home') {
            if (pageFlip) pageFlip.turnToPage(0);
        } else if (e.key === 'End') {
            if (pageFlip) pageFlip.turnToPage(totalFlipPages > 0 ? totalFlipPages - 1 : 29);
        } else if (e.key === '+' || e.key === '=') {
            if (zoomIndex < zoomLevels.length - 1) { zoomIndex++; applyZoom(); }
        } else if (e.key === '-') {
            if (zoomIndex > 0) { zoomIndex--; applyZoom(); }
        }
    });
});
</script>
@endsection
