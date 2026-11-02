<?php

namespace App\Support;

/**
 * Cleans a vector drawing before it is stored.
 *
 * An SVG is markup, not a picture file, and may carry scripts, event handlers
 * and references out to other addresses. Since drawings are served from the
 * portal's own address, anything left in one would run with the portal's own
 * privileges, so the parts that can carry behaviour are taken out.
 */
class Svg
{
    /**
     * Elements that exist to run something or to pull in something else.
     */
    protected const STRIP_ELEMENTS = [
        'script', 'foreignobject', 'handler', 'iframe', 'embed', 'object',
        'audio', 'video', 'set', 'animate', 'animatetransform', 'animatemotion',
    ];

    /**
     * Attributes that name a target to fetch or a document to replace.
     */
    protected const URL_ATTRIBUTES = ['href', 'xlink:href', 'src', 'action', 'formaction', 'data', 'from', 'to', 'values', 'by'];

    public static function looksLikeSvg(string $extension): bool
    {
        return strtolower($extension) === 'svg';
    }

    /**
     * The drawing with everything that could run stripped out.
     *
     * Where the markup cannot be parsed at all, nothing is returned and the
     * caller should refuse the file rather than store something unexamined.
     */
    public static function clean(string $markup): ?string
    {
        if (trim($markup) === '') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $document = new \DOMDocument;

        // Entities are not expanded, so a drawing cannot be used to read a
        // file off the server by declaring one.
        $loaded = $document->loadXML($markup, LIBXML_NONET | LIBXML_NOENT);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! $document->documentElement) {
            return null;
        }

        if (strtolower($document->documentElement->localName) !== 'svg') {
            return null;
        }

        static::scrub($document->documentElement);

        return $document->saveXML() ?: null;
    }

    /**
     * Walk the drawing, taking out the elements and attributes that carry
     * behaviour. Children are collected first, because removing a node while
     * iterating the live list would skip its neighbour.
     */
    protected static function scrub(\DOMElement $element): void
    {
        foreach (iterator_to_array($element->childNodes) as $child) {
            if ($child instanceof \DOMElement) {
                if (in_array(strtolower($child->localName), self::STRIP_ELEMENTS, true)) {
                    $element->removeChild($child);

                    continue;
                }

                static::scrub($child);

                continue;
            }

            // A processing instruction can carry a stylesheet reference.
            if ($child instanceof \DOMProcessingInstruction) {
                $element->removeChild($child);
            }
        }

        foreach (iterator_to_array($element->attributes ?? []) as $attribute) {
            $name = strtolower($attribute->nodeName);

            // on… handlers, in any spelling.
            if (str_starts_with($name, 'on')) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            if (in_array($name, self::URL_ATTRIBUTES, true) && ! static::addressIsSafe($attribute->nodeValue)) {
                $element->removeAttributeNode($attribute);

                continue;
            }

            // A style attribute can fetch and, in older browsers, run things.
            if ($name === 'style' && preg_match('/url\s*\(|expression|javascript:/i', (string) $attribute->nodeValue)) {
                $element->removeAttributeNode($attribute);
            }
        }
    }

    /**
     * Whether an address inside a drawing may stay. A reference within the
     * drawing itself is fine, as is an ordinary web or data address for a
     * picture; anything that could run is not.
     */
    protected static function addressIsSafe(?string $value): bool
    {
        $value = trim((string) $value);

        if ($value === '' || str_starts_with($value, '#')) {
            return true;
        }

        if (preg_match('/^data:image\/(png|jpeg|gif|webp);base64,/i', $value)) {
            return true;
        }

        return (bool) preg_match('#^(https?:)?//#i', $value);
    }
}
