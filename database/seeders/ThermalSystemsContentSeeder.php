<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Thermal Systems page content (category + 5 products).
 * Runs after PublicSiteContentSeeder so it supersedes the earlier placeholder copy.
 *
 * Specification values are typical industry ranges, not datasheet figures:
 * confirm against the equipment actually supplied before publishing.
 */
class ThermalSystemsContentSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach ($this->products() as $p) {
            DB::table('public_products')->where('url_path', $p['url_path'])->update([
                'name' => $p['title'],
                'headline' => $p['title'],
                'summary' => $p['summary'],
                'content_html' => $this->html($p),
                'key_features' => json_encode($p['features'], JSON_UNESCAPED_UNICODE),
                'applications' => json_encode($p['applications'], JSON_UNESCAPED_UNICODE),
                'materials' => json_encode($p['materials'], JSON_UNESCAPED_UNICODE),
                'technical_specs' => json_encode($p['specs'], JSON_UNESCAPED_UNICODE),
                'meta_description' => $p['meta'],
                'og_description' => $p['meta'],
                'updated_at' => $now,
            ]);
        }

        $meta = 'Cooling towers, shell and tube and finned-coil heat exchangers, plate heat exchangers, oil coolers and water-cooled cartridges for industrial process, power and HVAC duty.';

        DB::table('public_product_categories')
            ->where('url_path', '/product-category/thermal-systems/')
            ->update([
                'summary' => 'Cooling towers, shell and tube, finned-coil and plate heat exchangers, oil coolers and replacement water-cooled cartridges for process cooling, heat recovery and lubrication systems.',
                'description_html' => '<p>Heat has to go somewhere. Our thermal range covers the equipment that removes it from a plant: cooling towers that reject it to the atmosphere, heat exchangers that move it between process fluids, and oil coolers that keep hydraulic and lubrication systems inside their working temperature, with replacement cartridges to renew them.</p><p>Units are sized to your duty, fluids and site conditions, and supplied new or as replacements for existing equipment. Repair, re-tubing, fill replacement and plate re-gasketing are covered by our field services.</p>',
                'meta_description' => $meta,
                'og_description' => $meta,
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
                'url_path' => '/product/cooling-tower/',
                'title' => 'Cooling Tower',
                'h1' => 'Industrial Cooling Towers: Counterflow & Crossflow',
                'summary' => 'Induced-draft counterflow and crossflow cooling towers in FRP, timber or concrete, sized to your heat load and wet-bulb conditions.',
                'meta' => 'Industrial counterflow and crossflow cooling towers with FRP, timber or concrete structure, PVC or PP fill and low-drift eliminators for process and HVAC duty.',
                'intro' => 'A cooling tower rejects process heat by evaporating a small part of the circulating water. We supply induced-draft towers sized to your heat load, flow rate and local wet-bulb temperature, in the structure and fill that suit your water quality and duty cycle.',
                'sections' => [
                    'Tower Types' => [
                        '<strong>Counterflow:</strong> air rises against falling water through the fill. Compact footprint and high thermal efficiency, suited to clean circulating water.',
                        '<strong>Crossflow:</strong> air crosses the falling water horizontally. Lower pumping head and easy access to the distribution basin, suited to dirtier water and sites needing low fan power.',
                        '<strong>Modular and field-erected:</strong> factory-built cells for quick installation, or field-erected towers for large heat loads that are built cell by cell.',
                    ],
                    'Core Components' => [
                        '<strong>Fill:</strong> PVC or PP film fill for clean water, or splash fill where the water carries solids, oil or scale.',
                        '<strong>Drift eliminators:</strong> cellular PVC or PP blades that keep water carry-over at a fraction of a percent of circulating flow.',
                        '<strong>Fan and drive:</strong> axial FRP fan with gearbox or belt drive and a TEFC motor, with vibration cut-out as an option.',
                        '<strong>Water distribution:</strong> spray nozzles or gravity-fed basin for even loading across the fill.',
                        '<strong>Basin and casing:</strong> structure sized for the water volume, with access doors, ladders and handrails.',
                    ],
                    'Keeping It Running' => 'Fill fouling, basin leaks and gearbox wear are what cost a tower its performance. Our field team handles <a href="/services/cooling-tower-repair/">cooling tower repair</a>, fill replacement and fan drive alignment, so one supplier covers both the new tower and its upkeep.',
                ],
                'features' => [
                    'Sized to your heat load, range, approach and design wet-bulb temperature',
                    'Counterflow, crossflow, modular and field-erected configurations',
                    'Corrosion-resistant FRP structure that suits humid, chemical and coastal sites',
                    'Low-drift eliminators that limit water loss and plume carry-over',
                    'Efficient axial fans with variable speed drive as an option',
                    'Replacement towers designed to fit existing basins and piping',
                ],
                'applications' => [
                    'Process cooling in chemical, petrochemical and food plants',
                    'Condenser water for power generation and cogeneration',
                    'Air-conditioning and chiller plant heat rejection',
                    'Plastics, injection molding and compressor cooling',
                    'Pulp, paper and textile mills',
                ],
                'materials' => [
                    'FRP, hot-dip galvanized steel, timber or reinforced concrete structure',
                    'PVC or PP fill and drift eliminators',
                    'FRP or aluminium fan blades',
                    'Stainless steel or hot-dip galvanized fasteners',
                    'HDPE, PVC or stainless steel distribution piping and nozzles',
                ],
                'specs' => [
                    'Typical capacity: from small modular cells up to several thousand m³/h per tower',
                    'Typical cooling range: 5–10 °C, with approach to wet-bulb of 3–6 °C',
                    'Drift loss: typically 0.001–0.005% of circulating flow with low-drift eliminators',
                    'Design wet-bulb: set to local site conditions',
                    'Capacity, number of cells and fan power configured to your duty',
                ],
            ],
            [
                'url_path' => '/product/heat-exchanger/',
                'title' => 'Heat Exchanger',
                'h1' => 'Industrial Heat Exchangers: Shell and Tube & Finned Coil',
                'summary' => 'Shell and tube heat exchangers to TEMA standards and finned-coil air-cooled heat exchangers, built to your process duty.',
                'meta' => 'Shell and tube heat exchangers built to TEMA standards and finned-coil air-cooled heat exchangers, in carbon steel, stainless steel, copper and alloy tubes for process, power and HVAC duty.',
                'intro' => 'We build two families of heat exchanger. Shell and tube units move heat between two process fluids and are the standard choice for high pressure, high temperature and fouling duties. Finned-coil units reject heat straight to the air, cooling or heating a fluid where cooling water is scarce or unwanted. Both are built to your process data and site conditions.',
                'sections' => [
                    'Shell and Tube Heat Exchangers' => [
                        '<strong>Fixed tubesheet:</strong> simplest and lowest cost, for clean services where the shell side needs no mechanical cleaning.',
                        '<strong>U-tube:</strong> the bundle expands freely and can be pulled for shell-side cleaning, with the tube side limited to chemical cleaning.',
                        '<strong>Floating head:</strong> bundle removable for cleaning on both sides, for dirty services and wide temperature differences.',
                        'Thermal and mechanical design to TEMA classes R, C or B and recognized pressure vessel codes, with baffle layout balanced against pressure drop and vibration.',
                    ],
                    'Finned-Coil Heat Exchangers' => [
                        '<strong>Air-cooled coolers:</strong> process fluid in finned tubes with fans moving ambient air across the coil. No cooling water, no cooling tower and no water treatment.',
                        '<strong>Air heaters and coolers:</strong> steam, hot water or chilled water coils for heating or cooling process air, dryers and ventilation.',
                        '<strong>Radiators and dry coolers:</strong> closed-loop cooling of engine jacket water, glycol and compressor circuits.',
                        'Fin type, fin pitch, tube rows and circuiting chosen for the duty, the air side pressure drop and how dirty the air is.',
                    ],
                    'Choosing Between Them' => 'Shell and tube suits liquid-to-liquid and steam duties, high pressures and fluids that foul. Finned coil suits sites where cooling water is limited or expensive, and duties where the fluid can be cooled to within 10–15 °C of ambient air. Send your process data and we will recommend the type.',
                    'Service and Retubing' => 'Tube leaks, fouling, fin damage and corrosion are the usual end of a bundle or coil. We offer <a href="/services/heat-exchanger-repair/">heat exchanger repair</a>, retubing, coil cleaning and bundle replacement, so a failing exchanger can be renewed to its original footprint.',
                ],
                'features' => [
                    'Shell and tube and finned-coil designs from one supplier',
                    'Built to your process data, with thermal rating and drawings supplied',
                    'Fixed, U-tube and floating head bundles for different cleaning needs',
                    'Air-cooled coils that remove the need for cooling water',
                    'Wide choice of tube, fin and shell materials for corrosive services',
                    'Replacement bundles and coils matched to existing nozzles and frames',
                    'Hydrostatic test and inspection documentation on request',
                ],
                'applications' => [
                    'Process heating and cooling in refineries, chemical and petrochemical plants',
                    'Steam condensers and feed water heaters',
                    'Palm oil, food and beverage heating and cooling',
                    'Air-cooled process and lube oil cooling on water-scarce sites',
                    'Engine jacket water, glycol and compressor cooling',
                    'Steam and hot water air heaters for dryers and ventilation',
                    'Heat recovery from process streams and flue gas',
                ],
                'materials' => [
                    'Carbon steel or stainless steel shell, channels and coil frames',
                    'Tubes in carbon steel, stainless steel 304/316, duplex, copper alloy or titanium',
                    'Fins in aluminium, copper or galvanized steel, wound, extruded or plate type',
                    'Tubesheets in carbon steel, stainless steel or clad construction',
                    'Gaskets in spiral wound, graphite or PTFE',
                ],
                'specs' => [
                    'Shell and tube standards: TEMA classes R, C and B, with design to recognized pressure vessel codes',
                    'Shell and tube design pressure: typically up to 40 bar, higher by design',
                    'Shell and tube design temperature: typically up to 400 °C, depending on material',
                    'Finned coil approach: typically 10–15 °C above ambient air temperature',
                    'Finned coil fin pitch: commonly 2.5–5 mm, wider for dusty air',
                    'Surface area, passes, tube rows and fan power configured to your duty',
                ],
            ],
            [
                'url_path' => '/product/plate-heat-exchanger/',
                'title' => 'Plate Heat Exchanger',
                'h1' => 'Plate Heat Exchangers: Gasketed, Brazed & Welded',
                'summary' => 'Compact plate heat exchangers with high heat transfer per square metre, in gasketed, brazed and welded versions.',
                'meta' => 'Gasketed, brazed and welded plate heat exchangers in stainless steel and titanium plates, with compact footprint, high efficiency and easy capacity expansion.',
                'intro' => 'Corrugated plates give a plate heat exchanger several times the heat transfer of a shell and tube unit of the same size. The result is a compact, efficient exchanger with a close temperature approach, and in the gasketed version one that can be opened up and extended as your duty changes.',
                'sections' => [
                    'Plate Types' => [
                        '<strong>Gasketed:</strong> plates sealed by gaskets in a bolted frame. Opens fully for cleaning and inspection, and plates can be added if duty grows.',
                        '<strong>Brazed:</strong> plates brazed into a sealed block without gaskets or frame. Very compact and low cost, for clean fluids in refrigeration and HVAC.',
                        '<strong>Welded:</strong> plate pairs welded together for high pressure, high temperature or aggressive media where gaskets are unsuitable.',
                    ],
                    'Why Choose Plates' => [
                        'High turbulence keeps the plate surface cleaner and gives a close temperature approach, often under 2 °C.',
                        'Footprint and weight are a fraction of an equivalent shell and tube exchanger.',
                        'Capacity is raised by adding plates to a gasketed frame, with no new exchanger needed.',
                        'Small liquid holdup gives quick response to load changes.',
                    ],
                    'Maintenance' => 'Plates and gaskets are the wear items. Worn gaskets are the commonest cause of leaks, and replacement gaskets and plates can be supplied for most makes. See our <a href="/services/heat-exchanger-repair/">heat exchanger repair service</a> for cleaning, re-gasketing and pressure testing.',
                ],
                'features' => [
                    'High heat transfer in a compact, lightweight frame',
                    'Close temperature approach for efficient heat recovery',
                    'Gasketed units open fully for cleaning and inspection',
                    'Capacity can be extended by adding plates',
                    'Multiple plate patterns to balance heat transfer and pressure drop',
                    'Replacement plates and gaskets for common makes',
                ],
                'applications' => [
                    'Central cooling loops and closed-circuit cooling',
                    'Chiller, HVAC and district cooling systems',
                    'Food, beverage and dairy heating, cooling and pasteurizing',
                    'Palm oil and edible oil heating',
                    'Heat recovery between process streams',
                    'Swimming pool and utility water heating',
                ],
                'materials' => [
                    'Plates in stainless steel 304/316, titanium or Hastelloy',
                    'Gaskets in NBR, EPDM or Viton, chosen for the fluid and temperature',
                    'Carbon steel frame with protective coating',
                    'Brazed units with stainless steel plates and copper or nickel braze',
                ],
                'specs' => [
                    'Typical design pressure: gasketed up to 25 bar, welded and brazed higher',
                    'Typical design temperature: gasketed up to about 150–180 °C, depending on gasket',
                    'Plate thickness: commonly 0.4–0.8 mm',
                    'Temperature approach: as close as 1–2 °C in counterflow',
                    'Plate area, number of plates and connection sizes configured to your duty',
                ],
            ],
            [
                'url_path' => '/product/oil-cooler/',
                'title' => 'Oil Cooler',
                'h1' => 'Industrial Oil Coolers: Water-Cooled & Air-Cooled',
                'summary' => 'Shell and tube and air-cooled oil coolers for hydraulic, lubrication, gearbox and engine oil systems.',
                'meta' => 'Water-cooled shell and tube and air-cooled oil coolers for hydraulic, lubrication, gearbox and engine oil, with copper, stainless steel and aluminium construction.',
                'intro' => 'Oil that runs too hot thins out, oxidizes and wears the machine it is supposed to protect. An oil cooler holds hydraulic, lube and gearbox oil inside its working temperature, extending oil life and keeping viscosity, pressure and bearing life where they should be.',
                'sections' => [
                    'Cooler Types' => [
                        '<strong>Water-cooled shell and tube:</strong> oil on the shell side, cooling water through the tubes. Compact and efficient where cooling water is available.',
                        '<strong>Air-cooled:</strong> oil through a finned core with a fan, so no cooling water is needed. Suited to mobile, remote and water-scarce sites.',
                        '<strong>Plate type:</strong> brazed or gasketed plates for the most compact water-cooled solution on clean oil.',
                    ],
                    'Selecting a Cooler' => [
                        'Heat load to remove, from the power loss of the hydraulic or lube system.',
                        'Oil type and viscosity, flow rate and maximum allowable pressure drop.',
                        'Oil inlet temperature and the target outlet temperature.',
                        'Cooling water or ambient air temperature at the site.',
                        'Working pressure, with relief or bypass protection where flow can surge.',
                    ],
                    'Retrofits' => 'We can supply coolers to match existing mounting, connections and capacity. Send the old unit nameplate or the system data, and we will propose a replacement or an upgrade.',
                ],
                'features' => [
                    'Water-cooled and air-cooled options for any site',
                    'Compact designs for crowded machine rooms and mobile equipment',
                    'Removable tube bundles on larger shell and tube units',
                    'Integral low-fin copper tubes for more heat transfer on the oil side',
                    'Materials selected for oil, cooling water and sea-water duty',
                    'Replacement coolers matched to existing mounting and ports',
                    'Pressure tested before dispatch',
                ],
                'applications' => [
                    'Hydraulic power units and presses',
                    'Gearbox and bearing lubrication systems',
                    'Compressor and turbine oil circuits',
                    'Diesel engine and generator set oil cooling',
                    'Injection molding machines and machine tools',
                    'Marine and offshore machinery',
                ],
                'materials' => [
                    'Copper or cupronickel tubes for general duty',
                    'Stainless steel 304/316 or titanium for corrosive water',
                    'Aluminium finned cores for air-cooled units',
                    'Carbon steel or cast iron shell and end covers',
                    'Oil-resistant gaskets and O-rings',
                ],
                'specs' => [
                    'Typical oil working pressure: up to 16–25 bar, higher by design',
                    'Typical oil temperature: up to about 120 °C',
                    'Cooling water: typically up to 32–35 °C inlet',
                    'Heat duty: from a few kW to several hundred kW',
                    'Duty, flow, ports and mounting configured to your system',
                ],
            ],
            [
                'url_path' => '/product/water-cooled-cartridge/',
                'title' => 'Water-Cooled Cartridge',
                'h1' => 'Water-Cooled Cartridges: Replacement Cooler Tube Bundles',
                'summary' => 'Replaceable water-cooled tube bundle cartridges for oil coolers, compressor intercoolers and aftercoolers, and process coolers.',
                'meta' => 'Water-cooled cartridge tube bundles for oil coolers, compressor intercoolers and aftercoolers, built to fit existing shells in copper, cupronickel, stainless steel and titanium.',
                'intro' => 'A water-cooled cartridge is the removable tube bundle at the heart of a cooler: cooling water runs through the tubes while oil, air or gas passes over them inside the shell. When a bundle is fouled, leaking or corroded, replacing the cartridge restores full cooling duty without changing the shell, piping or mounting.',
                'sections' => [
                    'Cartridge Types' => [
                        '<strong>Straight-tube:</strong> tubes between two tubesheets, cleanable on the water side by rodding. The usual design for oil and process coolers.',
                        '<strong>U-tube:</strong> one tubesheet and free expansion of the bundle, for duties with a wide temperature difference.',
                        '<strong>Finned-tube:</strong> copper or aluminium fins on the outside of the tubes, for air and gas cooling in compressor intercoolers and aftercoolers.',
                    ],
                    'Built to Fit' => [
                        'Made to the dimensions of the original cartridge or drawn from the old bundle on site.',
                        'Tubesheet diameter, tube count, length and baffle positions matched to the existing shell.',
                        'Gasket and O-ring seats matched to the existing end covers.',
                        'Tube material can be upgraded where the original suffered corrosion or erosion.',
                    ],
                    'Repair or Replace' => 'A bundle with a few leaking tubes can often be plugged or retubed, while one with widespread pitting or fouling is usually cheaper to replace. Our <a href="/services/heat-exchanger-repair/">heat exchanger repair service</a> inspects and pressure tests the old cartridge so you can decide on firm facts.',
                ],
                'features' => [
                    'Drop-in replacement that keeps the existing shell, piping and mounting',
                    'Removable bundle for water-side cleaning and inspection',
                    'Optional wire turbulator inserts that raise tube-side heat transfer',
                    'Straight-tube, U-tube and finned-tube designs',
                    'Tube materials selected for fresh, cooling tower or sea water',
                    'Reverse-engineered from the old cartridge when drawings are missing',
                    'Pressure tested before dispatch',
                ],
                'applications' => [
                    'Hydraulic and lube oil coolers',
                    'Air compressor intercoolers and aftercoolers',
                    'Turbine, gearbox and bearing oil systems',
                    'Diesel engine and generator set coolers',
                    'Marine and sea-water cooled machinery',
                ],
                'materials' => [
                    'Tubes in copper, admiralty brass or cupronickel 90/10 and 70/30',
                    'Stainless steel 304/316 or titanium tubes for corrosive water',
                    'Tubesheets in naval brass, carbon steel or stainless steel',
                    'Copper or aluminium fins for air and gas cooling',
                    'Gaskets and O-rings chosen for the oil or gas being cooled',
                ],
                'specs' => [
                    'Typical tube outside diameter: 6–19 mm, depending on cooler size',
                    'Typical water-side pressure: up to 10–16 bar',
                    'Typical shell-side pressure: up to 25 bar for oil, higher by design',
                    'Cooling water: typically up to 32–35 °C inlet',
                    'Dimensions, tube count and passes matched to the existing cooler',
                ],
            ],
        ];
    }
}
