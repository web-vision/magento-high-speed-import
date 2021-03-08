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
 * @package     Fci_Model
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 * @method string getAttributeCode()
 * @method $this setAttributeCode(string $attributeCode)
 * @method string getAttributeId()
 * @method string getBackendType()
 * @method string getFrontendInput()
 * @method string getLabel()
 * @method string getIsRequired()
 * @method string|bool getDefaultValue()
 * @method string getIsUserDefined()
 * @method string|null getSourceModel()
 * @method array getValues()
 * @method $this setValue(string $value)
 * @method string getValue()
 * @method string getScope()
 */
class Fci_Model_Attribute extends Cemes_Object
{
    /**
     * This method will try to find the needle in the attribute values.
     * If there are no values it will return null.
     * If the needle is equal to a value this method will return the option id.
     * If the value is an array the method will only return the option id if either the store id is equal to the values
     * store id or if there is only one store id an it is zero.
     * If the attribute is not user defined it also checks if the needle is equal to the option id.
     * In any other case it returns null.
     *
     * @param $needle
     * @param $storeIds
     *
     * @return string|null
     */
    public function getValueId($needle, $storeIds)
    {
        if (!is_array($this->getValues())) {
            return null;
        }
        // iterate through values
        foreach ($this->getValues() as $optionId => $value) {
            // special cases for options_container
            if (($optionId === 'container1' || $optionId === 'container2')
                && ($needle === $optionId || $needle === $value)) {
                return $optionId;
            }
            // if needle = value return $key
            if ($needle == $value) {
                return $optionId;
            }
            if (is_array($value)) {
                foreach ($value as $store_view => $storeValue) {
                    if ($needle == $storeValue
                        && (in_array($store_view, $storeIds) || (count($storeIds) === 1 && $storeIds[0] === 0))) {
                        return $optionId;
                    }
                }
            }
        }
        // if needle is not a value maybe it's the id itself
        if ($this->getIsUserDefined() == 0 && array_key_exists($needle, $this->getValues())) {
            return $needle;
        }

        return null;
    }
}
