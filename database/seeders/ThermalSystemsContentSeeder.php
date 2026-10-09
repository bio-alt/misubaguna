<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Thermal Systems page content (category + 4 products).
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

        $meta = 'Cooling towers, shell and tube heat exchangers, plate heat exchangers and oil coolers for industrial process, power and HVAC duty.';

        DB::table('public_product_categories')
            ->where('url_path', '/product-category/thermal-systems/')
            ->update([
                'summary' => 'Cooling towers, shell and tube and plate heat exchangers, and oil coolers for process cooling, heat recovery and lubrication systems.',
                'description_html' => '<p>Heat has to go somewhere. Our thermal range covers the equipment that removes it from a plant: cooling towers that reject it to the atmosphere, heat exchangers that move it between process fluids, and oil coolers that keep hydraulic and lubrication systems inside their working temperature.</p><p>Units are sized to your duty, fluids and site conditions, and supplied new or as replacements for existing equipment. Repair, re-tubing, fill replacement and plate re-gasketing are covered by our field services.</p>',
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
                'h1' => 'Shell and Tube Heat Exchangers',
                'summary' => 'Custom-built shell and tube heat exchangers to TEMA standards, in fixed tubesheet, U-tube and floating head designs.',
                'meta' => 'Custom shell and tube heat exchangers built to TEMA standards, with fixed tubesheet, U-tube and floating head designs in carbon steel, stainless steel and alloy tubes.',
                'intro' => 'The shell and tube exchanger is the standard choice for high pressure, high temperature and heavily fouling duties. We build units to your process data and to TEMA standards, in a layout that suits how often the bundle has to be cleaned or pulled.',
                'sections' => [
                    'Construction Types' => [
                        '<strong>Fixed tubesheet:</strong> simplest and lowest cost, for clean services where the shell side needs no mechanical cleaning.',
                        '<strong>U-tube:</strong> the bundle expands freely and can be pulled for shell-side cleaning, with the tube side limited to chemical cleaning.',
                        '<strong>Floating head:</strong> bundle removable for cleaning on both sides, for dirty services and wide temperature differences.',
                    ],
                    'Design Basis' => [
                        'Thermal and mechanical design to your flow, temperatures, allowable pressure drop and fouling factors.',
                        'Pressure vessel design to recognized codes, with TEMA classes R, C or B according to duty.',
                        'Baffle layout chosen to balance heat transfer against pressure drop and vibration risk.',
                        'Nozzle sizes, orientation and supports matched to your piping and foundations.',
                    ],
                    'Service and Retubing' => 'Tube leaks, fouling and corrosion are the usual end of a bundle. We offer <a href="/services/heat-exchanger-repair/">heat exchanger repair</a>, retubing and bundle replacement, so a failing exchanger can be renewed to its original footprint.',
                ],
                'features' => [
                    'Built to your process data, with thermal rating and mechanical drawings supplied',
                    'Fixed, U-tube and floating head designs for different cleaning needs',
                    'Handles high pressure, high temperature and fouling fluids',
                    'Wide choice of tube and shell materials for corrosive services',
                    'Replacement bundles and exchangers matched to existing nozzle positions',
                    'Hydrostatic test and inspection documentation on request',
                ],
                'applications' => [
                    'Process heating and cooling in refineries, chemical and petrochemical plants',
                    'Steam condensers and feed water heaters',
                    'Palm oil, food and beverage heating and cooling',
                    'Heat recovery from process streams and flue gas',
                    'Compressor, engine and jacket water cooling',
                ],
                'materials' => [
                    'Carbon steel shell and channels',
                    'Tubes in carbon steel, stainless steel 304/316, duplex, copper alloy or titanium',
                    'Tubesheets in carbon steel, stainless steel or clad construction',
                    'Baffles and tie rods in carbon or stainless steel',
                    'Gaskets in spiral wound, graphite or PTFE',
                ],
                'specs' => [
                    'Standards: TEMA classes R, C and B, with design to recognized pressure vessel codes',
                    'Typical design pressure: up to 40 bar, higher by design',
                    'Typical design temperature: up to 400 °C, depending on material',
                    'Tube outside diameter: commonly 19.05 mm (3/4") or 25.4 mm (1")',
                    'Surface area, passes and tube length configured to your duty',
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
        ];
    }
}
