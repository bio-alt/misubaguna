# SEO Migration Baseline Notes — misubaguna.com

Generated: 2026-06-17 15:40:24 UTC

## What this is

This is a practical SEO baseline for the WordPress/Elementor → Laravel migration. It was built from accessible web search/fetch output and known public URLs discovered for misubaguna.com.

## Important limitation

This is **not** a full raw Screaming Frog crawl. The current execution environment cannot perform a direct raw HTTP crawl from the container, so these fields are intentionally marked as not measured where exact browser/raw HTML access is required:

- exact HTTP status code for every URL
- final redirect chain
- canonical tag
- robots meta tag
- exact word count
- image count and missing-alt count
- internal/external link count
- Open Graph tags
- JSON-LD/schema detection
- page size
- load time

Use this file as the migration control list first. Before launch, validate it with Screaming Frog or a server-side crawler.

## URL count

- Total rows: 33
- Core/regular pages: 6
- Product pages: 19
- Product category archives: 6
- Catalog page needing manual verification: 1
- PDF asset: 1

## Key SEO risks to fix before Laravel launch

1. Preserve every current product URL exactly, including trailing slash.
2. Homepage counters are rendered correctly in the browser, but crawler text extraction saw animated fallback zero values. In Laravel, output real counter values in server-rendered HTML.
3. Contact page title has a spacing typo: `withPT`.
4. Contact page billing address conflicts with footer/homepage: contact page says STC Senayan Lantai 4 No 80; footer/homepage says Lantai 2 No 89.
5. Footer typo: `Warehosuse`.
6. About page typos: `Our Mision`, `costumer’s`, `global manufactures`.
7. Career page may contain stale/dummy vacancy content: Due Date 31/01/2025, Test, Jkt.
8. `/indexpage/` appears to be a legacy public page. It may create duplicate/obsolete content. Decide whether to 301 redirect to `/` or noindex/canonicalize it.
9. `/catalog/` must be manually checked. Navigation contains Catalog; prior click returned internal error in browse environment.
10. Product category archive pages may be thin/duplicated. Decide index/noindex based on SEO strategy; if indexed, add useful intro copy.
11. Preserve or redirect the indexed company profile PDF URL under `/wp-content/uploads/...`.

## Laravel migration rule

For every row in `seo-baseline-current.csv`:

- Keep the same URL where possible.
- If changing URL is unavoidable, create a 301 redirect.
- Server-render the content in Blade.
- Keep title, meta description, H1/H2, canonical, image alt text, and internal links.
- Generate sitemap.xml and robots.txt.
- Confirm no page has accidental `noindex`.

