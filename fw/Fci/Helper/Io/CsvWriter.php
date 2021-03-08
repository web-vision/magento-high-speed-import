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
class Fci_Helper_Io_CsvWriter
{
    /**
     * @var string
     */
    protected $_path;

    /**
     * @var string
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_mode = 'wb';

    /**
     * @var resource
     */
    protected $_fileHandle;

    /**
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var bool
     */
    protected $usePhpFunction = true;

    /**
     * @var int
     */
    protected $_linesWritten = 0;

    /**
     * Sets the path for the file.
     *
     * @param string $path Path to the file.
     */
    public function setPath($path)
    {
        $path = rtrim($path, '/\\');
        $this->_path = $path . DIRECTORY_SEPARATOR;
    }

    /**
     * Sets the file name.
     *
     * @param string $file The filename.
     */
    public function setFile($file)
    {
        $this->_file = $file;
    }

    /**
     * Sets the mode to open the file with. Defaults to 'wb'.
     *
     * @param string $mode [optional] The mode to open the file.
     */
    public function setMode($mode = 'wb')
    {
        $this->_mode = $mode;
    }

    /**
     * Sets the file encoding. Defaults to 'UTF-8'.
     *
     * @param string $encoding The file encoding
     */
    public function setEncoding($encoding)
    {
        $this->_encoding = $encoding;
    }

    /**
     * Sets the delimiter for the csv values. Defaults to ';'.
     *
     * @param string $delimiter [optional] The delimiter character.
     */
    public function setDelimiter($delimiter = ';')
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Sets the enclosure for the csv values. Defaults to '"'.
     *
     * @param string $enclosure [optional] The enclosure character.
     */
    public function setEnclosure($enclosure = '"')
    {
        $this->enclosure = $enclosure;
    }

    /**
     * Sets if the php function should be used to write the data.
     *
     * @param bool $usePhpFunction The new value.
     */
    public function setUsePhpFunction($usePhpFunction)
    {
        $this->usePhpFunction = (bool)$usePhpFunction;
    }

    /**
     * Returns the amount of lines written.
     *
     * @return int
     */
    public function getLinesWritten()
    {
        return $this->_linesWritten;
    }

    /**
     * Cleans the string by doubling the enclosure.
     *
     * @param string $line
     *
     * @return string
     */
    protected function cleanString($line)
    {
        return str_replace($this->enclosure, $this->enclosure . $this->enclosure, $line);
    }

    /**
     * Tries to open the file.
     * If the folder doesn't exists this method will try to create the folder.
     *
     * @return bool
     * @throws Exception
     */
    public function open()
    {
        if ($this->_path === null || $this->_file === null) {
            throw new Exception('Path and file are not set.');
        }

        if (!@mkdir($this->_path, 0755, true) && !is_dir($this->_path)) {
            throw new Exception('Path is not present and couln\'t be created.');
        }

        if ($this->_fileHandle = fopen($this->_path . $this->_file, 'wb')) {
            $this->_linesWritten = 0;

            return true;
        }

        throw new Exception('Couldn\'t open file for writing.');
    }

    /**
     * @inheritDoc
     */
    public function writeLine($line)
    {
        if (!$this->_fileHandle || !is_resource($this->_fileHandle)) {
            return;
        }

        if (!is_array($line)) {
            $line = array($line);
        }
        /** @var array $line */

        $output = array();
        foreach ($line as $column) {
            $output[] = mb_convert_encoding($this->cleanString($column), $this->_encoding, 'auto');
        }

        if ($this->usePhpFunction) {
            fputcsv($this->_fileHandle, $output, $this->delimiter, $this->enclosure);
        } else {
            $content = $this->enclosure . implode($this->enclosure . $this->delimiter . $this->enclosure, $output)
                . $this->enclosure . PHP_EOL;
            fwrite($this->_fileHandle, $content, strlen($content));
        }
        $this->_linesWritten++;
        fflush($this->_fileHandle);
    }

    /**
     * Closes the file.
     */
    public function close()
    {
        if ($this->_fileHandle && is_resource($this->_fileHandle)) {
            fclose($this->_fileHandle);
        }
    }

    /**
     * Destructor to safely close the open handle.
     */
    public function __destruct()
    {
        $this->close();
    }
}
