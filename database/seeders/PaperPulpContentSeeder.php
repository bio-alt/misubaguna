<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Paper & Pulp Machinery page content (category + 7 products).
 * Runs after PublicSiteContentSeeder so it supersedes the earlier placeholder copy.
 */
class PaperPulpContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach ($this->products() as $p) {
            DB::table('public_products')->where('url_path', $p['url_path'])->update([
                'name' => $p['title'],
                'summary' => $p['summary'],
                'content_html' => $this->html($p),
                'key_features' => json_encode($p['features'], JSON_UNESCAPED_UNICODE),
                'applications' => json_encode($p['applications'], JSON_UNESCAPED_UNICODE),
                'materials' => json_encode($p['materials'], JSON_UNESCAPED_UNICODE),
                'technical_specs' => json_encode($p['specs'], JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);
        }

        DB::table('public_product_categories')
            ->where('url_path', '/product-category/paper-pulp-machinery/')
            ->update([
                'summary' => 'Stock preparation, paper machines, tissue lines, molded fiber plants and refiner and screen wear parts for paper mills.',
                'description_html' => '<p>From waste paper pulping to the pope reel, we supply the machinery a paper mill runs on. Our range covers pulping, cleaning, screening, thickening, dispersion, deinking, refining and approach flow, complete paper and tissue machines, molded fiber plants, and replacement refiner plates and screen baskets.</p><p>Equipment is available as a complete turnkey line, as individual sections, or as upgrades to an existing machine, with support for installation, commissioning and spare parts.</p>',
                'meta_description' => 'Stock preparation systems, paper machines, tissue machines, molded fiber plants, refiner plates and screen baskets for paper mills.',
                'og_description' => 'Stock preparation systems, paper machines, tissue machines, molded fiber plants, refiner plates and screen baskets for paper mills.',
                'updated_at' => $now,
            ]);
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
                'url_path' => '/product/hicon-pulper/',
                'title' => 'High-Consistency Pulper',
                'h1' => 'High-Consistency Batch Pulper & Pulping Line',
                'summary' => 'Helical-rotor batch pulper running at 15–16% consistency, with the drum pulpers, dilution pulpers and reject-handling equipment that make up a complete waste paper pulping section.',
                'intro' => 'The high-consistency batch pulper turns recovered paper into slurry at 15–16% consistency. A helical rotor generates intense fiber-to-fiber friction, so fibers separate efficiently while plastics, films and other contaminants stay in large pieces that are easy to remove. We supply it as part of a full pulping section, from bale breaking to reject disposal.',
                'sections' => [
                    'How the Batch Pulper Works' => [
                        '<strong>Loading:</strong> recovered paper is charged into the vat as a batch.',
                        '<strong>Slushing:</strong> the helical rotor circulates the stock at 15–16% consistency, so fibers rub against each other rather than against the rotor.',
                        '<strong>Separation:</strong> fibers are released while plastics, wire and other contaminants stay whole.',
                        '<strong>Dilution and discharge:</strong> the batch is diluted to about 4% and pumped out, ready for the next charge.',
                    ],
                    'Rotor and Drive' => 'The cast helical rotor is made from tough, wear-resistant stainless steel, computer balanced and machined for zero eccentricity, with hardened edges for even slushing and long life. Two drives are available: a compact integrated gearbox, or a belt-and-pulley drive that is simpler to maintain and allows the rotor speed to be changed.',
                    'The Pulping Section' => [
                        '<strong>Bale breaker:</strong> loosens tightly pressed bales and separates hard impurities before pulping, protecting downstream equipment.',
                        '<strong>Drum pulper:</strong> gentle slushing of recovered paper at 15–18% consistency, removing easily separated fines early at roughly 15–25 kWh per tonne.',
                        '<strong>Dilution pulper:</strong> works with the batch pulper, removing plastics through 6–8 mm drilled plates while diluting stock for transfer, so the next batch can start straight away.',
                        '<strong>Low-consistency offset and D-type pulpers:</strong> double-cone and D-rotor designs for intensive slushing with integrated removal of coarse rejects.',
                        '<strong>Ragger, rope cutter and grapple:</strong> continuous removal of plastic film, textiles and wire as a rope, with sized cutting and periodic extraction of heavy rejects from the sump.',
                        '<strong>Fiber saver and trommel screens:</strong> de-trashing that removes up to 70% of contaminants while limiting fiber loss.',
                        '<strong>Reject compacter:</strong> screw press that dewaters and compacts rejects to cut disposal volume and cost.',
                    ],
                    'Special-Duty Pulpers' => [
                        'Under-machine pulper for immediate recycling of wet broke and dry trim.',
                        'Continuous pulper for cup stock and carton packaging, separating polyethylene and aluminium from the fiber.',
                        'High-torque pulper for wet-strength grades, separating fiber from plastic, film and aluminium.',
                        'Low-consistency virgin pulper for white grades at 4–5% consistency with 12–16 mm screen plates.',
                    ],
                ],
                'features' => [
                    'Helical rotor delivers efficient defibering at 15–16% consistency with high fiber yield',
                    'Good ink detachment for deinking writing and printing grades',
                    'High-consistency pulping uses less water and power per tonne',
                    'Contaminants are kept intact for simple removal in coarse screening',
                    'Dilution pulper frees the batch pulper to start the next batch immediately',
                    'Ragger and rope cutter provide continuous removal of film, textiles and wire',
                    'Reject compacter reduces the volume and cost of disposal',
                    'Models for wet-strength, cup stock, virgin and broke pulping',
                ],
                'applications' => [
                    'Recycled packaging paper lines using old corrugated containers and mixed waste',
                    'Deinking lines for writing, printing and tissue grades',
                    'Wet-strength and carton packaging recycling',
                    'Broke and trim recovery directly beneath the paper machine',
                ],
                'materials' => [
                    'Stainless steel wetted vat and fittings',
                    'Cast stainless helical rotor with hard-faced edges',
                    'Drilled stainless extraction and screen plates',
                    'Heavy-duty bearings and drive shaft',
                ],
                'specs' => [
                    'Batch pulper capacity: 8–700 TPD across the model range',
                    'Operating consistency: 15–16% (batch pulper), 15–18% (drum pulper)',
                    'Batch discharge consistency: approx. 4%',
                    'Drive: integrated gearbox or belt-and-pulley',
                    'Dilution pulper screen plate: 6–8 mm drilled holes',
                    'Virgin pulper: 4–5% consistency, 12–16 mm plates',
                    'Drum pulper net energy: approx. 15–25 kWh/t',
                    'Sizes, capacity and drive power configured to plant capacity',
                ],
            ],
            [
                'url_path' => '/product/disc-refiner/',
                'title' => 'Double Disc Refiner',
                'h1' => 'Refiner Range: Twin Disc, Conical & Cylindrical',
                'summary' => 'Twin disc, conical and cylindrical refiners for controlled fiber development, with precise gap control and options for every furnish.',
                'intro' => 'Refining develops the bonding strength of fibers, and it is one of the biggest energy users in a stock preparation line. Our refiner range lets a mill match the machine to the fiber: a dual-zone twin disc refiner, a low-intensity conical refiner and a low-consistency cylindrical refiner, all with precise gap control and heavy-duty construction for continuous operation.',
                'sections' => [
                    'Twin Disc Refiner' => 'A floating rotor on a splined shaft refines on both faces at once and keeps the same disc gap on each side. The rotor weighs roughly 60% less than a conventional one, which cuts inertia and no-load power, and the open area is about 180% larger for higher throughput. Disc gap is set and held automatically, the bearings run in an oil bath for 24/7 duty, and quick-change disc mounting shortens grade changes. It suits stock preparation and approach flow for writing, printing, packaging and specialty grades.',
                    'Conical Refiner' => 'A progressive conical zone exposes fiber evenly across the refining surface. Low-intensity refining builds bonding potential with little cutting, and a tangential inlet with an optimized chamber keeps flow steady. A cantilever design gives quick access to rotor and stator, a simplified loading system shortens filling changes, and CNC-machined fillings are available in patterns for short, long or mixed fiber.',
                    'Refiner Discs' => 'Discs come in a wide choice of patterns and in stainless steel or special alloys: milled discs for high-intensity refining, cast discs for standard duty and welded discs for custom patterns on special fibers.',
                    'Cylindrical Low-Consistency Refiner' => 'A cylindrical chamber with tangential inlet and outlet keeps fibers in the active zone longer while limiting hydraulic losses. The stator position adjusts to micrometre precision while running, and an integrated magnetic trap removes metal before it reaches the refining zone.',
                ],
                'features' => [
                    'Precise gap control for stable freeness and consistent results from batch to batch',
                    'Lower specific energy per tonne refined and low no-load power',
                    'Gentle treatment that reduces fiber cutting and fines',
                    'Better tensile, burst and tear strength, formation and dewatering',
                    'Compatible with DCS and PLC systems for automated refining control',
                    'Compact footprint suited to retrofits, with quick filling changes',
                ],
                'applications' => [
                    'Stock preparation for virgin, recycled and blended furnishes',
                    'Packaging papers, board, writing and printing grades',
                    'Tissue stock preparation',
                    'Approach flow refining for final freeness adjustment',
                ],
                'materials' => [
                    'Stainless steel wetted parts',
                    'Heavy-duty cast housing and bearings',
                    'Wear-resistant stainless and chrome alloy refiner fillings',
                ],
                'specs' => [
                    'Twin disc refiner: 8–600 TPD, 37–3,000 kW',
                    'Conical refiner: 10–350 TPD, 30–600 kW, low intensity',
                    'Cylindrical refiner: 315–700 kW, several configurations',
                    'Disc options: milled, cast or welded; stainless steel or special alloys',
                    'Custom configurations available on request',
                ],
            ],
            [
                'url_path' => '/product/screening-cleaning-system/',
                'title' => 'Screening & Cleaning System',
                'h1' => 'Stock Screening, Cleaning & Thickening Systems',
                'summary' => 'Cleaners, coarse and fine screens, reject handling and thickeners that remove contaminants and protect the paper machine.',
                'intro' => 'A clean, uniform stock is essential for a stable paper machine. Our screening and cleaning equipment removes sand, glass, plastics, shives and stickies in stages, while recovering usable fiber from the reject streams and keeping power consumption low.',
                'sections' => [
                    'Cleaning' => [
                        '<strong>High-density cleaners</strong> in ceramic or steel remove sand, glass and heavy debris from the stock.',
                        '<strong>Medium-consistency cleaners</strong> for pulp mills and approach flow at 2–3% consistency.',
                        '<strong>Low-consistency cleaners</strong> in multi-stage banks (4.5-inch and 2.5-inch) for heavy contaminants, sand, glass and pins at up to 2% consistency.',
                        '<strong>Sand separator:</strong> shaftless screw that drains heavy rejects from junk trap sludge.',
                    ],
                    'Screening' => [
                        '<strong>Coarse screen:</strong> the first pressure screening stage after pulping and HD cleaning, at 2–3% consistency. A hard-faced, dynamically balanced step rotor pulses the stock to keep the hole or slot basket clear. Eight sizes up to 610 TPD.',
                        '<strong>Fine screen:</strong> final screening ahead of the headbox at 0.4–1% consistency. A multi-foil aerofoil rotor gives smooth, pulsation-free flow with less reject thickening and no stringing. Twelve sizes up to 1,360 TPD.',
                        '<strong>Disc screen and combo screen</strong> for deflaking and coarse screening of high-trash stock.',
                        '<strong>Reject sorters and washing-cycle screens</strong> that recover fiber from reject streams.',
                        '<strong>Fractionator</strong> that separates fibers mainly by length with an adjustable cut point.',
                        '<strong>Inflow, upflow and approach flow screens</strong> designed for low pulsation and reduced installed power.',
                        '<strong>Vibrating screen and reject fan press</strong> for removing and dewatering diverse rejects.',
                    ],
                    'Thickening' => 'Disc filters, double disc filters, gravity thickeners, folded thickeners and drum deckers raise stock consistency with low fiber loss and low maintenance.',
                ],
                'features' => [
                    'Staged contaminant removal protects refiners, pumps and the paper machine',
                    'Rotor designs that limit pulsation and keep basket openings clear',
                    'Reject handling recovers fiber and reduces disposal volume',
                    'Low pressure drop and reduced power consumption',
                    'Hole and slot baskets chosen for each stage',
                    'Compact designs and easy access for inspection',
                ],
                'applications' => [
                    'Coarse and fine screening in recycled pulp lines',
                    'Approach flow screening ahead of the headbox',
                    'Deinking plant screening and cleaning',
                    'Virgin and chemical pulp mill screening',
                ],
                'materials' => [
                    'Stainless steel screen vessels',
                    'Wedge-wire and milled-slot baskets, hard chrome plated',
                    'Hard-faced aerofoil and multi-vane rotors',
                    'Ceramic or steel hydrocyclone bodies',
                ],
                'specs' => [
                    'Coarse screen: 2–3% consistency, up to 610 TPD, 8 sizes',
                    'Fine screen: 0.4–1% consistency, up to 1,360 TPD, 12 sizes',
                    'Medium-consistency cleaner: 2–3% consistency',
                    'Low-consistency cleaner: up to 2% consistency',
                    'Screen basket holes: 1.0–4.0 mm; slots: 0.10–0.80 mm',
                    'Capacity and number of stages designed per line',
                ],
            ],
            [
                'url_path' => '/product/paper-machine-line/',
                'title' => 'Complete Paper Machine',
                'h1' => 'Complete Paper Machine Lines',
                'summary' => 'Turnkey paper machines from headbox to pope reel for kraft, board, writing, printing and specialty grades, up to 1,200 TPD and 1,200 m/min.',
                'intro' => 'We design and supply complete paper machine lines for kraft, board, writing, printing, newsprint and specialty grades, as well as individual sections and rebuilds for existing machines. Each line is configured to the grade, capacity and fiber furnish of the mill.',
                'sections' => [
                    'Machine Sections' => [
                        '<strong>Headbox:</strong> hydraulic or pressurized designs with turbulence generation, cross-direction basis weight control and quick grade changes. The pressurized type uses an air cushion for high-speed operation.',
                        '<strong>Wire section:</strong> Fourdrinier forming with table rolls, hydrofoils and suction boxes for even drainage, in single, double, triple, four or multi-wire layouts.',
                        '<strong>Press section:</strong> H-frame multi-nip presses with optimised nip geometry, or a shoe press with a 200–300 mm extended nip. In the shoe press a fixed beam with hydraulic cylinders loads a flexible blanket running on an oil film, and zoned loading trims the cross-direction moisture profile. Gentle, progressive pressing raises exit dryness while keeping bulk, cutting dryer steam by around 15–20%.',
                        '<strong>Dryer section:</strong> multi-cylinder pre-dryers and single-tier post-dryers in cast iron for durable, uniform heat transfer, with control of sheet curl.',
                        '<strong>Surface treatment:</strong> film press for metered sizing and coating with low chemical consumption.',
                        '<strong>Finishing:</strong> multi-nip machine calender with variable crown rolls for smoothness and caliper control.',
                        '<strong>Controls and winding:</strong> online measurement of basis weight, moisture and caliper, and a pope reel with automatic spool change without slowing the machine.',
                    ],
                    'Grades by Wire Configuration' => [
                        'Single wire: specialty grades, tissue, light and regular kraft',
                        'Double wire: kraft and white top liner kraft',
                        'Triple wire: kraft, duplex board and white top liner kraft',
                        'Four or multi-wire: kraft, duplex, art card, folding boxboard and heavy multilayer kraft',
                    ],
                    'Rebuilds & Upgrades' => 'Existing machines can be modernised with capacity increases, press and dryer rebuilds, headbox replacement, grade conversion, energy-saving retrofits and runnability improvements, often at far lower cost than a new line.',
                ],
                'features' => [
                    'Single-source supply from approach flow to pope reel',
                    'Complete turnkey line or individual sections',
                    'Uniform basis weight profile through cross-direction control',
                    'Shoe press option for higher dryness and lower steam use',
                    'Configurations from single wire to multi-wire for different grades',
                    'Rebuild and upgrade programmes for older machines',
                ],
                'applications' => [
                    'Kraft liner, testliner and fluting medium',
                    'Duplex board, folding boxboard and art card',
                    'Writing, printing and newsprint grades',
                    'Specialty papers',
                ],
                'materials' => [
                    'Stainless steel headbox flow channels',
                    'Ceramic wire-section dewatering elements',
                    'Cast iron dryer cylinders built to pressure vessel standards',
                    'Structural steel frame with protective coating',
                ],
                'specs' => [
                    'Capacity: 30–1,200 TPD',
                    'Design speed: up to 1,200 m/min',
                    'Deckle width: up to 10 m',
                    'Basis weight: 50–120 GSM',
                    'Wire configuration: single, double, triple, four or multi-wire',
                    'Shoe press: 200–300 mm nip, line load up to 1,200 kN/m',
                ],
            ],
            [
                'url_path' => '/product/tissue-machine/',
                'title' => 'Tissue Machine',
                'h1' => 'Crescent Former Tissue Machines',
                'summary' => 'Crescent former tissue machines with Yankee dryer, hood and pope reel for facial tissue, toilet rolls, napkins and kitchen towels.',
                'intro' => 'Our tissue machines use crescent former technology to deliver the softness, bulk and absorbency that consumers expect. The scope covers the full line from headbox to reel, together with stock preparation and process automation.',
                'sections' => [
                    'Main Equipment' => [
                        '<strong>Hydraulic headbox:</strong> optimised jet-to-wire ratio for uniform stock distribution, consistent formation and better softness.',
                        '<strong>Crescent former:</strong> stock is jetted into the nip between the outer forming wire and the felt wrapped around the forming roll, so the sheet is formed, dewatered and carried on the felt in one step. With no separate pickup felt there are fewer spares and less downtime. Load-cell tension control and felt conditioning showers keep forming stable across the speed range.',
                        '<strong>Wire section:</strong> ceramic elements and efficient dewatering for long wire life.',
                        '<strong>Suction press roll:</strong> removes water ahead of the Yankee and transfers the sheet, with a rubber-covered shell for high dryness and bulk retention.',
                        '<strong>Yankee cylinder:</strong> cast iron or fabricated steel, 12–16 ft in diameter, with an FEA-optimised shell, a precision-ground face for even coating and creping, a calculated thermal crown and siphon condensate removal. Expect roughly 3–5% steam savings compared with conventional cylinders.',
                        '<strong>Yankee hood:</strong> high-velocity hot air impingement for fast drying and energy savings.',
                        '<strong>Creping doctor:</strong> precise blade positioning and oscillation for even creping and softness.',
                        '<strong>Pope reel:</strong> gentle pneumatic loading that preserves bulk and builds consistent parent rolls.',
                        '<strong>Quality control system:</strong> real-time basis weight and moisture measurement to hold quality and reduce waste.',
                        '<strong>Felt and wire stretchers:</strong> pneumatic, auto-controlled tensioning to extend fabric life.',
                    ],
                ],
                'features' => [
                    'Crescent forming for high bulk, softness and absorbency',
                    'Optimised headbox for uniform formation',
                    'Fast, energy-efficient drying with hot air hood',
                    'Consistent creping and sheet quality',
                    'Bulk-preserving reel and winding',
                    'Integrated stock preparation and process automation',
                ],
                'applications' => [
                    'Facial tissue',
                    'Toilet tissue',
                    'Napkins',
                    'Kitchen towels and hand towels',
                ],
                'materials' => [
                    'Stainless steel headbox and wetted surfaces',
                    'Cast iron or steel Yankee cylinder',
                    'Ceramic wire-section elements',
                    'Rubber-covered suction press roll',
                ],
                'specs' => [
                    'Forming: crescent former, twin wire',
                    'Speed: up to 1,500 m/min',
                    'Basis weight: 13–35 GSM',
                    'Capacity: 100+ TPD per line',
                    'Yankee cylinder: 12–16 ft diameter, paper width up to 4,500 mm',
                    'Enquire for a tailored technical proposal',
                ],
            ],
            [
                'url_path' => '/product/molded-fiber-machine/',
                'title' => 'Molded Fiber Plant',
                'h1' => 'Molded Fiber Machines & Turnkey Plants',
                'summary' => 'Automatic and robotic molded fiber forming machines, trimming, molds and prototyping, from single machines to turnkey plants.',
                'intro' => 'Molded fiber offers a sustainable, biodegradable alternative to single-use plastic packaging. We supply the whole chain, from pulp preparation through forming, hot pressing, trimming and stacking, as standalone machines or as a complete turnkey plant.',
                'sections' => [
                    'Forming Machines' => [
                        '<strong>Large automatic tableware line (1500 × 1500 mm platen):</strong> reciprocating dipping-type forming with integrated hot press and automatic stacking, producing trimming-free output at 700–850 kg/day.',
                        '<strong>Compact automatic machine (1300 × 750 mm platen):</strong> in-line transfer and integrated hot press for space-limited plants, 350–500 kg/day.',
                        '<strong>Twin hot-press machine (1000 × 950 mm platen):</strong> two press stations for shorter drying cycles and servo-controlled transfer of pre-stacked parts, 350–500 kg/day.',
                        '<strong>Robotic forming machine:</strong> six-axis robot pick-and-place with closed-loop feedback to reduce handling damage and rejects.',
                        '<strong>Reciprocating forming machine:</strong> for complex shapes with shallow draft angles, with high-pressure automatic mesh cleaning, around 500 kg/day.',
                        '<strong>Semi-automatic machine:</strong> floating pulp distribution and platen locking, no water inlet required, manual or robotic operation, around 500 kg/day.',
                    ],
                    'Finishing' => 'The edge trimming machine delivers precise edges and dimensional accuracy, with a 760 × 600 mm platen, hydraulic or hydro-pneumatic drive and safety light curtains.',
                    'Molds, Prototyping & Development' => [
                        'Production molds machined on dedicated machining centres from corrosion-resistant alloy with a non-stick finish.',
                        'Rapid prototyping from 3D CAD to shorten time to market and development cost.',
                        'Product development support from concept to commercialisation, including material selection.',
                    ],
                ],
                'features' => [
                    'Fully automatic cycle from forming to hot pressing, trimming and stacking',
                    'Large 1500 × 1500 mm platen for high output per cycle',
                    'Robotic handling to cut rejects and labour',
                    'Machines sized from compact to high-capacity',
                    'In-house mold design, prototyping and product development',
                    'Biodegradable products suited to food-service use',
                ],
                'applications' => [
                    'Food-service tableware: plates, bowls, trays and clamshells',
                    'Electronics and appliance packaging',
                    'Industrial and automotive protective packaging',
                    'Egg trays and fruit packaging',
                ],
                'materials' => [
                    'Corrosion-resistant alloy molds with non-stick finish',
                    'Welded structural steel machine frame',
                    'Heated steel platens with hydraulic clamping',
                ],
                'specs' => [
                    'Platens: 1500 × 1500, 1300 × 750, 1000 × 950 mm',
                    'Output: 350–850 kg/day depending on model',
                    'Edge trimmer platen: 760 × 600 mm',
                    'Plant capacity and layout designed per project',
                ],
            ],
            [
                'url_path' => '/product/refiner-discs-screen-baskets/',
                'title' => 'Refiner Plates & Screen Baskets',
                'h1' => 'Refiner Plates, Fillings & Screen Baskets',
                'summary' => 'Replacement refiner fillings, plates and screen baskets for all major refiner and screen makes, built for long life and low energy use.',
                'intro' => 'Wear parts decide refining quality, screening efficiency, energy use and mill uptime. Our refiner fillings, plates and screen baskets are made to fit major refiner and screen brands, with metallurgy and bar or slot design matched to the furnish.',
                'sections' => [
                    'Refiner Fillings & Plates' => [
                        'Disc fillings and segments from 12 to 64 inches for twin disc and other disc refiners, with lower specific energy and longer life.',
                        'Zero-draft bar plates (bar widths from 0.8 to 5.0 mm in welded versions, 1.0–2.8 mm in cast, welded or milled versions) that keep sharp edges as they wear. Re-milling can extend life by 40–60%.',
                        'Dual-zone bar plates for mixed furnishes and reduced vibration.',
                        'Small-angle-sector plates with variable groove depth: 24 sectors instead of 12 on flat discs and 9 instead of 7 on conical fillings, for more bar crossings per revolution. Deep grooves at the inlet deflake and shallow grooves at the outlet fibrillate, giving two refining stages in one plate.',
                        'Conical fillings in monoblock and multi-section designs for the main conical refiner makes.',
                        'Disperser and deflaker discs with surface treatment for vibration-free running.',
                        'High-consistency defibrator fillings for MDF, HDF and fibreboard.',
                    ],
                    'Screen Baskets & Rotors' => [
                        '<strong>Hole baskets:</strong> 1.0–4.0 mm perforations, countersunk for high open area and even wear.',
                        '<strong>Slotted baskets:</strong> wedge wire, C-bar and milled slots from 0.10 to 0.80 mm for low pressure drop. Profiled wedge-bar baskets are built with 3, 4 or 5 wedge wires per unit length to trade open area against rigidity, in diameters from 200 to 2,000 mm and heights up to 2,000 mm. Each basket is stress relieved, electropolished, hard chrome plated and fully inspected.',
                        '<strong>High-consistency baskets:</strong> reinforced for 3–5% consistency screening.',
                        '<strong>Black liquor filter and digester separator baskets</strong> for chemical pulp mills.',
                        '<strong>Rotors:</strong> step, multi-vane aerofoil and centripetal designs matched to each screening stage.',
                    ],
                ],
                'features' => [
                    'Metallurgy and bar geometry matched to furnish and mill conditions',
                    'Zero-draft design keeps refining intensity stable through service life',
                    'Re-millable plates for lower cost per tonne',
                    'Basket profiles for low pressure drop and even wear',
                    'Fits all major refiner and screen makes as a replacement',
                    'Supplied with dynamically balanced rotors where required',
                ],
                'applications' => [
                    'Twin disc, conical and cylindrical refiner replacement',
                    'Coarse, fine and approach flow pressure screens',
                    'Disperser and deflaker upgrades',
                    'MDF and fibreboard defibrators',
                ],
                'materials' => [
                    'SS-304, 17-4 PH and martensitic chrome stainless alloys',
                    'Premium cast alloys with custom heat treatment, verified by spectroscopy',
                    'Wedge bars in SS316Ti on 2205 duplex stainless support rings',
                    'Hard chrome plating 100–400 µm with electropolished finish',
                    'Hard-faced cast rotors',
                ],
                'specs' => [
                    'Refiner fillings: 12–64 inch diameter',
                    'Hole baskets: 1.0–4.0 mm',
                    'Slotted baskets: 0.10–0.80 mm',
                    'Basket size: Ø200–2,000 mm, height up to 2,000 mm',
                    'Plate sectors: 7–24 per plate, flat disc and conical',
                    'Heat-treated alloys to suit the application',
                ],
            ],
        ];
    }
}
