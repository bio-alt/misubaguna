<?php

namespace Database\Seeders;

use App\Support\CouplingCatalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Coupling hub (/product/coupling/) and its ten family pages, built from App\Support\CouplingCatalog.
 * Runs from a migration so the live site gets it on deploy, and last in DatabaseSeeder so it
 * supersedes the placeholder coupling copy in the earlier seeders.
 */
class CouplingCatalogSeeder extends Seeder
{
    private const DOMAIN = 'https://misubaguna.com';

    public function run(): void
    {
        $this->upsert([
            'slug' => CouplingCatalog::HUB_SLUG,
            'name' => 'Coupling',
            'summary' => CouplingCatalog::seriesCount().' coupling series in ten families, from miniature servo couplings to grid couplings for mill drives. Choose a family below, or send us the motor power, speed and shaft sizes and we will size it for you.',
            'intro' => 'We supply '.CouplingCatalog::seriesCount().' coupling series in ten families, from miniature servo couplings to grid couplings for mill drives, plus spare elements, torque limiters and pump bell housings. Pick a family below, or send us the motor power, speed and both shaft sizes and we will propose the type and size.',
            'gallery' => ['jaw-family-1', 'gear-family', 'disc-family-1', 'pin-family-1'],
            'specs' => [
                'Coupling families: 10',
                'Series: '.CouplingCatalog::seriesCount().', with spare elements',
                'Torque range: 0.8 – 932,000 N·m',
                'Bore range: 3 – 600 mm',
            ],
            'features' => [
                'Ten coupling families from one supplier',
                'Replacement spiders, blocks, sleeves and pins',
                'Interchangeable with common GR, L, HRC, NM, FALK-R and tyre coupling sizes',
                'Sizing support from motor power, speed and shaft sizes',
            ],
            'applications' => ['Pumps and compressors', 'Motors and gear reducers', 'Conveyors and crushers', 'Fans and blowers', 'Machine tools and servo drives', 'Marine, power and mining equipment'],
            'meta' => 'Industrial couplings in ten families: jaw, plum blossom, H-block, gear, pin, tyre, disc, chain, grid, servo and rigid couplings, plus torque limiters and spare elements.',
            'sort_order' => 19,
        ]);

        foreach (CouplingCatalog::families() as $i => $family) {
            $this->upsert([
                'slug' => $family['slug'],
                'name' => $family['name'],
                'summary' => $family['summary'],
                'intro' => $family['intro'],
                'gallery' => $family['gallery'],
                'specs' => $family['specs'],
                'features' => $family['features'],
                'applications' => $family['applications'],
                'meta' => $family['summary'],
                'sort_order' => 19,
            ]);
        }
    }

    private function upsert(array $p): void
    {
        $url = '/product/'.$p['slug'].'/';
        $gallery = array_map(
            fn (string $img) => str_starts_with($img, '/') ? $img : CouplingCatalog::image($img),
            $p['gallery']
        );
        $title = $p['name'].' - PT Misuba Guna Indonesia';
        $meta = mb_strimwidth($p['meta'], 0, 300, '…');
        $now = now();

        $values = [
            'url_path' => $url,
            'name' => $p['name'],
            'product_group' => 'Power Transfer',
            'product_subgroup' => 'Coupling',
            'headline' => $p['name'],
            'summary' => $p['summary'],
            'content_html' => '<p>'.e($p['intro']).'</p>',
            'key_features' => json_encode($p['features'], JSON_UNESCAPED_UNICODE),
            'applications' => json_encode($p['applications'], JSON_UNESCAPED_UNICODE),
            'technical_specs' => json_encode($p['specs'], JSON_UNESCAPED_UNICODE),
            'gallery' => json_encode($gallery, JSON_UNESCAPED_SLASHES),
            'sort_order' => $p['sort_order'],
            'meta_title' => $title,
            'meta_description' => $meta,
            'canonical_url' => self::DOMAIN.$url,
            'og_title' => $title,
            'og_description' => $meta,
            'og_image_path' => $gallery[0],
            'robots_index' => true,
            'robots_follow' => true,
            'sitemap_include' => true,
            'sitemap_priority' => CouplingCatalog::isHub($p['slug']) ? '0.9' : '0.8',
            'sitemap_changefreq' => 'monthly',
            'is_published' => true,
            'updated_at' => $now,
        ];

        if (DB::table('public_products')->where('slug', $p['slug'])->exists()) {
            DB::table('public_products')->where('slug', $p['slug'])->update($values);
        } else {
            DB::table('public_products')->insert(['slug' => $p['slug'], 'created_at' => $now] + $values);
        }
    }
}
