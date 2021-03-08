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
class ParserFactory implements ParserInterface
{
    /**
     * @inheritDoc
     */
    public static function read($file, $type = null)
    {
        if (!$type) {
            $type = pathinfo($file, PATHINFO_EXTENSION);
        }

        switch(strtolower($type)) {
            case 'ini':
                return IniParser::read($file);
            case 'json':
                return JsonParser::read($file);
            case 'xml':
                return XmlParser::read($file);
            default:
                $shortenedFile = basename($file, '.' . $type);
                $type = pathinfo($shortenedFile, PATHINFO_EXTENSION);
                if ($type) {
                    return static::read($file, $type);
                }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public static function write($file, $data)
    {
        $type = pathinfo($file, PATHINFO_EXTENSION);

        switch(strtolower($type)) {
            case 'ini':
                IniParser::write($file, $data);
                break;
            case 'json':
                JsonParser::write($file, $data);
                break;
            case 'xml':
                XmlParser::write($file, $data);
                break;
        }
    }
}
