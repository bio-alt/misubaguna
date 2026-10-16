<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Material Handling category (/product-category/material-handling/) and its product pages:
 * bucket elevator, chain, scraper, screw and belt conveyors, plus conveyor spare and wear parts.
 * Runs from a migration so the live site gets it on deploy. Screw conveyor has no photo yet.
 *
 * Specification values are typical industry ranges, not datasheet figures: every unit is
 * designed to the buyer's material, capacity and layout.
 */
class MaterialHandlingSeeder extends Seeder
{
    public const GROUP = 'Material Handling';

    public const CATEGORY_SLUG = 'material-handling';

    private const DOMAIN = 'https://misubaguna.com';

    public function run(): void
    {
        $now = now();
        $categoryUrl = '/product-category/'.self::CATEGORY_SLUG.'/';
        $categoryMeta = 'Bucket elevators, chain, scraper, screw and belt conveyors, plus buckets, chains, idlers, pulleys and screw flights, designed to your bulk material and capacity.';

        DB::table('public_product_categories')->updateOrInsert(['url_path' => $categoryUrl], [
            'slug' => self::CATEGORY_SLUG,
            'name' => self::GROUP,
            'summary' => 'Bucket elevators, chain, scraper, screw and belt conveyors for moving bulk solids through a plant, plus the buckets, chains, idlers, pulleys and screw flights that keep them running.',
            'description_html' => '<p>Bulk material has to be lifted, carried and fed from one process step to the next. Our material handling range covers the equipment that does it: bucket elevators for vertical lifts, belt conveyors for long horizontal runs, chain and scraper conveyors for hot, abrasive or dusty material in enclosed casings, and screw conveyors for short, metered feeds.</p><p>Each conveyor is designed to your material, capacity, distance and layout, supplied new or built to replace an existing unit on the same footprint. Spare buckets, chains, sprockets, idlers, pulleys, belts and screw flights are available for most makes.</p>',
            'parent_group' => self::GROUP,
            'sort_order' => 9,
            'meta_title' => 'Material Handling Equipment - PT Misuba Guna Indonesia',
            'meta_description' => $categoryMeta,
            'canonical_url' => self::DOMAIN.$categoryUrl,
            'og_title' => 'Material Handling Equipment - PT Misuba Guna Indonesia',
            'og_description' => $categoryMeta,
            'og_image_path' => self::image('drag-chain-conveyor'),
            'robots_index' => true,
            'robots_follow' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '0.8',
            'sitemap_changefreq' => 'monthly',
            'schema_json' => null,
            'is_published' => true,
            'deleted_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        foreach ($this->products() as $i => $p) {
            $this->upsert($p, 30 + $i);
        }
    }

    /** Photos are cropped from the Material Handling and Conveyor Products pages of the company profile PDF. */
    public static function image(string $name): string
    {
        return '/images/products/material-handling/'.$name.'.webp';
    }

    /** @return array<int, string> */
    public static function slugs(): array
    {
        return array_column((new self)->products(), 'slug');
    }

    private function upsert(array $p, int $sortOrder): void
    {
        $url = '/product/'.$p['slug'].'/';
        $gallery = array_map([self::class, 'image'], $p['gallery']);
        $title = $p['title'].' - PT Misuba Guna Indonesia';
        $now = now();

        $values = [
            'url_path' => $url,
            'name' => $p['title'],
            'product_group' => self::GROUP,
            'product_subgroup' => $p['subgroup'],
            'headline' => $p['title'],
            'summary' => $p['summary'],
            'content_html' => $this->html($p),
            'key_features' => json_encode($p['features'], JSON_UNESCAPED_UNICODE),
            'applications' => json_encode($p['applications'], JSON_UNESCAPED_UNICODE),
            'materials' => json_encode($p['materials'], JSON_UNESCAPED_UNICODE),
            'technical_specs' => json_encode($p['specs'], JSON_UNESCAPED_UNICODE),
            'gallery' => json_encode($gallery, JSON_UNESCAPED_SLASHES),
            'sort_order' => $sortOrder,
            'meta_title' => $title,
            'meta_description' => $p['meta'],
            'canonical_url' => self::DOMAIN.$url,
            'og_title' => $title,
            'og_description' => $p['meta'],
            'og_image_path' => $gallery[0] ?? null,
            'robots_index' => true,
            'robots_follow' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '0.8',
            'sitemap_changefreq' => 'monthly',
            'is_published' => true,
            'deleted_at' => null,
            'updated_at' => $now,
        ];

        if (DB::table('public_products')->where('slug', $p['slug'])->exists()) {
            DB::table('public_products')->where('slug', $p['slug'])->update($values);
        } else {
            DB::table('public_products')->insert(['slug' => $p['slug'], 'created_at' => $now] + $values);
        }
    }

    private function html(array $p): string
    {
        $ul = fn (array $items) => '<ul>'.implode('', array_map(fn ($i) => "<li>$i</li>", $items)).'</ul>';

        $out = '<h1>'.$p['h1'].'</h1><p class="product-description">'.$p['intro'].'</p>';
        foreach ($p['sections'] as $title => $body) {
            $out .= "<h2>$title</h2>".(is_array($body) ? $ul($body) : "<p>$body</p>");
        }
        $out .= '<h2>Key Features and Benefits</h2>'.$ul($p['features']);
        $out .= '<h2>Applications</h2>'.$ul($p['applications']);
        $out .= '<h2>Materials of Construction</h2>'.$ul($p['materials']);
        $out .= '<h2>Technical Specifications</h2>'.$ul($p['specs']);

        return $out;
    }

    private function products(): array
    {
        return [
            [
                'slug' => 'bucket-elevator',
                'gallery' => ['bucket-elevator-installation', 'chain-bucket-elevator', 'elevator-buckets'],
                'title' => 'Bucket Elevator',
                'subgroup' => 'Vertical Conveying',
                'h1' => 'Bucket Elevators: Belt and Chain Type',
                'summary' => 'Belt and chain bucket elevators for lifting grain, meal, cement, clinker, fertilizer and other bulk solids vertically, designed to your capacity and lift height.',
                'meta' => 'Belt and chain bucket elevators with centrifugal or continuous discharge for cement, clinker, grain, fertilizer, coal and minerals, designed to your capacity and lift height.',
                'intro' => 'A bucket elevator lifts bulk material vertically in the smallest footprint of any conveyor. Buckets fixed to a belt or chain scoop material at the boot and discharge it at the head. We design the elevator to your material, capacity and lift height, choosing belt or chain, bucket type and discharge style to suit how the material flows and how hot or abrasive it is.',
                'sections' => [
                    'Elevator Types' => [
                        '<strong>Belt bucket elevator:</strong> buckets bolted to a rubber or steel-cord belt. Quiet, high speed and suited to free-flowing material such as grain, meal, sand and fertilizer, and to tall lifts.',
                        '<strong>Chain bucket elevator:</strong> buckets on one or two strands of round-link or plate chain. Suited to hot, lumpy or abrasive material such as clinker, coal and limestone.',
                        '<strong>Centrifugal discharge:</strong> spaced buckets at higher speed throw material clear of the head, for free-flowing, fine to small lump material.',
                        '<strong>Continuous discharge:</strong> closely spaced buckets at lower speed let material slide over the bucket ahead, for fragile, light or sluggish material.',
                    ],
                    'Core Components' => [
                        '<strong>Head section:</strong> drive pulley or sprocket, discharge chute, gear motor and holdback to stop run-back.',
                        '<strong>Casing:</strong> single or twin-leg bolted casing, dust-tight, with inspection doors.',
                        '<strong>Boot section:</strong> take-up pulley or sprocket with screw or gravity take-up and clean-out doors.',
                        '<strong>Buckets:</strong> steel, stainless steel, nylon or polyethylene, in shapes matched to the material.',
                    ],
                    'Spares and Retrofits' => 'Worn buckets, belts, chains and head pulley lagging are what stop an elevator. We supply replacements for most makes, see <a href="/product/conveyor-spare-parts/">conveyor spare and wear parts</a>.',
                ],
                'features' => [
                    'Lifts material vertically in a small footprint',
                    'Belt or chain type chosen for the material, temperature and lift height',
                    'Centrifugal or continuous discharge to suit how the material flows',
                    'Dust-tight casing with inspection and clean-out doors',
                    'Holdback and speed monitoring to protect the elevator',
                    'Replacement elevators designed to fit existing inlets and discharge chutes',
                ],
                'applications' => [
                    'Cement, clinker, raw meal and limestone',
                    'Grain, rice, feed and flour mills',
                    'Fertilizer and chemical plants',
                    'Coal, biomass and fly ash handling',
                    'Palm kernel, sugar and food processing',
                    'Mining and mineral processing',
                ],
                'materials' => [
                    'Mild steel or stainless steel casing',
                    'Rubber, steel-cord or heat-resistant belts',
                    'Forged round-link or plate chains in alloy steel',
                    'Buckets in steel, stainless steel, nylon or polyethylene',
                    'Abrasion-resistant liners at the boot and discharge',
                ],
                'specs' => [
                    'Typical capacity: from a few t/h up to several hundred t/h',
                    'Typical lift height: up to around 60 m, higher by design',
                    'Drive: gear motor with holdback, belt speed matched to the discharge type',
                    'Material temperature: chain type for hot material, typically up to 250 °C',
                    'Capacity, bucket size and spacing configured to your material',
                ],
            ],
            [
                'slug' => 'chain-conveyor',
                'gallery' => ['drag-chain-conveyor', 'single-strand-conveyor', 'multistrand-conveyor'],
                'title' => 'Chain Conveyor',
                'subgroup' => 'Conveyor',
                'h1' => 'Chain Conveyors: Drag Chain and En-Masse Type',
                'summary' => 'Enclosed drag chain and en-masse chain conveyors for moving bulk solids horizontally or on an incline, with multiple inlets and outlets in one dust-tight casing.',
                'meta' => 'Enclosed drag chain and en-masse chain conveyors for cement, grain, coal, ash and biomass, with multiple inlets and outlets, dust-tight casing and forged chain.',
                'intro' => 'A chain conveyor drags bulk material along an enclosed trough with flights fixed to one or two strands of chain. The whole bed of material moves together, so the conveyor handles hot, abrasive and dusty material in a sealed casing and can be fed and emptied at several points along its length.',
                'sections' => [
                    'Conveyor Types' => [
                        '<strong>Drag chain conveyor:</strong> flights on a single or double chain drag material along the trough floor. Robust and simple, for coal, ash, clinker and wood chips.',
                        '<strong>En-masse conveyor:</strong> skeleton flights move a deep, continuous bed of material at low speed. Gentle on the product and low on wear, for grain, meal, pellets and cement.',
                        '<strong>Horizontal, inclined and L-path layouts:</strong> straight runs, inclines and horizontal-to-vertical sections to fit the plant.',
                    ],
                    'Core Components' => [
                        '<strong>Chain and flights:</strong> forged or welded chain with steel or UHMW-PE flights sized to the material.',
                        '<strong>Trough casing:</strong> bolted, dust-tight casing with replaceable wear liners on the floor.',
                        '<strong>Drive and tail:</strong> drive sprocket with gear motor, tail sprocket with screw take-up.',
                        '<strong>Inlets and outlets:</strong> several feed points and slide-gate outlets along the length.',
                    ],
                    'Spares and Retrofits' => 'Chains, sprockets, flights and trough liners are the wear items. See <a href="/product/conveyor-spare-parts/">conveyor spare and wear parts</a> for replacements.',
                ],
                'features' => [
                    'Fully enclosed, dust-tight casing',
                    'Multiple inlets and outlets on one conveyor',
                    'Handles hot, abrasive and dusty material',
                    'Low speed operation for low wear and gentle handling',
                    'Horizontal, inclined and L-path layouts',
                    'Replaceable trough liners and flights',
                ],
                'applications' => [
                    'Cement and clinker plants',
                    'Grain silos, feed mills and storage terminals',
                    'Coal, bottom ash and fly ash handling in power plants',
                    'Biomass, wood chip and palm fibre boiler feed',
                    'Chemical, fertilizer and mineral processing',
                ],
                'materials' => [
                    'Mild steel, stainless steel or abrasion-resistant steel casing',
                    'Forged or welded alloy steel chain',
                    'Steel or UHMW-PE flights',
                    'Hardened sprockets with replaceable segments',
                    'Replaceable floor liners in AR steel, UHMW-PE or cast basalt',
                ],
                'specs' => [
                    'Typical capacity: from a few t/h up to several hundred t/h',
                    'Typical length: up to around 100 m per conveyor',
                    'Chain speed: typically 0.2–0.5 m/s',
                    'Inclination: up to around 15° as standard, steeper by design',
                    'Trough width, chain type and drive configured to your duty',
                ],
            ],
            [
                'slug' => 'scraper-conveyor',
                'gallery' => ['hardox-scraper-chain', 'esp-scraper-conveyor', 'palm-kernel-scraper', 'chain-scraper-flights'],
                'title' => 'Scraper Conveyor',
                'subgroup' => 'Conveyor',
                'h1' => 'Scraper Conveyors for Ash, Clinker and Bulk Solids',
                'summary' => 'Scraper chain conveyors and submerged scraper conveyors for ash, clinker, slag and abrasive bulk solids, built heavy for continuous duty.',
                'meta' => 'Scraper chain conveyors and submerged scraper conveyors for boiler ash, clinker, slag, coal and abrasive bulk solids, with heavy-duty chain and replaceable wear liners.',
                'intro' => 'A scraper conveyor pulls material along a trough with scraper flights fixed to heavy-duty chain. It is the workhorse for hot, wet, abrasive and lumpy material that would damage a belt, from boiler bottom ash and clinker to slag and coal. We build it heavy, with replaceable liners where the wear happens.',
                'sections' => [
                    'Conveyor Types' => [
                        '<strong>Scraper chain conveyor:</strong> scrapers on twin chains pull material along an open or covered trough. For coal, clinker, wood chips and abrasive solids.',
                        '<strong>Submerged scraper conveyor (SSC):</strong> runs under a water bath below the boiler, quenching and removing hot bottom ash and slag continuously.',
                        '<strong>Inclined discharge section:</strong> lifts and dewaters material before it drops to a truck, bunker or belt.',
                    ],
                    'Core Components' => [
                        '<strong>Chain:</strong> round-link or forged chain in hardened alloy steel.',
                        '<strong>Scraper flights:</strong> steel flights sized to the trough and material.',
                        '<strong>Trough:</strong> mild steel or AR steel, with cast basalt or ceramic liners for the most abrasive duties.',
                        '<strong>Drive and take-up:</strong> gear motor with overload protection and hydraulic or screw take-up.',
                    ],
                    'Spares and Retrofits' => 'Chain, flights, sprockets and trough liners can be supplied as replacements, see <a href="/product/conveyor-spare-parts/">conveyor spare and wear parts</a>. For worn troughs, our <a href="/product/ceramic-lining/">ceramic lining</a> extends wear life.',
                ],
                'features' => [
                    'Heavy-duty chain and flights for continuous duty',
                    'Handles hot, wet, lumpy and abrasive material',
                    'Submerged design quenches and removes boiler ash in one step',
                    'Replaceable trough liners in AR steel, basalt or ceramic',
                    'Overload protection on the drive',
                    'Built to replace existing conveyors on the same footprint',
                ],
                'applications' => [
                    'Boiler bottom ash and slag removal',
                    'Coal and biomass fired power plants',
                    'Cement clinker and kiln dust',
                    'Steel plant slag and scale',
                    'Palm oil mill boiler ash and fibre',
                    'Mining and mineral processing',
                ],
                'materials' => [
                    'Mild steel or AR steel trough',
                    'Hardened alloy steel round-link or forged chain',
                    'Steel scraper flights',
                    'Cast basalt, ceramic or UHMW-PE liners',
                    'Hardened or segmented sprockets',
                ],
                'specs' => [
                    'Typical capacity: from a few t/h up to around 100 t/h',
                    'Chain speed: typically 0.05–0.3 m/s, slower for submerged duty',
                    'Inclination: discharge sections up to around 35°',
                    'Material temperature: submerged type for hot ash and slag from the boiler',
                    'Trough width, chain size and length configured to your duty',
                ],
            ],
            [
                'slug' => 'screw-conveyor',
                'gallery' => [],
                'title' => 'Screw Conveyor',
                'subgroup' => 'Conveyor',
                'h1' => 'Screw Conveyors: Trough, Tubular and Shaftless',
                'summary' => 'Trough, tubular and shaftless screw conveyors and screw feeders for conveying and metering powders, granules and sticky bulk material over short distances.',
                'meta' => 'U-trough, tubular and shaftless screw conveyors and screw feeders in mild or stainless steel for cement, flour, sludge, biomass and chemicals, designed to your capacity.',
                'intro' => 'A screw conveyor moves material with a rotating helical flight inside a trough or tube. It is compact, enclosed and easy to feed and empty, and it doubles as a feeder that meters material at a steady rate. We design the screw diameter, pitch, speed and trough to your material and capacity.',
                'sections' => [
                    'Conveyor Types' => [
                        '<strong>U-trough screw conveyor:</strong> the standard type for horizontal and slightly inclined conveying, with a bolted cover for access.',
                        '<strong>Tubular screw conveyor:</strong> fully enclosed tube for dusty material, inclines and steep or vertical lifts.',
                        '<strong>Shaftless screw conveyor:</strong> a flight without a centre pipe, for sticky, fibrous or wet material such as sludge, palm fibre and food waste.',
                        '<strong>Screw feeder:</strong> variable pitch flight under a hopper or silo to draw down evenly and meter the feed rate.',
                    ],
                    'Core Components' => [
                        '<strong>Flight:</strong> sectional or helicoid flight in carbon or stainless steel, hard-faced for abrasive material.',
                        '<strong>Trough or tube:</strong> with inlet and outlet spouts, inspection covers and slide gates.',
                        '<strong>Hanger bearings and end seals:</strong> chosen for the material, with packing or air-purge seals.',
                        '<strong>Drive:</strong> shaft-mounted or foot-mounted gear motor, with variable speed for feeders.',
                    ],
                    'Spares and Retrofits' => 'Worn flights, hanger bearings and trough liners can be replaced without changing the whole conveyor, see <a href="/product/conveyor-spare-parts/">conveyor spare and wear parts</a>.',
                ],
                'features' => [
                    'Compact, enclosed design that keeps dust in',
                    'Conveys and meters material in one unit',
                    'U-trough, tubular, shaftless and feeder versions',
                    'Several inlets and outlets along the length',
                    'Stainless steel and food-grade options',
                    'Hard-faced flights for abrasive material',
                ],
                'applications' => [
                    'Cement, fly ash and lime handling',
                    'Flour, sugar, feed and food processing',
                    'Palm oil mill fibre, kernel and sludge',
                    'Wastewater sludge and filter cake',
                    'Biomass and boiler fuel feeding',
                    'Chemical, plastic and fertilizer plants',
                ],
                'materials' => [
                    'Mild steel, stainless steel 304 or 316 trough and flight',
                    'Hard-faced or AR steel flights for abrasive duty',
                    'Hanger bearings in hard iron, bronze, nylon or UHMW-PE',
                    'Shaftless spirals in high-strength alloy steel',
                    'UHMW-PE or AR steel trough liners',
                ],
                'specs' => [
                    'Typical screw diameter: 150–800 mm',
                    'Typical length: up to around 30 m per screw, longer with intermediate hangers',
                    'Inclination: horizontal to vertical, depending on type',
                    'Trough loading: typically 15–45%, depending on the material',
                    'Diameter, pitch, speed and drive configured to your capacity',
                ],
            ],
            [
                'slug' => 'belt-conveyor',
                'gallery' => ['sidewall-belt', 'thick-conveyor-belt'],
                'title' => 'Belt Conveyor',
                'subgroup' => 'Conveyor',
                'h1' => 'Belt Conveyors for Bulk Material Handling',
                'summary' => 'Troughed, flat and inclined belt conveyors for carrying bulk material over long distances, with idlers, pulleys, belts and drives sized to your capacity and layout.',
                'meta' => 'Troughed, flat, inclined and sidewall belt conveyors for coal, ore, aggregates, grain, fertilizer and palm products, with idlers, pulleys, belts and drives sized to your duty.',
                'intro' => 'A belt conveyor carries bulk material on a moving rubber belt supported by idlers. It is the most economical way to move large tonnages over long distances, from a short transfer conveyor to a long overland line. We design the belt width, speed, idlers, pulleys and drive to your material, capacity and route.',
                'sections' => [
                    'Conveyor Types' => [
                        '<strong>Troughed belt conveyor:</strong> three-roll idlers form the belt into a trough to carry more material. The standard type for bulk solids.',
                        '<strong>Flat belt conveyor:</strong> for bagged goods, cartons and picking lines.',
                        '<strong>Inclined and chevron belt:</strong> cleated or chevron belts carry material up steeper inclines.',
                        '<strong>Sidewall belt conveyor:</strong> corrugated sidewalls and cleats lift material up steep inclines in a small footprint.',
                        '<strong>Mobile and shuttle conveyors:</strong> for stockpiles, loading and distribution.',
                    ],
                    'Core Components' => [
                        '<strong>Belt:</strong> fabric or steel-cord, with heat, oil, fire or abrasion resistant covers.',
                        '<strong>Idlers:</strong> carrying, return, impact and self-aligning idlers.',
                        '<strong>Pulleys:</strong> drive, tail, snub and bend pulleys, with rubber or ceramic lagging.',
                        '<strong>Drive:</strong> gear motor or motor with gearbox and fluid coupling, with holdback on inclines.',
                        '<strong>Accessories:</strong> belt cleaners, skirting, take-up, covers, pull-cord and belt-sway switches.',
                    ],
                    'Spares and Retrofits' => 'Idlers, pulleys, belts and cleaners are available as replacements for most conveyors, see <a href="/product/conveyor-spare-parts/">conveyor spare and wear parts</a>. Drive couplings are covered by our <a href="/product/coupling/">coupling range</a>.',
                ],
                'features' => [
                    'Economical for high tonnage and long distances',
                    'Troughed, flat, inclined, sidewall and mobile designs',
                    'Belt covers chosen for heat, oil, fire or abrasion',
                    'Impact beds and skirting at loading points to limit spillage',
                    'Safety switches for pull-cord, belt sway and speed',
                    'Replacement conveyors and upgrades on existing structures',
                ],
                'applications' => [
                    'Coal handling in power plants and ports',
                    'Mining, quarry and aggregate plants',
                    'Cement and limestone handling',
                    'Fertilizer, grain and bulk terminals',
                    'Palm oil mills: fresh fruit bunch, kernel and fibre',
                    'Packaging, warehousing and bagged goods',
                ],
                'materials' => [
                    'Hot-dip galvanized or painted mild steel structure',
                    'Fabric (EP/NN) or steel-cord conveyor belt',
                    'Steel, HDPE or rubber-disc idlers',
                    'Rubber or ceramic lagged pulleys',
                    'Polyurethane or tungsten carbide belt cleaner blades',
                ],
                'specs' => [
                    'Typical belt width: 400–1,400 mm, wider by design',
                    'Typical belt speed: 1–4 m/s',
                    'Typical capacity: from tens of t/h up to over 1,000 t/h',
                    'Inclination: up to around 18° for troughed belts, steeper with chevron or sidewall belts',
                    'Belt, idlers, pulleys and drive power configured to your duty',
                ],
            ],
            [
                'slug' => 'conveyor-spare-parts',
                'gallery' => ['elevator-buckets', 'hardox-scraper-chain', 'conveyor-sprocket', 'forged-chain-link', 'sidewall-belt'],
                'title' => 'Conveyor Spare Parts',
                'subgroup' => 'Spare & Wear Parts',
                'h1' => 'Conveyor and Elevator Spare & Wear Parts',
                'summary' => 'Replacement elevator buckets, conveyor chains, sprockets, idlers, pulleys, belts and screw flights for bucket elevators and belt, chain, scraper and screw conveyors.',
                'meta' => 'Replacement elevator buckets, conveyor chains, sprockets, flights, idlers, pulleys, conveyor belts, belt cleaners and screw flights for most makes of conveyor and elevator.',
                'intro' => 'Most conveyor downtime comes from a handful of wear parts. We supply replacements for bucket elevators and belt, chain, scraper and screw conveyors, matched to your existing equipment from a drawing, a part number or a worn sample.',
                'sections' => [
                    'Parts We Supply' => [
                        '<strong>Bucket elevator:</strong> buckets in steel, stainless steel, nylon or polyethylene, elevator belts and chains, bucket bolts, head and boot pulleys.',
                        '<strong>Chain and scraper conveyor:</strong> forged and round-link chains, sprockets and segments, flights and scrapers, trough liners.',
                        '<strong>Belt conveyor:</strong> conveyor belts, carrying, return and impact idlers, drive and tail pulleys, lagging, belt cleaners and skirting.',
                        '<strong>Screw conveyor:</strong> sectional and helicoid flights, shaftless spirals, hanger bearings, end seals and trough liners.',
                    ],
                    'How to Order' => 'Send a drawing, part number, nameplate photo or a worn sample with the conveyor type and capacity. We match the part to your equipment and quote the replacement.',
                ],
                'features' => [
                    'Parts for bucket elevators and belt, chain, scraper and screw conveyors',
                    'Matched to existing equipment from a drawing, part number or sample',
                    'Upgraded wear materials for longer service life',
                    'One supplier for all the conveyors in a plant',
                ],
                'applications' => [
                    'Planned shutdown and overhaul spares',
                    'Breakdown replacement',
                    'Wear life upgrades on abrasive duties',
                    'Cement, power, mining, palm oil, grain and chemical plants',
                ],
                'materials' => [
                    'Alloy and hardened steel chains and sprockets',
                    'Steel, stainless steel, nylon and polyethylene buckets',
                    'Fabric and steel-cord conveyor belts',
                    'Rubber and ceramic pulley lagging',
                    'AR steel, UHMW-PE, basalt and ceramic liners',
                ],
                'specs' => [
                    'Supply basis: drawing, part number, nameplate or worn sample',
                    'Equipment covered: bucket elevator, belt, chain, scraper and screw conveyors',
                    'Belt widths, chain sizes and bucket sizes matched to your existing equipment',
                ],
            ],
        ];
    }
}
