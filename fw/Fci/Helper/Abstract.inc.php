<?php
abstract class Fci_Helper_Abstract {
    protected function _getDataFromArray($search, $haystack, $return = null) {
        return (array_key_exists($search, $haystack) && $haystack[$search] !== '') ? $haystack[$search] : $return;
    }
}