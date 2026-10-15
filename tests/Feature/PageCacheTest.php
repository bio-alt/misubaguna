<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** A cached page view must not open a MySQL connection (Hostinger caps them at 500 an hour). */
class PageCacheTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['cache.page_ttl' => 600]);

        foreach (['home' => '/', 'contact' => '/contact-us/'] as $slug => $path) {
            PublicPage::create([
                'slug' => $slug, 'url_path' => $path, 'title' => ucfirst($slug), 'is_published' => true,
                'sitemap_include' => true, 'sitemap_priority' => '0.8', 'sitemap_changefreq' => 'monthly',
            ]);
        }
    }

    public function test_second_view_is_served_from_cache_without_queries(): void
    {
        $first = $this->get('/')->assertOk();
        $this->assertFalse($first->headers->has('X-Page-Cache'));

        DB::enableQueryLog();
        $second = $this->get('/')->assertOk()->assertHeader('X-Page-Cache', 'HIT');

        $this->assertSame([], DB::getQueryLog());
        $this->assertSame($first->getContent(), $second->getContent());
    }

    public function test_robots_txt_needs_no_query_once_redirects_are_cached(): void
    {
        $this->get('/robots.txt')->assertOk();

        DB::enableQueryLog();
        $this->get('/robots.txt')->assertOk();
        $this->assertSame([], DB::getQueryLog());
    }

    public function test_form_pages_and_query_strings_are_never_cached(): void
    {
        $this->get('/contact-us/')->assertOk();
        $this->get('/contact-us/')->assertOk()->assertHeaderMissing('X-Page-Cache');

        $this->get('/?utm_source=x')->assertOk();
        $this->get('/?utm_source=x')->assertOk()->assertHeaderMissing('X-Page-Cache');
    }
}
