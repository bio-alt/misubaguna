<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\PublicSite\PublicPage;
use App\Support\PublicSeo;
use App\Mail\ContactInquiry;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function index()
    {
        $page = PublicPage::where('url_path', '/contact-us/')->where('is_published', true)->firstOrFail();
        $siteSettings = DB::table('site_settings')->pluck('value', 'key')->toArray();
        $seo = PublicSeo::buildSeoData($page, $siteSettings);

        $breadcrumbs = [
            ['name' => 'Home', 'url' => '/'],
            ['name' => 'Contact Us', 'url' => '/contact-us/'],
        ];

        $contactPageSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'ContactPage',
            'name' => 'Contact PT Misuba Guna Indonesia',
            'description' => 'Get in touch with PT Misuba Guna Indonesia for industrial engineering inquiries, expansion joint sizing, quotes, and plant maintenance services.',
            'url' => PublicSeo::canonicalUrl('/contact-us/'),
            'mainEntity' => PublicSeo::localBusinessSchema($siteSettings),
        ];

        $seo['schema_json'] = [
            PublicSeo::organizationSchema($siteSettings),
            $contactPageSchema,
            PublicSeo::breadcrumbSchema($breadcrumbs),
        ];

        return view('public.pages.contact', compact('page', 'seo', 'siteSettings', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        // Honeypot: bots fill the hidden "website" field, humans never see it.
        if ($request->filled('website')) {
            return redirect()->to('/contact-us/#inquiry-form')->with('contact_success', true);
        }

        $inquiry = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'phone' => ['required', 'string', 'max:40'],
            'category' => ['required', 'string', 'max:80'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        // Stored first, so the inquiry survives a mail failure.
        $stored = Inquiry::create($inquiry + [
            'kind' => Inquiry::KIND_CONTACT,
            'locale' => 'en',
            'ip_address' => $request->ip(),
        ]);

        try {
            Mail::to(config('mail.contact_to'))->send(new ContactInquiry($inquiry));
            $stored->forceFill(['emailed_at' => now()])->save();
        } catch (\Throwable $e) {
            report($e);

            return redirect()->to('/contact-us/#inquiry-form')->withInput()
                ->with('contact_error', 'Sorry, your inquiry could not be sent right now. Please email marketing@misubaguna.com or contact us on WhatsApp.');
        }

        return redirect()->to('/contact-us/#inquiry-form')->with('contact_success', true);
    }
}