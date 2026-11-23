<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    public function test_baseline_headers_on_https(): void
    {
        $response = $this->get('https://localhost/robots.txt');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000');
    }

    public function test_no_hsts_over_plain_http(): void
    {
        $this->get('http://localhost/robots.txt')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_robots_no_longer_lists_nonexistent_admin_paths(): void
    {
        $body = $this->get('/robots.txt')->getContent();

        $this->assertStringNotContainsString('/admin/', $body);
        $this->assertStringNotContainsString('/filament/', $body);
    }
}
