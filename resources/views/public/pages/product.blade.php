@extends('public.layout')

@section('content')
<style>
/* Professional Catalog Styles */
.catalog-container { 
    max-width: 1280px; 
    margin: 60px auto; 
    padding: 0 32px; 
    font-family: 'Plus Jakarta Sans', sans-serif; 
}
.catalog-breadcrumb { 
    margin-bottom: 24px; 
    font-size: 0.9rem; 
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
    grid-template-columns: 1fr 1fr; 
    gap: 60px; 
    margin-bottom: 60px; 
    align-items: start;
}
.catalog-image-placeholder {
    width: 100%; 
    aspect-ratio: 4 / 3; 
    background: #f9fafb; 
    border: 1px dashed #d1d5db; 
    border-radius: 12px;
    display: flex; 
    flex-direction: column; 
    align-items: center; 
    justify-content: center;
    color: #9ca3af; 
    font-weight: 600;
    overflow: hidden;
    position: relative;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
}
.catalog-image-placeholder img {
    max-width: 70%;
    opacity: 0.15;
    margin-bottom: 16px;
    filter: grayscale(100%);
    object-fit: contain;
}
.catalog-image-placeholder span {
    position: absolute;
    bottom: 24px;
    font-size: 0.9rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
}

.catalog-details h1 { 
    font-size: 2.75rem; 
    color: #111827; 
    margin-bottom: 24px; 
    font-weight: 800; 
    line-height: 1.2; 
    letter-spacing: -0.02em;
}
.catalog-details .product-summary { 
    font-size: 1.15rem; 
    line-height: 1.8; 
    color: #4b5563; 
    margin-bottom: 36px; 
}

.btn-inquire {
    display: inline-flex; 
    align-items: center; 
    justify-content: center;
    padding: 16px 36px; 
    background: #e67e22; 
    color: #fff; 
    font-weight: 700;
    text-transform: uppercase; 
    letter-spacing: 0.08em; 
    border-radius: 8px;
    text-decoration: none; 
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(230, 126, 34, 0.2);
}
.btn-inquire:hover { 
    background: #d35400; 
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(230, 126, 34, 0.3);
}

.catalog-body {
    background: #fff; 
    border: 1px solid #e5e7eb; 
    border-radius: 12px;
    padding: 48px; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.03); 
    margin-bottom: 60px;
}

.catalog-section { margin-bottom: 48px; }
.catalog-section:last-child { margin-bottom: 0; }
.catalog-section h2 {
    font-size: 1.5rem; 
    color: #111827; 
    margin-bottom: 24px; 
    font-weight: 800;
    border-bottom: 3px solid #e67e22; 
    display: inline-block; 
    padding-bottom: 10px;
}
.catalog-section ul {
    list-style: none; 
    padding: 0; 
    display: grid; 
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); 
    gap: 20px;
}
.catalog-section ul li {
    padding-left: 32px; 
    position: relative; 
    color: #374151; 
    line-height: 1.6; 
    font-size: 1rem;
    font-weight: 500;
}
.catalog-section ul li::before {
    content: '✓'; 
    position: absolute; 
    left: 0; 
    top: -2px; 
    color: #1a5276; 
    font-weight: 900; 
    font-size: 1.2rem;
}

.placeholder-text {
    padding: 24px 32px; 
    background: #f8fafc; 
    border-left: 4px solid #1a5276;
    color: #475569; 
    font-style: italic; 
    border-radius: 0 8px 8px 0; 
    margin-bottom: 48px;
    font-size: 1.05rem;
    line-height: 1.7;
}

@media (max-width: 992px) {
    .catalog-header { grid-template-columns: 1fr; gap: 40px; }
    .catalog-image-placeholder { aspect-ratio: 16 / 9; }
}
@media (max-width: 768px) {
    .catalog-section ul { grid-template-columns: 1fr; }
    .catalog-body { padding: 32px 24px; }
}
</style>

<div class="catalog-container">
    <div class="catalog-breadcrumb">
        <a href="/">Home</a> &gt; <a href="/catalog">Products</a> &gt; {{ $product->title ?? $product->name }}
    </div>

    <div class="catalog-header">
        @if($product->gallery && count($product->gallery) > 0)
            <div class="catalog-gallery">
                <div class="main-image-container" style="width: 100%; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; background: #fff;">
                    <img id="mainProductImage" src="{{ asset($product->gallery[0]) }}" alt="{{ $product->title ?? $product->name }}" style="width: 100%; display: block; object-fit: contain; aspect-ratio: 4 / 3; transition: opacity 0.2s;">
                </div>
                @if(count($product->gallery) > 1)
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 12px; margin-top: 12px;">
                    @foreach($product->gallery as $index => $img)
                        <div style="border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; background: #fff; cursor: pointer; transition: transform 0.1s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'" onclick="document.getElementById('mainProductImage').style.opacity=0; setTimeout(() => { document.getElementById('mainProductImage').src = '{{ asset($img) }}'; document.getElementById('mainProductImage').style.opacity=1; }, 150);">
                            <img src="{{ asset($img) }}" alt="{{ $product->title ?? $product->name }} Thumbnail {{ $index + 1 }}" style="width: 100%; display: block; object-fit: cover; aspect-ratio: 1 / 1;">
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        @else
            <div class="catalog-image-placeholder">
                <!-- Using the Misuba Guna logo as a placeholder reference -->
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
        <div class="placeholder-text">
            [Placeholder: Insert detailed operational context, manufacturing standards, and specific dimensions or variants available for this product line. This text area is reserved for comprehensive product documentation.]
        </div>

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
    </div>
</div>
@endsection
