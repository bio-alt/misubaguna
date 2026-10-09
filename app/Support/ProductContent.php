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
        $message = 'Hello PT Misuba Guna Indonesia, I would like to inquire about ' . $subject . '.';

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
}
