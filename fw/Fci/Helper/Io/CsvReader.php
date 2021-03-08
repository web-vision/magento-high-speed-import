<?php

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
 * @package     Fci_Helper
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Helper_Io_CsvReader
{
    /**
     * The folder where the csv is in.
     *
     * @var string
     */
    protected $_path;

    /**
     * The name of the csv file.
     *
     * @var string
     */
    protected $_file;

    /**
     * The delimiter for reading the csv file.
     *
     * @var string
     */
    protected $_delimiter = ',';

    /**
     * The enclosure for reading the csv file.
     *
     * @var string
     */
    protected $_enclosure = '"';

    /**
     * The file handle of the file for reading.
     *
     * @var Resource
     */
    protected $_fileHandle;

    /**
     * True if the file has a headline with column names or false otherwise.
     *
     * @var bool
     */
    protected $_hasHeadline = true;

    /**
     * The column names as an array.
     *
     * @var array
     */
    protected $_headline;

    /**
     * The amount of lines read.
     *
     * @var int
     */
    protected $_linesRead = 0;

    /**
     * Sets the folder where the file is located.
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $path = rtrim($path, '/\\');
        $this->_path = $path . DIRECTORY_SEPARATOR;
    }

    /**
     * Sets the name of the file.
     *
     * @param string $file
     */
    public function setFile($file)
    {
        $this->_file = ltrim($file, '/\\');
    }

    /**
     * Sets the delimiter char for reading the csv.
     *
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->_delimiter = $delimiter;
    }

    /**
     * Sets the enclosure char for reading the csv.
     *
     * @param string $enclosure
     */
    public function setEnclosure($enclosure)
    {
        $this->_enclosure = $enclosure;
    }

    /**
     * Sets if the csv file contains a headline with the column names.
     *
     * @param bool $hasHeadline
     */
    public function setHasHeadline($hasHeadline)
    {
        $this->_hasHeadline = $hasHeadline;
    }

    /**
     * Initializes the reading of the csv by opening the file handle, resetting some variables and reading the headline
     * if {@see _hasHeadline} is true.
     *
     * @throws Exception If the file was not found or the handle couldn't be opened.
     */
    public function init()
    {
        $this->_open();

        $this->_linesRead = 0;
        $this->_headline = null;

        $this->_getHeadline();
    }

    /**
     * Reads the next line and returns it as an array. If {@see _hasHeadline} is true is will be an associated array
     * with the column names as keys.
     * Returns false if the file handle is not initialized or if the last line has been read.
     *
     * @return array|bool
     */
    public function getLine()
    {
        if (is_resource($this->_fileHandle)) {
            $lineLimit = 4069;
            if (version_compare(PHP_VERSION, '5.1.0', '>=')) {
                $lineLimit = 0;
            }
            $rawLineArray = fgetcsv($this->_fileHandle, $lineLimit, $this->_delimiter, $this->_enclosure);
            if ($rawLineArray !== false) {
                if ($this->_linesRead === 0) {
                    $rawLineArray = $this->_fixBom($rawLineArray);
                }
                $this->_linesRead++;

                return $this->_mapHeadline($rawLineArray);
            }
        }

        return false;
    }

    /**
     * Returns the amount of lines that have been read.
     *
     * @return int
     */
    public function getLinesRead()
    {
        return $this->_linesRead;
    }

    /**
     * Closes the last file handle if present and then opens a new file handle with the set {@see _folder} and
     * {@see _file}.
     *
     * @throws Exception If the file was not found or the handle couldn't be opened.
     */
    protected function _open()
    {
        $this->_close();

        if ($this->_path === null || $this->_file === null) {
            throw new Exception('Path and file are not set.');
        }

        if (!file_exists($this->_path . $this->_file)) {
            throw new Exception('File not found.');
        }

        $this->_fileHandle = fopen($this->_path . $this->_file, 'rb');

        if (!is_resource($this->_fileHandle)) {
            throw new Exception('Couldn\'t open file.');
        }
    }

    /**
     * Closes the file handle if present.
     */
    protected function _close()
    {
        if (is_resource($this->_fileHandle)) {
            fclose($this->_fileHandle);
            $this->_fileHandle = null;
        }
    }

    /**
     * Reads the first line of the file and saves it in {@see _headline} for later use.
     */
    protected function _getHeadline()
    {
        if ($this->_hasHeadline) {
            $rawLineArray = $this->getLine();
            if ($rawLineArray !== false) {
                $this->_headline = $this->_trimValues($rawLineArray);
            }
        }
    }

    /**
     * If {@see _hasHeadline} is true and {@see _headline} is an array this method will map the column ids to the
     * column names from {@see _headline}.
     *
     * @param array $rawLineArray
     *
     * @return array
     */
    protected function _mapHeadline(array $rawLineArray)
    {
        if ($this->_hasHeadline && is_array($this->_headline)) {
            $lineArray = [];
            foreach ($rawLineArray as $id => $content) {
                $lineArray[$this->_headline[$id]] = $content;
            }

            return $this->_trimValues($lineArray);
        }

        return $this->_trimValues($rawLineArray);
    }

    /**
     * Iterates over the array and trims the values.
     *
     * @param array $array
     *
     * @return array
     */
    protected function _trimValues(array $array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = trim($value);
        }

        return $array;
    }

    /**
     * Removes the byte order mark (BOM) from the first column of the first line.
     *
     * @param array $rawLineArray
     *
     * @return array
     */
    protected function _fixBom(array $rawLineArray)
    {
        $bom = pack('H*', 'EFBBBF');
        $rawLineArray[0] = preg_replace("/^$bom/", '', $rawLineArray[0]);

        return $rawLineArray;
    }

    /**
     * Closes the file handle during deconstruction of the object.
     */
    public function __destruct()
    {
        $this->_close();
    }
}
