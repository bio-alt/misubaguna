<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IndexNowSubmit extends Command
{
    protected $signature = 'indexnow:submit {--all : Submit every sitemap URL} {--dry-run : Show what would be sent without sending} {url?* : Explicit URLs to submit}';

    protected $description = 'Notify IndexNow of new or changed sitemap URLs';

    public function handle(): int
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);
        $key = config('services.indexnow.key');

        if (! $host || ! $key) {
            $this->error('Missing app.url host or IndexNow key.');

            return self::FAILURE;
        }

        $state = $this->loadState();
        $explicit = (array) $this->argument('url');
        $toSubmit = []; // url => lastmod|null

        if ($explicit) {
            foreach ($explicit as $url) {
                $toSubmit[$url] = null;
            }
        } else {
            foreach ($this->sitemapEntries() as $url => $lastmod) {
                if ($this->option('all') || ! array_key_exists($url, $state) || $state[$url] !== $lastmod) {
                    $toSubmit[$url] = $lastmod;
                }
            }
        }

        foreach (array_keys($toSubmit) as $url) {
            if (parse_url((string) $url, PHP_URL_HOST) !== $host) {
                $this->warn("Skipping {$url}: not on host {$host}.");
                unset($toSubmit[$url]);
            }
        }

        if (! $toSubmit) {
            $this->info('Nothing new to submit.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->info('Dry run: would submit '.count($toSubmit).' URL(s):');
            foreach (array_keys($toSubmit) as $url) {
                $this->line($url);
            }

            return self::SUCCESS;
        }

        foreach (array_chunk($toSubmit, 10000, true) as $batch) {
            $body = json_encode([
                'host' => $host,
                'key' => $key,
                'keyLocation' => "https://{$host}/{$key}.txt",
                'urlList' => array_map('strval', array_keys($batch)),
            ], JSON_UNESCAPED_SLASHES);

            $response = Http::withBody($body, 'application/json; charset=utf-8')
                ->post(config('services.indexnow.endpoint'));

            if (! in_array($response->status(), [200, 202], true)) {
                $this->error('IndexNow submission failed: HTTP '.$response->status());
                $this->line($response->body());

                return self::FAILURE;
            }

            foreach ($batch as $url => $lastmod) {
                $state[$url] = $lastmod ?? ($state[$url] ?? null);
            }
            $this->saveState($state);
            $this->info('Submitted '.count($batch).' URL(s) (HTTP '.$response->status().').');
        }

        return self::SUCCESS;
    }

    /** @return array<string, string|null> */
    private function sitemapEntries(): array
    {
        $kernel = app(Kernel::class);
        $request = Request::create('/sitemap.xml');
        $response = $kernel->handle($request);
        $xml = $response->getContent();
        $kernel->terminate($request, $response);

        $entries = [];
        $doc = @simplexml_load_string((string) $xml);
        if ($doc === false) {
            return $entries;
        }
        foreach ($doc->url as $node) {
            $loc = trim((string) $node->loc);
            if ($loc !== '') {
                $lastmod = trim((string) $node->lastmod);
                $entries[$loc] = $lastmod !== '' ? $lastmod : null;
            }
        }

        return $entries;
    }

    private function statePath(): string
    {
        return config('services.indexnow.state_path') ?: storage_path('app/indexnow-state.json');
    }

    private function loadState(): array
    {
        $path = $this->statePath();
        if (! is_file($path)) {
            return [];
        }

        return json_decode((string) file_get_contents($path), true) ?: [];
    }

    private function saveState(array $state): void
    {
        @mkdir(dirname($this->statePath()), 0755, true);
        file_put_contents($this->statePath(), json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
