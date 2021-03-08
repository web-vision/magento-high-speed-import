<?php
if (!$GLOBALS['CEMES']['ACTIVE']) {
    die('Framework ist nicht aktiv');
}

/**
 * Container-Klasse
 *
 * @package Cemes-Framework
 * @version 1.0.0
 * @author  Tim Werdin
 *
 * Cemes_Autloader ist eine Klasse, die verwendet wird, um einen Autoloader zu registrieren.
 * WVTODO Check if class is PSR-4 compliant
 */
class Cemes_Autoloader
{
    /**
     * @access public
     * @static
     *
     * @param string $className
     *
     * @return void
     *
     * Diese statische Methode lädt eine Klasse, wenn sie benötigt wird.
     */
    public static function fwLoad($className)
    {
        // aus _ wird / um Unterordner zu bilden
        $classFile = CEMES_BASEDIR . str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className);
        if (is_file($classFile . '.inc.php')) {
            require_once($classFile . '.inc.php');
        } elseif (is_file($classFile . '.php')) {
            require_once($classFile . '.php');
        }
    }

    /**
     * verhindern das die Klasse instanziert oder geclont wird
     */
    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @access public
     * @static
     *
     * @param mixed $autoloader
     *
     * @return void
     *
     * registriert Autoloader
     */
    public static function registerAutoloader($autoloader = null)
    {
        if ($autoloader === null) {
            spl_autoload_register(array('Cemes_Autoloader', 'fwLoad'));
        } else {
            spl_autoload_register($autoloader);
        }
    }

    public static function resetAutoloaders()
    {
        foreach (spl_autoload_functions() as $autoloader) {
            spl_autoload_unregister($autoloader);
        }
        self::registerAutoloader();
    }
}
