<?php

use App\Http\Controllers\PublicSite\CareerController;
use App\Http\Controllers\PublicSite\CatalogController;
use App\Http\Controllers\PublicSite\ContactController;
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
Route::get('/career/', [CareerController::class, 'index'])->name('public.career');
Route::get('/catalog/', [CatalogController::class, 'index'])->name('public.catalog');

use App\Http\Controllers\PublicSite\ServiceController;
use App\Http\Controllers\PublicSite\ToolkitController;
use App\Models\PublicSite\PublicService;

Route::get('/product/{slug}/', [ProductController::class, 'show'])->name('public.products.show');
Route::get('/product-category/{slug}/', [ProductCategoryController::class, 'show'])->name('public.product-categories.show');

Route::get('/services/', [ServiceController::class, 'index'])->name('public.services.index');
Route::get('/services/{slug}/', [ServiceController::class, 'show'])->name('public.services.show');

Route::get('/toolkit/', [ToolkitController::class, 'index'])->name('public.toolkit.index');
Route::get('/toolkit/flange-standards/', [ToolkitController::class, 'flangeStandards'])->name('public.toolkit.flange-standards');
Route::get('/toolkit/calculator/', [ToolkitController::class, 'calculator'])->name('public.toolkit.calculator');
Route::get('/toolkit/material-specs/', [ToolkitController::class, 'materialSpecs'])->name('public.toolkit.material-specs');

Route::get('/sitemap.xml', function () {
    $pages = PublicPage::where('is_published', true)->where('sitemap_include', true)->get();
    $products = PublicProduct::where('is_published', true)->where('sitemap_include', true)->get();
    $categories = PublicProductCategory::where('is_published', true)->where('sitemap_include', true)->get();
    $services = PublicService::where('is_published', true)->where('sitemap_include', true)->get();

    $toolkits = [
        ['loc' => '/toolkit/', 'priority' => '0.8', 'changefreq' => 'weekly'],
        ['loc' => '/toolkit/flange-standards/', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => '/toolkit/calculator/', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['loc' => '/toolkit/material-specs/', 'priority' => '0.8', 'changefreq' => 'monthly'],
    ];

    return response()->view('public.sitemap', compact('pages', 'products', 'categories', 'services', 'toolkits'))
        ->header('Content-Type', 'application/xml');
})->name('public.sitemap');

Route::get('/robots.txt', function () {
    return response()->view('public.robots')
        ->header('Content-Type', 'text/plain; charset=utf-8');
})->name('public.robots');

Route::get('/llms.txt', function () {
    $file = public_path('llms.txt');
    if (file_exists($file)) {
        return response(file_get_contents($file), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
    abort(404);
})->name('public.llms');

Route::get('/llms-full.txt', function () {
    $file = public_path('llms-full.txt');
    if (file_exists($file)) {
        return response(file_get_contents($file), 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }
    abort(404);
})->name('public.llms-full');

Route::redirect('/indexpage', '/', 301);
Route::redirect('/indexpage/', '/', 301);
