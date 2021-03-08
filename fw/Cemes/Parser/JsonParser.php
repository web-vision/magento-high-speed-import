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
class JsonParser extends AbstractParser
{
    /**
     * @inheritDoc
     */
    public static function read($file)
    {
        static::_isReadable($file);

        return static::toArray(file_get_contents($file, true));
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
        return json_encode($array);
    }

    /**
     * @inheritDoc
     */
    public static function toArray($data)
    {
        return json_decode($data);
    }
}
