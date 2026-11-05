<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Filter Press Plates section: the category hub (/product-category/filter-press-plates/)
 * and its five plate-type product pages. Runs from a migration so the live site gets it
 * on deploy, and is registered last in DatabaseSeeder so it supersedes any placeholder copy.
 *
 * Specification values are typical industry ranges, not datasheet figures: the final
 * specification is confirmed per application. "On request" is used where a figure depends
 * on the duty. No prices, no minimum order, and no supplier or brand names anywhere.
 */
class FilterPressContentSeeder extends Seeder
{
    private const DOMAIN = 'https://misubaguna.com';

    private const GROUP = 'Filter Press Plates';

    private const IMG = '/images/products/filter-press/';

    public function run(): void
    {
        $this->upsertCategory();

        foreach ($this->products() as $sort => $p) {
            $this->upsertProduct($p, 31 + $sort);
        }
    }

    // ------------------------------------------------------------------ category

    private function upsertCategory(): void
    {
        $now = now();
        $slug = 'filter-press-plates';
        $url = '/product-category/'.$slug.'/';
        $meta = 'New and replacement filter press plates, 320–2000 mm: recessed chamber, membrane, CGR gasketed, plate-and-frame and special-material plates, made to match your existing press.';

        $values = [
            'url_path' => $url,
            'name' => self::GROUP,
            'summary' => 'New and replacement filter press plates from 320 to 2000 mm, made to match your existing press — plate size, thickness, port positions, handles and cloth fixing. Recessed chamber, membrane, gasketed, plate-and-frame and special-material plates for sludge, tailings, POME and process filtration.',
            'description_html' => $this->categoryHtml(),
            'parent_group' => 'Filtration',
            'sort_order' => 30,
            'meta_title' => 'Filter Press Plates — Chamber, Membrane & Gasketed',
            'meta_description' => $meta,
            'canonical_url' => self::DOMAIN.$url,
            'og_title' => 'Filter Press Plates | PT Misuba Guna Indonesia',
            'og_description' => $meta,
            'og_image_path' => self::IMG.'recessed-chamber-filter-plate/recessed-chamber-filter-plate-01.webp',
            'robots_index' => true,
            'robots_follow' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '0.9',
            'sitemap_changefreq' => 'monthly',
            'is_published' => true,
            'updated_at' => $now,
        ];

        if (DB::table('public_product_categories')->where('slug', $slug)->exists()) {
            DB::table('public_product_categories')->where('slug', $slug)->update($values);
        } else {
            DB::table('public_product_categories')->insert(['slug' => $slug, 'created_at' => $now] + $values);
        }
    }

    private function categoryHtml(): string
    {
        $intro = '<p>PT Misuba Guna Indonesia supplies new and replacement filter press plates for sludge dewatering, mineral processing and process filtration. Plates are made to match the press you already run, so a replacement set drops straight in: we work to the plate size, thickness, feed and filtrate port positions, handle type and cloth fixing of your existing press, or to the plate schedule of a new one.</p>'
            .'<p>Sizes run from 320&nbsp;mm up to 2000&nbsp;mm square, in reinforced or virgin polypropylene for general duty and in special materials — PVDF, cast iron, stainless steel or rubber-faced — for hot, abrasive or aggressive slurries. Choose a plate type below, or send us your press make and model with a photograph or sample of a worn plate and we will identify it and quote a matching set.</p>';

        $table = '<h2>Choosing a plate type</h2>'
            .'<p class="product-description">Typical values — the final specification is confirmed per application.</p>'
            .'<table>'
            .'<thead><tr>'
            .'<th>Plate type</th><th>Typical feed pressure</th><th>Cake dryness</th><th>Max temperature</th><th>Typical use</th><th>Relative cost</th>'
            .'</tr></thead>'
            .'<tbody>'
            .'<tr><td>Recessed chamber (PP)</td><td>0.6–1.6 MPa</td><td>Standard</td><td>~80 °C</td><td>General sludge, tailings, POME, wastewater</td><td>$</td></tr>'
            .'<tr><td>Membrane / diaphragm</td><td>~0.8–1.0 MPa feed, squeeze to ~1.6 MPa</td><td>Drier, shorter cycle</td><td>~80 °C (PP)</td><td>Where cake moisture and disposal cost matter</td><td>$$$</td></tr>'
            .'<tr><td>CGR / gasketed</td><td>As chamber</td><td>Standard</td><td>~80 °C</td><td>Leak-free, odour, toxic or drip-sensitive duty</td><td>$$</td></tr>'
            .'<tr><td>Plate &amp; frame</td><td>Low–medium</td><td>Adjustable cake thickness</td><td>~80 °C (PP)</td><td>Edible oil, small batch, precoat and polishing</td><td>$</td></tr>'
            .'<tr><td>Special material</td><td>Per design</td><td>Per design</td><td>High-temp / chemical</td><td>Aggressive chemicals, hot slurry (PVDF, cast iron, SS, rubber)</td><td>$$$$</td></tr>'
            .'</tbody></table>'
            .'<p><small>Relative cost is indicative only, to compare plate types — it is not a price. Squeeze pressures higher than shown are available on request.</small></p>';

        $apps = '<h2>Typical applications</h2>'
            .'<ul>'
            .'<li><strong>Palm oil mills</strong> — POME and effluent sludge dewatering</li>'
            .'<li><strong>Mining and minerals</strong> — tailings and concentrate dewatering</li>'
            .'<li><strong>Pulp and paper</strong> — effluent and lime mud</li>'
            .'<li><strong>Power plants</strong> — wastewater and ash or FGD slurry</li>'
            .'<li><strong>General wastewater</strong> — municipal and industrial treatment sludge</li>'
            .'</ul>'
            .'<p>Filter cloths, membrane squeeze diaphragms and plate-shifting spares can be supplied to suit the plates we provide — ask our team.</p>';

        return $intro.$table.$apps;
    }

    // ------------------------------------------------------------------ products

    private function upsertProduct(array $p, int $sort): void
    {
        $now = now();
        $url = '/product/'.$p['slug'].'/';
        $gallery = array_map(fn (string $n) => self::IMG.$p['slug'].'/'.$n, $p['gallery']);

        $values = [
            'url_path' => $url,
            'name' => $p['name'],
            'product_group' => self::GROUP,
            'product_subgroup' => $p['subgroup'],
            'headline' => $p['name'],
            'summary' => $p['summary'],
            'content_html' => $this->productHtml($p),
            'key_features' => json_encode($p['features'], JSON_UNESCAPED_UNICODE),
            'applications' => json_encode($p['applications'], JSON_UNESCAPED_UNICODE),
            'materials' => json_encode($p['materials'], JSON_UNESCAPED_UNICODE),
            'technical_specs' => json_encode($p['specs'], JSON_UNESCAPED_UNICODE),
            'gallery' => json_encode($gallery, JSON_UNESCAPED_SLASHES),
            'sort_order' => $sort,
            'meta_title' => $p['meta_title'],
            'meta_description' => $p['meta'],
            'canonical_url' => self::DOMAIN.$url,
            'og_title' => $p['meta_title'],
            'og_description' => $p['meta'],
            'og_image_path' => $gallery[0],
            'robots_index' => true,
            'robots_follow' => true,
            'sitemap_include' => true,
            'sitemap_priority' => '0.8',
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

    private function productHtml(array $p): string
    {
        $ul = fn (array $items) => '<ul>'.implode('', array_map(fn ($i) => '<li>'.$i.'</li>', $items)).'</ul>';

        return '<p class="product-description">'.$p['intro'].'</p>'
            .'<h2>When to use</h2>'.$ul($p['when_use'])
            .'<h2>When not to use</h2>'.$ul($p['when_not'])
            .'<h2>Options</h2>'.$ul($p['options']);
    }

    /** @return array<int, array<string, mixed>> */
    private function products(): array
    {
        return [
            [
                'slug' => 'recessed-chamber-filter-plate',
                'name' => 'Recessed Chamber Filter Plate',
                'subgroup' => 'Recessed Chamber Plate',
                'summary' => 'Polypropylene recessed chamber plates for general sludge, tailings and wastewater dewatering. Two matching plates close to form a sealed chamber that fills with slurry and holds the cake. Sizes 320–2000 mm, reinforced or virgin PP, made to match your press.',
                'meta_title' => 'Recessed Chamber Filter Plates (PP)',
                'meta' => 'Polypropylene recessed chamber filter plates, 320–2000 mm, for sludge, tailings, POME and wastewater dewatering. Reinforced or virgin PP, made to match your existing press. Request a quote.',
                'intro' => 'A recessed chamber plate is the workhorse of the filter press. Each plate has a recessed, pyramid-studded face; two plates close together to form a sealed chamber that fills with slurry under pump pressure. The solids build into a cake against the filter cloth while the filtrate drains away through the studded surface. It is the simplest and most economical plate for general dewatering, and the widest in size range.',
                'when_use' => [
                    'General sludge, tailings, POME and wastewater dewatering',
                    'Standard cake dryness is acceptable',
                    'Polypropylene gives enough corrosion resistance for the slurry',
                    'A wide range of plate sizes is needed, from 320 to 2000 mm',
                    'A cost-effective replacement set for an existing press',
                ],
                'when_not' => [
                    'A much drier cake is needed — use a membrane (diaphragm) plate',
                    'Filtrate must be contained drip-free — use a CGR gasketed plate',
                    'The slurry runs hotter than ~80 °C or is chemically aggressive — use a special-material plate',
                    'Very thin cakes or precoat and polishing duty — use plate and frame',
                ],
                'options' => [
                    'Reinforced polypropylene for strength, or virgin PP for food and purity-critical duty',
                    'Centre or corner feed, and open or closed (piped) filtrate discharge',
                    'Feed and filtrate ports drilled to match your existing press',
                    'Cloth fixing and handles to suit the press',
                    'Custom plate sizes on request',
                ],
                'features' => [
                    'Two plates form each sealed filtration chamber',
                    'Reinforced polypropylene for strength, virgin PP for purity-critical duty',
                    'Pyramid-studded drainage face supports the cloth and speeds filtrate flow',
                    'Centre or corner feed, open or closed filtrate discharge',
                    'Made to match the size, ports and cloth fixing of your existing press',
                    'Interchangeable with common press makes — send a sample or drawing',
                ],
                'applications' => [
                    'Palm oil mills — POME and effluent sludge dewatering',
                    'Mining and minerals — tailings and concentrate dewatering',
                    'Pulp and paper — effluent and lime mud',
                    'Power plants — wastewater and ash or FGD slurry',
                    'Municipal and industrial wastewater treatment',
                ],
                'materials' => [
                    'Reinforced polypropylene (PP-R)',
                    'Virgin polypropylene for food, pharmaceutical and fine-chemical duty',
                    'Polypropylene handles and port bushings',
                ],
                'specs' => [
                    'Plate sizes: 320, 450, 630, 800, 870, 1000, 1250, 1500, 2000 mm square',
                    'Chamber (cake) thickness: 25–40 mm typical, other on request',
                    'Design feed pressure: 0.6–1.6 MPa typical',
                    'Maximum operating temperature: ~80 °C (polypropylene)',
                    'Plate material: reinforced or virgin polypropylene',
                    'Feed position: centre feed, corner feed on request',
                    'Filtrate drainage: open discharge or closed (piped) discharge',
                    'Cloth fixing: over-the-barb or caulked edge, to suit your press',
                    'Handle type: moulded lugs or bolt-on handles to match the press',
                ],
                'gallery' => [
                    'recessed-chamber-filter-plate-01.webp',
                    'recessed-chamber-filter-plate-02.webp',
                    'recessed-chamber-filter-plate-03.webp',
                    'recessed-chamber-filter-plate-04.webp',
                    'recessed-chamber-filter-plate-05.webp',
                    'recessed-chamber-filter-plate-06.webp',
                ],
            ],
            [
                'slug' => 'membrane-filter-plate',
                'name' => 'Membrane Filter Plate',
                'subgroup' => 'Membrane (Diaphragm) Plate',
                'summary' => 'Membrane (diaphragm) filter plates squeeze the cake after filling for a drier cake and a shorter cycle. A flexible PP, TPE or rubber membrane inflates with water or air against the cake. Sizes 320–2000 mm, made to match your press.',
                'meta_title' => 'Membrane Filter Plates (Diaphragm)',
                'meta' => 'Membrane (diaphragm) filter plates that squeeze the cake for lower moisture and shorter cycles. PP, TPE or rubber membranes, 320–2000 mm, made to match your existing press.',
                'intro' => 'A membrane plate works like a recessed plate, but its face is a flexible diaphragm. Once the chamber is full, the membrane is inflated with water or air and squeezes the cake, driving out more liquid than feed pressure alone. The result is a drier cake and a shorter cycle, which cuts moisture, disposal cost and downstream drying. Membrane plates can be mixed with recessed plates in the same press.',
                'when_use' => [
                    'A drier cake is needed to cut moisture and disposal cost',
                    'A shorter filtration cycle raises throughput',
                    'Cake moisture must be consistent across the batch',
                    'Haulage or incineration cost depends on how wet the cake is',
                ],
                'when_not' => [
                    'General dewatering where cost is the priority — a recessed chamber plate is simpler',
                    'The slurry runs hotter than the membrane allows or is chemically aggressive — use a special-material plate',
                    'Very thin cakes or polishing duty — use plate and frame',
                ],
                'options' => [
                    'Membrane material: polypropylene, TPE or rubber, to suit temperature and media',
                    'Water or air squeeze',
                    'Mixed sets of membrane and recessed plates in one press',
                    'Feed and filtrate ports drilled to match your existing press',
                    'Custom plate sizes on request',
                ],
                'features' => [
                    'Membrane squeezes the cake for lower moisture and faster cycles',
                    'PP, TPE or rubber membranes for different temperatures and media',
                    'Can be mixed with recessed plates in one press',
                    'Water or air squeeze',
                    'Made to match your existing membrane or recessed press',
                ],
                'applications' => [
                    'Mining tailings where haulage and disposal cost depend on cake moisture',
                    'Palm oil mills — POME and sludge where a drier cake is wanted',
                    'Chemical and metallurgical precipitates',
                    'Wastewater sludge bound for landfill or incineration',
                    'Any duty where a drier cake shortens the cycle',
                ],
                'materials' => [
                    'Reinforced or virgin polypropylene plate body',
                    'Polypropylene, TPE or rubber membrane',
                ],
                'specs' => [
                    'Plate sizes: 320–2000 mm square',
                    'Membrane material: polypropylene, TPE or rubber',
                    'Feed pressure: ~0.8–1.0 MPa typical',
                    'Squeeze (membrane) pressure: up to ~1.6 MPa typical, higher on request',
                    'Cake (chamber) thickness: 25–35 mm typical, on request',
                    'Maximum operating temperature: ~80 °C (PP membrane), higher with rubber on request',
                    'Plate body material: reinforced or virgin polypropylene',
                    'Feed position: centre feed, corner feed on request',
                    'Filtrate drainage: open or closed discharge',
                    'Cloth fixing and handles: to match your press',
                ],
                'gallery' => [
                    'membrane-filter-plate-01.webp',
                    'membrane-filter-plate-02.webp',
                    'membrane-filter-plate-03.webp',
                    'membrane-filter-plate-04.webp',
                    'membrane-filter-plate-05.webp',
                    'membrane-filter-plate-06.webp',
                ],
            ],
            [
                'slug' => 'cgr-gasketed-filter-plate',
                'name' => 'CGR Gasketed Filter Plate',
                'subgroup' => 'CGR / Gasketed Plate',
                'summary' => 'Gasketed (CGR) recessed plates seal around the chamber and the ports for leak-free, drip-free filtration. They keep filtrate contained for odorous, toxic or drip-sensitive slurries. Sizes 320–2000 mm.',
                'meta_title' => 'CGR Gasketed Filter Plates',
                'meta' => 'Gasketed CGR filter plates for leak-free, drip-free filtration of odorous, toxic or drip-sensitive slurries. 320–2000 mm, made to match your existing press. Request a quote.',
                'intro' => 'A CGR (gasketed) plate is a recessed chamber plate with a sealing gasket set into a groove around the chamber and each port. The gasket seals plate to plate when the press closes, so filtrate does not drip between the plates. That keeps the press and the working area clean and dry, and contains odour, splash and volatile filtrates. Gasketed plates can also be built with a membrane for a drier cake.',
                'when_use' => [
                    'Filtrate must stay contained — no drip between the plates',
                    'The slurry is odorous, toxic, volatile or splash-sensitive',
                    'A clean, dry working area around the press is required',
                    'Closed, piped filtrate collection is needed',
                ],
                'when_not' => [
                    'Simple open duty where cost is the priority — a plain recessed chamber plate is cheaper',
                    'The slurry runs hot or is chemically aggressive beyond PP — use a special-material plate',
                ],
                'options' => [
                    'Gasket material selected for the slurry',
                    'Closed (piped) filtrate discharge',
                    'Combined with a membrane for a drier, contained cake',
                    'Feed and filtrate ports drilled to match your existing press',
                    'Custom plate sizes on request',
                ],
                'features' => [
                    'Gasket seals every chamber and port — no filtrate drip',
                    'Contains odour, splash and volatile media',
                    'Cleaner, safer working area around the press',
                    'Available with membrane squeeze for a drier cake',
                    'Made to match your existing gasketed press',
                ],
                'applications' => [
                    'Odorous or toxic slurries needing containment',
                    'Food, pharmaceutical and fine-chemical filtration',
                    'Volatile or flammable filtrates',
                    'Hazardous mining and metallurgical sludges',
                    'Any duty requiring a dry, drip-free press',
                ],
                'materials' => [
                    'Reinforced or virgin polypropylene',
                    'Gasket elastomer selected for the slurry',
                ],
                'specs' => [
                    'Plate sizes: 320–2000 mm square',
                    'Sealing: gasket around the chamber and the feed and filtrate ports',
                    'Design feed pressure: 0.6–1.6 MPa typical',
                    'Maximum operating temperature: ~80 °C (polypropylene)',
                    'Plate material: reinforced or virgin polypropylene',
                    'Gasket material: to suit the slurry, on request',
                    'Feed position: centre feed, corner feed on request',
                    'Filtrate drainage: closed (piped) discharge',
                    'Cloth fixing and handles: to match your press',
                ],
                'gallery' => [
                    'cgr-gasketed-filter-plate-01.webp',
                    'cgr-gasketed-filter-plate-02.webp',
                    'cgr-gasketed-filter-plate-03.webp',
                ],
            ],
            [
                'slug' => 'plate-and-frame-filter-plate',
                'name' => 'Plate and Frame Filter Plate',
                'subgroup' => 'Plate and Frame',
                'summary' => 'Plate-and-frame sets — solid plates alternating with open frames — give an adjustable cake thickness for precoat, polishing and small-batch filtration. Common in edible oil. Sizes 320–2000 mm.',
                'meta_title' => 'Plate and Frame Filter Plates',
                'meta' => 'Plate-and-frame filter plates with adjustable cake thickness for edible oil, precoat and polishing and small-batch filtration. 320–2000 mm, reinforced or virgin PP.',
                'intro' => 'A plate-and-frame set alternates solid filter plates with open frames. The frames form the chamber, so the cake thickness is set by the frame depth and is easily changed by swapping frames. This makes the design well suited to precoat and polishing filtration and to small-batch work such as edible oil, where cake volume and clarity matter more than throughput.',
                'when_use' => [
                    'Cake thickness needs to be set or varied by frame depth',
                    'Precoat or polishing filtration for clarity',
                    'Edible oil, juice, syrup and small-batch filtration',
                    'Catalyst or precious-metal recovery',
                ],
                'when_not' => [
                    'High-throughput automated dewatering — use recessed chamber or membrane plates',
                    'The driest possible cake is the goal — use membrane plates',
                ],
                'options' => [
                    'Frame depth chosen to set the cake thickness',
                    'Virgin PP for edible oil and food duty',
                    'Centre or corner feed',
                    'Feed and filtrate ports drilled to match your existing press',
                    'Custom plate and frame sizes on request',
                ],
                'features' => [
                    'Open frames set the cake thickness — change frames to change cake volume',
                    'Suited to precoat and polishing filtration',
                    'Virgin PP option for edible oil and food duty',
                    'Adjustable for small-batch and variable production',
                    'Made to match your existing plate-and-frame press',
                ],
                'applications' => [
                    'Edible and vegetable oil filtration and polishing',
                    'Precoat filtration of juices, syrups and fine chemicals',
                    'Small-batch and pilot-scale filtration',
                    'Catalyst and precious-metal recovery',
                ],
                'materials' => [
                    'Reinforced or virgin polypropylene plates and frames',
                ],
                'specs' => [
                    'Plate sizes: 320–2000 mm square',
                    'Construction: solid plates alternating with open frames',
                    'Cake thickness: set by frame depth, adjustable',
                    'Design pressure: low to medium, on request',
                    'Maximum operating temperature: ~80 °C (polypropylene)',
                    'Plate and frame material: reinforced or virgin polypropylene',
                    'Feed position: centre or corner feed',
                    'Filtrate drainage: open or closed discharge',
                    'Cloth fixing and handles: to match your press',
                ],
                'gallery' => [
                    'plate-and-frame-filter-plate-01.webp',
                    'plate-and-frame-filter-plate-02.webp',
                    'plate-and-frame-filter-plate-03.webp',
                    'plate-and-frame-filter-plate-04.webp',
                    'plate-and-frame-filter-plate-05.webp',
                    'plate-and-frame-filter-plate-06.webp',
                ],
            ],
            [
                'slug' => 'special-material-filter-plate',
                'name' => 'Special Material Filter Plate',
                'subgroup' => 'Special Material Plate',
                'summary' => 'Filter plates in PVDF, cast iron, stainless steel or rubber-faced construction for hot, abrasive or chemically aggressive slurries beyond the range of polypropylene. Material and design to suit the duty.',
                'meta_title' => 'Special Material Filter Plates (PVDF, Cast Iron, SS)',
                'meta' => 'Filter plates in PVDF, cast iron, stainless steel or rubber for hot, abrasive or chemically aggressive slurries beyond the range of polypropylene. Material and design to suit the duty.',
                'intro' => 'When the slurry is too hot, too abrasive or too aggressive for polypropylene, the plate is made from a special material instead. PVDF resists solvents and higher temperatures; cast iron and stainless steel take hot, high-pressure service; rubber-faced plates stand up to abrasive slurries. The material and design are selected for the process, so these plates are quoted to the duty rather than from a standard range.',
                'when_use' => [
                    'The slurry runs above the ~80 °C limit of polypropylene',
                    'Solvents, strong acids or strong alkalis attack PP',
                    'The slurry is hot and at high pressure',
                    'Abrasive tailings or concentrates wear PP too quickly',
                ],
                'when_not' => [
                    'Standard ambient duty within the range of polypropylene — a recessed chamber or membrane plate costs less',
                ],
                'options' => [
                    'PVDF for solvents and higher temperature',
                    'Cast iron or stainless steel for hot, high-pressure service',
                    'Rubber-faced plates for abrasive slurries',
                    'Material, size and design selected for your process',
                    'Ports drilled to match your existing press',
                ],
                'features' => [
                    'For duties beyond polypropylene — hot, abrasive or aggressive',
                    'PVDF for solvents and high temperature',
                    'Cast iron or stainless steel for hot, high-pressure service',
                    'Rubber-faced plates for abrasive slurries',
                    'Material and design selected for your process',
                ],
                'applications' => [
                    'Hot mineral and metallurgical slurries',
                    'Solvent and strong acid or alkali filtration',
                    'High-temperature chemical processes',
                    'Abrasive tailings and concentrates',
                ],
                'materials' => [
                    'PVDF',
                    'Cast iron',
                    'Stainless steel',
                    'Rubber-faced steel',
                ],
                'specs' => [
                    'Plate materials: PVDF, cast iron, stainless steel or rubber-faced',
                    'Plate sizes: to suit the duty, on request',
                    'Design pressure: per design',
                    'Maximum operating temperature: per material — above the ~80 °C PP limit',
                    'Chemical service: solvents, acids, alkalis and hot media, per material',
                    'Feed position: centre or corner feed',
                    'Filtrate drainage: open or closed discharge',
                    'Cloth fixing and handles: to match your press',
                ],
                'gallery' => [
                    'special-material-filter-plate-01.webp',
                    'special-material-filter-plate-02.webp',
                    'special-material-filter-plate-03.webp',
                    'special-material-filter-plate-04.webp',
                    'special-material-filter-plate-05.webp',
                ],
            ],
        ];
    }
}
