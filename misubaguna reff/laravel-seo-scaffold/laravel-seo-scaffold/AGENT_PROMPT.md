You are implementing the public Laravel website rebuild for misubaguna.com with SEO preservation as the highest priority.

Use the scaffold files provided in laravel-seo-scaffold.zip.

Objective:
Create the Laravel database structure for the public company website, preserving current WordPress URL structure and SEO metadata.

Rules:
1. Do not change working app logic outside the public website module.
2. Keep all public URLs from the SEO baseline exactly the same, including trailing slash canonical style.
3. Use server-rendered Blade pages, not an SPA.
4. Main page content must be visible in raw HTML.
5. Add migrations for:
   - public_pages
   - public_products
   - public_product_categories
   - public_redirects
   - public_media_assets
   - site_settings
6. Add model stubs under App\Models\PublicSite.
7. Add PublicSiteSeeder to preload URL paths and SEO fields from the baseline.
8. Add SEO Blade partial for:
   - title
   - meta description
   - canonical
   - robots
   - Open Graph
   - Twitter card
   - JSON-LD schema
9. Add route structure for:
   - /
   - /about-us/
   - /project-list/
   - /contact-us/
   - /career/
   - /catalog/
   - /product/{slug}/
   - /product-category/{slug}/
10. Keep /indexpage/ redirect disabled until Search Console check. Prepare it as a 301 candidate only.
11. Preserve the indexed PDF URL under /wp-content/uploads/2023/11/ if possible.
12. Do not invent new URL slugs.
13. Do not delete category archive URLs during first migration.

After implementation, report:
- migrations added
- tables created
- seeded row counts
- exact routes added
- any issue with trailing slash routing
- any URL that cannot be preserved
