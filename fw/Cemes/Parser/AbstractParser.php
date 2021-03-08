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
abstract class AbstractParser implements ParserInterface
{
    /**
     * Checks if the given file exists and is readable.
     *
     * @param string $file The path to the file.
     *
     * @return bool
     * @throws \Cemes\Parser\ParserException
     */
    protected static function _isReadable($file)
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new ParserException('File "' . $file . '" doesn\'t exists or isn\'t readable.');
        }

        return true;
    }

    /**
     * Checks if the given file exists and is writable.
     *
     * @param $file
     *
     * @return bool
     * @throws \Cemes\Parser\ParserException
     */
    protected static function _isWritable($file)
    {
        $path = dirname($file);

        while (!file_exists($path)) {
            $path = dirname($path);
        }

        if (!is_writable($path)) {
            throw new ParserException('Existing parent folder "' . $path . '" is not writable.');
        }

        if (file_exists($file) && !is_writable($file)) {
            throw new ParserException('The file exists but is not writable.');
        }

        return true;
    }

    /**
     * Writes the given output to the given file with the given mode.
     *
     * @param string $file
     * @param string $output
     * @param string $mode
     *
     * @throws \Cemes\Parser\ParserException
     */
    protected static function _writeToFile($file, $output, $mode = 'wb')
    {
        $path = dirname($file);

        if (!file_exists($path) && !@mkdir($path, 0755, true) && !is_dir($path)) {
            throw new ParserException('Unable to create folder "' . $path . '".');
        }

        if ($fileHandle = fopen($file, $mode)) {
            fwrite($fileHandle, $output);
            fclose($fileHandle);
        } else {
            throw new ParserException('Couldn\'t open file "' . $file . '".');
        }
    }

    /**
     * Parses the given object and converts it into a string in the parsers format.
     *
     * @param Object $object
     *
     * @return array
     * @throws \Cemes\Parser\ParserException
     */
    abstract protected static function fromObject($object);

    /**
     * Parses the given array and converts it into a string in the parsers format.
     *
     * @param array $array
     *
     * @return string
     * @throws \Cemes\Parser\ParserException
     */
    abstract protected static function fromArray($array);

    /**
     * Parses the given data and converts it into an array.
     *
     * @param mixed $data
     *
     * @return array
     * @throws \Cemes\Parser\ParserException
     */
    abstract protected static function toArray($data);
}
