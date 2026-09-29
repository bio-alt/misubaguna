# Laravel SEO Data Structure Scaffold — misubaguna.com

Generated: 2026-06-17 15:52:37 UTC

## What this contains

This scaffold creates the minimum Laravel data structure needed to rebuild the WordPress/Elementor public site without losing SEO.

Included:

- migrations for public pages, products, product categories, redirects, media assets, and site settings
- model stubs under `App\Models\PublicSite`
- `PublicSiteSeeder` preloaded from the current SEO baseline
- Blade SEO partial for title/meta/canonical/robots/OG/schema
- route snippet preserving WordPress URL structure
- support helper for basic Organization/Product schema

## Tables

### public_pages
For homepage, about, project list, contact, career, catalog, and future static pages.

### public_products
For all product pages such as fabric expansion joint, metal expansion joint, rubber lining, mechanical seal, coupling, etc.

### public_product_categories
For archive/category URLs such as `/product-category/corrosion-protection/`.

### public_redirects
For old URL handling such as `/indexpage/ -> /`.

### public_media_assets
For images, PDFs, Open Graph images, and alt text control.

### site_settings
For company name, email, phone, WhatsApp, addresses, and schema-level settings.

## Installation steps

1. Copy files into the new Laravel project.
2. Register or copy the route snippet into `routes/web.php`.
3. Run migrations:

```bash
php artisan migrate
```

4. Run the public site seeder:

```bash
php artisan db:seed --class=PublicSiteSeeder
```

5. Include the SEO partial in the public layout inside `<head>`:

```blade
@include('public.partials.seo', ['seo' => $seo])
```

6. Before launch, crawl all URLs and compare against `laravel-url-preservation-map.csv`.

## Important

The content_html fields are intentionally empty. They should be filled from the current WordPress pages after content extraction/cleanup.

Do not delete `/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf`. Keep that URL working or 301 redirect it.

Do not enable `/indexpage/` redirect until Search Console confirms there is no special traffic/backlink reason to keep it.

