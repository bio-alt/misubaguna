<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicProduct;
use App\Support\IndonesianPages;
use App\Support\QuoteRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRequestSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_url_builds_locale_aware_links(): void
    {
        $this->assertSame('/request-a-quote/?product=rubber-expansion-joint', QuoteRequest::url('rubber-expansion-joint'));
        $this->assertSame('/request-a-quote/?item=LK+Series', QuoteRequest::url(null, 'LK Series'));
        $this->assertSame('/request-a-quote/', QuoteRequest::url());

        app()->setLocale('id');
        $this->assertSame('/id/request-a-quote/?product=x', QuoteRequest::url('x'));
        $this->assertSame('/id/request-a-quote/thanks/Q-2026-ABCDEF/', QuoteRequest::thanksUrl('Q-2026-ABCDEF', 'id'));
        $this->assertSame('/request-a-quote/thanks/Q-2026-ABCDEF/', QuoteRequest::thanksUrl('Q-2026-ABCDEF', 'en'));
    }

    public function test_product_ignores_unknown_and_unpublished(): void
    {
        $this->product('draft-joint', 'Flexible Joint', ['is_published' => false]);
        $this->product('live-joint');

        $this->assertNull(QuoteRequest::product('nope'));
        $this->assertNull(QuoteRequest::product(null));
        $this->assertNull(QuoteRequest::product('draft-joint'));
        $this->assertSame('live-joint', QuoteRequest::product('live-joint')?->slug);
    }

    public function test_checklist_by_slug_group_and_default(): void
    {
        $fabric = $this->product('fabric-expansion-joint');
        $other = $this->product('foo', 'Flexible Joint');

        $this->assertSame('Duct size and shape (round or rectangular)', QuoteRequest::checklist($fabric)[0]);
        $this->assertSame('Pipe or duct size and end connection', QuoteRequest::checklist($other)[0]);
        $this->assertSame('Size or dimensions (or a drawing)', QuoteRequest::checklist(null)[0]);
    }

    public function test_indonesian_checklist_comes_from_content_file(): void
    {
        $slug = IndonesianPages::slugs()[0];
        $this->product($slug);
        app()->setLocale('id');

        $product = QuoteRequest::product($slug);

        $this->assertSame(IndonesianPages::content($slug)['name'], $product->name);
        $this->assertSame(IndonesianPages::content($slug)['quote_checklist'], QuoteRequest::checklist($product));
    }

    private function product(string $slug, string $group = 'Flexible Joint', array $extra = []): PublicProduct
    {
        return PublicProduct::updateOrCreate(['slug' => $slug], array_merge([
            'url_path' => '/product/'.$slug.'/',
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'product_group' => $group,
            'summary' => 'English summary.',
            'is_published' => true,
        ], $extra));
    }
}
