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

Route::get('/sitemap.xml', function () {
    $pages = PublicPage::where('is_published', true)->where('sitemap_include', true)->get();
    $products = PublicProduct::where('is_published', true)->where('sitemap_include', true)->get();
    $categories = PublicProductCategory::where('is_published', true)->where('sitemap_include', true)->get();
    $services = PublicService::where('is_published', true)->where('sitemap_include', true)->get();

    return response()->view('public.sitemap', compact('pages', 'products', 'categories', 'services'))
        ->header('Content-Type', 'application/xml');
})->name('public.sitemap');

Route::get('/robots.txt', function () {
    return response()->view('public.robots')
        ->header('Content-Type', 'text/plain');
})->name('public.robots');

Route::redirect('/indexpage', '/', 301);
Route::redirect('/indexpage/', '/', 301);
