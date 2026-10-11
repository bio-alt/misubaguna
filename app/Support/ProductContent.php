<?php

namespace App\Support;

use DOMDocument;
use DOMElement;

/**
 * Presentation helpers for the public product page.
 */
class ProductContent
{
    /** Narrative headings that duplicate a structured product field. */
    private const DUPLICATE_HEADINGS = [
        'key_features' => ['key features', 'key features and benefits', 'features', 'features and benefits', 'benefits'],
        'applications' => ['applications', 'typical applications'],
        'materials' => ['materials of construction', 'construction and material', 'materials', 'construction'],
        'technical_specs' => ['technical specifications', 'technical notes', 'specifications', 'technical data'],
    ];

    public const WHATSAPP_NUMBER = '628118715671';

    public const CATALOG_PDF = '/wp-content/uploads/2023/11/Misuba-Guna-Indonesia-Company-Profile_Interactive.pdf';

    public static function whatsappUrl(string $subject): string
    {
        $message = app()->getLocale() === 'id'
            ? 'Halo PT Misuba Guna Indonesia, saya ingin menanyakan ' . $subject . '.'
            : 'Hello PT Misuba Guna Indonesia, I would like to inquire about ' . $subject . '.';

        return 'https://wa.me/' . self::WHATSAPP_NUMBER . '?text=' . rawurlencode($message);
    }

    /**
     * Split "Label: value" strings into table rows. Strings without a label
     * come back with a null label so the view can render them full width.
     *
     * @return array<int, array{label: ?string, value: string}>
     */
    public static function specRows(?array $specs): array
    {
        $rows = [];

        foreach ((array) $specs as $spec) {
            $spec = trim((string) $spec);
            if ($spec === '') {
                continue;
            }

            $parts = preg_split('/:\s+/', $spec, 2);
            if (count($parts) === 2 && mb_strlen($parts[0]) <= 48) {
                $rows[] = ['label' => $parts[0], 'value' => $parts[1]];
            } else {
                $rows[] = ['label' => null, 'value' => $spec];
            }
        }

        return $rows;
    }

    /**
     * The first few labelled spec rows, for the buying zone and product cards.
     *
     * @param  array<int, array{label: ?string, value: string}>  $rows
     * @return array<int, array{label: string, value: string}>
     */
    public static function keyFacts(array $rows, int $limit = 3): array
    {
        $labelled = array_filter($rows, fn (array $row) => $row['label'] !== null);

        return array_slice(array_values($labelled), 0, $limit);
    }

    /**
     * Prepare the narrative HTML: demote its h1 (the page already has one) and
     * drop sections that the template renders from structured fields, so the
     * same list never appears twice.
     *
     * @param  array<string, bool>  $structured  field name => has data
     */
    public static function narrative(?string $html, array $structured): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $doc = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8"?><div id="root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $doc->getElementById('root');
        if (! $root) {
            return $html;
        }

        $dropHeadings = [];
        foreach (self::DUPLICATE_HEADINGS as $field => $names) {
            if (! empty($structured[$field])) {
                $dropHeadings = array_merge($dropHeadings, $names);
            }
        }

        $skipping = false;
        $skipLevel = 0;
        $remove = [];

        foreach (iterator_to_array($root->childNodes) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $level = self::headingLevel($node);

            if ($level !== null) {
                if ($skipping && $level <= $skipLevel) {
                    $skipping = false;
                }

                $text = mb_strtolower(trim(preg_replace('/\s+/', ' ', $node->textContent)));
                if (! $skipping && in_array($text, $dropHeadings, true)) {
                    $skipping = true;
                    $skipLevel = $level;
                }
            }

            if ($skipping) {
                $remove[] = $node;
            }
        }

        foreach ($remove as $node) {
            $root->removeChild($node);
        }

        foreach (iterator_to_array($root->getElementsByTagName('h1')) as $h1) {
            $h2 = $doc->createElement('h2');
            while ($h1->firstChild) {
                $h2->appendChild($h1->firstChild);
            }
            $h1->parentNode->replaceChild($h2, $h1);
        }

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $doc->saveHTML($child);
        }

        return trim($out);
    }

    private static function headingLevel(DOMElement $node): ?int
    {
        if (preg_match('/^h([1-6])$/i', $node->tagName, $m)) {
            return (int) $m[1];
        }

        return null;
    }

    /** Fallback when a product has no checklist of its own. */
    private const QUOTE_DEFAULT = [
        'Size or dimensions (or a drawing)',
        'Operating pressure and temperature',
        'Medium and operating conditions',
        'Quantity and delivery location',
    ];

    /** Per-group fallback, used when the product slug has no entry below. */
    private const QUOTE_BY_GROUP = [
        'Flexible Joint' => [
            'Pipe or duct size and end connection',
            'Design pressure and temperature',
            'Medium and movements to absorb',
            'Quantity and delivery location',
        ],
        'Corrosion Protection' => [
            'Equipment or part to protect, with dimensions',
            'Medium, concentration and temperature',
            'Substrate and its current condition',
            'Workshop or on-site work, and location',
        ],
        'Sealing System' => [
            'Size or part number (or a sample)',
            'Equipment the seal is fitted to',
            'Medium, pressure and temperature',
            'Quantity and delivery location',
        ],
        'Thermal Systems' => [
            'Heat duty, fluids and flow rates',
            'Inlet and outlet temperatures',
            'Design pressure and site conditions',
            'Quantity and delivery location',
        ],
        'Paper & Pulp Machinery' => [
            'Paper grade and raw material',
            'Target capacity (t/day)',
            'Existing equipment or line layout',
            'Site location and project timeline',
        ],
    ];

    /** What we need from the buyer to quote each product, keyed by slug. */
    private const QUOTE_BY_SLUG = [
        'fabric-expansion-joint' => [
            'Duct size and shape (round or rectangular)',
            'Gas temperature, operating and peak',
            'Duct pressure or vacuum, and dust or condensate load',
            'Axial, lateral and angular movement to absorb',
            'Quantity and delivery location',
        ],
        'metal-expansion-joint' => [
            'Pipe size and end type (flanged or weld end)',
            'Design pressure and temperature',
            'Axial, lateral and angular movement to absorb',
            'Medium and preferred bellows material',
            'Quantity and delivery location',
        ],
        'rubber-expansion-joint' => [
            'Pipe size and flange standard (JIS, ANSI, DIN)',
            'Working pressure, vacuum and temperature',
            'Medium: water, slurry, oil or chemical',
            'Face-to-face length and movement needed',
            'Quantity and delivery location',
        ],
        'ptfe-expansion-joint' => [
            'Pipe size and flange standard',
            'Chemical medium and concentration',
            'Operating pressure, vacuum and temperature',
            'Face-to-face length or number of convolutions',
            'Quantity and delivery location',
        ],
        'ptfe-lined-rubber-metal-expansion-joint' => [
            'Pipe size, flange standard and face-to-face length',
            'Chemical medium and concentration',
            'Design pressure, vacuum and temperature',
            'Preferred body: rubber or metal bellows',
            'Quantity and delivery location',
        ],
        'rubber-hose' => [
            'Hose inside diameter and length',
            'Medium: air, water, oil, steam, chemical or food',
            'Working pressure, temperature and suction or discharge duty',
            'End fittings or couplings required',
            'Quantity and delivery location',
        ],
        'flexible-metal-hose' => [
            'Nominal size and overall length',
            'End connections: flange, thread, union or weld',
            'Design pressure and temperature',
            'Medium, and any movement or vibration to absorb',
            'Quantity and delivery location',
        ],
        'ptfe-lining' => [
            'Item to line (pipe spool, fitting, vessel, tank) with drawing or dimensions',
            'Chemical medium and concentration',
            'Operating temperature, pressure and vacuum',
            'Required lining thickness or standard',
            'Quantity and delivery location',
        ],
        'rubber-lining' => [
            'Equipment type and surface area or dimensions',
            'Medium: chemical, abrasive slurry or both',
            'Operating temperature',
            'Preferred rubber type and hardness, if any',
            'Workshop or on-site application, and location',
        ],
        'ceramic-lining' => [
            'Wear area and dimensions (chute, cyclone, pipe bend, hopper)',
            'Material handled, particle size and flow velocity',
            'Operating temperature and impact level',
            'Current wear life or failure problem',
            'Workshop or on-site work, and location',
        ],
        'frp-lining' => [
            'Equipment to line (tank, vessel, duct, floor) and surface area',
            'Chemical medium and concentration',
            'Operating temperature',
            'Substrate (steel or concrete) and its current condition',
            'Site location and preferred schedule',
        ],
        'ceramic-coating' => [
            'Part or surface to coat, with dimensions',
            'Service condition: heat, wear, corrosion or chemical',
            'Operating temperature',
            'Substrate material and current condition',
            'Quantity and delivery or site location',
        ],
        'ptfe-coating' => [
            'Parts to coat (bolts, nuts, fittings) and their sizes',
            'Quantity per size',
            'Coating colour and thickness requirement',
            'Service environment: offshore, chemical or outdoor',
            'Delivery location',
        ],
        'gland-packing' => [
            'Shaft or stem diameter and stuffing box bore (or packing section)',
            'Equipment: pump, valve, mixer or agitator',
            'Medium, pressure and temperature',
            'Shaft speed (rpm) for rotating equipment',
            'Quantity (rings or kg) and delivery location',
        ],
        'oil-seal' => [
            'Shaft diameter, housing bore and width (or part number)',
            'Preferred lip type and material (NBR, FKM)',
            'Shaft speed, temperature and lubricant',
            'Equipment make and model, if known',
            'Quantity and delivery location',
        ],
        'o-ring' => [
            'Inside diameter and cross-section (or AS568 or JIS size)',
            'Material and hardness (NBR, FKM, EPDM, silicone)',
            'Medium and temperature',
            'Static or dynamic duty',
            'Quantity and delivery location',
        ],
        'gasket-sheet' => [
            'Sheet type (non-asbestos, graphite, PTFE, rubber) or the service',
            'Sheet size and thickness, or cut gasket sizes and flange standard',
            'Medium, pressure and temperature',
            'Quantity (sheets or pieces) and delivery location',
        ],
        'mechanical-seal' => [
            'Pump make, model and shaft size (or seal part number)',
            'Single or double, cartridge or component seal',
            'Medium, pressure, temperature and speed (rpm)',
            'Preferred face and elastomer materials',
            'Quantity and delivery location',
        ],
        'coupling' => [
            'Coupling type (gear, grid, jaw, disc, tyre) or part number',
            'Motor power (kW) and speed (rpm)',
            'Driver and driven shaft diameters and keyways',
            'Equipment being coupled and space between shaft ends',
            'Quantity and delivery location',
        ],
        'cooling-tower' => [
            'Water flow rate (m³/h) or heat load',
            'Hot water, cold water and design wet-bulb temperatures',
            'Application (process, power, HVAC) and water quality',
            'Site space, location and any noise limits',
            'New unit, replacement or upgrade',
        ],
        'heat-exchanger' => [
            'Hot and cold fluids, flow rates and temperatures',
            'Design pressure and temperature, shell and tube side',
            'Allowable pressure drop and fouling',
            'Material and code requirements (TEMA, ASME)',
            'Quantity and delivery location',
        ],
        'plate-heat-exchanger' => [
            'Hot and cold fluids, flow rates and inlet and outlet temperatures',
            'Design pressure and allowable pressure drop',
            'Preferred plate and gasket materials',
            'Existing unit make and model, if replacing plates or gaskets',
            'Quantity and delivery location',
        ],
        'oil-cooler' => [
            'Oil type, flow rate and inlet and target outlet temperatures',
            'Cooling medium (water or air) and its temperature',
            'Equipment served: gearbox, hydraulic, turbine or transformer',
            'Connection size and space limits',
            'Quantity and delivery location',
        ],
        'water-cooled-cartridge' => [
            'Existing cooler make and model, or a drawing of the bundle',
            'Shell bore, tube length and tube count',
            'Oil or gas side and water side temperatures and flows',
            'Cooling water quality and preferred tube material',
            'Quantity and delivery location',
        ],
        'hicon-pulper' => [
            'Raw material (OCC, waste paper, virgin pulp) and contaminant level',
            'Required capacity (t/day) and consistency',
            'Batch or continuous operation',
            'Existing line layout and power supply',
            'Site location and timeline',
        ],
        'disc-refiner' => [
            'Furnish type and target freeness',
            'Production rate (t/day) and consistency',
            'Plate diameter or existing refiner model',
            'Available motor power',
            'Site location and timeline',
        ],
        'screening-cleaning-system' => [
            'Furnish and contaminants to remove',
            'Production rate (t/day) and consistency',
            'Paper grade produced',
            'Existing screens or cleaners, if upgrading',
            'Site location and timeline',
        ],
        'paper-machine-line' => [
            'Paper grade and basis weight range (gsm)',
            'Target capacity (t/day), trim width and speed',
            'Raw material: recycled, virgin or mixed',
            'Site, utilities (steam, power) and building limits',
            'Project timeline',
        ],
        'tissue-machine' => [
            'Tissue grade (toilet, facial, napkin, towel) and gsm',
            'Target capacity (t/day), width and speed',
            'Raw material and fibre mix',
            'Site, energy source and layout limits',
            'Project timeline',
        ],
        'molded-fiber-machine' => [
            'Products to make (egg trays, fruit trays, industrial packaging, tableware)',
            'Output (pieces per hour or t/day)',
            'Raw material and drying method (sun, gas, diesel)',
            'Site area, energy source and level of automation',
            'Project timeline',
        ],
        'refiner-discs-screen-baskets' => [
            'Refiner or screen make, model and size',
            'Plate pattern or basket slot or hole size (drawing or sample)',
            'Furnish and production rate',
            'Current wear life',
            'Quantity and delivery location',
        ],
    ];

    /**
     * What a buyer should send us to get a quote for this product.
     *
     * @return array<int, string>
     */
    public static function quoteChecklist(?string $slug, ?string $group = null): array
    {
        return self::QUOTE_BY_SLUG[$slug ?? '']
            ?? self::QUOTE_BY_GROUP[$group ?? '']
            ?? self::QUOTE_DEFAULT;
    }
}
