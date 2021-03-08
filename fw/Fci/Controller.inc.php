<?php
class Fci_Controller {
    protected $model;
    protected $view;
    protected $config = 'default.xml';

    public function __construct() {
        $this->model = new Fci_Model();
    }

    public function initialisize() {
        $this->model->initLogger();
        $context = array('version' => implode('.', $this->_getVersionInfo()));
        Cemes_Registry::get('logger')->info('Magento High Speed Import v{version}', $context);
        $this->model->initTimers();
        $this->model->loadConfig($this->config);
        $this->model->initEvents();
        $this->model->connectToDatabase();
        $this->model->loadCache();
        $this->model->checkCsvFile();
        $this->model->checkIdentifier();
    }

    public function run() {
        $this->model->before();
        $this->model->getDataFromFile();
        $this->model->after();
    }

    public function setConfig($configFile) {
        $this->config = $configFile;
    }

    protected function _getVersionInfo() {
        return array(
            'major'     => '3', // Hauptversion
            'minor'     => '1', // Feature
            'revision'  => '3', // Bugfix
            'patch'     => '0', // Verbesserung
        );
    }
}
