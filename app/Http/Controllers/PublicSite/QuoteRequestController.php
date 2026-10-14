<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Mail\QuoteConfirmation;
use App\Mail\QuoteRequest as QuoteRequestMail;
use App\Models\Inquiry;
use App\Models\PublicSite\PublicProduct;
use App\Support\IndonesianPages;
use App\Support\ProductContent;
use App\Support\PublicSeo;
use App\Support\QuoteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * The quote request page (/request-a-quote/ and /id/request-a-quote/): a form that knows
 * the product, asks its quote checklist and takes drawings.
 */
class QuoteRequestController extends Controller
{
    private const MAX_FILES = 3;

    private const MAX_FILE_KB = 10240;

    private const FILE_EXTENSIONS = ['pdf', 'dwg', 'dxf', 'jpg', 'jpeg', 'png', 'xlsx', 'docx'];

    public function create(Request $request)
    {
        $locale = $this->useLocale($request);
        $product = QuoteRequest::product($request->query('product'));
        $checklist = QuoteRequest::checklist($product);
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $prefill = [
            'item' => Str::limit((string) $request->query('item', $product?->name ?? ''), 160, ''),
            'message' => Str::limit((string) $request->query('message', ''), 2000, ''),
        ];

        $breadcrumbs = $this->breadcrumbs($locale, $product);
        $path = QuoteRequest::url();

        $seo = [
            'meta_title' => PublicSeo::title($product
                ? __('Request a quote: :name', ['name' => $product->name])
                : __('Request a quote')),
            'meta_description' => __('Send your sizes, operating conditions and drawings. PT Misuba Guna Indonesia will check the details and quote.'),
            'canonical_url' => $path,
            'robots_index' => false,
            'robots_follow' => true,
            'og_locale' => $locale === 'id' ? 'id_ID' : 'en_US',
            'alternates' => ['en' => QuoteRequest::PATH, 'id' => IndonesianPages::PREFIX.QuoteRequest::PATH],
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
            ],
        ];

        return view('public.pages.quote', [
            'seo' => $seo,
            'siteSettings' => $siteSettings,
            'breadcrumbs' => $breadcrumbs,
            'product' => $product,
            'productUrl' => $product ? $this->productUrl($locale, $product->slug, $product->url_path) : null,
            'checklist' => $checklist,
            'prefill' => $prefill,
            'formAction' => $path,
            'sourceUrl' => $this->sameHostReferrerPath($request),
            'waUrl' => ProductContent::whatsappUrl($product?->name ?? __('your products')),
        ]);
    }

    public function store(Request $request)
    {
        $locale = $this->useLocale($request);

        // Honeypot: bots fill the hidden "website" field, humans never see it.
        if ($request->filled('website')) {
            return redirect()->to(QuoteRequest::url())->with('quote_received', true);
        }

        $product = QuoteRequest::product($request->input('product'));
        $checklist = QuoteRequest::checklist($product);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'location' => ['nullable', 'string', 'max:160'],
            'item' => [$product ? 'nullable' : 'required', 'string', 'max:160'],
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:500'],
            'message' => ['nullable', 'string', 'max:5000'],
            'files' => ['nullable', 'array', 'max:'.self::MAX_FILES],
            'files.*' => ['file', 'max:'.self::MAX_FILE_KB, 'extensions:'.implode(',', self::FILE_EXTENSIONS)],
            'source_url' => ['nullable', 'string', 'max:500'],
        ], [
            'files.max' => __('Attach up to 3 files.'),
            'files.*.max' => __('Each file must be 10 MB or smaller.'),
            'files.*.extensions' => __('Files must be PDF, DWG, DXF, JPG, PNG, XLSX or DOCX.'),
            'files.*.uploaded' => __('A file could not be uploaded. Use files of 10 MB or smaller.'),
        ]);

        $answers = [];
        foreach ($checklist as $i => $label) {
            $value = trim((string) ($request->input("answers.$i") ?? ''));
            if ($value !== '') {
                $answers[] = ['label' => $label, 'value' => $value];
            }
        }

        $validator->after(function ($validator) use ($answers, $request) {
            if ($answers === [] && ! $request->hasFile('files') && trim((string) $request->input('message')) === '') {
                $validator->errors()->add('answers', __('Please answer at least one question, attach a drawing, or describe what you need.'));
            }
        });

        $data = $validator->validate();

        $files = [];
        foreach ($request->file('files', []) as $upload) {
            $files[] = [
                'path' => $upload->storeAs(
                    'inquiries/'.now()->format('Y/m'),
                    Str::random(24).'.'.strtolower($upload->getClientOriginalExtension()),
                    'local'
                ),
                'name' => Str::limit($upload->getClientOriginalName(), 180, ''),
                'size' => (int) $upload->getSize(),
            ];
        }

        $sourceUrl = (string) ($data['source_url'] ?? '');

        $inquiry = Inquiry::create([
            'kind' => Inquiry::KIND_QUOTE,
            'reference' => Inquiry::nextReference(),
            'product_slug' => $product?->slug,
            'product_name' => $product?->name,
            'item' => $data['item'] ?? null,
            'name' => $data['name'],
            'company' => $data['company'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'location' => $data['location'] ?? null,
            'answers' => $answers,
            'message' => trim((string) ($data['message'] ?? '')) ?: null,
            'files' => $files,
            'locale' => $locale,
            'source_url' => str_starts_with($sourceUrl, '/') ? $sourceUrl : null,
            'ip_address' => $request->ip(),
        ]);

        // The request is already stored, so mail failures are reported, not shown to the buyer.
        try {
            Mail::to(config('mail.contact_to'))->send(new QuoteRequestMail($inquiry));
            $inquiry->forceFill(['emailed_at' => now()])->save();
        } catch (\Throwable $e) {
            report($e);
        }

        try {
            Mail::to($inquiry->email)->send(new QuoteConfirmation($inquiry));
            $inquiry->forceFill(['confirmed_at' => now()])->save();
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->to(QuoteRequest::thanksUrl($inquiry->reference, $locale));
    }

    public function thanks(Request $request, string $reference)
    {
        $locale = $this->useLocale($request);
        $inquiry = Inquiry::where('kind', Inquiry::KIND_QUOTE)->where('reference', $reference)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();

        $productUrl = $inquiry->product_slug
            ? $this->productUrl($locale, $inquiry->product_slug, PublicProduct::where('slug', $inquiry->product_slug)->value('url_path'))
            : null;

        $breadcrumbs = [
            ['name' => $locale === 'id' ? 'Beranda' : 'Home', 'url' => '/'],
            ['name' => __('Request a quote'), 'url' => QuoteRequest::url()],
            ['name' => $inquiry->reference, 'url' => QuoteRequest::thanksUrl($inquiry->reference, $locale)],
        ];

        $seo = [
            'meta_title' => PublicSeo::title(__('Quote request received')),
            'meta_description' => __('Your quote request has been received by PT Misuba Guna Indonesia.'),
            'canonical_url' => QuoteRequest::url(),
            'robots_index' => false,
            'robots_follow' => true,
            'og_locale' => $locale === 'id' ? 'id_ID' : 'en_US',
            'schema_json' => [
                PublicSeo::organizationSchema($siteSettings),
                PublicSeo::breadcrumbSchema($breadcrumbs),
            ],
        ];

        return view('public.pages.quote-thanks', [
            'seo' => $seo,
            'siteSettings' => $siteSettings,
            'breadcrumbs' => $breadcrumbs,
            'inquiry' => $inquiry,
            'productUrl' => $productUrl,
            'waUrl' => ProductContent::whatsappUrl(__('quote request :reference', ['reference' => $inquiry->reference])),
        ]);
    }

    /** A stored file, reached through the signed link in the sales email. */
    public function download(Inquiry $inquiry, int $index)
    {
        $file = $inquiry->files[$index] ?? null;
        abort_unless($file && Storage::disk('local')->exists($file['path']), 404);

        return Storage::disk('local')->download($file['path'], $file['name']);
    }

    /** /id/... routes render in Indonesian; everything else in English. */
    private function useLocale(Request $request): string
    {
        $locale = $request->is('id/*') ? 'id' : 'en';
        app()->setLocale($locale);

        return $locale;
    }

    /** The page the buyer came from, as a path, when it is on this site. */
    private function sameHostReferrerPath(Request $request): string
    {
        $referrer = (string) $request->headers->get('referer', '');

        if (parse_url($referrer, PHP_URL_HOST) !== $request->getHost()) {
            return '';
        }

        return Str::limit((string) parse_url($referrer, PHP_URL_PATH), 500, '');
    }

    private function breadcrumbs(string $locale, $product): array
    {
        $crumbs = [['name' => $locale === 'id' ? 'Beranda' : 'Home', 'url' => '/']];

        if ($product) {
            $crumbs[] = ['name' => $product->name, 'url' => $this->productUrl($locale, $product->slug, $product->url_path)];
        }

        $crumbs[] = ['name' => __('Request a quote'), 'url' => QuoteRequest::url()];

        return $crumbs;
    }

    private function productUrl(string $locale, string $slug, ?string $urlPath): string
    {
        if ($locale === 'id' && IndonesianPages::has($slug)) {
            return IndonesianPages::url($slug);
        }

        return PublicSeo::path($urlPath ?: '/product/'.$slug.'/');
    }
}
