<?php

namespace App\Support;

/**
 * Coupling range: the /product/coupling/ hub and its ten family pages.
 *
 * Series, sizes and ranges come from the supplier catalog transcribed in
 * docs/catalogs/jlbh-couplings.md. Ranges are the smallest and largest printed
 * values for each series (nominal torque unless marked max). The supplier is not
 * named on the public pages. Full dimension tables stay off the site and are sent on request.
 */
class CouplingCatalog
{
    public const HUB_SLUG = 'coupling';

    private const IMG = '/images/products/power-transfer/couplings/';

    public static function families(): array
    {
        return array_map(fn (array $f) => self::expand($f), self::FAMILIES);
    }

    public static function family(?string $slug): ?array
    {
        foreach (self::FAMILIES as $family) {
            if ($family['slug'] === $slug) {
                return self::expand($family);
            }
        }

        return null;
    }

    public static function isHub(?string $slug): bool
    {
        return $slug === self::HUB_SLUG;
    }

    public static function seriesCount(): int
    {
        return array_sum(array_map(fn (array $f) => count($f['series']), self::FAMILIES));
    }

    public static function image(string $name): string
    {
        return self::IMG . $name . '.webp';
    }

    private static function expand(array $family): array
    {
        $family['url'] = '/product/' . $family['slug'] . '/';
        $family['gallery'] = array_map(fn ($n) => self::image($n), $family['gallery']);
        $family['series'] = array_map(function (array $s) {
            $s['image'] = self::image($s['image']);

            return $s;
        }, $family['series']);

        return $family;
    }

    private const FAMILIES = [
        [
            'slug' => 'jaw-coupling',
            'name' => 'Jaw Coupling',
            'tile' => 'Star (spider) jaw couplings in keyed, clamp, taper-bush, expansion-sleeve and flange hubs, with spare spiders.',
            'summary' => 'Curved-jaw couplings with a polyurethane star spider, from compact L-type to heavy XL/GR clamp hubs. Interchangeable with the common GR, L, HRC and pump-coupling sizes.',
            'intro' => 'A jaw coupling joins two hubs through an elastomer spider that sits between their claws. The spider damps shock and vibration and absorbs small radial, angular and axial misalignment, and it is the only wearing part. Choose the hub style by how it fixes to the shaft: keyed bore, clamp screws, taper bush, expansion sleeve or a bolted flange.',
            'gallery' => ['xl-gr-clamp', 'xl-gr-jaw', 'jaw-family-1', 'gr-spider'],
            'specs' => [
                'Nominal torque: 4.5 – 12,000 N·m',
                'Bore range: 5 – 130 mm',
                'Elastomer: Polyurethane spider, 92 ShA, 98 ShA or 64 ShD',
                'Hub fixing: Keyway, clamp, taper bush, expansion sleeve or flange',
            ],
            'features' => [
                'Spider damps shock and vibration between motor and load',
                'Absorbs radial, angular and axial misalignment',
                'Fail-safe: the jaws keep driving if the spider wears',
                'Only the spider wears, and it is replaced without new hubs',
                'GR, L, HRC and pump-coupling sizes for existing machines',
            ],
            'applications' => ['Pumps and compressors', 'Fans and blowers', 'Gear reducers', 'Machine tools', 'Conveyors', 'Hydraulic power units'],
            'series' => [
                ['code' => 'XL/GR', 'name' => 'Star coupling, keyed hubs', 'image' => 'xl-gr-jaw', 'line' => 'Standard curved-jaw coupling in GR sizes, XL0–XL12 (GR14–GR110).', 'facts' => ['16 – 9,000 N·m', 'Bores 8 – 125 mm']],
                ['code' => 'XL/GR-C', 'name' => 'Star coupling, clamp hubs', 'image' => 'xl-gr-clamp', 'line' => 'Clamp-screw hubs grip the shaft with no keyway play.', 'facts' => ['21 – 9,000 N·m', 'Bores 8 – 125 mm']],
                ['code' => 'XL/GR-E', 'name' => 'Expansion-sleeve star coupling', 'image' => 'xl-gr-expansion-sleeve', 'line' => 'Internal expansion sleeve for keyless, backlash-free locking.', 'facts' => ['21 – 3,600 N·m', 'Bores 10 – 105 mm']],
                ['code' => 'XL/GR-TB', 'name' => 'Taper-bush star coupling', 'image' => 'xl-gr-taper-bush', 'line' => 'Fits standard taper bushes 1008 – 4545 for quick bore changes.', 'facts' => ['75 – 9,000 N·m', 'Bores 10 – 110 mm']],
                ['code' => 'XL/GR-F', 'name' => 'Flange-type star coupling', 'image' => 'xl-gr-flange', 'line' => 'Bolted flange hub so the spider can be changed without moving the machines.', 'facts' => ['75 – 9,000 N·m', 'Bores 10 – 130 mm']],
                ['code' => 'XL-S', 'name' => 'Split clamp star coupling', 'image' => 'xl-split-clamp', 'line' => 'Forged steel hubs with double clamp screws plus keyway.', 'facts' => ['Up to 7,200 N·m max', 'Bores to 110 mm']],
                ['code' => 'XL-D', 'name' => 'Separable clamp star coupling', 'image' => 'xl-separable-clamp', 'line' => 'Separable clamp hubs for keyed shafts on machine tools and heavy conveyors.', 'facts' => ['Up to 12,000 N·m max', 'Bores to 120 mm']],
                ['code' => 'CR', 'name' => 'CR jaw coupling', 'image' => 'cr-jaw', 'line' => 'Curved-jaw coupling CR050 – CR90100 with matching spiders.', 'facts' => ['Bores 5 – 110 mm']],
                ['code' => 'L', 'name' => 'L-type three-jaw coupling', 'image' => 'l-jaw', 'line' => 'Compact three-jaw coupling with steel or aluminium hubs, L035 – L275.', 'facts' => ['4.5 – 397 N·m', 'Bores to 75 mm']],
                ['code' => 'HRC', 'name' => 'HRC coupling', 'image' => 'hrc', 'line' => 'Cast hubs in B (bored), F and H (taper bush) styles, HRC070 – HRC280.', 'facts' => ['31 – 3,150 N·m', 'Bores to 115 mm']],
                ['code' => 'OMT-ND', 'name' => 'Aluminium pump coupling', 'image' => 'omt-nd', 'line' => 'Light aluminium hubs with polyurethane insert, ND48 – ND143, for motor–pump sets.', 'facts' => ['Motor bores 11 – 75 mm']],
                ['code' => 'GR', 'name' => 'GR star spider', 'image' => 'gr-spider', 'line' => 'Replacement spiders GR19 – GR110 in three hardness grades.', 'facts' => ['7.5 – 9,000 N·m']],
                ['code' => 'HRC-P', 'name' => 'HRC spider', 'image' => 'hrc-spider', 'line' => 'Replacement elastomers HRC70 – HRC280.', 'facts' => []],
                ['code' => 'R', 'name' => 'R polyurethane spider', 'image' => 'r-spider', 'line' => 'Replacement inserts R42 – R103 for aluminium pump couplings.', 'facts' => []],
                ['code' => 'T', 'name' => 'T hexagonal pad', 'image' => 't-pad', 'line' => 'Six- and eight-lobe pads T75 – T210 in PU, rubber or FKM.', 'facts' => []],
            ],
        ],
        [
            'slug' => 'plum-blossom-coupling',
            'name' => 'Plum Blossom Coupling',
            'tile' => 'ML (LM) plum blossom couplings, NM and FALK-R claw couplings, with spare elements.',
            'summary' => 'Claw couplings that transmit torque through a multi-lobe polyurethane element: ML (GB/T 5272 LM) plum blossom couplings, NM multi-cam couplings and FALK-R housed couplings.',
            'intro' => 'Two claw hubs mesh through a multi-lobe elastic element that is loaded in compression. The element cushions starting shock, damps vibration and takes up axial, radial and angular offset, so the coupling needs no lubrication. ML suits most medium-duty drives; NM gives a compact cam-ring design; FALK-R adds a cover for high torque.',
            'gallery' => ['ml-plum', 'ml-plum-2', 'mt-element', 'ml-brake-disc'],
            'specs' => [
                'Nominal torque: 20 – 15,000 N·m',
                'Bore range: 7 – 190 mm',
                'Element: Polyurethane or rubber, 4 to 12 lobes',
                'Standard: ML to GB/T 5272 (LM)',
            ],
            'features' => [
                'Compression-loaded element cushions start-up shock',
                'Compensates axial, radial and angular displacement',
                'No lubrication or maintenance between element changes',
                'Element resists wear, oil and temperature',
            ],
            'applications' => ['Mining and metallurgy drives', 'Pumps and fans', 'Compressors', 'Conveyors', 'Lifting and hoisting', 'Gear reducers'],
            'series' => [
                ['code' => 'ML', 'name' => 'ML plum blossom coupling', 'image' => 'ml-plum', 'line' => 'GB/T 5272 LM type, ML1 – ML13, also with brake-disc hubs.', 'facts' => ['25 – 12,500 N·m', 'Bores 10 – 140 mm']],
                ['code' => 'MT', 'name' => 'MT plum blossom element', 'image' => 'mt-element', 'line' => 'Polyurethane elements MT1 – MT13 for ML couplings, 4 to 12 lobes.', 'facts' => []],
                ['code' => 'NM', 'name' => 'NM claw coupling', 'image' => 'nm', 'line' => 'Cam-ring claw coupling NM50 – NM214 with an elastic ring insert.', 'facts' => ['20 – 2,400 N·m', 'Bores 7 – 95 mm']],
                ['code' => 'FALK-R', 'name' => 'FALK-R claw coupling', 'image' => 'falk-r', 'line' => 'Housed claw coupling 3R – 80R with polyurethane element.', 'facts' => ['34 – 15,000 N·m', 'Bores to 190 mm']],
                ['code' => 'FALK-E', 'name' => 'FALK-R elastomer', 'image' => 'falk-element', 'line' => 'Replacement polyurethane elements 5R – 80R.', 'facts' => []],
                ['code' => 'AR', 'name' => 'AR elastic ring', 'image' => 'ar-pad', 'line' => 'Multi-lug elastic rings AR28 – AR90, 8 to 12 lugs.', 'facts' => []],
            ],
        ],
        [
            'slug' => 'h-block-coupling',
            'name' => 'H-Block Elastic Coupling',
            'tile' => 'HTLA, HTLB and HTLD block couplings, HDS spacer and HTLP brake-disc couplings, with H blocks.',
            'summary' => 'Flexible couplings that drive through separate H-shaped elastic blocks. Blocks change without moving the hubs; spacer and brake-disc versions are available.',
            'intro' => 'An H-block coupling carries torque through a ring of H-shaped rubber or polyurethane blocks between a claw hub and a flanged hub. The blocks absorb shock and misalignment and can be replaced individually. The HDS spacer type leaves room to pull pump internals, and the HTLP type combines the coupling with a brake disc for conveyors and cranes.',
            'gallery' => ['htlb', 'htla', 'htld', 'h-block'],
            'specs' => [
                'Nominal torque: 19 – 19,000 N·m',
                'Bore range: Up to 190 mm',
                'Elastic blocks: NBR 75–85 ShA (-30 to 110 °C) or PU 75–98 ShA (-40 to 100 °C)',
                'Versions: Standard, flange ring, spacer and brake disc',
            ],
            'features' => [
                'Blocks replace individually without moving the machines',
                'Damps shock loads and torsional vibration',
                'Spacer type gives room for pump and seal service',
                'Brake-disc type combines coupling and brake in one assembly',
            ],
            'applications' => ['Centrifugal pumps', 'Conveyors and crushers', 'Cranes and hoists', 'Fans', 'Compressors', 'Steel and mining drives'],
            'series' => [
                ['code' => 'HTLA', 'name' => 'HTLA H-block coupling', 'image' => 'htla', 'line' => 'Compact two-hub H-block coupling, sizes 58 – 280.', 'facts' => ['19 – 3,900 N·m', 'Bores to 110 mm']],
                ['code' => 'HTLB', 'name' => 'HTLB H-block coupling', 'image' => 'htlb', 'line' => 'Flange-ring H-block coupling for heavier drives, sizes 110 – 480.', 'facts' => ['180 – 18,000 N·m', 'Bores to 180 mm']],
                ['code' => 'HTLD', 'name' => 'HTLD H-block coupling', 'image' => 'htld', 'line' => 'Bolted flange H-block coupling, sizes 110 – 280.', 'facts' => ['225 – 5,500 N·m', 'Bores to 125 mm']],
                ['code' => 'HDS', 'name' => 'HDS spacer coupling', 'image' => 'hds', 'line' => 'H-block coupling with spacer lengths 100 – 250 mm, sizes 80 – 440.', 'facts' => ['85 – 19,000 N·m', 'Bores to 190 mm']],
                ['code' => 'HTLP', 'name' => 'HTLP brake-disc coupling', 'image' => 'htlp', 'line' => 'H-block coupling with brake disc 315 – 500 mm diameter.', 'facts' => ['500 – 10,800 N·m', 'Bores to 165 mm']],
                ['code' => 'H', 'name' => 'H elastic block', 'image' => 'h-block', 'line' => 'Replacement blocks H80 – H710 in NBR or polyurethane.', 'facts' => []],
            ],
        ],
        [
            'slug' => 'gear-coupling',
            'name' => 'Gear Coupling',
            'tile' => 'NL and TGL nylon-sleeve gear couplings, TGCL all-steel and GIICL drum gear couplings.',
            'summary' => 'Crowned-tooth gear couplings, from maintenance-free nylon-sleeve NL and TGL types to all-steel TGCL and GIICL drum gear couplings for heavy torque.',
            'intro' => 'Two externally toothed hubs engage an internally toothed sleeve. Crowned teeth let the hubs tilt, so the coupling accepts angular, radial and axial misalignment while carrying high torque in a small diameter. Nylon sleeves run dry and quietly on light and medium drives; steel sleeves take the heavy, shock-loaded duty of mills, cranes and conveyors.',
            'gallery' => ['tgl', 'nl', 'gear-family', 'giicl'],
            'specs' => [
                'Nominal torque: 10 – 174,000 N·m',
                'Bore range: 6 – 265 mm',
                'Sleeve: Nylon (NL, TGL) or steel (TGCL, GIICL)',
                'Motor range (NL): 0.75 – 180 kW',
            ],
            'features' => [
                'High torque for its outside diameter',
                'Crowned teeth accept angular, radial and axial offset',
                'Nylon sleeves need no lubrication and run quietly',
                'Steel sleeves take heavy and shock-loaded duty',
                'TGL flange version mounts to SAE engine flywheels',
            ],
            'applications' => ['Hydraulic pumps', 'Electric motors and reducers', 'Rolling mills', 'Cranes and hoists', 'Conveyors', 'Engine-driven equipment'],
            'series' => [
                ['code' => 'NL', 'name' => 'NL nylon-sleeve gear coupling', 'image' => 'nl', 'line' => 'Steel hubs with a nylon internal-tooth sleeve, NL1 – NL10.', 'facts' => ['40 – 3,150 N·m', 'Bores 6 – 100 mm']],
                ['code' => 'NL-S', 'name' => 'NL nylon sleeve', 'image' => 'nl-sleeve', 'line' => 'Replacement nylon sleeves NL1 – NL10.', 'facts' => []],
                ['code' => 'TGL', 'name' => 'TGL curved-tooth coupling', 'image' => 'tgl', 'line' => 'Curved-tooth hubs with nylon sleeve, TGL14 – TGL125.', 'facts' => ['10 – 2,500 N·m', 'Bores 6 – 125 mm']],
                ['code' => 'TGL-F', 'name' => 'TGL flywheel flange coupling', 'image' => 'tgl-flange', 'line' => 'Nylon flange for SAE 6.5" – 14" flywheels on engine drives.', 'facts' => ['Bores 20 – 125 mm']],
                ['code' => 'TGCL', 'name' => 'TGCL steel drum gear coupling', 'image' => 'tgcl', 'line' => 'All-steel crowned gear sleeve, TGCL85 – TGCL440.', 'facts' => ['550 – 174,000 N·m', 'Bores 18 – 265 mm']],
                ['code' => 'GIICL', 'name' => 'GIICL drum gear coupling', 'image' => 'giicl', 'line' => 'Narrow drum-tooth gear coupling, GIICL1 – GIICL14.', 'facts' => ['Bores 16 – 250 mm']],
            ],
        ],
        [
            'slug' => 'pin-coupling',
            'name' => 'Pin & Bush Coupling',
            'tile' => 'ZL pin-and-gear-ring, HL nylon pin, TL and FCL rubber-bush couplings.',
            'summary' => 'Flanged couplings that drive through nylon pins or rubber-bushed pins: simple, rugged and repairable on site, for medium to very high torque.',
            'intro' => 'Pin couplings join two flanged hubs with pins that are either solid nylon (HL, ZL) or steel pins sleeved in rubber bushes (TL, FCL). The pins cushion shock and allow small misalignment, and they are cheap to replace when worn. ZL adds a gear ring around the pins for higher torque in heavy industry.',
            'gallery' => ['zl', 'hl', 'pin-family-1', 'fcl'],
            'specs' => [
                'Nominal torque: 6.3 – 180,000 N·m',
                'Bore range: 9 – 340 mm',
                'Flexible element: Nylon pins or rubber-bushed pins',
                'Sizes: ZL1–ZL15, HL1–HL14, TL1–TL13, FCL90–FCL630',
            ],
            'features' => [
                'Few parts, simple to inspect and repair on site',
                'Pins cushion shock and allow small misalignment',
                'Worn pins or bushes replace at low cost',
                'Covers small pumps up to large mill drives',
            ],
            'applications' => ['Water pumps', 'Motor–reducer drives', 'Metallurgy and mining', 'Power plants', 'Cement and chemical plants', 'Hoists'],
            'series' => [
                ['code' => 'ZL', 'name' => 'ZL pin and gear-ring coupling', 'image' => 'zl', 'line' => 'Nylon pins inside a gear ring for high torque, ZL1 – ZL15.', 'facts' => ['112 – 160,000 N·m', 'Bores 12 – 300 mm']],
                ['code' => 'HL', 'name' => 'HL nylon pin coupling', 'image' => 'hl', 'line' => 'Flanged hubs driven through nylon pins, HL1 – HL14.', 'facts' => ['250 – 180,000 N·m', 'Bores 12 – 340 mm']],
                ['code' => 'TL', 'name' => 'TL bushed-pin coupling', 'image' => 'tl', 'line' => 'Steel pins with rubber elastic sleeves, TL1 – TL13.', 'facts' => ['6.3 – 16,000 N·m', 'Bores 9 – 170 mm']],
                ['code' => 'FCL', 'name' => 'FCL bushed-pin coupling', 'image' => 'fcl', 'line' => 'Flange coupling with rubber-bushed pins, FCL90 – FCL630.', 'facts' => ['Flange 90 – 630 mm']],
            ],
        ],
        [
            'slug' => 'tyre-coupling',
            'name' => 'Tyre & Elastic Sleeve Coupling',
            'tile' => 'F and UL tyre couplings, Omega split couplings, CFA highly elastic and J/S sleeve couplings.',
            'summary' => 'Highly flexible couplings with a rubber tyre, split urethane element or toothed elastomer sleeve. They take large misalignment and soak up shock and torsional vibration.',
            'intro' => 'These couplings use a large, soft flexible element: a rubber tyre (F, UL), a split urethane element that fits radially (Omega), segmented rubber (CFA) or a toothed elastomer sleeve (J/S). They tolerate more misalignment than jaw or gear couplings and protect bearings and gearboxes from shock. Split elements change without moving the machines.',
            'gallery' => ['f-tyre', 'omega', 'ul-tyre', 'js-sleeve'],
            'specs' => [
                'Nominal torque: 10 – 38,400 N·m',
                'Bore range: 8 – 229 mm',
                'Element: Rubber tyre, urethane or toothed elastomer sleeve',
                'Hubs: Bored or taper bush (F type B, F and H)',
            ],
            'features' => [
                'Takes large angular, parallel and axial misalignment',
                'Soaks up shock loads and torsional vibration',
                'No lubrication and no metal-to-metal contact',
                'Split elements replace without moving the machines',
            ],
            'applications' => ['Pumps and compressors', 'Crushers and mixers', 'Fans', 'Conveyors', 'Generator sets', 'Marine drives'],
            'series' => [
                ['code' => 'F', 'name' => 'F tyre coupling', 'image' => 'f-tyre', 'line' => 'Rubber tyre with B, F or H (taper bush) flanges, F040 – F250.', 'facts' => ['25 – 14,680 N·m', 'Bores to 190 mm']],
                ['code' => 'UL', 'name' => 'UL tyre coupling', 'image' => 'ul-tyre', 'line' => 'Tyre coupling with clamp-ring rubber tyre, UL1 – UL16.', 'facts' => ['10 – 10,000 N·m', 'Bores 11 – 140 mm']],
                ['code' => 'OMEGA', 'name' => 'Omega split coupling', 'image' => 'omega', 'line' => 'Split urethane element bolted to hubs, E2 – E140.', 'facts' => ['22 – 38,400 N·m', 'Bores to 229 mm']],
                ['code' => 'CFA', 'name' => 'CFA highly elastic coupling', 'image' => 'cfa', 'line' => 'Segmented rubber flange coupling, CFA-01 – CFA-400.', 'facts' => ['10 – 4,000 N·m', 'Bores 8 – 130 mm']],
                ['code' => 'J/S', 'name' => 'J/S sleeve coupling', 'image' => 'js-sleeve', 'line' => 'Toothed elastomer sleeve, solid (J) or split (S), sizes 3 – 14.', 'facts' => ['Bores 9 – 127 mm']],
                ['code' => 'H/HS', 'name' => 'H/HS elastomer sleeve', 'image' => 'h-hs-sleeve', 'line' => 'Replacement solid (H) and split (HS) sleeves, sizes 3 – 14.', 'facts' => []],
            ],
        ],
        [
            'slug' => 'disc-coupling',
            'name' => 'Disc Coupling',
            'tile' => 'DJM single-disc and SJM double-disc spacer couplings with stainless steel disc packs.',
            'summary' => 'All-steel diaphragm couplings with stacked stainless disc packs: zero backlash, torsionally stiff and maintenance-free, rated to 280 °C.',
            'intro' => 'Disc couplings transmit torque through thin stainless spring-steel laminations bolted alternately to each hub. The flexing disc packs take misalignment with no sliding parts, so there is no backlash, no wear and no lubrication. DJM uses one disc pack; SJM uses two packs with a spacer to take parallel offset and leave room between shaft ends.',
            'gallery' => ['sjm', 'djm', 'disc-family-1', 'disc-family-2'],
            'specs' => [
                'Nominal torque: 9.8 – 173,830 N·m',
                'Bore range: 3 – 290 mm',
                'Flexible element: Stainless spring-steel disc packs',
                'Temperature: Up to 280 °C',
            ],
            'features' => [
                'Zero backlash with high torsional stiffness',
                'No wearing parts and no lubrication',
                'Low reaction force on bearings',
                'Spacer type leaves room to service pumps and seals',
            ],
            'applications' => ['Process pumps', 'Compressors and blowers', 'Turbines and generators', 'Paper and steel mills', 'Test rigs', 'High-temperature drives'],
            'series' => [
                ['code' => 'DJM', 'name' => 'DJM single-disc coupling', 'image' => 'djm', 'line' => 'One stainless disc pack between two hubs, DJM00 – DJM23.', 'facts' => ['9.8 – 173,830 N·m', 'Bores 3 – 290 mm']],
                ['code' => 'SJM', 'name' => 'SJM double-disc spacer coupling', 'image' => 'sjm', 'line' => 'Two disc packs with a spacer, SJM00 – SJM14.', 'facts' => ['9.8 – 25,410 N·m', 'Bores 3 – 155 mm']],
            ],
        ],
        [
            'slug' => 'chain-grid-coupling',
            'name' => 'Chain & Grid Coupling',
            'tile' => 'KC and GL roller chain couplings, JS and T10 serpentine grid couplings.',
            'summary' => 'Roller chain couplings for simple, tolerant low-speed drives and serpentine spring grid couplings for heavy torque with shock damping.',
            'intro' => 'A chain coupling wraps a duplex roller chain around two sprocket hubs. It is compact, easy to fit and tolerant of dusty, hot and wet sites. A grid coupling drives through a serpentine steel spring in grooved hubs; the spring flexes under load to cushion shock and torsional vibration, and the T10 type has a horizontally split cover for service in place.',
            'gallery' => ['kc-chain', 'js-grid', 'gl-chain', 't10-grid'],
            'specs' => [
                'Torque range: 40 – 932,000 N·m',
                'Bore range: 13 – 600 mm',
                'Flexible element: Duplex roller chain or serpentine spring grid',
                'Covers: Optional chain cover; vertical or horizontal split grid covers',
            ],
            'features' => [
                'Chain type is simple, compact and quick to fit',
                'Tolerates heat, humidity and dust',
                'Grid spring cushions shock and torsional vibration',
                'Grid couplings cover very high torque in one range',
            ],
            'applications' => ['Conveyors', 'Crushers and mills', 'Mixers and agitators', 'Steel and cement plants', 'Pumps', 'Paper machines'],
            'series' => [
                ['code' => 'KC', 'name' => 'KC chain coupling', 'image' => 'kc-chain', 'line' => 'Duplex roller chain coupling with cover, 4012 – 16018.', 'facts' => ['Up to 26,400 N·m max', 'Bores to 160 mm']],
                ['code' => 'GL', 'name' => 'GL chain coupling', 'image' => 'gl-chain', 'line' => 'Roller chain coupling GL1 – GL15, with or without cover.', 'facts' => ['40 – 25,000 N·m', 'Bores 16 – 190 mm']],
                ['code' => 'JS', 'name' => 'JS grid coupling', 'image' => 'js-grid', 'line' => 'Serpentine spring coupling with vertical split cover, JS1 – JS15.', 'facts' => ['45 – 50,000 N·m', 'Bores 18 – 240 mm']],
                ['code' => 'T10', 'name' => 'T10 grid coupling', 'image' => 't10-grid', 'line' => 'Serpentine spring coupling with horizontal split cover, 1020T – 1260T.', 'facts' => ['52 – 932,000 N·m', 'Bores 13 – 600 mm']],
            ],
        ],
        [
            'slug' => 'servo-coupling',
            'name' => 'Servo & Precision Coupling',
            'tile' => 'Aluminium jaw, disc, bellows and slit couplings for servo motors, encoders and ball screws.',
            'summary' => 'Small, low-inertia aluminium couplings with clamp hubs for servo and stepper motors, encoders and ball screws. Zero-backlash disc, bellows and slit designs.',
            'intro' => 'Motion-control drives need couplings that do not lose position. These aluminium couplings fix with clamp screws and transmit torque through a stainless disc, a stainless bellows, a helical slit or a polyurethane spider. Disc and bellows types give zero backlash and high torsional stiffness for frequent forward and reverse motion.',
            'gallery' => ['xl-aluminium', 'grc', 'servo-family-2', 'sdm'],
            'specs' => [
                'Torque range: 0.8 – 520 N·m',
                'Bore range: 3 – 68 mm',
                'Hubs: Aluminium alloy, clamp screw fixing',
                'Element: Stainless disc, stainless bellows, helical slit or PU spider',
            ],
            'features' => [
                'Zero backlash for forward and reverse motion',
                'Low inertia for fast acceleration',
                'Clamp hubs fix without keyways',
                'Disc and bellows types are torsionally stiff',
            ],
            'applications' => ['Servo and stepper motors', 'Ball screws and linear stages', 'Encoders', 'CNC machine tools', 'Robotics and automation', 'Packaging machines'],
            'series' => [
                ['code' => 'XL-A', 'name' => 'Aluminium jaw coupling', 'image' => 'xl-aluminium', 'line' => 'Aluminium clamp hubs with PU spider, XL-14 – XL-120.', 'facts' => ['1.1 – 520 N·m', 'Bores 3 – 68 mm']],
                ['code' => 'SDM', 'name' => 'SDM double-disc coupling', 'image' => 'sdm', 'line' => 'Two 304 stainless discs, 12 – 82 mm outside diameter.', 'facts' => ['Bores 3 – 42 mm']],
                ['code' => 'DDM', 'name' => 'DDM single-disc coupling', 'image' => 'ddm', 'line' => 'One 304 stainless disc for compact, stiff drives, 12 – 82 mm OD.', 'facts' => ['Bores 3 – 42 mm']],
                ['code' => 'GRC', 'name' => 'GRC bellows coupling', 'image' => 'grc', 'line' => 'Stainless bellows between aluminium clamp hubs, GRC-16 – GRC-105.', 'facts' => ['0.8 – 200 N·m', 'Bores 4 – 50 mm']],
                ['code' => 'GC', 'name' => 'GC slit coupling', 'image' => 'gc', 'line' => 'One-piece helical slit coupling for encoders and light drives.', 'facts' => ['0.9 – 2 N·m', 'Bores 4 – 12 mm']],
            ],
        ],
        [
            'slug' => 'rigid-coupling-torque-limiter',
            'name' => 'Rigid Coupling & Torque Limiter',
            'tile' => 'JK clamp-shell rigid couplings, TLC and JTAL torque limiters, pump bell housings and brackets.',
            'summary' => 'JK clamp-shell rigid couplings, friction and ball-detent torque limiters that protect drives from overload, and the bell housings and brackets that mount pumps to motors.',
            'intro' => 'Rigid couplings join aligned shafts with no flexibility; the JK clamp shell fits without moving either machine. Torque limiters slip (TLC friction type) or disengage (JTAL ball type) at a set torque to protect gearboxes and machines from jams. Bell housings and floor brackets align a hydraulic pump to its motor and enclose the coupling.',
            'gallery' => ['jk', 'tlc', 'jtal', 'jk-2'],
            'specs' => [
                'Rigid coupling torque: 25 – 15,000 N·m (JK)',
                'Torque limiter setting: 2.9 – 1,860 N·m (TLC), 5 – 1,200 N·m (JTAL)',
                'Bell housings: Motors 0.55 – 45 kW (PK aluminium)',
                'Bore range: 6 – 210 mm',
            ],
            'features' => [
                'Clamp-shell rigid coupling fits without moving the machines',
                'Adjustable slip or release torque protects against overload',
                'Bell housings align and guard pump–motor couplings',
                'Aluminium and cast-iron housings and floor brackets',
            ],
            'applications' => ['Line shafts and agitators', 'Conveyors and packaging lines', 'Hydraulic power units', 'Pump–motor sets', 'Machine tools', 'Automation equipment'],
            'series' => [
                ['code' => 'JK', 'name' => 'JK clamp-shell rigid coupling', 'image' => 'jk', 'line' => 'Split shell clamped by bolts, JK-40 – JK-300.', 'facts' => ['25 – 15,000 N·m', 'Bores to 210 mm']],
                ['code' => 'TLC', 'name' => 'TLC friction torque limiter', 'image' => 'tlc', 'line' => 'Friction plates slip at an adjustable torque, TLC200 – TLC1024.', 'facts' => ['2.9 – 1,860 N·m', 'Bores 6 – 90 mm']],
                ['code' => 'JTAL', 'name' => 'JTAL ball torque limiter', 'image' => 'jtal', 'line' => 'Steel-ball detent disengages on overload, sizes 01 – 06.', 'facts' => ['5 – 1,200 N·m', 'Bores 8 – 80 mm']],
                ['code' => 'PK', 'name' => 'PK aluminium bell housing', 'image' => 'pk', 'line' => 'Motor–pump bell housings PK140 – PK450.', 'facts' => ['Motors 0.55 – 45 kW']],
                ['code' => 'DL', 'name' => 'DL / DLK pump bracket', 'image' => 'dl-bracket', 'line' => 'HT250 cast-iron floor-mounted pump brackets.', 'facts' => []],
                ['code' => 'DL-F', 'name' => 'DL-F cast-iron bell housing', 'image' => 'dl-f', 'line' => 'Cast-iron bell housings for flange-mounted pumps.', 'facts' => []],
            ],
        ],
    ];
}
