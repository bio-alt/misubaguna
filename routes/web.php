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

Route::get('/product/{slug}/', [ProductController::class, 'show'])->name('public.products.show');
Route::get('/product-category/{slug}/', [ProductCategoryController::class, 'show'])->name('public.product-categories.show');

Route::get('/sitemap.xml', function () {
    $pages = PublicPage::where('is_published', true)->where('sitemap_include', true)->get();
    $products = PublicProduct::where('is_published', true)->where('sitemap_include', true)->get();
    $categories = PublicProductCategory::where('is_published', true)->where('sitemap_include', true)->get();

    return response()->view('public.sitemap', compact('pages', 'products', 'categories'))
        ->header('Content-Type', 'application/xml');
})->name('public.sitemap');

Route::get('/robots.txt', function () {
    return response()->view('public.robots')
        ->header('Content-Type', 'text/plain');
})->name('public.robots');

Route::redirect('/indexpage', '/', 301);
Route::redirect('/indexpage/', '/', 301);
