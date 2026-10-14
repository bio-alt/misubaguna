<?php

use App\Http\Controllers\PublicSite\CareerController;
use App\Http\Controllers\PublicSite\CatalogController;
use App\Http\Controllers\PublicSite\ContactController;
use App\Http\Controllers\PublicSite\EjmaCalculatorController;
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\PageController;
use App\Http\Controllers\PublicSite\ProductCategoryController;
use App\Http\Controllers\PublicSite\ProductController;
use App\Http\Controllers\PublicSite\ProjectController;
use App\Models\PublicSite\PublicPage;
use App\Models\PublicSite\PublicProduct;
use App\Models\PublicSite\PublicProductCategory;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('public.home');

Route::get('/about-us/', [PageController::class, 'about'])->name('public.about');
Route::get('/project-list/', [ProjectController::class, 'index'])->name('public.projects.index');
Route::get('/contact-us/', [ContactController::class, 'index'])->name('public.contact');
Route::post('/contact-us/', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('public.contact.store');
Route::get('/career/', [CareerController::class, 'index'])->name('public.career');
Route::post('/career/', [CareerController::class, 'store'])->middleware('throttle:5,10,career')->name('public.career.store');
Route::get('/request-a-quote/', [\App\Http\Controllers\PublicSite\QuoteRequestController::class, 'create'])->name('public.quote.create');
Route::get('/id/request-a-quote/', [\App\Http\Controllers\PublicSite\QuoteRequestController::class, 'create'])->name('public.id.quote.create');
Route::get('/catalog/',[CatalogController::class, 'index'])->name('public.catalog');

use App\Http\Controllers\PublicSite\ServiceController;
use App\Http\Controllers\PublicSite\ToolkitController;
use App\Models\PublicSite\PublicService;

Route::get('/product/{slug}/', [ProductController::class, 'show'])->name('public.products.show');

// Indonesian versions of the most-searched product pages (content: resources/content/id/products).
Route::get('/id/', [\App\Http\Controllers\PublicSite\IndonesianProductController::class, 'index'])->name('public.id.index');
Route::get('/id/product/{slug}/', [\App\Http\Controllers\PublicSite\IndonesianProductController::class, 'show'])->name('public.id.products.show');
Route::get('/product-category/{slug}/', [ProductCategoryController::class, 'show'])->name('public.product-categories.show');

Route::get('/services/', [ServiceController::class, 'index'])->name('public.services.index');
Route::get('/services/{slug}/', [ServiceController::class, 'show'])->name('public.services.show');

Route::get('/toolkit/', [ToolkitController::class, 'index'])->name('public.toolkit.index');
Route::get('/toolkit/flange-standards/', [ToolkitController::class, 'flangeStandards'])->name('public.toolkit.flange-standards');
Route::get('/toolkit/calculator/', [ToolkitController::class, 'calculator'])->name('public.toolkit.calculator');
Route::get('/toolkit/material-specs/', [ToolkitController::class, 'materialSpecs'])->name('public.toolkit.material-specs');
Route::get('/toolkit/ejma-bellows-calculator/', [EjmaCalculatorController::class, 'index'])->name('public.toolkit.ejma-calculator');
Route::post('/toolkit/ejma-bellows-calculator/', [EjmaCalculatorController::class, 'store'])->middleware('throttle:30,60,ejma')->name('public.toolkit.ejma-calculator.store');
Route::get('/toolkit/expansion-joint-selector/', [ToolkitController::class, 'expansionJointSelector'])->name('public.toolkit.expansion-joint-selector');
Route::get('/toolkit/chemical-resistance/', [ToolkitController::class, 'chemicalResistance'])->name('public.toolkit.chemical-resistance');

Route::get('/sitemap.xml', function () {
    $pages = PublicPage::where('is_published', true)->where('sitemap_include', true)->get();
    $products = PublicProduct::where('is_published', true)->where('sitemap_include', true)->get();
    $categories = PublicProductCategory::publishedWithProducts()->where('sitemap_include', true);
    $services = PublicService::where('is_published', true)->where('sitemap_include', true)->get();

    // lastmod comes from the view file, so it only moves when the page itself changes.
    $toolkits = collect([
        '/toolkit/' => 'index',
        '/toolkit/flange-standards/' => 'flange-standards',
        '/toolkit/calculator/' => 'calculator',
        '/toolkit/material-specs/' => 'material-specs',
        '/toolkit/ejma-bellows-calculator/' => 'ejma-bellows-calculator',
        '/toolkit/expansion-joint-selector/' => 'expansion-joint-selector',
        '/toolkit/chemical-resistance/' => 'chemical-resistance',
    ])->map(fn ($view, $loc) => [
        'loc' => $loc,
        'lastmod' => date('Y-m-d', @filemtime(resource_path("views/public/toolkit/{$view}.blade.php")) ?: time()),
        'priority' => '0.8',
        'changefreq' => 'monthly',
    ])->values()->all();

    $idProducts = \App\Support\IndonesianPages::products();

    return response()->view('public.sitemap', compact('pages', 'products', 'categories', 'services', 'toolkits', 'idProducts'))
        ->header('Content-Type', 'application/xml');
})->name('public.sitemap');

Route::get('/robots.txt', function () {
    return response()->view('public.robots')
        ->header('Content-Type', 'text/plain; charset=utf-8');
})->name('public.robots');

// On the live host the static copies in public/ (built by `php artisan seo:llms`) are served first;
// these routes build the same text on the fly wherever no static file exists.
Route::get('/llms.txt', fn () => response(\App\Support\LlmsText::short(), 200, ['Content-Type' => 'text/plain; charset=utf-8']))
    ->name('public.llms');

Route::get('/llms-full.txt', fn () => response(\App\Support\LlmsText::full(), 200, ['Content-Type' => 'text/plain; charset=utf-8']))
    ->name('public.llms-full');

Route::redirect('/indexpage', '/', 301);
Route::redirect('/indexpage/', '/', 301);
