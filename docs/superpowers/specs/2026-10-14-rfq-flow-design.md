# Product-aware quote request (RFQ) flow — design

Date: 2026-10-14
Status: approved in brainstorming, awaiting spec review

## Goal

Turn product-page traffic into complete, never-lost quote requests. Today every
"Request a quote" button lands on a blank `/contact-us/` form, the product's
quote checklist is display-only, drawings cannot be attached, and inquiries are
emailed but not stored.

Success: a buyer on any product page reaches a form that already knows the
product, answers that product's checklist, attaches drawings, and receives a
reference number; sales receives a complete email; every request is stored in
the database even when mail fails.

## Decisions (from brainstorming)

| Topic | Decision |
|---|---|
| Form location | Dedicated page `/request-a-quote/?product={slug}` |
| Checklist | One optional text input per `ProductContent::quoteChecklist()` item |
| Uploads | Up to 3 files, 10 MB each: pdf, dwg, dxf, jpg, png, xlsx, docx |
| After submit | Thank-you page with reference + buyer confirmation email |
| Storage | One `inquiries` table for quote and contact submissions |
| Admin UI | None (out of scope); sales works from email |

## Routes and entry points

- `GET /request-a-quote/` — query params `product` (optional slug) and `item`
  (optional free text, max 160 chars). Unknown or unpublished slug → generic form.
- `POST /request-a-quote/` — throttled (`throttle:5,10,quote`), redirects to the
  thank-you page.
- `GET /request-a-quote/thanks/{reference}` — 404 for an unknown reference.
  Shows reference, product name and the checklist answers only, never contact
  details.
- `GET /inquiries/files/{inquiry}/{index}` — signed route (`URL::temporarySignedRoute`,
  60 days) that streams a stored file as a download. Used in the internal email.
- Indonesian mirrors `/id/request-a-quote/` and `/id/request-a-quote/thanks/{reference}`
  use the same controller and view with `app()->setLocale('id')`; the form posts
  to `/id/request-a-quote/`.
- SEO: quote and thank-you pages are `noindex,follow`, have a canonical and a
  BreadcrumbList, and are excluded from `sitemap.xml` and `llms*.txt`.

Entry points rewired to the quote page:

- `public/partials/quote-actions.blade.php` → `?product={slug}` (the partial
  receives the slug alongside `subject`).
- `public/partials/product-card.blade.php` → `?product={slug}`.
- `public/pages/product.blade.php` bottom CTA (line ~269) and
  `public/pages/category.blade.php` CTA → quote page (category passes no product).
- `public/partials/coupling-range.blade.php` → `?product={family slug}&item={series code + name}`.
- `public/toolkit/expansion-joint-selector.blade.php` "Request a Quote" →
  quote page with `item` set to the selector's recommendation summary.
- `public/services/index.blade.php` and `public/services/show.blade.php` quote
  buttons → quote page with `item` = service name.
- Indonesian product pages link to `/id/request-a-quote/?product=…`.
- `/contact-us/` stays the general enquiry page and gains a short
  "Need a price? Request a quote →" link. Navigation, home, material-specs and
  chemical-resistance links are unchanged.

## Page and form

Follows the catalog design rules (teal `#176b70`, `public/css/catalog.css`
tokens, compact type, no `<header>` element inside content, no red/orange).

1. Product strip (only with a valid product): thumbnail, name, category, link
   back to the product page.
2. Contact: name, company, email, phone/WhatsApp (required, same limits as the
   contact form), delivery location (optional, max 160).
3. Product / model: text, prefilled from `item` or the product name; required
   only when no product is given; max 160.
4. Checklist: one optional text input (max 500) per checklist item, labelled
   with the item text. Without a product, `QUOTE_DEFAULT` is used. Submitted as
   `answers[n]`; the server re-derives labels from the checklist, never trusts
   posted labels.
5. Anything else: optional textarea, max 5000.
6. Files: `files[]`, max 3, each `max:10240`, mimes pdf, dwg, dxf, jpg, jpeg,
   png, xlsx, docx. DWG/DXF are validated by extension because their MIME
   detection is unreliable.
7. Honeypot `website` field, hidden like the other forms.
8. Primary button "Send quote request"; secondary WhatsApp button
   (`ProductContent::whatsappUrl`).

Rule: at least one checklist answer, one file, or a non-empty message is
required. Error text: "Please answer at least one question, attach a drawing,
or describe what you need."

On validation failure, text input is preserved (`withInput()`); the error
summary notes that files must be attached again.

All UI strings go through `__()` with Indonesian entries in `lang/id.json`.
Checklist labels are translated through `__()` as well; untranslated labels
fall back to English.

## Data: `inquiries` table

| Column | Type | Notes |
|---|---|---|
| id | bigint | |
| kind | string(16) | `quote` or `contact`, indexed |
| reference | string(20), nullable, unique | `Q-YYYY-XXXXXX`, quotes only |
| product_slug | string, nullable | |
| product_name | string, nullable | snapshot at submit time |
| item | string(160), nullable | product / model field |
| name, company, email, phone | string | |
| location | string(160), nullable | |
| category | string(80), nullable | contact form dropdown |
| answers | json, nullable | `[{label, value}]`, empty answers dropped |
| message | text, nullable | |
| files | json, nullable | `[{path, name, size}]` |
| locale | string(5) | `en` or `id` |
| source_url | string(500), nullable | referrer path if same host |
| ip_address | string(45), nullable | |
| emailed_at | timestamp, nullable | internal email sent |
| confirmed_at | timestamp, nullable | buyer confirmation sent |
| timestamps | | |

Model `App\Models\Inquiry` with array casts for `answers`/`files` and
`nextReference()`, using the same random-suffix approach as
`EjmaCalculation::nextReference()` (`Q-` prefix, 6 chars, unique retry).

Files are stored on the `local` disk at
`inquiries/YYYY/MM/{Str::random(24)}.{ext}`; original names are kept in
`files` (trimmed to 180 chars).

## Submit flow (`QuoteRequestController@store`)

1. Honeypot filled → redirect to `/request-a-quote/` with a generic success
   flash (no row created).
2. Validate (rules above).
3. Store files, then create the `Inquiry` (`kind=quote`) with a new reference.
4. `Mail::to(config('mail.contact_to'))->send(new QuoteRequestMail($inquiry))`,
   set `emailed_at`; on exception `report()` and continue.
5. `Mail::to($inquiry->email)->send(new QuoteConfirmationMail($inquiry))` in the
   inquiry's locale, set `confirmed_at`; on exception `report()` and continue.
6. Redirect to the (locale-matching) thank-you page.

### Internal email `QuoteRequestMail`

- Subject: `Quote request {reference} — {product or item} — {company}`.
- Reply-To: buyer email and name.
- Body: contact details, product with link, checklist answers table, message,
  source page, locale, and a list of files with signed 60-day download links.
- Attachments: all files attached when their total size ≤ 15 MB; otherwise none
  attached and the email says to use the links.

### Buyer confirmation `QuoteConfirmationMail`

- Subject: `We received your quote request {reference}` (translated).
- Body: reference, product, short answer summary, "our sales team will review
  it and reply" (no promised turnaround), WhatsApp link prefilled with
  "Hello, about quote {reference}". No attachments.

### Thank-you page

Reference prominently, product, answers summary, WhatsApp button prefilled with
the reference, link back to the product or catalog.

## Contact form change

`ContactController@store` creates an `Inquiry` (`kind=contact`, no reference,
`category` and `message` filled, `locale=en`) before sending the existing
`ContactInquiry` mail, then sets `emailed_at` on success. Mail failure keeps
today's visible error message. No other behaviour changes.

## Error handling

- Unknown/unpublished product slug → generic form, no error.
- Over-long `item` → truncated to 160 for display and storage.
- Mail failures → logged with `report()`; the inquiry stays stored with null
  `emailed_at`/`confirmed_at`.
- Signed file route → 403 on invalid/expired signature, 404 on missing index or
  missing file.

## Testing (feature tests, PHPUnit, existing test DB setup)

- Page renders the slug-specific, group-specific and default checklist.
- Unknown slug renders the generic form.
- Valid submit stores the row and files, sends both mails (`Mail::fake`,
  `Storage::fake`), redirects to the thank-you page with the reference.
- "At least one" rule rejects an empty request.
- File count, size and type limits.
- Honeypot creates no row and sends no mail.
- Mail exception still stores the row and redirects to the thank-you page.
- Thank-you page 404s on an unknown reference and hides contact details.
- Signed file route serves the file; tampered or expired signature → 403.
- `/id/request-a-quote/` renders Indonesian labels and stores `locale=id`.
- Contact form submission now creates a `kind=contact` row.
- Product page and coupling partial link to `/request-a-quote/?product=…`.

## Out of scope

Admin panel or inbox UI, CRM sync, quote status tracking, multi-product
baskets, file retention/cleanup jobs.

## Deployment notes

- Requires `php artisan migrate` on the live server (deploy script runs it).
- Check the live PHP `upload_max_filesize` ≥ 10M and `post_max_size` ≥ 32M;
  adjust via Hostinger PHP settings if lower.
