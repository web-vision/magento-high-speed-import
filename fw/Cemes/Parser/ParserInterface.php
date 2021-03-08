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
interface ParserInterface
{
    /**
     * Reads data from a file and parses it.
     *
     * @param string $file The path to the file.
     *
     * @return array|bool
     * @throws \Cemes\Parser\ParserException
     */
    public static function read($file);

    /**
     * Writes the given data into the given file.
     *
     * @param string       $file The path to the file.
     * @param array|Object $data The data to write into the file.
     *
     * @return void
     * @throws \Cemes\Parser\ParserException
     */
    public static function write($file, $data);
}
