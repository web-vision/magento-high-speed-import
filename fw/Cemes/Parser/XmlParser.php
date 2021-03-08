<?php

namespace Cemes\Parser;

use \DOMDocument;
use DOMNode;

/**
 * web-vision GmbH
 *
 * NOTICE OF LICENSE
 *
 * <!--LICENSETEXT-->
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.web-vision.de for more information.
 *
 * @category    WebVision
 * @package     Cemes\Parser
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class XmlParser extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public static function read($file)
    {
        static::_isReadable($file);

        $xml = new DOMDocument();
        $xml->load($file);

        return static::toArray($xml);
    }

    /**
     * @inheritDoc
     */
    public static function write($file, $data)
    {
        static::_isWritable($file);

        // WVTODO don't use DomDocument to write build XML and write it
        // directly write the xml with XMLWriter
        // benefit: faster, less memory, proper indention
        // @see http://nnarhinen.github.io/2011/01/15/Serving-large-xml-files.html
        $output = is_object($data) ? static::fromObject($data) : static::fromArray($data);

        static::_writeToFile($file, $output);
    }

    /**
     * @inheritDoc
     */
    protected static function fromObject($object)
    {
        $array = \Cemes_StdLib::objectToArray($object);

        return static::fromArray($array);
    }

    /**
     * @inheritDoc
     */
    protected static function fromArray($array)
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        if (!is_array($array)) {
            return $xml;
        }

        $value = reset($array);
        $rootNode = key($array);

        $xml->appendChild(static::_arrayToXml($xml, $rootNode, $value));

        return $xml->saveXML();
    }

    /**
     * @inheritDoc
     */
    public static function toArray($data)
    {
        $output = [];

        if (!$data instanceof DOMDocument && !$data instanceof DOMNode) {
            return $output;
        }

        switch ($data->nodeType) {
            // root element
            case XML_DOCUMENT_NODE:
                $output = static::toArray($data->documentElement);
                break;
            // comment
            case XML_COMMENT_NODE:
                $output = '';
                break;
            // CDATA content
            case XML_CDATA_SECTION_NODE:
                $output['@cdata'] = trim($data->textContent);
                break;
            // normal content
            case XML_TEXT_NODE:
                $output = trim($data->textContent);
                break;
            // node with childs
            case XML_ELEMENT_NODE:
                // parse children
                for ($i = 0; $i < $data->childNodes->length; $i++) {
                    $child = $data->childNodes->item($i);
                    $tmp = static::toArray($child);
                    if (isset($child->tagName)) {
                        $tag = $child->tagName;
                        if (!array_key_exists($tag, $output)) {
                            $output[$tag] = [];
                        }
                        $output[$tag][] = $tmp;
                    } else {
                        if ($tmp !== '') {
                            $output = $tmp;
                        }
                    }
                }

                // cleanup
                if (is_array($output)) {
                    // if only one node with same name assign it directly
                    foreach ($output as $tag => $value) {
                        if (is_array($value) && count($value) === 1) {
                            $output[$tag] = $value[0];
                        }
                    }
                    // if node was empty set it to an empty string instead of an empty array
                    if (empty($output)) {
                        $output = '';
                    }
                }

                // parse attributes
                if ($data->attributes->length) {
                    $attributes = [];
                    foreach ($data->attributes as $attrName => $attrNode) {
                        $attributes[$attrName] = $attrNode->value;
                    }
                    if (!is_array($output)) {
                        $output = ['@value' => $output];
                    }
                    $output['@attributes'] = $attributes;
                }
                break;
        }

        return $output;
    }

    protected static function _arrayToXml(DOMDocument $xml, $nodeName, $array)
    {
        $node = $xml->createElement($nodeName);

        if (is_array($array)) {
            // process attributes
            if (array_key_exists('@attributes', $array)) {
                foreach ($array['@attributes'] as $key => $value) {
                    if (!static::isValidTagName($key)) {
                        throw new ParserException(
                            'Illegal attribute name. Attribute: "' . $key . '" in node "' . $nodeName . '"'
                        );
                    }

                    $node->setAttribute($key, self::bool2str($value));
                }
                unset($array['@attributes']);
            }

            // check if a direct value is present as @value or @cdata
            // if yes store the value and return as there can't be any subnodes
            if (array_key_exists('@value', $array)) {
                $node->appendChild($xml->createTextNode(self::bool2str($array['@value'])));
                unset($array['@value']);

                return $node;
            }
            if (array_key_exists('@cdata', $array)) {
                $node->appendChild($xml->createCDATASection($array['@cdata']));
                unset($array['@cdata']);

                return $node;
            }
        }

        // process subarray
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (!static::isValidTagName($key)) {
                    throw new ParserException('Illegal tag name. Tag: "' . $key . '" in node "' . $nodeName . '"');
                }

                // if first sub key is numeric create multiple nodes with current key
                // otherwise create node recursively
                if (is_array($value) && is_numeric(key($value))) {
                    foreach ($value as $subValue) {
                        $node->appendChild(static::_arrayToXml($xml, $key, $subValue));
                    }
                } else {
                    $node->appendChild(static::_arrayToXml($xml, $key, $value));
                }
                unset($array[$key]);
            }
        }

        // if it is not an array create a text node
        if (!is_array($array)) {
            $node->appendChild($xml->createTextNode(static::bool2str($array)));
        }

        return $node;
    }

    /**
     * Converts strict boolean values true and false to a string.
     *
     * @param mixed $value The value to parse.
     *
     * @return string
     */
    protected static function bool2str($value)
    {
        $value = $value === true ? 'true' : $value;
        $value = $value === false ? 'false' : $value;

        return $value;
    }

    /**
     * Check if the tag name is a valid xml tag name.
     *
     * @see http://www.w3.org/TR/xml/#sec-common-syn
     *
     * @param $tagName
     *
     * @return bool
     */
    public static function isValidTagName($tagName)
    {
        $pattern = '/^[a-z_]+[a-z0-9:\-._]*[^:]*$/i';

        return preg_match($pattern, $tagName, $matches) && $matches[0] === $tagName;
    }
}
