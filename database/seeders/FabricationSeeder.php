<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Fabrication category (/product-category/fabrication/) and its product pages: storage tanks,
 * pressurized tanks (pressure vessels), pinion gears and worm and rack gear sets.
 * Runs from a migration so the live site gets it on deploy. The tank pages carry workshop photos;
 * the gear pages have none yet and show the "Photo on request" plate.
 *
 * Specification values are typical industry ranges, not datasheet figures: every item is
 * fabricated to the buyer's drawing, duty and applicable code.
 */
class FabricationSeeder extends Seeder
{
    public const GROUP = 'Fabrication';

    public const CATEGORY_SLUG = 'fabrication';

    private const DOMAIN = 'https://misubaguna.com';

    public function run(): void
    {
        $now = now();
        $categoryUrl = '/product-category/'.self::CATEGORY_SLUG.'/';
        $categoryMeta = 'Custom fabricated storage tanks, pressurized tanks and pressure vessels, pinion gears, worm gears and rack gears, built to your drawing, duty and applicable code.';

        DB::table('public_product_categories')->updateOrInsert(['url_path' => $categoryUrl], [
            'slug' => self::CATEGORY_SLUG,
            'name' => self::GROUP,
            'summary' => 'Storage tanks and pressurized tanks fabricated in carbon steel, stainless steel and alloy, plus machined pinion, worm and rack gears, all made to your drawing or duty.',
            'description_html' => '<p>Some equipment cannot be bought off a shelf. Our fabrication range covers made-to-order vessels and gears: atmospheric storage tanks and pressurized tanks for liquids and gases, and pinion, worm and rack gear sets for drives, lifts and linear motion.</p><p>Each item is built to your drawing, sample or process duty, in the material and to the code you specify. Send a drawing, a worn part or a duty sheet and we quote the fabrication, machining, inspection and delivery together.</p>',
            'parent_group' => self::GROUP,
            'sort_order' => 10,
            'meta_title' => 'Custom Fabrication: Tanks, Pressure Vessels and Gears - PT Misuba Guna Indonesia',
            'meta_description' => $categoryMeta,
            'canonical_url' => self::DOMAIN.$categoryUrl,
            'og_title' => 'Custom Fabrication: Tanks, Pressure Vessels and Gears - PT Misuba Guna Indonesia',
            'og_description' => $categoryMeta,
            'og_image_path' => self::image('pressure-vessel-horizontal'),
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
            $this->upsert($p, 40 + $i);
        }
    }

    public static function image(string $name): string
    {
        return '/images/products/fabrication/'.$name.'.webp';
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
                'slug' => 'storage-tank',
                'gallery' => ['storage-tank-horizontal', 'storage-tank-delivery'],
                'title' => 'Storage Tank',
                'subgroup' => 'Vessels & Tanks',
                'h1' => 'Storage Tanks: Fabricated Atmospheric Tanks',
                'summary' => 'Vertical and horizontal atmospheric storage tanks in carbon steel, stainless steel and alloy for water, fuel, chemicals and process liquids, fabricated to your capacity and site.',
                'meta' => 'Fabricated vertical and horizontal storage tanks in carbon steel, stainless steel and alloy for water, fuel, chemicals and process liquids, built to your capacity, drawing and applicable code.',
                'intro' => 'A storage tank holds liquid at or near atmospheric pressure until the process needs it. We fabricate tanks to your volume, liquid and footprint: shop-built for smaller sizes, or cut, rolled and welded in sections and erected on site for large diameters. Material, wall thickness, roof and nozzle layout are set by the liquid, temperature and the code you specify.',
                'sections' => [
                    'Tank Types' => [
                        '<strong>Vertical cylindrical tank:</strong> flat or sloped bottom with a fixed cone or dome roof, for the largest volumes on a small footprint.',
                        '<strong>Horizontal tank:</strong> cylindrical shell on saddles with dished or flat heads, for fuel, oil and day-tank duty and where headroom is limited.',
                        '<strong>Open-top and floating-roof tanks:</strong> for water, effluent and liquids where vapour control or evaporation loss matters.',
                        '<strong>Insulated and jacketed tanks:</strong> with insulation, heating coils or a jacket for liquids that must stay hot or cool.',
                    ],
                    'Core Components' => [
                        '<strong>Shell and bottom:</strong> rolled plate welded in courses, with the plate thickness stepped by height for tall tanks.',
                        '<strong>Roof:</strong> cone, dome or flat, with roof manway, vent and gauge hatch.',
                        '<strong>Nozzles and fittings:</strong> inlet, outlet, drain, overflow, level gauge and instrument connections, flanged to your standard.',
                        '<strong>Access and supports:</strong> shell manway, ladder, platform, handrail, lifting lugs, saddles or legs.',
                        '<strong>Surface protection:</strong> blasting and coating, or an internal lining for corrosive liquids, see <a href="/services/rubber-lining/">rubber lining</a> and <a href="/product/frp-lining/">FRP lining</a>.',
                    ],
                    'Inspection and Testing' => 'Weld seams are inspected visually and, where the specification calls for it, by NDT, and the tank is tested for leaks by water fill or vacuum box. Inspection and documents are agreed in the quotation.',
                ],
                'features' => [
                    'Fabricated to your volume, liquid and footprint',
                    'Shop-built or site-erected to suit tank size',
                    'Carbon steel, stainless steel and alloy options',
                    'Insulated, jacketed and heated versions',
                    'Nozzle, manway and platform layout to your drawing',
                    'Internal lining or coating for corrosive liquids',
                ],
                'applications' => [
                    'Process water, raw water and fire water storage',
                    'Fuel, diesel and lubricating oil day tanks',
                    'Chemical, acid and caustic storage',
                    'Palm oil, CPO and edible oil plants',
                    'Boiler feed, condensate and cooling water',
                    'Effluent and wastewater holding',
                ],
                'materials' => [
                    'Carbon steel plate',
                    'Stainless steel 304 / 316',
                    'Duplex stainless and alloy plate on request',
                    'Internal rubber, FRP or PTFE lining for corrosive duty',
                    'Epoxy or polyurethane external coating',
                ],
                'specs' => [
                    'Typical volume: from around 1 m³ up to several thousand m³',
                    'Typical design pressure: atmospheric, up to around 0.1 barg for vented tanks',
                    'Design temperature: ambient up to around 200 °C, by material and wall thickness',
                    'Roof, bottom and nozzle layout configured to your drawing',
                    'Design code and inspection scope as specified by the buyer',
                ],
            ],
            [
                'slug' => 'pressurized-tank',
                'gallery' => ['pressure-vessel-horizontal', 'pressure-vessel-workshop'],
                'title' => 'Pressurized Tank',
                'subgroup' => 'Vessels & Tanks',
                'h1' => 'Pressurized Tanks and Pressure Vessels',
                'summary' => 'Fabricated pressure vessels for air, steam, gas and process fluids, including air receivers, separators and reaction vessels, built to your design pressure, temperature and code.',
                'meta' => 'Fabricated pressurized tanks and pressure vessels in carbon steel and stainless steel for compressed air, steam, gas and process fluids, built to your design pressure, temperature and code.',
                'intro' => 'A pressurized tank holds gas or liquid above atmospheric pressure, so wall thickness, welds, heads and nozzle reinforcement all have to be sized for the design pressure and temperature. We fabricate pressure vessels to your process data or drawing, in the material and to the design code you specify, and test them before delivery.',
                'sections' => [
                    'Vessel Types' => [
                        '<strong>Air receiver:</strong> compressed air storage that smooths compressor load and supplies peak demand.',
                        '<strong>Separator and knock-out vessel:</strong> removes liquid, oil or condensate from a gas stream.',
                        '<strong>Steam and hot water vessel:</strong> flash tanks, blowdown tanks and accumulators for boiler plants.',
                        '<strong>Process and reaction vessel:</strong> with jacket, coil or agitator nozzles for heating, cooling and mixing.',
                        '<strong>Vertical and horizontal layouts:</strong> on legs, lugs, skirt or saddles to suit the plant.',
                    ],
                    'Core Components' => [
                        '<strong>Shell and heads:</strong> rolled plate or pipe shell with elliptical or hemispherical heads.',
                        '<strong>Nozzles and manways:</strong> reinforced openings, flanged to your standard, with inspection manway.',
                        '<strong>Fittings:</strong> pressure gauge, safety valve, drain and level connections, and instrument ports.',
                        '<strong>Supports:</strong> legs, lugs, skirt or saddles, with lifting lugs.',
                    ],
                    'Inspection and Testing' => 'Pressure vessels are hydrostatically tested to the code test pressure, with weld inspection and documentation as specified. The design code, inspection body and test requirements are agreed before fabrication starts.',
                ],
                'features' => [
                    'Fabricated to your design pressure, temperature and medium',
                    'Vertical or horizontal layouts on legs, skirt or saddles',
                    'Reinforced nozzles and manways to your standard',
                    'Hydrostatic test before delivery',
                    'Carbon steel and stainless steel construction',
                    'Insulation, jacket or internal lining options',
                ],
                'applications' => [
                    'Compressed air receivers',
                    'Steam, flash and blowdown vessels',
                    'Gas and oil separators',
                    'Chemical and process reaction vessels',
                    'Hydraulic accumulators and surge tanks',
                    'Boiler and heat-recovery plants',
                ],
                'materials' => [
                    'Carbon steel plate and pipe',
                    'Stainless steel 304 / 316',
                    'Low-alloy steel for higher temperature duty',
                    'Internal lining or cladding for corrosive media',
                    'Heat-resistant external coating',
                ],
                'specs' => [
                    'Typical volume: from around 0.1 m³ up to several tens of m³',
                    'Typical design pressure: from low pressure up to around 40 barg, higher by design',
                    'Design temperature: from sub-ambient up to around 400 °C, by material',
                    'Test: hydrostatic at the code test pressure',
                    'Design code, inspection scope and certificates as specified by the buyer',
                ],
            ],
            [
                'slug' => 'pinion-gear',
                'gallery' => [],
                'title' => 'Pinion Gear',
                'subgroup' => 'Gears & Power Transmission',
                'h1' => 'Pinion Gears: Machined to Your Drawing or Sample',
                'summary' => 'Spur and helical pinion gears and pinion shafts machined from forged or bar steel to your drawing or worn sample, for gearboxes, mill drives, kilns and heavy machinery.',
                'meta' => 'Spur and helical pinion gears and pinion shafts machined from alloy steel to your drawing or worn sample, with heat treatment and tooth finishing, for gearboxes, mill drives and heavy machinery.',
                'intro' => 'A pinion is the small gear that drives a larger gear, ring gear or rack, so it takes the highest tooth loading and wears first. We machine pinions and pinion shafts to your drawing, or reverse-engineer them from a worn sample, in alloy steel heat treated to the hardness the duty needs.',
                'sections' => [
                    'Pinion Types' => [
                        '<strong>Spur pinion:</strong> straight teeth on a parallel shaft, the standard type for general drives.',
                        '<strong>Helical pinion:</strong> angled teeth that run quieter and carry more load than spur teeth.',
                        '<strong>Pinion shaft:</strong> gear teeth cut integrally on the shaft, for gearbox input and output stages.',
                        '<strong>Kiln, mill and drive pinions:</strong> large pinions that mesh with girth gears and ring gears.',
                    ],
                    'How We Make It' => [
                        '<strong>Material:</strong> forged or bar alloy steel chosen for the load and heat treatment.',
                        '<strong>Machining:</strong> turning, tooth cutting, keyway, spline and bore to your tolerance.',
                        '<strong>Heat treatment:</strong> through hardening, carburizing or induction hardening to the hardness you specify.',
                        '<strong>Finishing and inspection:</strong> tooth profile finishing, dimensional and hardness check before delivery.',
                    ],
                    'Replacement from a Sample' => 'If there is no drawing, send the worn pinion and its mating gear. We measure module, number of teeth, pressure angle, helix angle and face width, and make a replacement that meshes with the existing gear. See also <a href="/services/gearbox-repair/">gearbox repair and rebuilding</a>.',
                ],
                'features' => [
                    'Made to your drawing, or copied from a worn sample',
                    'Spur, helical and integral pinion shafts',
                    'Alloy steel with through, case or induction hardening',
                    'Keyway, spline and bore to your fit',
                    'Matched to the existing mating gear or rack',
                    'Single pieces and small batches',
                ],
                'applications' => [
                    'Industrial gearboxes and reducers',
                    'Rotary kiln, ball mill and dryer drives',
                    'Cranes, hoists and winches',
                    'Sugar mill and palm oil mill machinery',
                    'Rack and pinion lifts and gates',
                    'Mining and heavy machinery',
                ],
                'materials' => [
                    'Alloy steel such as 4140 / 4340 or equivalent',
                    'Case-hardening steel such as 8620 or equivalent',
                    'Carbon steel for lighter duty',
                    'Cast iron or bronze on request',
                ],
                'specs' => [
                    'Typical module: from around 1 up to 30 or more',
                    'Typical outside diameter: from a few tens of mm up to over 1 m',
                    'Hardness and case depth set by the duty and the mating gear',
                    'Tooth accuracy grade as specified on the drawing',
                    'Module, teeth, pressure angle and helix angle matched to your gear',
                ],
            ],
            [
                'slug' => 'worm-and-rack-gear',
                'gallery' => [],
                'title' => 'Worm and Rack Gear',
                'subgroup' => 'Gears & Power Transmission',
                'h1' => 'Worm Gears and Rack Gears: Machined to Order',
                'summary' => 'Worm shafts, worm wheels and straight or helical gear racks machined to your drawing or worn sample, for lifting, slewing and linear drive systems.',
                'meta' => 'Worm shafts, bronze worm wheels and straight or helical gear racks machined to your drawing or worn sample, for lifts, gates, presses and linear drive systems.',
                'intro' => 'A worm gear set gives a large speed reduction in one stage and holds its position when the drive stops, so it is common in lifting and positioning drives. A rack converts a pinion\'s rotation into straight-line motion for gates, presses and travelling carriages. We machine both to your drawing, or copy them from a worn set.',
                'sections' => [
                    'Gear Types' => [
                        '<strong>Worm shaft:</strong> a threaded shaft, single or multi-start, ground or turned and hardened.',
                        '<strong>Worm wheel:</strong> a bronze rim on a steel or cast iron centre, cut to mesh with the worm.',
                        '<strong>Straight rack:</strong> straight-toothed bar in standard or made-to-length sections.',
                        '<strong>Helical rack:</strong> angled teeth for quieter running and higher load capacity.',
                    ],
                    'How We Make It' => [
                        '<strong>Worm and wheel:</strong> hardened steel worm run against a bronze wheel, matched as a set so the contact pattern is correct.',
                        '<strong>Rack:</strong> cut from bar in sections that butt together, with hardening and straightening as the duty needs.',
                        '<strong>Finishing and inspection:</strong> dimensional and hardness check, and a trial mesh where the mating gear is supplied.',
                    ],
                    'Replacement from a Sample' => 'Send the worn worm, wheel or rack with its pinion. We measure the module or pitch, lead, number of starts, pressure angle and centre distance, then make a set that fits your gearbox or machine. See also <a href="/product/pinion-gear/">pinion gears</a> and <a href="/services/gearbox-repair/">gearbox repair</a>.',
                ],
                'features' => [
                    'Worm and wheel supplied as a matched set',
                    'Single and multi-start worms',
                    'Straight and helical racks, made-to-length sections',
                    'Hardened worm against a bronze wheel',
                    'Made to your drawing, or copied from a worn set',
                    'Fits existing gearboxes and machines',
                ],
                'applications' => [
                    'Lifting and hoisting equipment',
                    'Gates, doors and sliding mechanisms',
                    'Press, shear and machine tool feed drives',
                    'Slewing and turntable drives',
                    'Steering and positioning mechanisms',
                    'Conveyor and mill gearboxes',
                ],
                'materials' => [
                    'Hardened alloy or carbon steel for worms',
                    'Tin bronze or aluminium bronze for worm wheel rims',
                    'Cast iron or steel wheel centres',
                    'Carbon or alloy steel for racks, hardened as required',
                ],
                'specs' => [
                    'Typical module: from around 1 up to 20',
                    'Typical rack length: made in sections up to around 3 m, butted to the length you need',
                    'Typical reduction per worm stage: around 5:1 up to 100:1',
                    'Module or pitch, lead, starts and pressure angle matched to your set',
                    'Hardness and tooth accuracy as specified on the drawing',
                ],
            ],
        ];
    }
}
