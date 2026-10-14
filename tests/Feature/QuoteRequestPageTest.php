<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicProduct;
use App\Support\IndonesianPages;
use App\Support\PublicSeo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteRequestPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_product_strip_and_its_checklist(): void
    {
        $this->product('fabric-expansion-joint');

        $this->get('/request-a-quote/?product=fabric-expansion-joint')
            ->assertOk()
            ->assertSee('Fabric Expansion Joint')
            ->assertSee('<label for="qr-answer-0">Duct size and shape (round or rectangular)</label>', false)
            ->assertSee('name="product" value="fabric-expansion-joint"', false)
            ->assertSee('name="answers[0]"', false)
            ->assertSee('href="/product/fabric-expansion-joint/"', false);
    }

    public function test_without_product_shows_default_checklist_and_required_item(): void
    {
        $html = $this->get('/request-a-quote/')->assertOk()
            ->assertSee('Size or dimensions (or a drawing)')
            ->getContent();

        $this->assertMatchesRegularExpression('/<input[^>]*name="item"[^>]*required/', $html);
    }

    public function test_unknown_product_falls_back_to_generic_form(): void
    {
        $this->get('/request-a-quote/?product=nope')
            ->assertOk()
            ->assertSee('Size or dimensions (or a drawing)')
            ->assertDontSee('name="product" value="nope"', false);
    }

    public function test_page_is_noindex_with_canonical(): void
    {
        $this->get('/request-a-quote/?product=x')
            ->assertOk()
            ->assertSee('noindex', false)
            ->assertSee('<link rel="canonical" href="'.PublicSeo::canonicalUrl('/request-a-quote/').'">', false);
    }

    public function test_prefill_is_escaped_and_truncated(): void
    {
        $item = '<script>x</script>'.str_repeat('a', 500);
        $message = str_repeat('m', 3000);

        $html = $this->get('/request-a-quote/?'.http_build_query(['item' => $item, 'message' => $message]))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>x', $html);
        preg_match('/name="item"[^>]*value="([^"]*)"/', $html, $m);
        $this->assertLessThanOrEqual(160, mb_strlen(html_entity_decode($m[1] ?? '')));
        $this->assertStringContainsString(str_repeat('m', 2000), $html);
        $this->assertStringNotContainsString(str_repeat('m', 2001), $html);
    }

    public function test_indonesian_page_is_in_indonesian(): void
    {
        $this->product('rubber-expansion-joint');
        $label = IndonesianPages::content('rubber-expansion-joint')['quote_checklist'][0];

        $this->get('/id/request-a-quote/?product=rubber-expansion-joint')
            ->assertOk()
            ->assertSee('<html lang="id">', false)
            ->assertSee(e($label), false)
            ->assertSee('action="/id/request-a-quote/"', false);
    }

    private function product(string $slug, string $group = 'Flexible Joint'): PublicProduct
    {
        return PublicProduct::updateOrCreate(['slug' => $slug], [
            'url_path' => '/product/'.$slug.'/',
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'product_group' => $group,
            'summary' => 'English summary.',
            'is_published' => true,
        ]);
    }
}
