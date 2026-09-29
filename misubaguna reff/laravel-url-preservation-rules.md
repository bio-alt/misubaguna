# Laravel URL Preservation / Redirect Plan — misubaguna.com

Generated: 2026-06-17 15:49:22 UTC

## Goal

Move WordPress/Elementor to Laravel without losing SEO by preserving existing public URLs and only redirecting legacy/broken URLs intentionally.

## Files created

- `laravel-url-preservation-map.csv` — full URL-by-URL route/preservation plan
- `laravel-redirect-map.csv` — redirect/special handling list only

## Recommended decisions

### Preserve as 200 pages

Preserve all main pages and product pages exactly:

```text
/
/about-us/
/project-list/
/contact-us/
/career/
/product/fabric-expansion-joint/
/product/metal-expansion-joint/
/product/rubber-expansion-joint/
/product/ptfe-expansion-joint/
/product/ptfe-lined-rubber-metal-expansion-joint/
/product/rubber-hose/
/product/flexible-metal-hose/
/product/ptfe-lining/
/product/rubber-lining/
/product/ceramic-lining/
/product/frp-lining/
/product/ceramic-coating/
/product/ptfe-coating/
/product/gland-packing/
/product/oil-seal/
/product/o-ring/
/product/gasket-sheet/
/product/mechanical-seal/
/product/coupling/
```

### Preserve or improve category pages

Current category archive pages should either be preserved with useful content or intentionally noindexed/redirected after checking Search Console:

```text
/product-category/corrosion-protection/
/product-category/coating/
/product-category/rubber-hose/
/product-category/ptfe-expansion-joint/
/product-category/sealing-system/
/product-category/gasket-sheet/
```

Recommended now: preserve them first during migration. Improve/noindex later after traffic data check. Do not delete them during launch.

### Redirect legacy page

```text
/indexpage/  ->  /  [301]
```

Reason: looks like old duplicate homepage content. Check Google Search Console first, then redirect.

### Repair catalog

```text
/catalog/  ->  keep as 200 page
```

Reason: Catalog is in navigation. Build a clean Laravel catalog page with PDF/company profile download. Do not launch it broken.

### Preserve PDF asset

```text
/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf
```

Best: copy this PDF into the same public Laravel path so the old URL still works as 200.
Fallback: 301 redirect to the new PDF URL.

## Laravel route structure suggestion

Use dynamic CMS-backed routes for products/categories instead of hardcoding every product page.

```php
use App\Http\Controllers\PublicSite\HomeController;
use App\Http\Controllers\PublicSite\PageController;
use App\Http\Controllers\PublicSite\ProductController;
use App\Http\Controllers\PublicSite\ProductCategoryController;
use App\Http\Controllers\PublicSite\ProjectController;
use App\Http\Controllers\PublicSite\ContactController;
use App\Http\Controllers\PublicSite\CareerController;
use App\Http\Controllers\PublicSite\CatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('public.home');

Route::get('/about-us/', [PageController::class, 'about'])->name('public.about');
Route::get('/project-list/', [ProjectController::class, 'index'])->name('public.projects.index');
Route::get('/contact-us/', [ContactController::class, 'index'])->name('public.contact');
Route::get('/career/', [CareerController::class, 'index'])->name('public.career');
Route::get('/catalog/', [CatalogController::class, 'index'])->name('public.catalog');

Route::get('/product/{slug}/', [ProductController::class, 'show'])->name('public.products.show');
Route::get('/product-category/{slug}/', [ProductCategoryController::class, 'show'])->name('public.product-categories.show');

// Legacy duplicate page. Apply after Search Console check.
Route::redirect('/indexpage/', '/', 301);
```

## Trailing slash rule

The current WordPress URLs use trailing slashes. Laravel must preserve this in canonical URLs and sitemap.

Important: test Laravel route matching on your server. If `/about-us/` does not match correctly, add web-server or middleware handling carefully. Do not create both `/about-us` and `/about-us/` as separate indexable pages.

Preferred canonical style:

```text
https://misubaguna.com/product/fabric-expansion-joint/
```

Non-canonical without slash should 301 to slash:

```text
/product/fabric-expansion-joint -> /product/fabric-expansion-joint/
```

## Launch rule

Before replacing WordPress:

1. Crawl old site.
2. Crawl Laravel staging.
3. Compare every URL in `laravel-url-preservation-map.csv`.
4. Confirm all preserved pages return 200.
5. Confirm `/indexpage/` returns 301 only after approval.
6. Confirm `/catalog/` is not broken.
7. Confirm the old PDF URL returns 200 or 301.
8. Submit new sitemap in Search Console.

