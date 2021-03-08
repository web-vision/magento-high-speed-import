<?php

/**
 * Class Headline
 */
class Headline extends Fci_Objects_AbstractScript
{
    /** @var string */
    protected $_eventName;

    /** @var string */
    protected $_input;

    /** @var string */
    protected $_file;

    /** @var array */
    protected $_headline;

    /** @var array */
    protected $_content;

    /** @var string */
    protected $_delimiter;

    /** @var string */
    protected $_enclosure;

    /** @var Psr\Log\LoggerInterface */
    protected $_logger;

    /**
     * @inheritDoc
     */
    public function __construct($params)
    {
        $configuration = Fci_Helper_Config::getInstance();

        $filePath = $configuration->getFilePath();
        $this->_file = dirname(ROOT_BASEDIR . $filePath)
            . DIRECTORY_SEPARATOR
            . basename($filePath);

        $this->_delimiter = $configuration->getFileDelimiter();
        $this->_enclosure = $configuration->getFileEnclosure();

        $this->_eventName = $params['event'];
        $this->_input = $params['input'];
        $this->_headline = explode($this->_delimiter, $params['input']);

        $this->_logger = Cemes_Registry::get('logger');
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $this
                ->_loadContentFromFile()
                ->_checkIfHeadlineExists()
                ->_prepareHeadline()
                ->_prepareContent()
                ->_addHeadlineToContent()
                ->_writeContentToFile();
        } catch (Exception $e) {
            $this->_logger
                ->warning(
                    $e->getMessage()
                    . ' Das Hinzufügen der Überschrift wurde übersprungen.'
                );
            return false;
        }

        return true;
    }

    /**
     * Load content from import file.
     *
     * @throws Exception
     *
     * @return $this
     */
    protected function _loadContentFromFile()
    {
        $content = file_get_contents($this->_file);

        if (! $content) {
            throw new Exception('Die angeforderte Datei existiert nicht oder hat keinen Inhalt.');
        }

        $this->_content = explode(PHP_EOL, trim($content));

        return $this;
    }

    /**
     * Check if headline already exists.
     *
     * @throws Exception
     *
     * @return $this
     */
    protected function _checkIfHeadlineExists()
    {
        if (! array_key_exists(0, $this->_content)) {
            throw new Exception('Erste Zeile konnte nicht gelesen werden.');
        }

        $rows = explode($this->_delimiter, $this->_content[0]);
        $isHeadline = false;

        foreach ($rows as $index => $row) {
            if (array_key_exists($index, $this->_headline)) {
                if ($row === $this->_headline[$index]) {
                    $isHeadline = true;
                    break;
                }
            }
        }

        if ($isHeadline) {
            throw new Exception('Überschrift existiert bereits.');
        }

        return $this;
    }


    /**
     * Prepare headline to write.
     *
     * @throws Exception
     *
     * @return $this
     */
    protected function _prepareHeadline()
    {
        $headline = [];

        foreach ($this->_headline as $row) {
            if (substr_count($row, $this->_enclosure) === 0) {
                $row = $this->_enclosure . $row . $this->_enclosure;
            }

            if (substr_count($row, $this->_enclosure) !== 2) {
                throw new Exception('Überschrift hat ein falsches Format.');
            }

            $headline[] = $row;
        }

        $this->_headline = implode($this->_delimiter, $headline);

        return $this;
    }

    /**
     * Prepare content to write.
     *
     * @return $this
     */
    protected function _prepareContent()
    {
        $this->_content = implode(PHP_EOL, $this->_content);

        return $this;
    }

    /**
     * Add headline to content variable.
     *
     * @return $this
     */
    protected function _addHeadlineToContent()
    {
        $content = [
            $this->_headline,
            $this->_content
        ];

        $this->_content = implode(PHP_EOL, $content);

        return $this;
    }

    /**
     * Write content to import file.
     *
     * @return $this
     */
    protected function _writeContentToFile()
    {
        file_put_contents($this->_file, $this->_content);

        $this->_logger
            ->info('Überschrift wurde automatisch hinzugefügt.');

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConfiguration()
    {
        return [
            'event' => $this->_eventName,
            'input' => $this->_input,
        ];
    }
}