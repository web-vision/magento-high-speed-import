<?php

/**
 * Class SimpleXmlToCsv
 */
class SimpleXmlToCsv extends Fci_Objects_AbstractScript
{
    /**
     * @var string
     */
    protected $_xmlFile;

    /**
     * @var string
     */
    protected $_csvFile;

    /**
     * @var string
     */
    protected $_delimiter;

    /**
     * @var string
     */
    protected $_enclosure;

    /**
     * @var SimpleXMLElement
     */
    protected $_xmlData;

    /**
     * @var Fci_Helper_Io_CsvWriter
     */
    protected $_csvWriter;

    /**
     * @var string
     */
    protected $_xPath;

    /**
     * @var string
     */
    protected $_xmlRawData = '';

    /**
     * @var int
     */
    protected $_columnCount = 0;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * SimpleXmlToCsv constructor.
     *
     * @param $params
     */
    public function __construct($params)
    {
        $this->_eventName = $params['event'];
        $this->_xmlFile = $params['xml'];
        $this->_xPath = $params['xPath'];

        $config = Fci_Helper_Config::getInstance();
        $this->_csvFile = $config->getFilePath();
        $this->_delimiter = $config->getFileDelimiter();
        $this->_enclosure = $config->getFileEnclosure();

        $this->logger = Cemes_Registry::get('logger');
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->_openFileHandle()
            ->_readXmlFile()
            ->_parseXmlToArray()
            ->_writeHeaderRow()
            ->_writeCsvRows()
            ->_closeFileHandle();

        return true;
    }

    /**
     * Opens a file handle
     *
     * @return $this
     */
    protected function _openFileHandle()
    {
        $path = dirname(ROOT_BASEDIR . $this->_csvFile);
        $file = basename($this->_csvFile);

        $csvWriter = new Fci_Helper_Io_CsvWriter();
        $csvWriter->setPath($path);
        $csvWriter->setFile($file);
        $csvWriter->setEnclosure($this->_enclosure);
        $csvWriter->setDelimiter($this->_delimiter);
        $csvWriter->setUsePhpFunction(false);
        $csvWriter->open();

        $this->_csvWriter = $csvWriter;

        return $this;
    }

    /**
     * Close a file handle
     *
     * @return $this
     */
    protected function _closeFileHandle()
    {
        $this->_csvWriter->close();

        return $this;
    }

    /**
     * Get the content of a xml file
     *
     * @return $this
     */
    protected function _readXmlFile()
    {
        $path = dirname(ROOT_BASEDIR . $this->_csvFile);
        $this->_xmlRawData = file_get_contents($path . '/' . $this->_xmlFile);

        return $this;
    }

    /**
     * Parse xml to array with given xPath
     *
     * @return $this
     */
    protected function _parseXmlToArray()
    {
        $xmlResource = new SimpleXMLElement($this->_xmlRawData);
        $this->_xmlData = $xmlResource->xpath($this->_xPath);

        return $this;
    }

    /**
     * Writes the first row to csv file
     *
     * @return $this
     */
    protected function _writeHeaderRow()
    {
        $temp = [];
        foreach ($this->_xmlData[0] as $key => $value) {
            $temp[] = trim($key);
        }

        $this->_columnCount = count($temp);
        $this->_csvWriter->writeLine($temp);

        return $this;
    }

    /**
     * Writes the data rows to csv file
     *
     * @return $this
     */
    protected function _writeCsvRows()
    {
        foreach ($this->_xmlData as $article) {
            $temp = [];
            foreach ($article as $articleValue) {
                $temp[] = trim($articleValue);
            }

            if (count($temp) !== $this->_columnCount) {
                $this->logger->warning(
                    'XmlToCsv Convert: Data in XML File: '
                    . $this->_xmlFile
                    . '  for:'
                    . $temp[0]
                    . ' is incomplete and skipped'
                );
                continue;
            }

            $this->_csvWriter->writeLine($temp);
        }

        return $this;
    }

    /**
     * Returns an array with all params of the script and the current values.
     *
     * @return array
     */
    public function getConfiguration()
    {
        return [
            'event' => $this->_eventName,
            'xml'   => $this->_xmlFile,
            'xPath' => $this->_xPath,
        ];
    }
}
