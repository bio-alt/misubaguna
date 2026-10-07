@extends('public.layout')

@section('title', 'Material Property Library | Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Searchable compact SolidWorks-style engineering material database with physical, mechanical, thermal properties and chemical resistance for 45+ industrial materials.')

@section('content')
<style>
    /* Compact Hero Header */
    .mat-hero-compact {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 70%, #0f766e 100%);
        color: #fff;
        padding: 24px 32px;
        border-bottom: 2px solid #0d9488;
    }

    .mat-hero-inner {
        max-width: 1360px;
        margin: 0 auto;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
    }

    .mat-hero-title h1 {
        font-size: 24px;
        font-weight: 800;
        color: #f8fafc;
        margin: 0 0 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mat-hero-title p {
        font-size: 13.5px;
        color: #94a3b8;
        margin: 0;
    }

    .mat-stats-inline {
        display: flex;
        gap: 12px;
        background: rgba(15, 23, 42, 0.6);
        padding: 6px 16px;
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.1);
        font-size: 12px;
        font-weight: 700;
        color: #2dd4bf;
    }

    .toolkit-container {
        max-width: 1360px;
        margin: 16px auto 32px;
        padding: 0 32px;
    }

    /* Single Compact Control Toolbar */
    .compact-toolbar {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
        justify-content: space-between;
    }

    .search-input-wrap {
        position: relative;
        flex: 1;
        min-width: 240px;
    }

    .search-input-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        color: #0d9488;
    }

    .search-input {
        width: 100%;
        padding: 7px 32px 7px 36px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        outline: none;
        transition: border 0.15s;
        background: #f8fafc;
    }

    .search-input:focus {
        background: #fff;
        border-color: #0d9488;
    }

    .search-clear-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        font-size: 14px;
        display: none;
    }

    .cat-select {
        padding: 7px 12px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        background: #fff;
        outline: none;
        cursor: pointer;
    }

    .unit-system-toggle {
        display: flex;
        background: #f1f5f9;
        padding: 2px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .unit-btn {
        padding: 5px 12px;
        font-size: 12px;
        font-weight: 800;
        color: #64748b;
        background: none;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .unit-btn.active {
        background: #0d9488;
        color: #fff;
    }

    /* SolidWorks 2-Column Split Layout */
    .sw-mat-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        min-height: 580px;
    }

    @media (min-width: 900px) {
        .sw-mat-layout {
            grid-template-columns: 310px 1fr;
        }
    }

    /* Compact Left Sidebar Tree */
    .sw-sidebar {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        max-height: 620px;
        overflow-y: auto;
    }

    .sw-sidebar-header {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748b;
        padding-bottom: 8px;
        margin-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cat-folder {
        margin-bottom: 6px;
    }

    .cat-folder-title {
        font-size: 12.5px;
        font-weight: 700;
        color: #1e293b;
        padding: 6px 8px;
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
    }

    .cat-folder-title:hover {
        background: #f0fdfa;
        color: #0d9488;
    }

    .cat-folder-title svg {
        width: 14px;
        height: 14px;
        transition: transform 0.15s;
        color: #64748b;
    }

    .cat-folder.collapsed .cat-folder-title svg {
        transform: rotate(-90deg);
    }

    .cat-item-list {
        padding-left: 6px;
        margin-top: 2px;
        display: flex;
        flex-direction: column;
        gap: 1px;
    }

    .cat-folder.collapsed .cat-item-list {
        display: none;
    }

    .mat-item {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        padding: 5px 8px;
        border-radius: 5px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-left: 3px solid transparent;
    }

    .mat-item:hover {
        background: #f0fdfa;
        color: #0d9488;
    }

    .mat-item.active {
        background: #f0fdfa;
        color: #0f766e;
        border-left-color: #0d9488;
        font-weight: 700;
    }

    .mat-item-standard {
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        background: #e2e8f0;
        padding: 1px 5px;
        border-radius: 3px;
        font-family: monospace;
    }

    .mat-item.active .mat-item-standard {
        background: #0d9488;
        color: #fff;
    }

    /* Compact Property Viewer */
    .sw-viewer {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
    }

    .sw-mat-header {
        border-bottom: 1.5px solid #f1f5f9;
        padding-bottom: 14px;
        margin-bottom: 14px;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .sw-mat-title-group h2 {
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px;
    }

    .sw-mat-sub {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }

    .badge-cat {
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 11.5px;
    }

    .badge-standard {
        background: #f1f5f9;
        color: #334155;
        font-weight: 700;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 11.5px;
        font-family: monospace;
        border: 1px solid #e2e8f0;
    }

    .sw-actions {
        display: flex;
        gap: 8px;
    }

    .btn-sw-action {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
        transition: all 0.15s;
    }

    .btn-sw-action:hover {
        background: #f0fdfa;
        border-color: #0d9488;
        color: #0d9488;
    }

    /* Inline Metric Summary Strip (Replaces Large Cards) */
    .metric-strip {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 8px 14px;
        margin-bottom: 16px;
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        align-items: center;
        justify-content: space-around;
    }

    .metric-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12.5px;
    }

    .metric-item-lbl {
        color: #64748b;
        font-weight: 600;
    }

    .metric-item-val {
        color: #0d9488;
        font-weight: 800;
        font-family: monospace;
    }

    /* Compact Tabs */
    .sw-tabs {
        display: flex;
        gap: 6px;
        border-bottom: 1.5px solid #e5e7eb;
        margin-bottom: 16px;
    }

    .sw-tab-btn {
        background: none;
        border: none;
        padding: 8px 14px;
        font-size: 13px;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        margin-bottom: -1.5px;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .sw-tab-btn.active {
        color: #0d9488;
        border-bottom-color: #0d9488;
    }

    .sw-tab-pane {
        display: none;
    }

    .sw-tab-pane.active {
        display: block;
    }

    /* High-Density Property Table */
    .prop-table {
        width: 100%;
        border-collapse: collapse;
    }

    .prop-table th {
        background: #f8fafc;
        text-align: left;
        padding: 8px 12px;
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
        border-bottom: 1.5px solid #e2e8f0;
        letter-spacing: 0.04em;
    }

    .prop-table td {
        padding: 7px 12px;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 500;
    }

    .prop-table tr:hover td {
        background: #f8fafc;
    }

    .prop-name {
        font-weight: 700;
        color: #0f172a;
    }

    .prop-val {
        font-weight: 800;
        color: #0d9488;
        font-family: monospace;
        font-size: 14px;
    }

    .prop-unit {
        color: #64748b;
        font-size: 11.5px;
    }

    /* Chemical Rating Badges */
    .rating-badge {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
    }

    .rating-excellent { background: #dcfce7; color: #15803d; }
    .rating-good      { background: #e0f2fe; color: #0369a1; }
    .rating-fair      { background: #fef3c7; color: #b45309; }
    .rating-poor      { background: #fee2e2; color: #b91c1c; }

    /* Misuba Products Usage Box */
    .usage-card {
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        border-radius: 8px;
        padding: 14px 16px;
        margin-top: 14px;
    }

    .usage-card h4 {
        font-size: 13.5px;
        font-weight: 800;
        color: #0f766e;
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .usage-card p {
        font-size: 13px;
        color: #115e59;
        line-height: 1.5;
        margin: 0;
    }

    /* Comparison Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        z-index: 99999;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .modal-backdrop.open {
        display: flex;
    }

    .modal-box {
        background: #fff;
        border-radius: 12px;
        max-width: 980px;
        width: 100%;
        max-height: 88vh;
        overflow-y: auto;
        padding: 20px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 12px;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 16px;
    }

    .modal-header h3 {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 22px;
        cursor: pointer;
        color: #64748b;
    }

    .compare-table {
        width: 100%;
        border-collapse: collapse;
    }

    .compare-table th, .compare-table td {
        padding: 8px 10px;
        border: 1px solid #e2e8f0;
        text-align: left;
        font-size: 12.5px;
    }

    .compare-table th {
        background: #f8fafc;
        font-weight: 800;
    }

    /* Toast Notification */
    .toast-msg {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%) translateY(80px);
        background: #0f172a;
        color: #5eead4;
        padding: 10px 20px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 13px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        border: 1px solid #0d9488;
        transition: transform 0.25s ease;
        z-index: 999999;
    }
    .toast-msg.show {
        transform: translateX(-50%) translateY(0);
    }
</style>

<!-- Compact Hero Header -->
<div class="mat-hero-compact">
    <div class="mat-hero-inner">
        <div class="mat-hero-title">
            <h1>
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Material Property Library
            </h1>
            <p>SolidWorks-compliant material specifications. Inspect mechanical, thermal, and chemical resistance data.</p>
        </div>
        <div class="mat-stats-inline">
            <span>45+ Materials</span>
            <span>•</span>
            <span>9 Families</span>
            <span>•</span>
            <span>SI / Imperial</span>
        </div>
    </div>
</div>

<div class="toolkit-container">
    <!-- Single Combined Control Toolbar -->
    <div class="compact-toolbar">
        <div class="search-input-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input type="text" id="mat-search" class="search-input" placeholder="Search material name or grade (SS316L, Inconel, PTFE, Viton)..." oninput="filterMaterials()">
            <button class="search-clear-btn" id="search-clear" onclick="clearSearch()">&times;</button>
        </div>

        <select class="cat-select" id="cat-filter-select" onchange="selectCatSelect(this.value)">
            <option value="ALL">All Material Families (45+)</option>
            <option value="Carbon & Low Alloy Steels">Carbon & Low Alloy Steels</option>
            <option value="Austenitic & Stainless Steels">Austenitic & Stainless Steels</option>
            <option value="Duplex & Super Duplex">Duplex & Super Duplex</option>
            <option value="Nickel & High-Temp Alloys">Nickel & High-Temp Alloys</option>
            <option value="Non-Ferrous Alloys (Copper, Brass, Alu)">Non-Ferrous Alloys</option>
            <option value="Titanium & Special Reactive Metals">Titanium & Reactive Metals</option>
            <option value="Elastomers & Rubbers">Elastomers & Rubbers</option>
            <option value="Fluoropolymers & Engineering Plastics">Fluoropolymers & Plastics</option>
            <option value="Ceramics, Refractories & Gaskets">Ceramics & Gasket Sheets</option>
        </select>

        <div class="unit-system-toggle">
            <button class="unit-btn active" id="unit-si" onclick="setUnitSystem('SI')">SI Metric</button>
            <button class="unit-btn" id="unit-imp" onclick="setUnitSystem('IMP')">Imperial US</button>
        </div>
    </div>

    <!-- SolidWorks 2-Column Split Layout -->
    <div class="sw-mat-layout">
        <!-- Sidebar Tree -->
        <div class="sw-sidebar">
            <div class="sw-sidebar-header">
                <span>Directory</span>
                <span id="mat-count-badge" style="color:#0d9488; font-weight:800;">Loading...</span>
            </div>

            <div id="tree-container">
                <!-- Dynamically rendered tree -->
            </div>
        </div>

        <!-- Property Viewer -->
        <div class="sw-viewer" id="sw-viewer">
            <div class="sw-mat-header">
                <div class="sw-mat-title-group">
                    <h2 id="view-name">Stainless Steel 316L</h2>
                    <div class="sw-mat-sub">
                        <span class="badge-cat" id="view-category">Austenitic Stainless Steel</span>
                        <span class="badge-standard" id="view-standard">UNS S31603 / ASTM A240</span>
                    </div>
                </div>
                <div class="sw-actions">
                    <button class="btn-sw-action" onclick="openCompareModal()">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        Compare
                    </button>
                    <button class="btn-sw-action" onclick="copyMaterialJSON()">
                        <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        Copy Specs
                    </button>
                </div>
            </div>

            <!-- Compact Inline Metric Summary Strip -->
            <div class="metric-strip">
                <div class="metric-item">
                    <span class="metric-item-lbl">Yield:</span>
                    <span class="metric-item-val" id="qc-ys">240 MPa</span>
                </div>
                <div class="metric-item">
                    <span class="metric-item-lbl">Density:</span>
                    <span class="metric-item-val" id="qc-dens">8000 kg/m³</span>
                </div>
                <div class="metric-item">
                    <span class="metric-item-lbl">Max Temp:</span>
                    <span class="metric-item-val" id="qc-temp">870 °C</span>
                </div>
                <div class="metric-item">
                    <span class="metric-item-lbl">Modulus:</span>
                    <span class="metric-item-val" id="qc-em">193 GPa</span>
                </div>
            </div>

            <!-- Tabs -->
            <div class="sw-tabs">
                <button class="sw-tab-btn active" onclick="switchSWTab(this, 'tab-mech')">
                    Mechanical & Physical
                </button>
                <button class="sw-tab-btn" onclick="switchSWTab(this, 'tab-thermal')">
                    Thermal Properties
                </button>
                <button class="sw-tab-btn" onclick="switchSWTab(this, 'tab-chem')">
                    Chemical Resistance
                </button>
                <button class="sw-tab-btn" onclick="switchSWTab(this, 'tab-apps')">
                    Misuba Applications
                </button>
            </div>

            <!-- Tab 1: Mechanical & Physical -->
            <div class="sw-tab-pane active" id="tab-mech">
                <table class="prop-table">
                    <thead>
                        <tr>
                            <th>Property Description</th>
                            <th>Value</th>
                            <th>Units</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-mech-body">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Tab 2: Thermal Properties -->
            <div class="sw-tab-pane" id="tab-thermal">
                <table class="prop-table">
                    <thead>
                        <tr>
                            <th>Thermal Property</th>
                            <th>Value</th>
                            <th>Units</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-thermal-body">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Tab 3: Chemical Resistance -->
            <div class="sw-tab-pane" id="tab-chem">
                <table class="prop-table">
                    <thead>
                        <tr>
                            <th>Chemical / Environmental Media</th>
                            <th>Resistance Rating</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-chem-body">
                        <!-- Populated via JS -->
                    </tbody>
                </table>
            </div>

            <!-- Tab 4: Industrial Applications -->
            <div class="sw-tab-pane" id="tab-apps">
                <p id="view-desc" style="color:#334155; line-height:1.5; font-size:13.5px; margin-bottom:14px;"></p>
                <div class="usage-card">
                    <h4>
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                        PT Misuba Guna Indonesia Product Compatibility
                    </h4>
                    <p id="view-misuba-app"></p>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Comparison Modal -->
<div class="modal-backdrop" id="compare-modal">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Side-by-Side Material Comparison</h3>
            <button class="modal-close" onclick="closeCompareModal()">&times;</button>
        </div>
        <p style="font-size:13px; color:#64748b; margin-bottom:14px;">Select materials to compare key mechanical, thermal, and chemical resistance properties:</p>

        <div style="display:flex; gap:12px; margin-bottom:16px; flex-wrap:wrap;">
            <select id="comp-mat-1" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-weight:700; flex:1;" onchange="renderComparison()"></select>
            <select id="comp-mat-2" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-weight:700; flex:1;" onchange="renderComparison()"></select>
            <select id="comp-mat-3" style="padding:8px 12px; border-radius:6px; border:1px solid #cbd5e1; font-weight:700; flex:1;" onchange="renderComparison()"></select>
        </div>

        <table class="compare-table" id="compare-table">
            <!-- Populated dynamically -->
        </table>
    </div>
</div>

<!-- Toast Banner -->
<div class="toast-msg" id="toast-msg">
    <span id="toast-text">Copied material specs to clipboard!</span>
</div>

<script>
    // Massive Comprehensive SolidWorks-style Material Library Database (45+ Materials)
    const materialsData = [
        // ==========================================
        // 1. CARBON & LOW ALLOY STEELS
        // ==========================================
        {
            id: 'astm-a105',
            name: 'Carbon Steel ASTM A105',
            category: 'Carbon & Low Alloy Steels',
            standard: 'ASTM A105 / ASME SA105',
            desc: 'Standard forged carbon steel for high-temperature service piping components including flanges, fittings, and valve bodies.',
            misubaApp: 'Forged pipe flanges (DIN, JIS, ASME), valve bodies, expansion joint weld ends, and structural pipe supports.',
            mech: { elasticModulus: 200, poissonRatio: 0.29, shearModulus: 77, density: 7850, yieldStrength: 250, tensileStrength: 485, elongation: 30, hardness: '137 HB' },
            thermal: { conductivity: 51.9, specificHeat: 486, expansionCoeff: 11.7, maxTemp: 425, minTemp: -29 },
            chem: { acids: 'Poor', alkalis: 'Fair', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Poor' }
        },
        {
            id: 'astm-a36',
            name: 'Carbon Steel ASTM A36 / SS400',
            category: 'Carbon & Low Alloy Steels',
            standard: 'ASTM A36 / JIS G3101 SS400',
            desc: 'General purpose structural carbon steel plate with excellent weldability and machinability for construction and machine components.',
            misubaApp: 'Expansion joint tie rods, base plates, duct flanges, structural frames, and machinery housings.',
            mech: { elasticModulus: 200, poissonRatio: 0.26, shearModulus: 79.3, density: 7850, yieldStrength: 250, tensileStrength: 400, elongation: 23, hardness: '119 HB' },
            thermal: { conductivity: 51.0, specificHeat: 486, expansionCoeff: 12.0, maxTemp: 400, minTemp: -20 },
            chem: { acids: 'Poor', alkalis: 'Fair', solvents: 'Good', oils: 'Excellent', steam: 'Fair', seawater: 'Poor' }
        },
        {
            id: 'astm-a106-b',
            name: 'Carbon Steel Pipe ASTM A106 Gr B',
            category: 'Carbon & Low Alloy Steels',
            standard: 'ASTM A106 Grade B / ASME SA106',
            desc: 'Seamless carbon steel pipe specification for high-temperature service in power plants, oil refineries, and gas piping.',
            misubaApp: 'High-temperature piping spools, expansion joint pipe ends, boiler header pipes, and pressure vessel connections.',
            mech: { elasticModulus: 200, poissonRatio: 0.29, shearModulus: 77, density: 7850, yieldStrength: 240, tensileStrength: 415, elongation: 30, hardness: '137 HB' },
            thermal: { conductivity: 50.0, specificHeat: 486, expansionCoeff: 12.1, maxTemp: 425, minTemp: -29 },
            chem: { acids: 'Poor', alkalis: 'Fair', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Poor' }
        },
        {
            id: 'astm-a350-lf2',
            name: 'Low Temp Carbon Steel A350 LF2',
            category: 'Carbon & Low Alloy Steels',
            standard: 'ASTM A350 LF2 / ASME SA350',
            desc: 'Notch-tough carbon steel forging grade intended primarily for low-temperature service down to -46°C with Charpy V-notch impact testing.',
            misubaApp: 'Cryogenic and low-temperature flange forgings, LNG pipeline valves, expansion joint flanges, and pressure vessel nozzles.',
            mech: { elasticModulus: 200, poissonRatio: 0.29, shearModulus: 77, density: 7850, yieldStrength: 250, tensileStrength: 485, elongation: 30, hardness: '160 HB' },
            thermal: { conductivity: 50.5, specificHeat: 480, expansionCoeff: 11.9, maxTemp: 400, minTemp: -46 },
            chem: { acids: 'Poor', alkalis: 'Fair', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Poor' }
        },
        {
            id: 'astm-a516-70',
            name: 'Pressure Vessel Plate A516 Gr 70',
            category: 'Carbon & Low Alloy Steels',
            standard: 'ASTM A516 Grade 70 / ASME SA516',
            desc: 'High tensile strength carbon steel plate intended for welded pressure vessels where improved notch toughness is required.',
            misubaApp: 'Pressure vessel shells, expansion joint duct covers, boiler steam drums, and storage tank walls.',
            mech: { elasticModulus: 200, poissonRatio: 0.29, shearModulus: 77, density: 7850, yieldStrength: 260, tensileStrength: 485, elongation: 21, hardness: '143 HB' },
            thermal: { conductivity: 51.5, specificHeat: 486, expansionCoeff: 11.8, maxTemp: 450, minTemp: -46 },
            chem: { acids: 'Poor', alkalis: 'Fair', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Poor' }
        },
        {
            id: 'aisi-4140',
            name: 'Chromoly Alloy Steel AISI 4140',
            category: 'Carbon & Low Alloy Steels',
            standard: 'AISI 4140 / 42CrMo4 / UNS G41400',
            desc: 'Chromium-molybdenum low alloy steel featuring high fatigue strength, toughness, and wear resistance after heat treatment.',
            misubaApp: 'High-strength stud bolting, heavy shafting, gearbox gears, valve stems, and coupling bolts.',
            mech: { elasticModulus: 205, poissonRatio: 0.29, shearModulus: 80, density: 7850, yieldStrength: 655, tensileStrength: 850, elongation: 25, hardness: '280 HB' },
            thermal: { conductivity: 42.6, specificHeat: 470, expansionCoeff: 12.3, maxTemp: 500, minTemp: -40 },
            chem: { acids: 'Poor', alkalis: 'Fair', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Poor' }
        },
        {
            id: 'astm-a193-b7',
            name: 'Alloy Steel Stud Bolt A193 B7',
            category: 'Carbon & Low Alloy Steels',
            standard: 'ASTM A193 Grade B7 / AISI 4140 Q&T',
            desc: 'Heat-treated chromium-molybdenum alloy steel specification for high-pressure and high-temperature flange bolting.',
            misubaApp: 'Standard flange stud bolts (ASME B16.5, DIN, JIS), valve bonnet bolting, and pressure vessel cover studs.',
            mech: { elasticModulus: 205, poissonRatio: 0.29, shearModulus: 80, density: 7850, yieldStrength: 725, tensileStrength: 860, elongation: 16, hardness: '321 HB' },
            thermal: { conductivity: 42.0, specificHeat: 470, expansionCoeff: 12.4, maxTemp: 450, minTemp: -29 },
            chem: { acids: 'Poor', alkalis: 'Fair', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Poor' }
        },

        // ==========================================
        // 2. AUSTENITIC & STAINLESS STEELS
        // ==========================================
        {
            id: 'ss304',
            name: 'Stainless Steel 304',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S30400 / ASTM A240 / 1.4301',
            desc: 'The standard 18/8 austenitic stainless steel with excellent formability, weldability, and resistance to atmospheric corrosion.',
            misubaApp: 'Bellows for standard metal expansion joints, flexible hose braiding, flange rings, and storage vessel liners.',
            mech: { elasticModulus: 193, poissonRatio: 0.29, shearModulus: 77, density: 8000, yieldStrength: 215, tensileStrength: 505, elongation: 70, hardness: '160 HB' },
            thermal: { conductivity: 16.2, specificHeat: 500, expansionCoeff: 17.2, maxTemp: 870, minTemp: -196 },
            chem: { acids: 'Good', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Good', seawater: 'Fair' }
        },
        {
            id: 'ss304l',
            name: 'Stainless Steel 304L',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S30403 / ASTM A240 / 1.4306',
            desc: 'Low-carbon variation of 304 stainless steel preventing intergranular corrosion and chromium carbide precipitation during welding.',
            misubaApp: 'Welded expansion joint assemblies, chemical storage tanks, sanitary piping, and welded flange spools.',
            mech: { elasticModulus: 193, poissonRatio: 0.29, shearModulus: 77, density: 8000, yieldStrength: 170, tensileStrength: 485, elongation: 55, hardness: '150 HB' },
            thermal: { conductivity: 16.2, specificHeat: 500, expansionCoeff: 17.2, maxTemp: 800, minTemp: -196 },
            chem: { acids: 'Good', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Good', seawater: 'Fair' }
        },
        {
            id: 'ss316',
            name: 'Stainless Steel 316',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S31600 / ASTM A240 / 1.4401',
            desc: 'Molybdenum-bearing austenitic stainless steel providing superior resistance to pitting corrosion in chloride and acid environments.',
            misubaApp: 'Corrosive chemical expansion joints, marine flexible metal hoses, PTFE lined pipe stubs, and valve trims.',
            mech: { elasticModulus: 193, poissonRatio: 0.30, shearModulus: 77, density: 8000, yieldStrength: 290, tensileStrength: 580, elongation: 50, hardness: '165 HB' },
            thermal: { conductivity: 16.3, specificHeat: 500, expansionCoeff: 16.0, maxTemp: 870, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Good' }
        },
        {
            id: 'ss316l',
            name: 'Stainless Steel 316L',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S31603 / ASTM A240 / 1.4404',
            desc: 'Low-carbon 316 grade offering maximum weldability and resistance to sensitization in heavy welded sections.',
            misubaApp: 'Chemical plant metal expansion joints, food & pharmaceutical piping, PTFE lined pipe fittings, and high-temp valve trims.',
            mech: { elasticModulus: 193, poissonRatio: 0.30, shearModulus: 77, density: 8000, yieldStrength: 240, tensileStrength: 550, elongation: 60, hardness: '150 HB' },
            thermal: { conductivity: 16.3, specificHeat: 500, expansionCoeff: 16.0, maxTemp: 870, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Good' }
        },
        {
            id: 'ss316ti',
            name: 'Stainless Steel 316Ti',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S31635 / DIN 1.4571 / ASTM A240',
            desc: 'Titanium-stabilized version of 316 stainless steel designed for elevated temperature service between 550°C and 800°C.',
            misubaApp: 'High-temperature exhaust expansion joints, power plant ductwork, heat exchanger tubing, and chemical reactors.',
            mech: { elasticModulus: 193, poissonRatio: 0.30, shearModulus: 77, density: 8000, yieldStrength: 240, tensileStrength: 540, elongation: 40, hardness: '170 HB' },
            thermal: { conductivity: 16.0, specificHeat: 500, expansionCoeff: 16.5, maxTemp: 870, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Good' }
        },
        {
            id: 'ss321',
            name: 'Stainless Steel 321',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S32100 / ASTM A240 / 1.4541',
            desc: 'Titanium-stabilized austenitic stainless steel providing immunity to intergranular corrosion in high heat environments.',
            misubaApp: 'Bellows for high-temperature exhaust joints, boiler ducting, furnace piping, and engine manifold expansion joints.',
            mech: { elasticModulus: 193, poissonRatio: 0.30, shearModulus: 77, density: 8030, yieldStrength: 240, tensileStrength: 570, elongation: 50, hardness: '165 HB' },
            thermal: { conductivity: 16.1, specificHeat: 500, expansionCoeff: 16.6, maxTemp: 900, minTemp: -196 },
            chem: { acids: 'Good', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Fair' }
        },
        {
            id: 'ss310s',
            name: 'Stainless Steel 310S (Heat Resistant)',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S31008 / ASTM A240 / 1.4845',
            desc: 'High nickel and chromium austenitic alloy with exceptional oxidation resistance in continuous service up to 1150°C.',
            misubaApp: 'Kiln & furnace expansion joints, thermal oxidizers, cement plant ductwork joints, and high-temp heat exchangers.',
            mech: { elasticModulus: 200, poissonRatio: 0.30, shearModulus: 77, density: 7980, yieldStrength: 245, tensileStrength: 590, elongation: 40, hardness: '175 HB' },
            thermal: { conductivity: 14.2, specificHeat: 500, expansionCoeff: 15.8, maxTemp: 1150, minTemp: -196 },
            chem: { acids: 'Good', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Fair' }
        },
        {
            id: 'ss904l',
            name: 'Stainless Steel 904L',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS N08904 / ASTM B625 / 1.4539',
            desc: 'High-alloy austenitic stainless steel with high copper content specifically developed to resist sulfuric acid concentrations.',
            misubaApp: 'Sulfuric acid piping expansion joints, fertilizer plant valves, phosphoric acid pumps, and seawater coolers.',
            mech: { elasticModulus: 190, poissonRatio: 0.31, shearModulus: 75, density: 8050, yieldStrength: 230, tensileStrength: 530, elongation: 35, hardness: '160 HB' },
            thermal: { conductivity: 13.0, specificHeat: 450, expansionCoeff: 15.8, maxTemp: 400, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'ss17-4ph',
            name: '17-4 PH Stainless Steel',
            category: 'Austenitic & Stainless Steels',
            standard: 'UNS S17400 / ASTM A564 / 1.4542',
            desc: 'Precipitation-hardening martensitic stainless steel delivering high strength, hardness, and corrosion resistance similar to 304.',
            misubaApp: 'High-pressure valve stems, pump shafts, turbine blades, coupling pins, and mechanical seal components.',
            mech: { elasticModulus: 196, poissonRatio: 0.27, shearModulus: 77, density: 7750, yieldStrength: 1170, tensileStrength: 1310, elongation: 10, hardness: '388 HB' },
            thermal: { conductivity: 18.4, specificHeat: 460, expansionCoeff: 10.8, maxTemp: 315, minTemp: -196 },
            chem: { acids: 'Good', alkalis: 'Good', solvents: 'Excellent', oils: 'Excellent', steam: 'Good', seawater: 'Fair' }
        },

        // ==========================================
        // 3. DUPLEX & SUPER DUPLEX STEELS
        // ==========================================
        {
            id: 'duplex2205',
            name: 'Duplex Stainless Steel 2205',
            category: 'Duplex & Super Duplex',
            standard: 'UNS S31803 / S32205 / 1.4462',
            desc: 'Austenitic-ferritic duplex steel offering double the mechanical strength of 316L combined with exceptional resistance to stress corrosion cracking.',
            misubaApp: 'Offshore oil & gas expansion joints, chemical tanker piping, desulfurization plant expansion joints, and high-pressure flanges.',
            mech: { elasticModulus: 200, poissonRatio: 0.30, shearModulus: 77, density: 7800, yieldStrength: 450, tensileStrength: 655, elongation: 25, hardness: '290 HB' },
            thermal: { conductivity: 19.0, specificHeat: 500, expansionCoeff: 13.5, maxTemp: 300, minTemp: -50 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'superduplex2507',
            name: 'Super Duplex 2507',
            category: 'Duplex & Super Duplex',
            standard: 'UNS S32750 / 1.4410 / EN 10088-3',
            desc: 'Super duplex alloy with 25% chromium designed for extremely aggressive organic/inorganic acid solutions and marine environments with PREN >42.',
            misubaApp: 'Desalination plant piping, aggressive chemical processing joints, subsea manifolds, and critical flange bolting.',
            mech: { elasticModulus: 200, poissonRatio: 0.30, shearModulus: 77, density: 7800, yieldStrength: 550, tensileStrength: 750, elongation: 25, hardness: '310 HB' },
            thermal: { conductivity: 15.0, specificHeat: 500, expansionCoeff: 13.0, maxTemp: 250, minTemp: -50 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'zeron100',
            name: 'Zeron 100 Super Duplex',
            category: 'Duplex & Super Duplex',
            standard: 'UNS S32760 / 1.4501 / EN 10088-3',
            desc: 'Highly alloyed super duplex stainless steel with copper and tungsten additions for enhanced corrosion resistance in hot seawater and mineral acids.',
            misubaApp: 'Marine seawater pumps, offshore oil platform expansion joints, pollution control scrubbers, and sulfuric acid valves.',
            mech: { elasticModulus: 200, poissonRatio: 0.30, shearModulus: 77, density: 7840, yieldStrength: 550, tensileStrength: 750, elongation: 25, hardness: '300 HB' },
            thermal: { conductivity: 15.0, specificHeat: 500, expansionCoeff: 13.0, maxTemp: 250, minTemp: -50 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },

        // ==========================================
        // 4. NICKEL & HIGH-TEMP ALLOYS
        // ==========================================
        {
            id: 'inconel600',
            name: 'Inconel 600',
            category: 'Nickel & High-Temp Alloys',
            standard: 'UNS N06600 / W.Nr. 2.4816',
            desc: 'Standard engineering material for applications requiring resistance to severe heat corrosion and high-purity water environments.',
            misubaApp: 'Nuclear reactor piping expansion joints, furnace retorts, chemical processing vessels, and high-temp gaskets.',
            mech: { elasticModulus: 207, poissonRatio: 0.29, shearModulus: 80, density: 8470, yieldStrength: 240, tensileStrength: 655, elongation: 40, hardness: '150 HB' },
            thermal: { conductivity: 14.9, specificHeat: 444, expansionCoeff: 13.3, maxTemp: 1093, minTemp: -196 },
            chem: { acids: 'Good', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Good' }
        },
        {
            id: 'inconel625',
            name: 'Inconel 625',
            category: 'Nickel & High-Temp Alloys',
            standard: 'UNS N06625 / W.Nr. 2.4856',
            desc: 'Nickel-chromium-molybdenum alloy with niobium addition providing high strength without heat treatment and outstanding resistance to severe oxidation.',
            misubaApp: 'High-performance metal expansion joint bellows for severe thermal cycling, refinery cat crackers, and power plant ducting.',
            mech: { elasticModulus: 205, poissonRatio: 0.30, shearModulus: 79, density: 8440, yieldStrength: 460, tensileStrength: 880, elongation: 50, hardness: '220 HB' },
            thermal: { conductivity: 9.8, specificHeat: 410, expansionCoeff: 12.8, maxTemp: 980, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'inconel718',
            name: 'Inconel 718 (Hardened Superalloy)',
            category: 'Nickel & High-Temp Alloys',
            standard: 'UNS N07718 / W.Nr. 2.4668',
            desc: 'High-strength, corrosion-resistant nickel-chromium superalloy usable from cryogenic temperatures up to 704°C.',
            misubaApp: 'High-temperature spring components, expansion joint tie rods under high stress, gas turbine fasteners, and valve stems.',
            mech: { elasticModulus: 200, poissonRatio: 0.29, shearModulus: 77, density: 8190, yieldStrength: 1100, tensileStrength: 1375, elongation: 15, hardness: '360 HB' },
            thermal: { conductivity: 11.4, specificHeat: 435, expansionCoeff: 13.0, maxTemp: 704, minTemp: -250 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'incoloy825',
            name: 'Incoloy 825',
            category: 'Nickel & High-Temp Alloys',
            standard: 'UNS N08825 / W.Nr. 2.4858',
            desc: 'Nickel-iron-chromium alloy with molybdenum and copper added for exceptional resistance to reducing and oxidizing acids like sulfuric and phosphoric.',
            misubaApp: 'Sulfuric acid pickling expansion joints, oil & gas sour gas piping, offshore coolers, and chemical digester piping.',
            mech: { elasticModulus: 196, poissonRatio: 0.29, shearModulus: 76, density: 8140, yieldStrength: 240, tensileStrength: 580, elongation: 45, hardness: '160 HB' },
            thermal: { conductivity: 11.1, specificHeat: 440, expansionCoeff: 14.0, maxTemp: 540, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'hastelloy-c276',
            name: 'Hastelloy C-276',
            category: 'Nickel & High-Temp Alloys',
            standard: 'UNS N10276 / W.Nr. 2.4819',
            desc: 'Universal nickel-molybdenum-chromium superalloy with tungsten addition offering unmatched resistance to wet chlorine gas and strong oxidizers.',
            misubaApp: 'Chemical plant expansion joint bellows, acid lining retainers, chlorination system valves, and heat exchanger tubes.',
            mech: { elasticModulus: 205, poissonRatio: 0.30, shearModulus: 79, density: 8890, yieldStrength: 355, tensileStrength: 785, elongation: 60, hardness: '210 HB' },
            thermal: { conductivity: 10.2, specificHeat: 427, expansionCoeff: 11.2, maxTemp: 1040, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'monel400',
            name: 'Monel 400',
            category: 'Nickel & High-Temp Alloys',
            standard: 'UNS N04400 / W.Nr. 2.4360',
            desc: 'Nickel-copper alloy exhibiting high strength and toughness over a wide temperature range with superior resistance to hydrofluoric acid and seawater.',
            misubaApp: 'Seawater piping expansion joints, refinery HF alkylation unit valves, heat exchanger shells, and marine pumps.',
            mech: { elasticModulus: 179, poissonRatio: 0.32, shearModulus: 66, density: 8800, yieldStrength: 240, tensileStrength: 550, elongation: 48, hardness: '140 HB' },
            thermal: { conductivity: 21.8, specificHeat: 427, expansionCoeff: 13.9, maxTemp: 480, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'alloy20',
            name: 'Alloy 20 (Carpenter 20)',
            category: 'Nickel & High-Temp Alloys',
            standard: 'UNS N08020 / ASTM B463 / 2.4660',
            desc: 'Iron-nickel-chromium alloy designed specifically for maximum resistance to acid attack, particularly in hot sulfuric acid environments.',
            misubaApp: 'Sulfuric acid storage expansion joints, pickling tank heating coils, chemical pumps, and mixing equipment.',
            mech: { elasticModulus: 193, poissonRatio: 0.31, shearModulus: 74, density: 8080, yieldStrength: 240, tensileStrength: 550, elongation: 40, hardness: '160 HB' },
            thermal: { conductivity: 12.2, specificHeat: 500, expansionCoeff: 14.7, maxTemp: 500, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Good' }
        },

        // ==========================================
        // 5. NON-FERROUS ALLOYS (COPPER, BRASS, ALU)
        // ==========================================
        {
            id: 'alu-6061',
            name: 'Aluminum 6061-T6',
            category: 'Non-Ferrous Alloys (Copper, Brass, Alu)',
            standard: 'UNS A96061 / ASTM B221',
            desc: 'Precipitation-hardened aluminum alloy containing magnesium and silicon with good mechanical properties and structural weldability.',
            misubaApp: 'Lightweight flange covers, cooling tower fan blades, structural frames, and pneumatic valve manifolds.',
            mech: { elasticModulus: 68.9, poissonRatio: 0.33, shearModulus: 26, density: 2700, yieldStrength: 276, tensileStrength: 310, elongation: 17, hardness: '95 HB' },
            thermal: { conductivity: 167, specificHeat: 896, expansionCoeff: 23.6, maxTemp: 150, minTemp: -200 },
            chem: { acids: 'Poor', alkalis: 'Poor', solvents: 'Good', oils: 'Good', steam: 'Poor', seawater: 'Fair' }
        },
        {
            id: 'alu-7075',
            name: 'Aluminum 7075-T6 (High Strength)',
            category: 'Non-Ferrous Alloys (Copper, Brass, Alu)',
            standard: 'UNS A97075 / ASTM B209',
            desc: 'One of the highest strength aluminum alloys available with zinc as the primary alloying element, comparable to many steels.',
            misubaApp: 'High-stress structural brackets, aerospace fittings, lightweight coupling hubs, and precision fixtures.',
            mech: { elasticModulus: 71.7, poissonRatio: 0.33, shearModulus: 26.9, density: 2810, yieldStrength: 503, tensileStrength: 572, elongation: 11, hardness: '150 HB' },
            thermal: { conductivity: 130, specificHeat: 960, expansionCoeff: 23.2, maxTemp: 120, minTemp: -200 },
            chem: { acids: 'Poor', alkalis: 'Poor', solvents: 'Good', oils: 'Good', steam: 'Poor', seawater: 'Poor' }
        },
        {
            id: 'copper-c110',
            name: 'Copper C11000 (ETP)',
            category: 'Non-Ferrous Alloys (Copper, Brass, Alu)',
            standard: 'UNS C11000 / ASTM B152',
            desc: 'Electrolytic tough pitch copper offering extremely high electrical conductivity (100% IACS) and superior thermal conductivity.',
            misubaApp: 'Busbars, heat exchanger fins, thermal grounding straps, electrical contacts, and gasket sealing rings.',
            mech: { elasticModulus: 115, poissonRatio: 0.34, shearModulus: 44, density: 8890, yieldStrength: 69, tensileStrength: 220, elongation: 45, hardness: '45 HB' },
            thermal: { conductivity: 388, specificHeat: 385, expansionCoeff: 17.0, maxTemp: 200, minTemp: -200 },
            chem: { acids: 'Fair', alkalis: 'Good', solvents: 'Excellent', oils: 'Excellent', steam: 'Fair', seawater: 'Fair' }
        },
        {
            id: 'brass-c360',
            name: 'Brass C36000 (Free-Cutting)',
            category: 'Non-Ferrous Alloys (Copper, Brass, Alu)',
            standard: 'UNS C36000 / ASTM B16',
            desc: 'Copper-zinc-lead alloy known for ultimate machinability, smooth surface finish, and good corrosion resistance in non-acidic media.',
            misubaApp: 'Brass fittings, instrument valve stems, lubricating nozzles, and thermal spray coating feedstock.',
            mech: { elasticModulus: 97, poissonRatio: 0.31, shearModulus: 37, density: 8500, yieldStrength: 310, tensileStrength: 390, elongation: 18, hardness: '130 HB' },
            thermal: { conductivity: 115, specificHeat: 380, expansionCoeff: 20.5, maxTemp: 200, minTemp: -196 },
            chem: { acids: 'Poor', alkalis: 'Good', solvents: 'Excellent', oils: 'Excellent', steam: 'Fair', seawater: 'Fair' }
        },
        {
            id: 'alu-bronze-c954',
            name: 'Aluminum Bronze C95400',
            category: 'Non-Ferrous Alloys (Copper, Brass, Alu)',
            standard: 'UNS C95400 / ASTM B148',
            desc: 'High-strength aluminum bronze providing superior wear resistance, high shock load capacity, and immunity to marine biofouling.',
            misubaApp: 'Heavy duty marine valve discs, butterfly valve seats, pump impellers, sleeve bearings, and worm gears.',
            mech: { elasticModulus: 110, poissonRatio: 0.32, shearModulus: 42, density: 7450, yieldStrength: 240, tensileStrength: 585, elongation: 18, hardness: '170 HB' },
            thermal: { conductivity: 59, specificHeat: 420, expansionCoeff: 16.2, maxTemp: 260, minTemp: -196 },
            chem: { acids: 'Fair', alkalis: 'Good', solvents: 'Excellent', oils: 'Excellent', steam: 'Good', seawater: 'Excellent' }
        },
        {
            id: 'cupronickel-9010',
            name: 'Cupronickel 90/10 (C70600)',
            category: 'Non-Ferrous Alloys (Copper, Brass, Alu)',
            standard: 'UNS C70600 / ASTM B111',
            desc: '90% copper and 10% nickel alloy providing exceptional resistance to seawater corrosion, erosion, and bio-fouling.',
            misubaApp: 'Seawater heat exchanger tubes, marine expansion joints, offshore cooling piping, and desalination condensers.',
            mech: { elasticModulus: 135, poissonRatio: 0.34, shearModulus: 50, density: 8940, yieldStrength: 105, tensileStrength: 310, elongation: 40, hardness: '95 HB' },
            thermal: { conductivity: 45, specificHeat: 380, expansionCoeff: 17.1, maxTemp: 300, minTemp: -196 },
            chem: { acids: 'Fair', alkalis: 'Good', solvents: 'Excellent', oils: 'Excellent', steam: 'Good', seawater: 'Excellent' }
        },

        // ==========================================
        // 6. TITANIUM & SPECIAL REACTIVE METALS
        // ==========================================
        {
            id: 'titanium-gr2',
            name: 'Titanium Grade 2',
            category: 'Titanium & Special Reactive Metals',
            standard: 'UNS R50400 / ASTM B265',
            desc: 'Commercially pure titanium providing optimal strength-to-weight ratio and immune resistance to seawater, wet chlorine, and organic acids.',
            misubaApp: 'Plate heat exchanger plates, chlor-alkali plant expansion joints, titanium valve trims, and marine piping.',
            mech: { elasticModulus: 105, poissonRatio: 0.37, shearModulus: 45, density: 4510, yieldStrength: 275, tensileStrength: 345, elongation: 20, hardness: '160 HB' },
            thermal: { conductivity: 21.9, specificHeat: 523, expansionCoeff: 8.6, maxTemp: 400, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'titanium-gr5',
            name: 'Titanium Grade 5 (Ti-6Al-4V)',
            category: 'Titanium & Special Reactive Metals',
            standard: 'UNS R56400 / ASTM B348',
            desc: 'Alpha-beta titanium alloy combining high mechanical strength (over 880 MPa yield) with low density and excellent corrosion resistance.',
            misubaApp: 'High strength titanium bolts, aerospace expansion joint hardware, turbine blades, and high-pressure chemical components.',
            mech: { elasticModulus: 114, poissonRatio: 0.34, shearModulus: 44, density: 4430, yieldStrength: 880, tensileStrength: 950, elongation: 14, hardness: '330 HB' },
            thermal: { conductivity: 6.7, specificHeat: 526, expansionCoeff: 8.6, maxTemp: 400, minTemp: -210 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'zirconium-702',
            name: 'Zirconium 702',
            category: 'Titanium & Special Reactive Metals',
            standard: 'UNS R60702 / ASTM B551',
            desc: 'Unalloyed zirconium offering virtually total resistance to corrosive organic and mineral acids, strong alkalis, and molten salts.',
            misubaApp: 'Extreme hydrochloric acid processing piping, acetic acid expansion joints, chemical reactor liners, and thermowells.',
            mech: { elasticModulus: 99, poissonRatio: 0.35, shearModulus: 36, density: 6510, yieldStrength: 207, tensileStrength: 379, elongation: 16, hardness: '160 HB' },
            thermal: { conductivity: 22.0, specificHeat: 285, expansionCoeff: 5.7, maxTemp: 350, minTemp: -196 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },

        // ==========================================
        // 7. ELASTOMERS & RUBBERS
        // ==========================================
        {
            id: 'epdm',
            name: 'EPDM Rubber',
            category: 'Elastomers & Rubbers',
            standard: 'ASTM D2000 M4CA / ISO 1629 EPDM',
            desc: 'Ethylene Propylene Diene Monomer rubber featuring outstanding ozone, weathering, hot water, and saturated steam resistance.',
            misubaApp: 'Rubber expansion joints for HVAC & cooling water lines, rubber lining for water tanks, and outdoor flange gaskets.',
            mech: { elasticModulus: 0.005, poissonRatio: 0.49, shearModulus: 0.0017, density: 1150, yieldStrength: 12, tensileStrength: 15, elongation: 400, hardness: '65 Shore A' },
            thermal: { conductivity: 0.25, specificHeat: 2100, expansionCoeff: 160, maxTemp: 150, minTemp: -40 },
            chem: { acids: 'Good', alkalis: 'Excellent', solvents: 'Poor', oils: 'Poor', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'nbr',
            name: 'NBR (Nitrile Rubber)',
            category: 'Elastomers & Rubbers',
            standard: 'ASTM D2000 BG / ISO 1629 NBR',
            desc: 'Acrylonitrile butadiene rubber designed specifically for superior oil, grease, fuel, and hydrocarbon resistance.',
            misubaApp: 'Oil & fuel rubber expansion joints, hydraulic oil seals, O-rings, and oil pipeline rubber lining.',
            mech: { elasticModulus: 0.008, poissonRatio: 0.49, shearModulus: 0.0025, density: 1200, yieldStrength: 14, tensileStrength: 18, elongation: 350, hardness: '70 Shore A' },
            thermal: { conductivity: 0.25, specificHeat: 1980, expansionCoeff: 140, maxTemp: 120, minTemp: -30 },
            chem: { acids: 'Fair', alkalis: 'Good', solvents: 'Fair', oils: 'Excellent', steam: 'Fair', seawater: 'Excellent' }
        },
        {
            id: 'hnbr',
            name: 'HNBR (Hydrogenated Nitrile)',
            category: 'Elastomers & Rubbers',
            standard: 'ASTM D2000 DH / ISO 1629 HNBR',
            desc: 'Hydrogenated nitrile rubber offering enhanced heat resistance (up to 160°C), superior tensile strength, and abrasion resistance.',
            misubaApp: 'Oilfield drilling expansion joints, high-pressure hydraulic seals, automotive timing belts, and sour gas seals.',
            mech: { elasticModulus: 0.012, poissonRatio: 0.49, shearModulus: 0.004, density: 1250, yieldStrength: 20, tensileStrength: 28, elongation: 300, hardness: '75 Shore A' },
            thermal: { conductivity: 0.25, specificHeat: 1950, expansionCoeff: 135, maxTemp: 160, minTemp: -35 },
            chem: { acids: 'Good', alkalis: 'Good', solvents: 'Fair', oils: 'Excellent', steam: 'Good', seawater: 'Excellent' }
        },
        {
            id: 'viton',
            name: 'Viton® / FKM Fluoroelastomer',
            category: 'Elastomers & Rubbers',
            standard: 'ASTM D2000 HK / ISO 1629 FKM',
            desc: 'High-performance fluoropolymer elastomer offering extreme chemical resistance to fuels, concentrated acids, and high heat up to 230°C.',
            misubaApp: 'High-temp chemical rubber expansion joints, aggressive acid rubber linings, Viton O-rings, and mechanical shaft seals.',
            mech: { elasticModulus: 0.010, poissonRatio: 0.49, shearModulus: 0.0033, density: 1850, yieldStrength: 15, tensileStrength: 17, elongation: 250, hardness: '75 Shore A' },
            thermal: { conductivity: 0.20, specificHeat: 1400, expansionCoeff: 160, maxTemp: 230, minTemp: -20 },
            chem: { acids: 'Excellent', alkalis: 'Good', solvents: 'Fair', oils: 'Excellent', steam: 'Good', seawater: 'Excellent' }
        },
        {
            id: 'ffkm',
            name: 'FFKM (Kalrez® Perfluoroelastomer)',
            category: 'Elastomers & Rubbers',
            standard: 'ASTM D2000 FK / ISO 1629 FFKM',
            desc: 'The ultimate elastomeric seal material combining the elastomeric properties of rubber with near PTFE-like chemical inertness up to 325°C.',
            misubaApp: 'Critical chemical pump O-rings, semiconductor processing seals, high-temperature aggressive acid seals, and aerospace gaskets.',
            mech: { elasticModulus: 0.015, poissonRatio: 0.49, shearModulus: 0.005, density: 2010, yieldStrength: 18, tensileStrength: 22, elongation: 160, hardness: '80 Shore A' },
            thermal: { conductivity: 0.18, specificHeat: 1300, expansionCoeff: 150, maxTemp: 325, minTemp: -15 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'neoprene',
            name: 'Neoprene / CR Rubber',
            category: 'Elastomers & Rubbers',
            standard: 'ASTM D2000 BC / ISO 1629 CR',
            desc: 'Chloroprene synthetic rubber providing balanced physical properties, moderate oil resistance, and excellent mechanical toughness.',
            misubaApp: 'General industrial expansion joints, anti-vibration mounts, conveyor belt covers, and pipe sleeve seals.',
            mech: { elasticModulus: 0.006, poissonRatio: 0.49, shearModulus: 0.0020, density: 1350, yieldStrength: 11, tensileStrength: 16, elongation: 380, hardness: '60 Shore A' },
            thermal: { conductivity: 0.19, specificHeat: 2200, expansionCoeff: 170, maxTemp: 110, minTemp: -35 },
            chem: { acids: 'Fair', alkalis: 'Good', solvents: 'Fair', oils: 'Good', steam: 'Fair', seawater: 'Good' }
        },
        {
            id: 'natural-rubber',
            name: 'Natural Rubber (NR / Pure Gum)',
            category: 'Elastomers & Rubbers',
            standard: 'ASTM D2000 AA / ISO 1629 NR',
            desc: 'Natural polyisoprene rubber providing unmatched elasticity, tear strength, high rebound resilience, and outstanding abrasive wear resistance.',
            misubaApp: 'Slurry pump rubber linings, abrasive chute liners, mining expansion joints, and high-impact rubber pads.',
            mech: { elasticModulus: 0.004, poissonRatio: 0.49, shearModulus: 0.0013, density: 1050, yieldStrength: 18, tensileStrength: 24, elongation: 550, hardness: '50 Shore A' },
            thermal: { conductivity: 0.15, specificHeat: 1880, expansionCoeff: 220, maxTemp: 80, minTemp: -50 },
            chem: { acids: 'Fair', alkalis: 'Good', solvents: 'Poor', oils: 'Poor', steam: 'Poor', seawater: 'Good' }
        },

        // ==========================================
        // 8. FLUOROPOLYMERS & ENGINEERING PLASTICS
        // ==========================================
        {
            id: 'ptfe-virgin',
            name: 'PTFE (Virgin Teflon®)',
            category: 'Fluoropolymers & Engineering Plastics',
            standard: 'ASTM D4894 / D1457 / ISO 12086',
            desc: 'Polytetrafluoroethylene polymer with near-universal chemical inertness, lowest coefficient of friction, and wide operating temperature range.',
            misubaApp: 'PTFE pipe lining, PTFE expansion joint bellows, PTFE sheet gaskets, slide bearings, and valve seat rings.',
            mech: { elasticModulus: 0.5, poissonRatio: 0.46, shearModulus: 0.17, density: 2180, yieldStrength: 23, tensileStrength: 30, elongation: 300, hardness: '55 Shore D' },
            thermal: { conductivity: 0.25, specificHeat: 970, expansionCoeff: 120, maxTemp: 260, minTemp: -200 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'ptfe-glass',
            name: 'PTFE 25% Glass Filled',
            category: 'Fluoropolymers & Engineering Plastics',
            standard: 'ASTM D4745',
            desc: 'Glass-reinforced PTFE offering significantly lower creep, higher compressive strength, and enhanced wear resistance compared to virgin PTFE.',
            misubaApp: 'High-pressure valve seats, mechanical seal backup rings, heavy load slide pads, and flange gaskets.',
            mech: { elasticModulus: 0.95, poissonRatio: 0.44, shearModulus: 0.32, density: 2250, yieldStrength: 28, tensileStrength: 22, elongation: 220, hardness: '65 Shore D' },
            thermal: { conductivity: 0.45, specificHeat: 950, expansionCoeff: 75, maxTemp: 260, minTemp: -200 },
            chem: { acids: 'Excellent', alkalis: 'Good', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'pfa',
            name: 'PFA Fluoropolymer',
            category: 'Fluoropolymers & Engineering Plastics',
            standard: 'ASTM D3307 / ISO 12086',
            desc: 'Melt-processible fluoropolymer combining the chemical resistance of PTFE with melt flow properties enabling seamless thick linings.',
            misubaApp: 'Molded PFA lining for valves & pumps, ultra-pure chemical piping, PFA expansion joint liners, and semiconductor tubing.',
            mech: { elasticModulus: 0.65, poissonRatio: 0.46, shearModulus: 0.22, density: 2150, yieldStrength: 15, tensileStrength: 28, elongation: 300, hardness: '60 Shore D' },
            thermal: { conductivity: 0.23, specificHeat: 980, expansionCoeff: 120, maxTemp: 260, minTemp: -200 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'pvdf',
            name: 'PVDF (Kynar® Fluoropolymer)',
            category: 'Fluoropolymers & Engineering Plastics',
            standard: 'ASTM D3222 / ISO 10931',
            desc: 'High-rigidity fluoropolymer with superior mechanical strength, creep resistance, and abrasion resistance compared to PTFE.',
            misubaApp: 'PVDF lined steel pipes, chemical distribution valves, filter press plates, and acid piping nozzles.',
            mech: { elasticModulus: 2.1, poissonRatio: 0.35, shearModulus: 0.77, density: 1780, yieldStrength: 50, tensileStrength: 55, elongation: 50, hardness: '78 Shore D' },
            thermal: { conductivity: 0.19, specificHeat: 1200, expansionCoeff: 130, maxTemp: 150, minTemp: -40 },
            chem: { acids: 'Excellent', alkalis: 'Good', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Excellent' }
        },
        {
            id: 'peek',
            name: 'PEEK (Polyether Ether Ketone)',
            category: 'Fluoropolymers & Engineering Plastics',
            standard: 'ASTM D6262',
            desc: 'Ultra-high performance engineering thermoplastic with exceptional mechanical strength, stiffness, thermal resistance, and chemical immunity.',
            misubaApp: 'High-pressure pump wear rings, compressor valve plates, high-temp electrical insulators, and seal cages.',
            mech: { elasticModulus: 3.6, poissonRatio: 0.40, shearModulus: 1.3, density: 1320, yieldStrength: 100, tensileStrength: 110, elongation: 45, hardness: '85 Shore D' },
            thermal: { conductivity: 0.25, specificHeat: 1340, expansionCoeff: 47, maxTemp: 250, minTemp: -60 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'nylon-66',
            name: 'Nylon 66 (PA66)',
            category: 'Fluoropolymers & Engineering Plastics',
            standard: 'ASTM D5989 / ISO 1874',
            desc: 'Extruded polyamide engineering plastic featuring high mechanical strength, stiffness, wear resistance, and self-lubricating properties.',
            misubaApp: 'Mechanical gears, wear strips, slide blocks, pipe supports, and insulating bushings.',
            mech: { elasticModulus: 2.8, poissonRatio: 0.39, shearModulus: 1.0, density: 1140, yieldStrength: 85, tensileStrength: 90, elongation: 30, hardness: '80 Shore D' },
            thermal: { conductivity: 0.24, specificHeat: 1670, expansionCoeff: 80, maxTemp: 100, minTemp: -40 },
            chem: { acids: 'Poor', alkalis: 'Good', solvents: 'Excellent', oils: 'Excellent', steam: 'Poor', seawater: 'Good' }
        },
        {
            id: 'uhmwpe',
            name: 'UHMWPE (Polyethylene)',
            category: 'Fluoropolymers & Engineering Plastics',
            standard: 'ASTM D4020 / ISO 15527',
            desc: 'Ultra-high molecular weight polyethylene providing unmatched impact strength and lowest sliding friction among all thermoplastics.',
            misubaApp: 'Chute & hopper abrasion liners, chain guides, suction box covers, and slurry pipe liners.',
            mech: { elasticModulus: 0.7, poissonRatio: 0.46, shearModulus: 0.24, density: 930, yieldStrength: 23, tensileStrength: 40, elongation: 350, hardness: '62 Shore D' },
            thermal: { conductivity: 0.41, specificHeat: 1850, expansionCoeff: 180, maxTemp: 80, minTemp: -150 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Good', oils: 'Good', steam: 'Poor', seawater: 'Excellent' }
        },

        // ==========================================
        // 9. CERAMICS, REFRACTORIES & GASKETS
        // ==========================================
        {
            id: 'alumina-995',
            name: 'Alumina Ceramic 99.5% Al2O3',
            category: 'Ceramics, Refractories & Gaskets',
            standard: 'ASTM C784 / ISO 6474',
            desc: 'High-density technical ceramic featuring extreme Mohs hardness (9), exceptional abrasion resistance, and chemical immunity to hot acids.',
            misubaApp: 'Ceramic lining tiles for slurry pipes & chutes, ceramic pump sleeves, spray nozzles, and cyclone separator linings.',
            mech: { elasticModulus: 370, poissonRatio: 0.22, shearModulus: 152, density: 3900, yieldStrength: 300, tensileStrength: 350, elongation: 0, hardness: '1650 HV' },
            thermal: { conductivity: 30, specificHeat: 880, expansionCoeff: 8.0, maxTemp: 1600, minTemp: -200 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'silicon-carbide',
            name: 'Silicon Carbide (Sintered SiC)',
            category: 'Ceramics, Refractories & Gaskets',
            standard: 'ASTM C1286 / ISO 20507',
            desc: 'Extreme hardness ceramic (Mohs 9.5) with outstanding thermal shock resistance and diamond-like wear resistance in slurry applications.',
            misubaApp: 'Mechanical seal faces, slurry pump sleeves, blast nozzles, and high-temp heat exchanger tubes.',
            mech: { elasticModulus: 410, poissonRatio: 0.14, shearModulus: 180, density: 3150, yieldStrength: 390, tensileStrength: 450, elongation: 0, hardness: '2500 HV' },
            thermal: { conductivity: 120, specificHeat: 670, expansionCoeff: 4.0, maxTemp: 1400, minTemp: -200 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'frp-vinylester',
            name: 'FRP / GRP (Vinyl Ester Resin)',
            category: 'Ceramics, Refractories & Gaskets',
            standard: 'ASTM D3299 / BS 4994',
            desc: 'Fiberglass reinforced plastic composed of E-glass fibers embedded in premium vinyl ester resin for structural acid tank lining.',
            misubaApp: 'FRP chemical tank linings, FGD scrubber expansion joints, acid ductwork, and composite pipe sleeves.',
            mech: { elasticModulus: 14.5, poissonRatio: 0.28, shearModulus: 5.2, density: 1800, yieldStrength: 150, tensileStrength: 210, elongation: 2, hardness: '45 Barcol' },
            thermal: { conductivity: 0.30, specificHeat: 1200, expansionCoeff: 22.0, maxTemp: 120, minTemp: -40 },
            chem: { acids: 'Excellent', alkalis: 'Good', solvents: 'Good', oils: 'Excellent', steam: 'Fair', seawater: 'Excellent' }
        },
        {
            id: 'flex-graphite',
            name: 'Flexible Expanded Graphite',
            category: 'Ceramics, Refractories & Gaskets',
            standard: 'ASTM F104 / EN 13555',
            desc: 'Pure exfoliated graphite compressed into flexible sheeting offering zero creep relaxation, self-lubrication, and thermal resistance up to 550°C.',
            misubaApp: 'Spiral wound gasket filler, high-pressure steam valve packing rings, tanged metal reinforced graphite gaskets, and heat exchanger gaskets.',
            mech: { elasticModulus: 1.2, poissonRatio: 0.20, shearModulus: 0.5, density: 1000, yieldStrength: 5, tensileStrength: 9, elongation: 2, hardness: 'Compressibility 40%' },
            thermal: { conductivity: 140, specificHeat: 710, expansionCoeff: -0.4, maxTemp: 550, minTemp: -200 },
            chem: { acids: 'Excellent', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        },
        {
            id: 'cnaf-gasket',
            name: 'CNAF (Non-Asbestos Sheet)',
            category: 'Ceramics, Refractories & Gaskets',
            standard: 'ASTM F104 / BS 7531 Grade X',
            desc: 'Compressed aramid fiber bound with NBR rubber designed for general industrial flange sealing of water, steam, hydrocarbons, and mild chemicals.',
            misubaApp: 'Flat flange gaskets for pipe flanges (DIN/JIS/ASME), pump casing gaskets, and valve bonnet joints.',
            mech: { elasticModulus: 0.8, poissonRatio: 0.25, shearModulus: 0.3, density: 1800, yieldStrength: 8, tensileStrength: 14, elongation: 1, hardness: 'Compressibility 10%' },
            thermal: { conductivity: 0.40, specificHeat: 1050, expansionCoeff: 25, maxTemp: 250, minTemp: -50 },
            chem: { acids: 'Fair', alkalis: 'Good', solvents: 'Good', oils: 'Excellent', steam: 'Good', seawater: 'Excellent' }
        },
        {
            id: 'mica-gasket',
            name: 'Mica Sheet (High Temp Gasket)',
            category: 'Ceramics, Refractories & Gaskets',
            standard: 'ASTM F104 / IEC 60371',
            desc: 'Phlogopite mica paper impregnated with high-temperature silicone binder capable of continuous service up to 1000°C.',
            misubaApp: 'Ultra-high temperature gas turbine flange gaskets, burner gaskets, furnace expansion joint gaskets, and exhaust manifolds.',
            mech: { elasticModulus: 2.5, poissonRatio: 0.22, shearModulus: 1.0, density: 2100, yieldStrength: 12, tensileStrength: 25, elongation: 1, hardness: 'Compressibility 12%' },
            thermal: { conductivity: 0.45, specificHeat: 800, expansionCoeff: 10.0, maxTemp: 1000, minTemp: -100 },
            chem: { acids: 'Good', alkalis: 'Excellent', solvents: 'Excellent', oils: 'Excellent', steam: 'Excellent', seawater: 'Excellent' }
        }
    ];

    let currentSelectedId = 'ss316l';
    let currentUnitSystem = 'SI';
    let currentSelectedCategoryPill = 'ALL';

    const categoryBadgeColors = {
        'Carbon & Low Alloy Steels': { bg: '#e0f2fe', color: '#0369a1' },
        'Austenitic & Stainless Steels': { bg: '#ccfbf1', color: '#0f766e' },
        'Duplex & Super Duplex': { bg: '#e0e7ff', color: '#4338ca' },
        'Nickel & High-Temp Alloys': { bg: '#f3e8ff', color: '#6b21a8' },
        'Non-Ferrous Alloys (Copper, Brass, Alu)': { bg: '#fef3c7', color: '#b45309' },
        'Titanium & Special Reactive Metals': { bg: '#ffe4e6', color: '#be123c' },
        'Elastomers & Rubbers': { bg: '#dcfce7', color: '#15803d' },
        'Fluoropolymers & Engineering Plastics': { bg: '#cffafe', color: '#0e7490' },
        'Ceramics, Refractories & Gaskets': { bg: '#ffedd5', color: '#c2410c' }
    };

    document.addEventListener('DOMContentLoaded', () => {
        renderTree();
        selectMaterial(currentSelectedId);
        populateCompareSelects();

        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && document.activeElement.tagName !== 'INPUT' && document.activeElement.tagName !== 'SELECT') {
                e.preventDefault();
                document.getElementById('mat-search').focus();
            }
        });
    });

    function selectCatSelect(val) {
        currentSelectedCategoryPill = val;
        renderTree();
    }

    function clearSearch() {
        document.getElementById('mat-search').value = '';
        document.getElementById('search-clear').style.display = 'none';
        renderTree();
    }

    // Render Left Directory Tree
    function renderTree() {
        const treeContainer = document.getElementById('tree-container');
        const searchInput = document.getElementById('mat-search');
        const searchKeyword = searchInput.value.toLowerCase().trim();

        document.getElementById('search-clear').style.display = searchKeyword ? 'block' : 'none';

        let categories = [...new Set(materialsData.map(m => m.category))];
        if (currentSelectedCategoryPill !== 'ALL') {
            categories = categories.filter(c => c === currentSelectedCategoryPill);
        }

        let filteredCount = 0;
        let html = '';

        categories.forEach(cat => {
            const catMaterials = materialsData.filter(m => 
                m.category === cat && 
                (m.name.toLowerCase().includes(searchKeyword) || 
                 m.standard.toLowerCase().includes(searchKeyword) ||
                 m.desc.toLowerCase().includes(searchKeyword))
            );

            if (catMaterials.length > 0) {
                filteredCount += catMaterials.length;
                html += `
                    <div class="cat-folder">
                        <div class="cat-folder-title" onclick="toggleFolder(this)">
                            <span>📁 ${cat} (${catMaterials.length})</span>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                        <div class="cat-item-list">
                            ${catMaterials.map(m => `
                                <div class="mat-item ${m.id === currentSelectedId ? 'active' : ''}" onclick="selectMaterial('${m.id}')">
                                    <span>${m.name}</span>
                                    <span class="mat-item-standard">${m.standard.split('/')[0].trim()}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        });

        if (filteredCount === 0) {
            html = '<div style="color:#94a3b8; font-size:12.5px; text-align:center; padding:30px 10px;">No matching materials</div>';
        }

        treeContainer.innerHTML = html;
        document.getElementById('mat-count-badge').innerText = `${filteredCount} Materials`;
    }

    function toggleFolder(el) {
        el.parentElement.classList.toggle('collapsed');
    }

    function filterMaterials() {
        renderTree();
    }

    // Set Unit System (SI vs Imperial)
    function setUnitSystem(sys) {
        currentUnitSystem = sys;
        document.getElementById('unit-si').classList.toggle('active', sys === 'SI');
        document.getElementById('unit-imp').classList.toggle('active', sys === 'IMP');
        selectMaterial(currentSelectedId);
    }

    // Select & Display Material Properties
    function selectMaterial(id) {
        currentSelectedId = id;
        const mat = materialsData.find(m => m.id === id);
        if (!mat) return;

        // Highlight active item in sidebar
        document.querySelectorAll('.mat-item').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.mat-item').forEach(el => {
            if (el.getAttribute('onclick').includes(id)) el.classList.add('active');
        });

        // Fill Header
        document.getElementById('view-name').innerText = mat.name;

        const catBadge = document.getElementById('view-category');
        catBadge.innerText = mat.category;
        const colorStyle = categoryBadgeColors[mat.category] || { bg: '#ccfbf1', color: '#0f766e' };
        catBadge.style.backgroundColor = colorStyle.bg;
        catBadge.style.color = colorStyle.color;

        document.getElementById('view-standard').innerText = mat.standard;
        document.getElementById('view-desc').innerText = mat.desc;
        document.getElementById('view-misuba-app').innerText = mat.misubaApp;

        // Quick Glance Inline Summary Metric Strip
        const m = mat.mech;
        const t = mat.thermal;
        if (currentUnitSystem === 'SI') {
            document.getElementById('qc-ys').innerText = `${m.yieldStrength} MPa`;
            document.getElementById('qc-dens').innerText = `${m.density} kg/m³`;
            document.getElementById('qc-temp').innerText = `${t.maxTemp} °C`;
            document.getElementById('qc-em').innerText = `${m.elasticModulus} GPa`;
        } else {
            document.getElementById('qc-ys').innerText = `${(m.yieldStrength * 0.145038).toFixed(1)} ksi`;
            document.getElementById('qc-dens').innerText = `${(m.density * 0.000036127).toFixed(4)} lb/in³`;
            document.getElementById('qc-temp').innerText = `${(t.maxTemp * 9/5 + 32).toFixed(0)} °F`;
            document.getElementById('qc-em').innerText = `${(m.elasticModulus * 145038).toExponential(2)} psi`;
        }

        // Mechanical Table
        let mechRows = '';
        if (currentUnitSystem === 'SI') {
            mechRows = `
                <tr><td class="prop-name">Elastic Modulus (E)</td><td class="prop-val">${m.elasticModulus}</td><td class="prop-unit">GPa</td></tr>
                <tr><td class="prop-name">Poisson's Ratio (ν)</td><td class="prop-val">${m.poissonRatio}</td><td class="prop-unit">-</td></tr>
                <tr><td class="prop-name">Shear Modulus (G)</td><td class="prop-val">${m.shearModulus}</td><td class="prop-unit">GPa</td></tr>
                <tr><td class="prop-name">Mass Density (ρ)</td><td class="prop-val">${m.density}</td><td class="prop-unit">kg/m³</td></tr>
                <tr><td class="prop-name">Tensile Yield Strength (σy)</td><td class="prop-val">${m.yieldStrength}</td><td class="prop-unit">MPa</td></tr>
                <tr><td class="prop-name">Ultimate Tensile Strength (σu)</td><td class="prop-val">${m.tensileStrength}</td><td class="prop-unit">MPa</td></tr>
                <tr><td class="prop-name">Elongation at Break</td><td class="prop-val">${m.elongation}%</td><td class="prop-unit">%</td></tr>
                <tr><td class="prop-name">Material Hardness</td><td class="prop-val">${m.hardness}</td><td class="prop-unit">-</td></tr>
            `;
        } else {
            const ePsi = (m.elasticModulus * 145038).toExponential(2);
            const gPsi = (m.shearModulus * 145038).toExponential(2);
            const densLb = (m.density * 0.000036127).toFixed(4);
            const ysKsi = (m.yieldStrength * 0.145038).toFixed(1);
            const tsKsi = (m.tensileStrength * 0.145038).toFixed(1);

            mechRows = `
                <tr><td class="prop-name">Elastic Modulus (E)</td><td class="prop-val">${ePsi}</td><td class="prop-unit">psi</td></tr>
                <tr><td class="prop-name">Poisson's Ratio (ν)</td><td class="prop-val">${m.poissonRatio}</td><td class="prop-unit">-</td></tr>
                <tr><td class="prop-name">Shear Modulus (G)</td><td class="prop-val">${gPsi}</td><td class="prop-unit">psi</td></tr>
                <tr><td class="prop-name">Mass Density (ρ)</td><td class="prop-val">${densLb}</td><td class="prop-unit">lb/in³</td></tr>
                <tr><td class="prop-name">Tensile Yield Strength (σy)</td><td class="prop-val">${ysKsi}</td><td class="prop-unit">ksi</td></tr>
                <tr><td class="prop-name">Ultimate Tensile Strength (σu)</td><td class="prop-val">${tsKsi}</td><td class="prop-unit">ksi</td></tr>
                <tr><td class="prop-name">Elongation at Break</td><td class="prop-val">${m.elongation}%</td><td class="prop-unit">%</td></tr>
                <tr><td class="prop-name">Material Hardness</td><td class="prop-val">${m.hardness}</td><td class="prop-unit">-</td></tr>
            `;
        }
        document.getElementById('tbl-mech-body').innerHTML = mechRows;

        // Thermal Table
        let thermalRows = '';
        if (currentUnitSystem === 'SI') {
            thermalRows = `
                <tr><td class="prop-name">Thermal Conductivity (k)</td><td class="prop-val">${t.conductivity}</td><td class="prop-unit">W/(m·K)</td></tr>
                <tr><td class="prop-name">Specific Heat (cp)</td><td class="prop-val">${t.specificHeat}</td><td class="prop-unit">J/(kg·K)</td></tr>
                <tr><td class="prop-name">Coeff of Thermal Expansion (α)</td><td class="prop-val">${t.expansionCoeff}</td><td class="prop-unit">10⁻⁶/K</td></tr>
                <tr><td class="prop-name">Max Continuous Operating Temp</td><td class="prop-val">${t.maxTemp}</td><td class="prop-unit">°C</td></tr>
                <tr><td class="prop-name">Min Service Temperature</td><td class="prop-val">${t.minTemp}</td><td class="prop-unit">°C</td></tr>
            `;
        } else {
            const condBtu = (t.conductivity * 0.5778).toFixed(2);
            const maxF = (t.maxTemp * 9/5 + 32).toFixed(0);
            const minF = (t.minTemp * 9/5 + 32).toFixed(0);

            thermalRows = `
                <tr><td class="prop-name">Thermal Conductivity (k)</td><td class="prop-val">${condBtu}</td><td class="prop-unit">Btu/(hr·ft·°F)</td></tr>
                <tr><td class="prop-name">Specific Heat (cp)</td><td class="prop-val">${t.specificHeat}</td><td class="prop-unit">J/(kg·K)</td></tr>
                <tr><td class="prop-name">Coeff of Thermal Expansion (α)</td><td class="prop-val">${t.expansionCoeff}</td><td class="prop-unit">10⁻⁶/°F</td></tr>
                <tr><td class="prop-name">Max Continuous Operating Temp</td><td class="prop-val">${maxF}</td><td class="prop-unit">°F</td></tr>
                <tr><td class="prop-name">Min Service Temperature</td><td class="prop-val">${minF}</td><td class="prop-unit">°F</td></tr>
            `;
        }
        document.getElementById('tbl-thermal-body').innerHTML = thermalRows;

        // Chemical Resistance Table
        const c = mat.chem;
        const getRatingBadge = (val) => {
            const cls = val.toLowerCase();
            return `<span class="rating-badge rating-${cls}">${val}</span>`;
        };

        const chemRows = `
            <tr><td class="prop-name">Dilute & Concentrated Acids</td><td>${getRatingBadge(c.acids)}</td><td style="color:#64748b; font-size:12px;">Inorganic/organic acid resistance</td></tr>
            <tr><td class="prop-name">Caustic Alkalis & Bases</td><td>${getRatingBadge(c.alkalis)}</td><td style="color:#64748b; font-size:12px;">Sodium hydroxide / potassium solutions</td></tr>
            <tr><td class="prop-name">Organic Solvents & Esters</td><td>${getRatingBadge(c.solvents)}</td><td style="color:#64748b; font-size:12px;">Ketones, aromatics, alcohols</td></tr>
            <tr><td class="prop-name">Oils, Greases & Hydrocarbons</td><td>${getRatingBadge(c.oils)}</td><td style="color:#64748b; font-size:12px;">Petroleum, hydraulic fluids, diesel</td></tr>
            <tr><td class="prop-name">Saturated & Superheated Steam</td><td>${getRatingBadge(c.steam)}</td><td style="color:#64748b; font-size:12px;">High-temp boiler steam lines</td></tr>
            <tr><td class="prop-name">Seawater & Salt Spray</td><td>${getRatingBadge(c.seawater)}</td><td style="color:#64748b; font-size:12px;">Marine pitting & galvanic corrosion</td></tr>
        `;
        document.getElementById('tbl-chem-body').innerHTML = chemRows;
    }

    function switchSWTab(btn, paneId) {
        document.querySelectorAll('.sw-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.sw-tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(paneId).classList.add('active');
    }

    // Toast Notification Feedback
    function showToast(msg) {
        const toast = document.getElementById('toast-msg');
        document.getElementById('toast-text').innerText = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 2500);
    }

    // Copy Material JSON Data
    function copyMaterialJSON() {
        const mat = materialsData.find(m => m.id === currentSelectedId);
        if (!mat) return;
        navigator.clipboard.writeText(JSON.stringify(mat, null, 2)).then(() => {
            showToast(`Copied ${mat.name} specs to clipboard!`);
        });
    }

    // Comparison Modal
    function populateCompareSelects() {
        const opts = materialsData.map(m => `<option value="${m.id}">${m.name} (${m.standard.split('/')[0].trim()})</option>`).join('');
        document.getElementById('comp-mat-1').innerHTML = opts;
        document.getElementById('comp-mat-2').innerHTML = opts;
        document.getElementById('comp-mat-3').innerHTML = opts;

        document.getElementById('comp-mat-1').value = 'ss304';
        document.getElementById('comp-mat-2').value = 'ss316l';
        document.getElementById('comp-mat-3').value = 'inconel625';
    }

    function openCompareModal() {
        document.getElementById('compare-modal').classList.add('open');
        renderComparison();
    }

    function closeCompareModal() {
        document.getElementById('compare-modal').classList.remove('open');
    }

    function renderComparison() {
        const m1 = materialsData.find(m => m.id === document.getElementById('comp-mat-1').value);
        const m2 = materialsData.find(m => m.id === document.getElementById('comp-mat-2').value);
        const m3 = materialsData.find(m => m.id === document.getElementById('comp-mat-3').value);

        if (!m1 || !m2 || !m3) return;

        const html = `
            <thead>
                <tr>
                    <th>Property</th>
                    <th>${m1.name}</th>
                    <th>${m2.name}</th>
                    <th>${m3.name}</th>
                </tr>
            </thead>
            <tbody>
                <tr><td style="font-weight:700;">Standard Grade</td><td>${m1.standard}</td><td>${m2.standard}</td><td>${m3.standard}</td></tr>
                <tr><td style="font-weight:700;">Density (kg/m³)</td><td>${m1.mech.density}</td><td>${m2.mech.density}</td><td>${m3.mech.density}</td></tr>
                <tr><td style="font-weight:700;">Elastic Modulus (GPa)</td><td>${m1.mech.elasticModulus}</td><td>${m2.mech.elasticModulus}</td><td>${m3.mech.elasticModulus}</td></tr>
                <tr><td style="font-weight:700;">Yield Strength (MPa)</td><td>${m1.mech.yieldStrength}</td><td>${m2.mech.yieldStrength}</td><td>${m3.mech.yieldStrength}</td></tr>
                <tr><td style="font-weight:700;">Tensile Strength (MPa)</td><td>${m1.mech.tensileStrength}</td><td>${m2.mech.tensileStrength}</td><td>${m3.mech.tensileStrength}</td></tr>
                <tr><td style="font-weight:700;">Thermal Cond. (W/m·K)</td><td>${m1.thermal.conductivity}</td><td>${m2.thermal.conductivity}</td><td>${m3.thermal.conductivity}</td></tr>
                <tr><td style="font-weight:700;">Max Operating Temp (°C)</td><td>${m1.thermal.maxTemp}</td><td>${m2.thermal.maxTemp}</td><td>${m3.thermal.maxTemp}</td></tr>
                <tr><td style="font-weight:700;">Seawater Resistance</td><td>${m1.chem.seawater}</td><td>${m2.chem.seawater}</td><td>${m3.chem.seawater}</td></tr>
                <tr><td style="font-weight:700;">Acid Resistance</td><td>${m1.chem.acids}</td><td>${m2.chem.acids}</td><td>${m3.chem.acids}</td></tr>
            </tbody>
        `;
        document.getElementById('compare-table').innerHTML = html;
    }
</script>
@endsection
