# Public Site Content Import Notes

This pack is the next migration step after the SEO schema scaffold.

What is included:

- public-site-content-import.csv
- PublicSiteContentSeeder.php
- AGENT_PROMPT_CONTENT_IMPORT.md

The content is cleaned and structured for Laravel, not a blind copy of Elementor output. That is intentional: Elementor navigation/footer duplication should not be migrated into content_html.

Manual review required for:

- contact-us: billing address conflict must be resolved
- career: old/dummy vacancy content must be cleaned
- catalog: current navigation points to catalog; Laravel must make it a valid 200 page
- ceramic-lining, frp-lining, ceramic-coating, ptfe-coating: current crawl output was limited/noisy, confirm final content before launch
- gasket-sheet: previous baseline flagged possible duplicate/repeated heading/content

Run order:

1. Run migration scaffold and PublicSiteSeeder first.
2. Copy PublicSiteContentSeeder.php into database/seeders.
3. Run:

```bash
php artisan db:seed --class=PublicSiteContentSeeder
```

4. Review rows marked needs_manual_review=true in public-site-content-import.csv.
