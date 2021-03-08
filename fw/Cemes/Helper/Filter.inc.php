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
 * Die Klasse soll Datenfiltern
 *
 */
class Cemes_Helper_Filter {
    public function toFloat($value) {
        return floatval($value);
    }

    public function toInt($value) {
        return intval($value);
    }

    public function toAlpha($value) {
        preg_match_all('/([A-Za-z]+)/', $value, $matches);
        return implode($matches[0], '');
    }

    public function toNum($value) {
        preg_match_all('/([0-9.,]+)/', $value, $matches);
        return implode($matches[0], '');
    }

    public function toAlphaNum($value) {
        preg_match_all('/([0-9A-Za-z]+)/', $value, $matches);
        return implode($matches[0], '');
    }
}