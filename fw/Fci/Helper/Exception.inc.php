<?php

class Fci_Helper_Exception
{
    protected $_method;

    protected $_params;

    protected $_result;

    protected $_strict;

    public function __construct($exceptionParams = [])
    {
        $this->_method = $exceptionParams['method'];
        $this->_params = [];
        if (is_array($exceptionParams['params']['param']) && is_int(key($exceptionParams['params']['param']))) {
            foreach ($exceptionParams['params']['param'] as $param) {
                $this->_params[] = $this->getCastedValue($param);
            }
        } else {
            $this->_params[] = $this->getCastedValue($exceptionParams['params']['param']);
        }
        $this->_result = $this->getCastedValue($exceptionParams['result']);
        $this->_strict = $exceptionParams['strict'];
    }

    protected function getCastedValue($value)
    {
        $output = $value;
        if (is_array($value) && Cemes_StdLib::array_keys_exists(['@value', '@attributes'], $value)) {
            if (array_key_exists('type', $value['@attributes'])) {
                switch ($value['@attributes']['type']) {
                    case 'int':
                        $output = (int)$value['@value'];
                        break;
                    case 'float':
                    case 'decimal':
                        $output = (float)$value['@value'];
                        break;
                    case 'key':
                        $output = ['key' => $value['@value']];
                        break;
                    case 'bool':
                        $output = $value['@value'] === 'false' ? false : (bool)$value['@value'];
                        break;
                    default:
                        $output = $value['@value'];
                }
            }
        }

        return $output;
    }

    public function parse($product = [])
    {
        $params = [];
        foreach ($this->_params as $param) {
            if (is_array($param) && array_key_exists('key', $param)) {
                $params[] = $product[$param['key']];
            } else {
                $params[] = $param;
            }
        }

        if ($this->_strict) {
            return call_user_func_array($this->_method, $params) === $this->_result;
        }

        return call_user_func_array($this->_method, $params) == $this->_result;
    }
}
