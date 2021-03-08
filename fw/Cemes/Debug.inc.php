<?php
if (!$GLOBALS['CEMES']['ACTIVE'])
    die('Framework ist nicht aktiv');
/**
 * @package Cemes-Framework
 * @version 1.0.0
 * @author Tim Werdin
 */
class Cemes_Debug {
    public static function debug($var) {
        print '<pre>';
        var_dump($var);
        print '</pre>';
    }
}