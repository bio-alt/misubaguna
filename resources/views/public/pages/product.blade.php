@extends('public.layout')

@section('content')
<style>
/* Compact Professional Catalog Styles */
.catalog-container { 
    max-width: 1280px; 
    margin: 20px auto 40px; 
    padding: 0 24px; 
    font-family: 'Plus Jakarta Sans', sans-serif; 
}
.catalog-breadcrumb { 
    margin-bottom: 12px; 
    font-size: 0.8rem; 
    color: #6b7280; 
    font-weight: 500;
}
.catalog-breadcrumb a { 
    color: #1a5276; 
    text-decoration: none; 
    transition: color 0.2s;
}
.catalog-breadcrumb a:hover { 
    color: #e67e22; 
}

.catalog-header { 
    display: grid; 
    grid-template-columns: 380px 1fr; 
    gap: 24px; 
    margin-bottom: 24px; 
    align-items: start;
}
.catalog-image-placeholder {
    width: 100%; 
    aspect-ratio: 4 / 3; 
    background: #f9fafb; 
    border: 1px dashed #d1d5db; 
    border-radius: 8px;
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: center;
    color: #9ca3af; 
    font-weight: 600;
    overflow: hidden;
    position: relative;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}
.catalog-image-placeholder img {
    max-width: 60%;
    opacity: 0.15;
    margin-bottom: 10px;
    filter: grayscale(100%);
    object-fit: contain;
}
.catalog-image-placeholder span {
    position: absolute;
    bottom: 12px;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.catalog-details h1 { 
    font-size: 1.6rem; 
    color: #111827; 
    margin-bottom: 8px; 
    font-weight: 800; 
    line-height: 1.2; 
    letter-spacing: -0.01em;
}
.catalog-details .product-summary { 
    font-size: 0.875rem; 
    line-height: 1.5; 
    color: #4b5563; 
    margin-bottom: 14px; 
}

.btn-inquire {
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    padding: 8px 20px; 
    background: #e67e22; 
    color: #fff; 
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase; 
    letter-spacing: 0.06em; 
    border-radius: 6px;
    text-decoration: none; 
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(230, 126, 34, 0.2);
}
.btn-inquire:hover { 
    background: #d35400; 
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(230, 126, 34, 0.3);
}

.catalog-body {
    background: #fff; 
    border: 1px solid #e5e7eb; 
    border-radius: 10px;
    padding: 20px 24px; 
    box-shadow: 0 4px 16px rgba(0,0,0,0.02); 
    margin-bottom: 32px;
}

.product-narrative-content {
    margin-bottom: 20px; 
    font-size: 0.85rem; 
    line-height: 1.5; 
    color: #374151;
}
.product-narrative-content h1 {
    font-size: 1.25rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 6px 0;
}
.product-narrative-content h2, 
.product-narrative-content h3 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #1f2937;
    margin: 10px 0 4px 0;
}
.product-narrative-content p {
    margin-bottom: 8px;
    font-size: 0.85rem;
    line-height: 1.5;
}

.catalog-section { margin-bottom: 18px; }
.catalog-section:last-child { margin-bottom: 0; }
.catalog-section h2 {
    font-size: 1rem; 
    color: #111827; 
    margin-bottom: 10px; 
    font-weight: 800;
    border-bottom: 2px solid #e67e22; 
    display: inline-block; 
    padding-bottom: 3px;
}
.catalog-section ul {
    list-style: none; 
    padding: 0; 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); 
    gap: 6px 14px;
}
.catalog-section ul li {
    padding-left: 20px; 
    position: relative; 
    color: #374151; 
    line-height: 1.45; 
    font-size: 0.825rem;
    font-weight: 500;
}
.catalog-section ul li::before {
    content: '✓'; 
    position: absolute; 
    left: 0; 
    top: -1px; 
    color: #1a5276; 
    font-weight: 900; 
    font-size: 0.85rem;
}

@media (max-width: 992px) {
    .catalog-header { grid-template-columns: 1fr; gap: 20px; }
    .catalog-image-placeholder { aspect-ratio: 16 / 9; }
}
@media (max-width: 768px) {
    .catalog-section ul { grid-template-columns: 1fr; }
    .catalog-body { padding: 16px; }
}
</style>

<div class="catalog-container">
    <div class="catalog-breadcrumb">
        <a href="/">Home</a> &gt; <a href="/catalog">Products</a> &gt; {{ $product->title ?? $product->name }}
    </div>

    <div class="catalog-header">
        @if($product->gallery && count($product->gallery) > 0)
            <div class="catalog-gallery">
                <div class="main-image-container" style="width: 100%; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; background: #fff;">
                    <img id="mainProductImage" src="{{ asset($product->gallery[0]) }}" alt="{{ $product->title ?? $product->name }}" style="width: 100%; display: block; object-fit: contain; aspect-ratio: 4 / 3; transition: opacity 0.2s;">
                </div>
                @if(count($product->gallery) > 1)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 8px; margin-top: 8px;">
                    @foreach($product->gallery as $index => $img)
                        <div style="border-radius: 6px; overflow: hidden; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; transition: transform 0.1s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="document.getElementById('mainProductImage').style.opacity=0; setTimeout(() => { document.getElementById('mainProductImage').src = '{{ asset($img) }}'; document.getElementById('mainProductImage').style.opacity=1; }, 150);">
                            <img src="{{ asset($img) }}" alt="{{ $product->title ?? $product->name }} Thumbnail {{ $index + 1 }}" style="width: 100%; display: block; object-fit: cover; aspect-ratio: 1 / 1;">
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        @else
            <div class="catalog-image-placeholder">
                <img src="https://misubaguna.com/wp-content/uploads/2021/07/2021-04-20-18_55_17-Window.jpg" alt="Misuba Guna Placeholder">
                <span>Product Image Placeholder</span>
            </div>
        @endif
        
        <div class="catalog-details">
            <h1>{{ $product->title ?? $product->name }}</h1>
            
            @if($product->summary)
                <div class="product-summary">
                    {{ $product->summary }}
                </div>
            @endif

            <a href="/contact-us" class="btn-inquire">Request a Quote</a>
        </div>
    </div>

    <div class="catalog-body">
        @if($product->content_html)
            <div class="product-narrative-content">
                {!! $product->content_html !!}
            </div>
        @endif

        @if($product->key_features && count($product->key_features) > 0)
        <div class="catalog-section">
            <h2>Key Features and Benefits</h2>
            <ul>
                @foreach($product->key_features as $feature)
                    <li>{{ $feature }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($product->applications && count($product->applications) > 0)
        <div class="catalog-section">
            <h2>Applications</h2>
            <ul>
                @foreach($product->applications as $app)
                    <li>{{ $app }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($product->materials && count($product->materials) > 0)
        <div class="catalog-section">
            <h2>Construction and Material</h2>
            <ul>
                @foreach($product->materials as $material)
                    <li>{{ $material }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($product->technical_specs && count($product->technical_specs) > 0)
        <div class="catalog-section">
            <h2>Technical Notes</h2>
            <ul>
                @foreach($product->technical_specs as $spec)
                    <li>{{ $spec }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Compact Technical Engineering Support CTA Card -->
        <div class="engineering-consultation-card" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 8px; padding: 18px 22px; color: #fff; margin-top: 20px; position: relative; overflow: hidden; box-shadow: 0 6px 16px rgba(0,0,0,0.12);">
            <div style="position: relative; z-index: 2;">
                <div style="display: inline-block; background: #e67e22; color: #fff; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; padding: 4px 10px; border-radius: 12px; margin-bottom: 8px;">
                    Technical Support
                </div>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #ffffff; margin-bottom: 6px; font-family: 'Plus Jakarta Sans', sans-serif;">
                    Need Sizing, Drawings, or a Custom Quote for {{ $product->title ?? $product->name }}?
                </h3>
                <p style="font-size: 0.825rem; color: #cbd5e1; line-height: 1.45; max-width: 800px; margin-bottom: 12px;">
                    Our technical engineering team provides tailored consultation, CAD verification, and rapid pricing. Contact us with your line size, pressure, temperature, and media parameters.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                    <a href="/contact-us" class="btn-inquire" style="background: #e67e22; color: #ffffff; padding: 7px 16px; font-size: 0.775rem;">
                        Contact Engineering Team
                    </a>
                    <a href="https://wa.me/6281119253388?text=Hello%20PT%20Misuba%20Guna%20Indonesia,%20I%20would%20like%20to%20inquire%20about%20{{ urlencode($product->title ?? $product->name) }}" target="_blank" style="display: inline-flex; align-items: center; justify-content: center; padding: 7px 16px; background: #25d366; color: #fff; font-size: 0.775rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; border-radius: 6px; text-decoration: none; transition: all 0.2s ease;">
                        <svg style="width: 16px; height: 16px; margin-right: 6px; fill: currentColor;" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        WhatsApp Inquiry
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
