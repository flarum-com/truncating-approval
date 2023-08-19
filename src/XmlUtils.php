<?php

/*
 * This file is part of flarum-com/truncating-approval.
 *
 * Copyright (c) 2023 Flarum Commercial Team.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FlarumCom\TruncatingApproval;

use DOMDocument;
use InvalidArgumentException;

class XmlUtils
{
    static protected function ignoredTags(): array
    {
        return [
            // Start symbol tag for unparsing
            's',
            // Text
            'p',
            // End symbol tag for unparsing
            'e',
            // Plain text content
            't',
            // Rich text content
            'r',
        ];
    }

    /**
     * Parses an XML string into an array.
     * 
     * @throws InvalidArgumentException if the XML is invalid
     *
     * @param string $xml
     * @return array
     */
    static public function parseXmlToArray(string $xml): array
    {
        $parserFlags = ((LIBXML_VERSION >= 20700) ? LIBXML_COMPACT | LIBXML_PARSEHUGE : 0) | LIBXML_NOCDATA;
        $parsedXml = simplexml_load_string($xml, 'SimpleXMLElement', $parserFlags);

        if ($parsedXml === false) {
            throw new InvalidArgumentException('Cannot load XML: ' . libxml_get_last_error()->message);
        }

        $contentArray = json_decode(json_encode($parsedXml), true);

        return $contentArray;
    }

    /**
     * Provided an input array, this method recursively returns an array of all keys in the array mapped to the number of instances.
     *
     * @example Example
     * ```php
     * $array = [
     *    'foo' => 'bar',
     *    'baz' => [
     *      'foo' => 'bar',
     *      'bar' => 'baz',
     *    ]
     * ];
     * 
     * $this->arrayKeysRecursive($array);
     * 
     * // Returns:
     * [
     *   'foo' => 2,
     *   'baz' => 1,
     *   'bar' => 1,
     * ]
     * ```
     */
    static public function getXmlTagsFromArrayRecursive(array $array): array
    {
        $arrayKeys = [];

        foreach ($array as $key => $value) {
            $arrayKeys[$key] = array_key_exists($key, $arrayKeys) ? $arrayKeys[$key] + 1 : 1;

            if (is_array($value)) {
                $recursiveKeys = self::getXmlTagsFromArrayRecursive($value);

                foreach ($recursiveKeys as $recursiveKey => $count) {
                    $arrayKeys[$recursiveKey] = array_key_exists($recursiveKey, $arrayKeys) ? $arrayKeys[$recursiveKey] + $count : $count;
                }
            }
        }

        foreach (self::ignoredTags() as $ignoredTag) {
            unset($arrayKeys[$ignoredTag]);
        }

        return $arrayKeys;
    }

    static public function stripXmlTagsFromXmlString(array $tags, string $xml): string
    {
        $dom = new DOMDocument();
        $dom->loadXML($xml);

        foreach ($tags as $tag) {
            $iterator = $dom->getElementsByTagName($tag)->getIterator();

            while ($iterator->valid()) {
                $iterator->current()->parentNode->removeChild($iterator->current());
                $iterator->next();
            }
        }

        return $dom->saveXML($dom->documentElement, LIBXML_NOEMPTYTAG);
    }
}
