<?php

namespace Tests\Feature;

use App\Models\PublicSite\PublicPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IndexNowSubmitTest extends TestCase
{
    use RefreshDatabase;

    private string $statePath;

    private const KEY = '59b88577051bfb5bed0b46ba44913fde';

    protected function setUp(): void
    {
        parent::setUp();

        $this->statePath = sys_get_temp_dir().'/indexnow-state-'.uniqid().'.json';
        config([
            'services.indexnow.state_path' => $this->statePath,
            'services.indexnow.key' => self::KEY,
            'app.url' => 'https://misubaguna.com',
        ]);

        PublicPage::create([
            'slug' => 'home',
            'url_path' => '/',
            'title' => 'Home',
            'is_published' => true,
            'sitemap_include' => true,
        ]);
        PublicPage::create([
            'slug' => 'about',
            'url_path' => '/about/',
            'title' => 'About',
            'is_published' => true,
            'sitemap_include' => true,
        ]);

    }

    protected function tearDown(): void
    {
        @unlink($this->statePath);
        parent::tearDown();
    }

    private function sentUrls(): array
    {
        $urls = [];
        foreach (Http::recorded() as [$request]) {
            $urls[] = $request->data()['urlList'];
        }

        return $urls;
    }

    public function test_first_run_submits_sitemap_urls_with_correct_payload(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $this->artisan('indexnow:submit')->assertSuccessful();

        Http::assertSentCount(1);
        Http::assertSent(function (Request $r) {
            $d = $r->data();

            return $r->url() === 'https://api.indexnow.org/indexnow'
                && str_contains($r->header('Content-Type')[0], 'application/json')
                && $d['host'] === 'misubaguna.com'
                && $d['key'] === self::KEY
                && $d['keyLocation'] === 'https://misubaguna.com/'.self::KEY.'.txt'
                && in_array('https://misubaguna.com/', $d['urlList'], true)
                && in_array('https://misubaguna.com/about/', $d['urlList'], true);
        });
        $this->assertFileExists($this->statePath);
    }

    public function test_second_run_submits_nothing(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $this->artisan('indexnow:submit')->assertSuccessful();
        $this->artisan('indexnow:submit')
            ->expectsOutputToContain('Nothing new to submit.')
            ->assertSuccessful();

        Http::assertSentCount(1);
    }

    public function test_changed_lastmod_resubmits_only_that_url(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $this->artisan('indexnow:submit')->assertSuccessful();

        $state = json_decode(file_get_contents($this->statePath), true);
        $state['https://misubaguna.com/about/'] = '2000-01-01';
        file_put_contents($this->statePath, json_encode($state));

        $this->artisan('indexnow:submit')->assertSuccessful();

        Http::assertSentCount(2);
        $this->assertSame(['https://misubaguna.com/about/'], $this->sentUrls()[1]);
    }

    public function test_all_flag_resubmits_everything(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $this->artisan('indexnow:submit')->assertSuccessful();
        $this->artisan('indexnow:submit --all')->assertSuccessful();

        Http::assertSentCount(2);
        $sent = $this->sentUrls();
        $this->assertContains('https://misubaguna.com/', $sent[1]);
        $this->assertContains('https://misubaguna.com/about/', $sent[1]);
    }

    public function test_explicit_url_is_always_submitted(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $this->artisan('indexnow:submit https://misubaguna.com/custom/')->assertSuccessful();
        $this->artisan('indexnow:submit https://misubaguna.com/custom/')->assertSuccessful();

        Http::assertSentCount(2);
        $this->assertSame([['https://misubaguna.com/custom/'], ['https://misubaguna.com/custom/']], $this->sentUrls());
    }

    public function test_foreign_host_url_is_skipped(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $this->artisan('indexnow:submit https://example.com/x https://misubaguna.com/ok/')
            ->expectsOutputToContain('Skipping https://example.com/x')
            ->assertSuccessful();

        $this->assertSame([['https://misubaguna.com/ok/']], $this->sentUrls());
    }

    public function test_api_error_fails_and_leaves_state_unchanged(): void
    {
        Http::fake(['*' => Http::response('forbidden', 403)]);

        $this->artisan('indexnow:submit')
            ->expectsOutputToContain('403')
            ->assertFailed();

        $this->assertFileDoesNotExist($this->statePath);
    }

    public function test_dry_run_sends_nothing_and_keeps_state(): void
    {
        Http::fake(['*' => Http::response('', 200)]);
        $this->artisan('indexnow:submit --dry-run')->assertSuccessful();

        Http::assertNothingSent();
        $this->assertFileDoesNotExist($this->statePath);
    }

    public function test_key_file_exists_with_exact_content(): void
    {
        $path = public_path(self::KEY.'.txt');
        $this->assertFileExists($path);
        $this->assertSame(self::KEY, file_get_contents($path));
    }
}
