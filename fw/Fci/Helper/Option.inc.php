<?php

/**
 * web-vision GmbH
 *
 * NOTICE OF LICENSE
 *
 * <!--LICENSETEXT-->
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.web-vision.de for more information.
 *
 * @category    WebVision
 * @package     Fci_Helper
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Fci_Helper_Option extends Fci_Helper_Abstract
{
    /**
     * @var Fci_Helper_Option
     */
    protected static $instance;

    /**
     * Singleton method to return just one instance of the class.
     *
     * @return Fci_Helper_Option
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Fci_Helper_Option constructor.
     */
    private function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning the object.
     */
    private function __clone()
    {
    }

    /**
     * Processes the given data and creates Fci_Objects_Option objects from it.
     *
     * @param array $data
     *
     * @return Fci_Objects_Option[]
     */
    public function processData($data)
    {
        $options = [];
        $titles = explode(',', $this->_getDataFromArray('option_title', $data));
        $type = explode(',', $this->_getDataFromArray('option_type', $data));
        $required = explode(',', $this->_getDataFromArray('option_required', $data));
        $position = explode(',', $this->_getDataFromArray('option_position', $data));
        $price = explode(',', $this->_getDataFromArray('option_price', $data));
        $price_type = explode(',', $this->_getDataFromArray('option_price_type', $data));
        $sku = explode(',', $this->_getDataFromArray('option_sku', $data));
        $additional = explode(',', $this->_getDataFromArray('option_additional', $data));

        foreach ($titles as $i => $title) {
            if ($title === '') {
                continue;
            }

            $option = new Fci_Objects_Option();
            $option->setTitle($title);
            $option->setType(Fci_Enum_OptionType::get($this->stringToEnum($type[$i])));
            if ($this->_getDataFromArray($i, $required)) {
                $option->setRequired($required[$i]);
            }
            if ($this->_getDataFromArray($i, $position)) {
                $option->setPosition($position[$i]);
            }
            if ($this->_getDataFromArray($i, $price)) {
                $option->setPrice($price[$i]);
            }
            if ($this->_getDataFromArray($i, $price_type)) {
                $option->setPriceType(Fci_Enum_PriceType::get($this->stringToEnum($price_type[$i])));
            }
            if ($this->_getDataFromArray($i, $sku)) {
                $option->setSku($sku[$i]);
            }
            if ($this->_getDataFromArray($i, $additional)) {
                $option->setAdditional($additional[$i]);
            }
            $options[] = $option;
        }

        return $options;
    }

    /**
     * Normalizes the given string to be used as an enum identifier.
     *
     * @param string $string
     *
     * @return string
     */
    protected function stringToEnum($string)
    {
        return preg_replace('/[^A-Z]/', '', strtoupper($string));
    }
}
