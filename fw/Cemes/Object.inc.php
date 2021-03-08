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
 * @package     Cemes
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Cemes_Object implements ArrayAccess, Iterator, Countable
{
    /**
     * @var array
     */
    protected $_data;

    /**
     * @var array
     */
    protected $_keyCache = [];

    /**
     * @var bool
     */
    protected $_hasChanges = false;

    /**
     * @var array
     */
    protected $_changedKeys = [];

    /**
     * @var bool
     */
    protected $_new = false;

    public function __clone()
    {
        foreach ($this->_data as $key => $value) {
            if ($value instanceof self) {
                $this[$key] = clone $value;
            }
        }
    }

    public function __construct($data = [])
    {
        if ($data !== false && $data !== null) {
            foreach ((array)$data as $key => $value) {
                $this[$key] = $value;
            }
        }
    }

    public function setIsNew($value = true)
    {
        $this->_new = $value;
    }

    public function isNew()
    {
        return $this->_new;
    }

    public function toArray(array $keys = [])
    {
        if (empty($keys)) {
            return $this->_toArray();
        }

        $return = [];
        foreach ($keys as $key) {
            if (isset($this->_data[$key]) && $this->_data[$key] instanceof self) {
                $return[$key] = $this->_data[$key]->_toArray();
            } elseif (isset($this->_data[$key])) {
                $return[$key] = $this->_data[$key];
            } else {
                $return[$key] = null;
            }
        }

        return $return;
    }

    protected function _toArray()
    {
        $data = [];

        foreach ($this->_data as $key => $value) {
            if ($value instanceof self) {
                $data[$key] = $value->_toArray();
            } else {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /******************************************************************************/
    /************************* ArrayAccess implementation *************************/
    /******************************************************************************/
    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return isset($this->_data[$offset]);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->_data[] = $value;
        } else {
            $this->_data[$offset] = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->_data[$offset]);
    }

    /******************************************************************************/
    /************************** Iterator implementation  **************************/
    /******************************************************************************/
    /**
     * @inheritDoc
     */
    public function current()
    {
        return current($this->_data);
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        return next($this->_data);
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        reset($this->_data);
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->current() !== false;
    }

    /******************************************************************************/
    /************************** Countable implementation **************************/
    /******************************************************************************/
    /**
     * @inheritDoc
     */
    public function count()
    {
        return count($this->_data);
    }

    /******************************************************************************/
    /******************************* new functions ********************************/
    /******************************************************************************/
    /**
     * @param string $methodName
     * @param array  $methodArguments
     *
     * @return array|bool
     */
    public function __call($methodName, $methodArguments)
    {
        switch (substr($methodName, 0, 3)) {
            case 'get':
                $value = $this->_getCleanKey($methodName);

                return $this->getData($value, isset($args[0]) ? $args[0] : null);
                break;
            case 'set':
                $key = $this->_getCleanKey($methodName);
                $this->setData($key, $methodArguments[0]);
                break;
            case 'has':
                $key = $this->_getCleanKey($methodName);

                return $this->hasData($key);
            case 'uns':
                $key = $this->_getCleanKey($methodName);

                return $this->unsData($key);
                break;
            default:
                return false;
        }
    }

    /**
     * @param string $key
     * @param null   $index
     *
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ($key === '') {
            return $this->_data;
        }

        $return = [];

        $andKeys = explode('_and_', $key);
        foreach ($andKeys as $andKey) {
            // accept a/b/c as ['a']['b']['c']
            $slashKeys = explode('/', $andKey);
            $value = null;
            for ($i = 0, $count = count($slashKeys); $i < $count; $i++) {
                $key = $slashKeys[$i];
                if ($key === '') {
                    break;
                }

                // if value is null and key exists save it
                if ($value === null && isset($this->_data[$key])) {
                    $value = $this->_data[$key];
                } // if value is not null we have a subkey, so if subkey exists save the value
                elseif ($value !== null && isset($value[$key])) {
                    $value = $value[$key];
                } else {
                    break;
                }

                // if last element is reached
                if ($i + 1 === count($slashKeys)) {
                    // if index is not null
                    if ($index !== null) {
                        // if value is still an array and the index exists save it
                        if (is_array($value) && isset($value[$index])) {
                            $return[$andKey] = $value[$index];
                        } // if value is an Cemes_Object try to get the data at the given index
                        elseif ($value instanceof self) {
                            $return[$andKey] = $value->getData($index);
                        }
                    } else // if index is null save the value
                    {
                        $return[$andKey] = $value;
                    }
                }
            }
        }

        // if return is an empty array return null, else if return only holds one element give back the element else give back the array
        return empty($return) ? null : ((count($return) === 1) ? current($return) : $return);
    }

    public function setData($key, $value)
    {
        $this->_hasChanges = true;
        // accept a/b/c as ['a']['b']['c']
        $slashKeys = explode('/', $key);
        $data = &$this->_data;
        for ($i = 0, $count = count($slashKeys); $i < $count; $i++) {
            $key = $slashKeys[$i];
            if ($key === '' || $i + 1 === count($slashKeys)) {
                if ($key === '') {
                    $data[] = $value;
                } else {
                    $data[$key] = $value;
                }
                break;
            }

            $data = &$data[$key];
        }
    }

    public function hasData($key = '')
    {
        $return = false;

        if (empty($key)) {
            return $return;
        }

        // accept a/b/c as ['a']['b']['c']
        $slashKeys = explode('/', $key);
        // if there is only one key unser it
        if (count($slashKeys) === 1 && isset($this->_data[$slashKeys[0]])) {
            return true;
        }

        $data = $this->_data;

        // go through all keys
        for ($i = 0, $count = count($slashKeys); $i < $count; $i++) {
            $key = $slashKeys[$i];
            if ($key === '') {
                break;
            }

            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                break;
            }

            // if last element is reached
            if ($i + 1 === count($slashKeys)) {
                $return = true;
            }
        }

        return $return;
    }

    public function unsData($key = '')
    {
        $this->_hasChanges = true;
        if ($key === '') {
            $this->_data = null;

            return true;
        }
        if (!$this->hasData($key)) {
            return false;
        }

        // accept a/b/c as ['a']['b']['c']
        $slashKeys = explode('/', $key);
        // if there is only one key unser it
        if (count($slashKeys) === 1) {
            unset($this->_data[$slashKeys[0]]);
        } else // else $value is data
        {
            $value = &$this->_data;
        }
        // go through all keys
        for ($i = 0, $count = count($slashKeys); $i < $count; $i++) {
            $key = $slashKeys[$i];
            if ($key === '') {
                break;
            }

            if (isset($value[$key])) {
                if ($i + 1 === count($slashKeys)) {
                    unset($value[$key]);
                } else {
                    $value = &$value[$key];
                }
            } else {
                break;
            }
        }

        return true;
    }

    /**
     * @param array $data
     * @param array $objects
     *
     * @return array|string
     */
    public function debug($data = null, &$objects = [])
    {
        if ($data === null) {
            $hash = spl_object_hash($this);
            if (!empty($objects[$hash])) {
                return '*** RECURSION ***';
            }
            $objects[$hash] = true;
            $data = $this->getData();
        }
        $debug = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $debug[$key] = $value;
            } elseif (is_array($value)) {
                $debug[$key] = $this->debug($value, $objects);
            } elseif ($value instanceof Cemes_Object) {
                $debug[$key . ' (' . get_class($value) . ')'] = $value->debug(null, $objects);
            }
        }

        return $debug;
    }

    protected function _getCleanKey($str)
    {
        if (!isset($this->_keyCache[$str])) {
            $this->_keyCache[$str] = strtolower(
                preg_replace('/(.)([A-Z])/', '$1_$2', str_replace('_', '/', substr($str, 3)))
            );
        }

        return $this->_keyCache[$str];
    }

    public function isEmpty()
    {
        return count($this->_data) === 0;
    }

    public function hasChanges()
    {
        return $this->_hasChanges;
    }

    public function clearHasChanges()
    {
        $this->_hasChanges = false;
    }
}
