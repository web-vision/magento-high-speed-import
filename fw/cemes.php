<?php

define('CDS', DIRECTORY_SEPARATOR);
define('CEMES_BASEDIR', __DIR__ . CDS);
define('CEMES_REL_BASEDIR', str_replace(dirname($_SERVER['SCRIPT_FILENAME']).'/', '', str_replace('\\', '/', CEMES_BASEDIR)));
define('ROOT_BASEDIR', dirname(CEMES_BASEDIR).CDS);

// global variable for framework
$CEMES = array();
$CEMES['INFO']['VERSION'] = '1.0.0';
$CEMES['INFO']['AUTHOR'] = 'Tim Werdin (web-vision.de)';
$CEMES['INFO']['COPYRIGHT'] = '2010';

include_once(CEMES_BASEDIR.'Cemes'.CDS.'bin'.CDS.'config.inc.php');

class Cemes {
    /**
     * @var Cemes
     */
    private static $instance = NULL;
    /**
     * @var bool
     */
    private $css = false;

    private static $_cli;

    /**
     * @static
     * @return Cemes
     */
    public static function load() {
        // Cemes aktivieren andere Dateien lassen sich nicht aufrufen wenn diese
        // Variable nicht gesetzt ist
        $GLOBALS['CEMES']['ACTIVE'] = true;
        // Variable die alle Fehler speichert
        $GLOBALS['CEMES']['ERRORS'] = array();
        // Autoloader initialisieren
        require_once CEMES_BASEDIR . 'Cemes' . CDS . 'Autoloader.inc.php';
        Cemes_Autoloader::registerAutoloader();
        return self::getInstance();
    }

    /**
     * @static
     * @return Cemes
     */
    public static function getInstance() {
        if(is_null(self::$instance))
            self::$instance = new Cemes();
        return self::$instance;
    }

    /**
     * @param string $moduleName
     * @param array $params
     * @param string $framework
     * @return Object
     */
    public static function loadModule($moduleName, $params = array(), $framework = 'Cemes') {
        // Modulname bauen aus Framework und mit _ statt /
        $moduleName = $framework.'_'.str_replace('/', '_', $moduleName);
        // wenn $params kein array ist dann mach eines daraus
        if(!is_array($params))
            $params = array($params);
        // testen ob die Klasse existiert
        if(class_exists($moduleName)) {
            // wenn es eine getInstance Methode gibt rufe diese auf
            if(method_exists($moduleName, 'getInstance'))
                return call_user_func_array(array($moduleName, 'getInstance'), $params);
            else {
                // es gibt keine getInstance Methode
                // prüfe ob eine __construct Methode definiert wurde da sonst eine Exception ausgeworfen wird
                if(method_exists($moduleName, '__construct'))
                    return call_user_func_array(array(new ReflectionClass($moduleName), 'newInstance'), $params);
                else
                    return new $moduleName();
            }
        }

        return null;
    }

    /**
     * @param int $errorReporting
     * @param bool $displayErrors
     */
    public function handleErrors($errorReporting = null, $displayErrors = false) {
        // register error handler
        Cemes_ErrorHandler::registerErrorHandler();
        // set error reporting if given
        if (null !== $errorReporting) {
            error_reporting($errorReporting);
        }
        if (ini_get('display_errors') !== (int)$displayErrors) {
            ini_set('display_errors', (int)$displayErrors);
        }
    }

    /**
     * @param null $number int
     */
    public function displayErrors($number = null) {
        Cemes_ErrorHandler::getInstance()->displayErrors($number);
    }

    /**
     * @return bool
     */
    public static function isCli()
    {
        if (static::$_cli === null) {
            static::$_cli = PHP_SAPI === 'cli'
                || (array_key_exists('argc', $_SERVER) && is_numeric($_SERVER['argc']) && $_SERVER['argc'] > 0);
        }

        return static::$_cli;
    }

    /**
     * make an output for the stylesheet
     */
    public function insertCSS() {
        if(!$this->css && !static::isCli()) {
            print '<link type="text/css" rel="stylesheet" href="'.CEMES_REL_BASEDIR.'Cemes/bin/css/cemes.css">'."\r\n";
            $this->css = true;
        }
    }

    private function __construct(){}
    private function __clone(){}
}
