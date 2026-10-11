<?php

namespace App\Support;

class MaterialLibrary
{
    /**
     * Families in display order, with the short label used on filter chips.
     */
    public const FAMILIES = [
        'Carbon & Low Alloy Steels' => 'Carbon & Alloy Steel',
        'Austenitic & Stainless Steels' => 'Stainless Steel',
        'Duplex & Super Duplex' => 'Duplex',
        'Nickel & High-Temp Alloys' => 'Nickel Alloys',
        'Non-Ferrous Alloys (Copper, Brass, Alu)' => 'Copper & Aluminium',
        'Titanium & Special Reactive Metals' => 'Titanium & Zirconium',
        'Elastomers & Rubbers' => 'Elastomers',
        'Fluoropolymers & Engineering Plastics' => 'Fluoropolymers & Plastics',
        'Ceramics, Refractories & Gaskets' => 'Ceramics, FRP & Gaskets',
    ];

    /**
     * Product pages a material can link to: slug => [name, inquiry category].
     */
    public const PRODUCTS = [
        'metal-expansion-joint' => ['Metal Expansion Joint', 'Flexible Joint'],
        'fabric-expansion-joint' => ['Fabric Expansion Joint', 'Flexible Joint'],
        'rubber-expansion-joint' => ['Rubber Expansion Joint', 'Flexible Joint'],
        'ptfe-expansion-joint' => ['PTFE Expansion Joint', 'Flexible Joint'],
        'ptfe-lined-rubber-metal-expansion-joint' => ['PTFE Lined Expansion Joint', 'Flexible Joint'],
        'flexible-metal-hose' => ['Flexible Metal Hose', 'Flexible Joint'],
        'ptfe-lining' => ['PTFE Lining', 'Corrosion Protection'],
        'rubber-lining' => ['Rubber Lining', 'Corrosion Protection'],
        'ceramic-lining' => ['Ceramic Lining', 'Corrosion Protection'],
        'ceramic-coating' => ['Ceramic Coating', 'Corrosion Protection'],
        'frp-lining' => ['FRP Lining', 'Corrosion Protection'],
        'gasket-sheet' => ['Gasket Sheet', 'Sealing System'],
        'gland-packing' => ['Gland Packing', 'Sealing System'],
        'o-ring' => ['O-Ring', 'Sealing System'],
        'oil-seal' => ['Oil Seal', 'Sealing System'],
        'mechanical-seal' => ['Mechanical Seal', 'Sealing System'],
        'coupling' => ['Coupling', 'General Inquiry'],
        'cooling-tower' => ['Cooling Tower', 'General Inquiry'],
        'heat-exchanger' => ['Heat Exchanger', 'General Inquiry'],
        'plate-heat-exchanger' => ['Plate Heat Exchanger', 'General Inquiry'],
    ];

    /**
     * All materials, each with its product links resolved to name/url/category.
     * "fabrications" are made-to-drawing items with no product page; the view links them to a quote.
     */
    public static function all(): array
    {
        $materials = json_decode(file_get_contents(resource_path('data/material-specs.json')), true);

        return array_map(function (array $material) {
            $material['products'] = array_values(array_map(fn (string $slug) => [
                'name' => self::PRODUCTS[$slug][0],
                'url' => "/product/{$slug}/",
                'category' => self::PRODUCTS[$slug][1],
            ], array_filter($material['products'], fn (string $slug) => isset(self::PRODUCTS[$slug]))));
            $material['fabrications'] ??= [];

            return $material;
        }, $materials);
    }
}
