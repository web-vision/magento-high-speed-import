<?php

namespace Cemes\Parser;

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
class IniParser extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public static function read($file)
    {
        static::_isReadable($file);

        $data = parse_ini_file($file, true, INI_SCANNER_TYPED);

        return $data ?: null;
    }

    /**
     * @inheritDoc
     */
    public static function write($file, $data)
    {
        static::_isWritable($file);

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
        $output = '';
        foreach ($array as $key => $value) {
            // section
            if (is_array($value)) {
                $output .= '[' . $key . ']' . PHP_EOL;
                foreach ($value as $subKey => $subValue) {
                    // value is an array
                    if (is_array($subValue)) {
                        foreach ($subValue as $subSubValue) {
                            $output .= $subKey . '[] = ';
                            $output .= is_numeric($subSubValue) ? $subSubValue : '"' . $subSubValue . '"';
                            $output .= PHP_EOL;
                        }
                    } else {
                        $output .= $subKey . ' = ';
                        $output .= is_numeric($subValue) ? $subValue : '"' . $subValue . '"';
                        $output .= PHP_EOL;
                    }
                }
            } else {
                $output .= $key . ' = ';
                $output .= is_numeric($value) ? $value : '"' . $value . '"';
                $output .= PHP_EOL;
            }
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public static function toArray($data)
    {
        return parse_ini_string($data, true, INI_SCANNER_TYPED);
    }
}
