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
class Fci_Helper_MandatoryCsv
{
    /**
     * @var Fci_Helper_Category
     */
    protected static $instance;

    /**
     * @var resource
     */
    protected $fileHandle;

    /**
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @var string
     */
    protected $enclosure = '"';

    /**
     * Singleton method to return just one instance of the class.
     *
     * @return $this
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Fci_Helper_CsvWriter constructor.
     */
    protected function __construct()
    {
        $config = Fci_Helper_Config::getInstance();
        $mandatoryFields = $config->getMandatoryFields();
        if (!empty($mandatoryFields)) {
            $dir = dirname(ROOT_BASEDIR . $config->getFilePath());
            $this->fileHandle = fopen($dir . CDS . 'missingMandatories.csv', 'wb');
            $this->delimiter = $config->getFileDelimiter();
            $this->enclosure = $config->getFileEnclosure();
        }
    }

    /**
     * Private method to prevent cloning the instance.
     */
    protected function __clone()
    {
    }

    /**
     * Writes the given field names to the file.
     *
     * @param array $fieldNames
     */
    public function writeFieldNames($fieldNames = [])
    {
        if ($this->fileHandle !== null) {
            if (!is_array($fieldNames)) {
                $fieldNames = [$fieldNames];
            }

            $fieldNames = array_merge(['missingFields'], $fieldNames);

            fputcsv($this->fileHandle, $fieldNames, $this->delimiter, $this->enclosure);
        }
    }

    /**
     * Writes the line to the file. The missing fields will be concatenated by a comma and added as a column to the
     * line.
     *
     * @param array $line
     * @param array $missing
     */
    public function writeLine($line, $missing)
    {
        if ($this->fileHandle !== null) {
            $missing = implode(', ', $missing);
            $line = array_merge([$missing], $line);
            fputcsv($this->fileHandle, $line, $this->delimiter, $this->enclosure);
        }
    }

    /**
     * Closes the file handle.
     */
    public function close()
    {
        if ($this->fileHandle !== null) {
            fclose($this->fileHandle);
        }
    }
}
