<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * NDT Inspection and On-Site 3D Scanning service pages.
 * Safe to run on its own: only touches these two rows.
 *   php artisan db:seed --class=InspectionServicesSeeder
 */
class InspectionServicesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $services = [
            [
                'slug' => 'ndt-inspection',
                'url_path' => '/services/ndt-inspection/',
                'name' => 'NDT Inspection Services',
                'service_group' => 'Mechanical & Field Services',
                'headline' => 'Non-Destructive Testing (NDT): UT, Phased Array, TOFD, Radiography, Hardness & PMI',
                'summary' => 'On-site non-destructive testing for welds, piping, pressure vessels, and rotating equipment, including ultrasonic testing and thickness gauging, phased array (PAUT) and TOFD, radiography, hardness testing, and positive material identification.',
                'key_features' => json_encode(['Ultrasonic testing (UT) for weld flaw detection and lamination checks', 'Ultrasonic wall thickness gauging and corrosion monitoring on piping, tanks, and vessels', 'Phased array (PAUT) corrosion mapping and weld inspection with recorded, reviewable data', 'Time-of-flight diffraction (TOFD) for accurate crack sizing on butt welds', 'Radiographic testing (RT) for weld quality verification', 'Portable hardness testing and positive material identification (PMI) to verify alloy grades']),
                'typical_problems' => json_encode(['Unknown remaining wall thickness on corroded or eroded piping and vessels', 'Suspected cracks or defects in welds after repair, fabrication, or long service', 'Mixed-up or unverified material grades in spare parts and spools', 'Hardness changes after welding or heat exposure that risk brittle failure', 'Insurance, shutdown, or fitness-for-service decisions without reliable inspection data']),
                'scope_of_work' => json_encode(['Inspection planning with the client: components, critical locations, and acceptance criteria', 'Surface preparation and on-site UT, PAUT, TOFD, or RT examination', 'Thickness grid measurement and corrosion mapping on piping and equipment', 'Hardness testing and PMI on welds, base metals, and spare parts', 'Inspection report with findings, thickness data, defect locations, and repair recommendations']),
                'industries_served' => json_encode(['Power & Energy Sector', 'Petrochemical & Gas Processing', 'Pulp & Paper Mills', 'Palm Oil Mills', 'Mining & Mineral Processing', 'Manufacturing & Steel Works']),
                'materials_equipment' => json_encode(['Ultrasonic Flaw Detector', 'Ultrasonic Thickness Gauge', 'Phased Array (PAUT) & TOFD Unit', 'Radiography Equipment', 'Portable Hardness Tester', 'XRF PMI Analyzer']),
                'related_products' => json_encode(['metal-expansion-joint', 'heat-exchanger', 'ptfe-lining', 'ceramic-lining']),
                'sort_order' => 13,
                'meta_title' => 'NDT Inspection Services (UT, PAUT, TOFD, RT, PMI) - PT Misuba Guna Indonesia',
                'meta_description' => 'On-site NDT inspection in Indonesia: ultrasonic testing, thickness gauging, phased array, TOFD, radiography, hardness testing, and PMI for welds, piping, and pressure equipment.',
                'canonical_url' => 'https://misubaguna.com/services/ndt-inspection/',
                'og_title' => 'NDT Inspection Services (UT, PAUT, TOFD, RT, PMI) - PT Misuba Guna Indonesia',
                'og_description' => 'On-site NDT inspection in Indonesia: ultrasonic testing, thickness gauging, phased array, TOFD, radiography, hardness testing, and PMI for welds, piping, and pressure equipment.',
                'robots_index' => true,
                'robots_follow' => true,
                'sitemap_include' => true,
                'sitemap_priority' => '0.8',
                'sitemap_changefreq' => 'monthly',
                'is_published' => true,
            ],
            [
                'slug' => 'onsite-3d-scanning',
                'url_path' => '/services/onsite-3d-scanning/',
                'name' => 'On-Site 3D Scanning',
                'service_group' => 'Mechanical & Field Services',
                'headline' => 'On-Site 3D Scanning for Reverse Engineering and Wear & Deformation Analysis',
                'summary' => 'Portable 3D scanning at your plant to capture accurate geometry of worn, obsolete, or undocumented parts, producing CAD models for replacement manufacturing and scan-to-CAD comparison reports for wear and deformation.',
                'key_features' => json_encode(['Portable high-accuracy 3D scanning performed on site, often without removing the part', 'Reverse engineering of worn or obsolete parts into editable CAD models and drawings', 'Scan-to-CAD and scan-to-scan comparison with colour deviation maps', 'Quantified wear, erosion, and deformation measurement for repair or replacement decisions', 'Scan data ready for in-house manufacturing of replacement parts']),
                'typical_problems' => json_encode(['Spare parts with no drawings, discontinued by the OEM, or with long lead times', 'Worn components where the original geometry must be rebuilt before remanufacture', 'Unknown wear rate on impellers, rotors, liners, refiner plates, and chutes', 'Shell ovality, bending, or distortion that is hard to measure with hand tools', 'Replacement parts that do not fit because the installed condition differs from the drawing']),
                'scope_of_work' => json_encode(['Site survey and scan planning, including targets and access during shutdown windows', 'On-site 3D scanning of components or equipment areas', 'Point cloud processing and mesh generation', 'Reverse engineering into CAD models and 2D manufacturing drawings', 'Deviation analysis report comparing scan with CAD or previous scans', 'Optional manufacture of replacement parts from the scanned data']),
                'industries_served' => json_encode(['Pulp & Paper Mills', 'Power & Energy Sector', 'Mining & Mineral Processing', 'Palm Oil Mills', 'Petrochemical & Gas Processing', 'Manufacturing & Steel Works']),
                'materials_equipment' => json_encode(['Portable 3D Scanner', 'Scanning Targets & Reference Markers', 'Point Cloud & Mesh Processing Software', 'CAD Modelling Software']),
                'related_products' => json_encode(['refiner-discs-screen-baskets', 'hicon-pulper', 'coupling', 'ceramic-lining']),
                'sort_order' => 14,
                'meta_title' => 'On-Site 3D Scanning & Reverse Engineering - PT Misuba Guna Indonesia',
                'meta_description' => 'On-site 3D scanning in Indonesia for reverse engineering of worn or obsolete parts and scan-to-CAD wear and deformation analysis.',
                'canonical_url' => 'https://misubaguna.com/services/onsite-3d-scanning/',
                'og_title' => 'On-Site 3D Scanning & Reverse Engineering - PT Misuba Guna Indonesia',
                'og_description' => 'On-site 3D scanning in Indonesia for reverse engineering of worn or obsolete parts and scan-to-CAD wear and deformation analysis.',
                'robots_index' => true,
                'robots_follow' => true,
                'sitemap_include' => true,
                'sitemap_priority' => '0.8',
                'sitemap_changefreq' => 'monthly',
                'is_published' => true,
            ],
        ];

        foreach ($services as $service) {
            $exists = DB::table('public_services')->where('url_path', $service['url_path'])->exists();
            DB::table('public_services')->updateOrInsert(
                ['url_path' => $service['url_path']],
                array_merge($service, $exists ? ['updated_at' => $now] : ['created_at' => $now, 'updated_at' => $now])
            );
        }
    }
}
