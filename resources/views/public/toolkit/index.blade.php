@extends('public.layout')

@section('title', 'Engineering Toolkit | PT Misuba Guna Indonesia')
@section('meta_description', 'Access comprehensive engineering tables, sizing guides, and technical references.')

@section('content')
<style>
    .toolkit-hero {
        background-color: #f8fafc;
        padding: 80px 32px 40px;
        text-align: center;
        border-bottom: 1px solid #e5e7eb;
    }

    .toolkit-hero h1 {
        font-size: 40px;
        font-weight: 800;
        color: #111827;
        margin-bottom: 16px;
    }

    .toolkit-hero p {
        font-size: 18px;
        color: #4b5563;
        max-width: 600px;
        margin: 0 auto;
    }

    .toolkit-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 32px;
        max-width: 1280px;
        margin: 60px auto;
        padding: 0 32px;
    }

    .toolkit-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 32px;
        text-align: center;
        transition: all 0.3s ease;
        text-decoration: none;
        display: block;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    }

    .toolkit-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-color: #2b9d9f;
    }

    .toolkit-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 24px;
        background: #f0fdfa;
        color: #0d9488;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .toolkit-icon svg {
        width: 32px;
        height: 32px;
    }

    .toolkit-card h3 {
        font-size: 20px;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 12px;
    }

    .toolkit-card p {
        font-size: 15px;
        color: #4b5563;
        margin-bottom: 0;
        line-height: 1.6;
    }
</style>

<div class="toolkit-hero">
    <h1>Engineering Toolkit</h1>
    <p>Comprehensive engineering tables, sizing guides, and technical references to support your industrial projects.</p>
</div>

<h2 class="visually-hidden">Engineering tools</h2>
<div class="toolkit-grid">
    <!-- Flange Standards Card -->
    <a href="/toolkit/flange-standards/" class="toolkit-card">
        <div class="toolkit-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <h3>Flange Standards</h3>
        <p>Comprehensive dimensions and bolt hole data for DIN, JIS, and ASME flange standards.</p>
    </a>

    <!-- Scientific Calculator Card -->
    <a href="/toolkit/calculator/" class="toolkit-card">
        <div class="toolkit-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
        </div>
        <h3>Scientific Calculator</h3>
        <p>Full-featured scientific calculator with trigonometric, logarithmic, memory functions, calculation history, and unit conversion tools.</p>
    </a>

    <!-- Material Specs Library Card -->
    <a href="/toolkit/material-specs/" class="toolkit-card">
        <div class="toolkit-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h3>Material Specs Library</h3>
        <p>Mechanical, thermal, and chemical resistance data for 55 steels, alloys, elastomers, plastics, and ceramics, with side-by-side comparison.</p>
    </a>

    <!-- Expansion Joint Selector Card -->
    <a href="/toolkit/expansion-joint-selector/" class="toolkit-card">
        <div class="toolkit-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h4m10 0h4M7 7v10m10-10v10M9.5 7l1.25 2.5L9.5 12l1.25 2.5L9.5 17m5-10l-1.25 2.5L14.5 12l-1.25 2.5L14.5 17" />
            </svg>
        </div>
        <h3>Expansion Joint Selector</h3>
        <p>Calculate pipe thermal expansion between anchors and get the right rubber, PTFE, metal, or fabric expansion joint for your conditions.</p>
    </a>

    <!-- Chemical Resistance Guide Card -->
    <a href="/toolkit/chemical-resistance/" class="toolkit-card">
        <div class="toolkit-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3h6m-5 0v6.5L4.5 19a1.5 1.5 0 001.3 2.25h12.4A1.5 1.5 0 0019.5 19L14 9.5V3M7 15h10" />
            </svg>
        </div>
        <h3>Chemical Resistance Guide</h3>
        <p>Check PTFE, EPDM, NBR, Viton, Hypalon, 316SS, ceramic, and FRP compatibility against 40+ industrial chemicals.</p>
    </a>

    <!-- EJMA Bellows Calculator Card -->
    <a href="/toolkit/ejma-bellows-calculator/" class="toolkit-card">
        <div class="toolkit-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h2m14 0h2M5 7v10m14-10v10M8 7v10m3-10v10m3-10v10m3-10v10" />
            </svg>
        </div>
        <h3>EJMA Bellows Calculator</h3>
        <p>Check a metal bellows expansion joint design against EJMA Standards for unreinforced, reinforced and toroidal bellows, reviewed by our engineers.</p>
    </a>
</div>
@endsection
