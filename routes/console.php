<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('seo:llms', function () {
    file_put_contents(public_path('llms.txt'), \App\Support\LlmsText::short());
    file_put_contents(public_path('llms-full.txt'), \App\Support\LlmsText::full());
    $this->info('Wrote public/llms.txt and public/llms-full.txt from the published catalog.');
})->purpose('Rebuild llms.txt and llms-full.txt from the published catalog');
