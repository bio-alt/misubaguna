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

<div class="toolkit-grid">
    <!-- Flange Standards Card -->
    <a href="{{ route('public.toolkit.flange-standards') }}" class="toolkit-card">
        <div class="toolkit-icon">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
        </div>
        <h3>Flange Standards</h3>
        <p>Comprehensive dimensions and bolt hole data for DIN, JIS, and ASME flange standards.</p>
    </a>

    <!-- Add more cards in the future here -->
</div>
@endsection
