<?php
if (!$GLOBALS['CEMES']['ACTIVE'])
    die('Framework ist nicht aktiv');
/*
 * Singleton-Klasse
 *
 * @package Cemes-Framework
 * @version 1.0.0
 * @author Tim Werdin
 *
 * Die Klasse soll Datenvalidieren
 *
 */
class Cemes_Helper_Validate {
    public function isInt($value) {
        if(is_int($value) || preg_match('/^[0-9]+$/', $value))
            return true;
        else
            return false;
    }

    public function isFloat($value) {
        if(is_float($value) || preg_match('/^[0-9.,]+$/', $value))
            return true;
        else
            return false;
    }

    public function isAlpha($value) {
        if(ctype_alpha($value) || preg_match('/^[a-zA-Z]+$/', $value))
            return true;
        else
            return false;
    }

    public function isNum($value) {
        if(is_numeric($value))
            return true;
        else
            return false;
    }

    public function isAlphaNum($value) {
        if(ctype_alnum($value) || preg_match('/^[0-9a-zA-Z]+$/', $value))
            return true;
        else
            return false;
    }

    public function isBool($value) {
        if(is_bool($value) || $value === 'true' || $value === 'false' || $value === 1 || $value === 0 || $value === '1' || $value === '0')
            return true;
        else
            return false;
    }

    public static function validateType($value) {
        if(Cemes_Helper_Validate::isBool($value))
            return ($value && $value !== "false");
        else if(Cemes_Helper_Validate::isInt($value))
            return (int) $value;
        else if(Cemes_Helper_Validate::isFloat($value))
            return (float) $value;
        else
            return $value;
    }
}