<?php
if (!$GLOBALS['CEMES']['ACTIVE'])
    die('Framework ist nicht aktiv');
/**
 * @package Cemes-Framework
 * @version 1.1.0
 * @author Tim Werdin
 *
 * Cemes_Helper_Config verwaltet Daten aus einer oder mehreren Konfig Dateien
 */

class Cemes_Helper_Config {
    /**
     * @var array|Cemes_Object
     */
    private $configs = null;

    /**
     * @var string
     */
    private $environment = '';

    /**
     * @var string
     */
    private $configPath;

    /**
     * @param string $environment
     */
    public function setEnvironment($environment) {
        $this->environment = (preg_match('%'.CDS.'$%', $environment)) ? $environment : $environment.CDS;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath($path) {
        $this->configPath = (preg_match('%'.CDS.'$%', $path)) ? $path : $path.CDS;
        return $this;
    }

    /**
     * @param string $fileName
     * @param bool $useSection
     * @return bool
     */
    public function load($fileName, $useSection = FALSE) {
        $file = $this->configPath.$this->environment.$fileName;

        $conf = \Cemes\Parser\ParserFactory::read($file);

        if(!$conf) {
            return false;
        }

        $conf = new Cemes_Object($conf);

        // wenn useSection = true und section noch nicht existiert
        if($useSection && !array_key_exists($fileName, $this->configs)) {
            $this->configs[$fileName] = $conf;
        } elseif(!$useSection && is_null($this->configs)) { // wenn useSection = false und noch keine config gespeichert
            $this->configs = $conf;
        }

        return true;
    }

    /**
     * @param string $fileName
     * @param string $config Die config die gespeichert werden soll
     */
    public function save($fileName, $config = '', $rootNode = 'config') {
        $file = $this->configPath.$this->environment.$fileName;

        if(empty($config)) {
            $data = $this->configs;
        } else {
            $data = $this->configs[$config];
        }

        \Cemes\Parser\ParserFactory::write($file, [$rootNode => $data]);
    }

    /**
     * @param string $key
     * @param string $section
     * @return mixed
     */
    public function get($key, $section = '') {
        if(!is_array($this->configs)) {
            return $this->configs->getData($key);
        } else if(is_array($this->configs) && !empty($section) && array_key_exists($section, $this->configs)) {
            return $this->configs[$section]->getData($key);
        } else {
            return false;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $section
     */
    public function set($key, $value, $section = '') {
        if(!is_array($this->configs)) {
            $this->configs->setData($key, $value);
        } else if(is_array($this->configs) && !empty($section) && array_key_exists($section, $this->configs)) {
            $this->configs[$section]->setData($key, $value);
        }
    }

    /**
     * @param string $key
     * @param string $section
     */
    public function uns($key, $section = '') {
        if(!is_array($this->configs)) {
            $this->configs->unsData($key);
        } else if(is_array($this->configs) && !empty($section) && array_key_exists($section, $this->configs)) {
            $this->configs[$section]->unsData($key);
        }
    }

    /**
     * @param string $section
     * @return int
     */
    public function count($section = '') {
        if(!is_array($this->configs)) {
            if(is_object($this->configs)) {
                return $this->configs->count();
            }
        } else if(is_array($this->configs) && !empty($section) && array_key_exists($section, $this->configs)) {
            if(is_object($this->configs[$section])) {
                return $this->configs[$section]->count();
            }
        }

        return 0;
    }
}
