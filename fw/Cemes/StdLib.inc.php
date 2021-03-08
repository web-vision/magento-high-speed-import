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
 * @package     Cemes_Mhsi
 * @copyright   Copyright (c) 2001-2017 web-vision GmbH (http://www.web-vision.de)
 * @license     <!--LICENSEURL-->
 * @author      Tim Werdin <t.werdin@web-vision.de>
 */
class Cemes_StdLib
{
    /**
     * Checks if $search is between min and max. If $strict is false this will include $min and $max.
     *
     * @static
     *
     * @param int|float $search The value to check for.
     * @param int|float $min    The min value.
     * @param int|float $max    The max value.
     * @param bool      $strict If min and max should be included.
     *
     * @return bool
     */
    public static function between($search, $min, $max, $strict = false)
    {
        if (!is_numeric($search) || !is_numeric($min) || !is_numeric($max)) {
            return false;
        }

        if ($strict) {
            return ($min < $search && $search < $max);
        }

        return ($min <= $search && $search <= $max);
    }

    /**
     * Checks if $needle exists as a key in $haystack recursively.
     *
     * @static
     *
     * @param string $needle   The key to search for.
     * @param array  $haystack The array to search in.
     *
     * @return bool
     */
    public static function array_key_exists_recursive($needle, $haystack)
    {
        foreach ($haystack as $key => $value) {
            if ($needle == $key) {
                return true;
            }
            if (is_array($value)) {
                if (self::array_key_exists_recursive($needle, $value)) {
                    return true;
                }

                continue;
            }
        }

        return false;
    }

    /**
     * Checks if a key exists in an array incasesensitive.
     *
     * @static
     *
     * @param string $needle   The key to search for.
     * @param array  $haystack The array to search in.
     *
     * @return bool
     */
    public static function array_ikey_exists($needle, $haystack)
    {
        return in_array(strtolower($needle), array_map('strtolower', array_keys($haystack)));
    }

    /**
     * Checks if all given needles are found in the haystack.
     * Returns the missing needles or true if all needles exist.
     *
     * @static
     *
     * @param array $needles  The needles to check for.
     * @param array $haystack The array to search in.
     *
     * @return array|bool
     */
    public static function array_keys_exists($needles, $haystack)
    {
        $needles = (array)$needles;

        $false = [];
        foreach ($needles as $needle) {
            if (array_key_exists($needle, $haystack) === false) {
                $false[] = $needle;
            }
        }
        if (!empty($false)) {
            return $false;
        }

        return true;
    }

    /**
     * Removes all occurrences of the given value in the array.
     *
     * @static
     *
     * @param array $array  The array to search in.
     * @param mixed $values The values to remove.
     * @param bool  $strict If the value should be search strictly.
     */
    public static function array_remove_values(&$array, $values, $strict = false)
    {
        $values = (array)$values;

        foreach ($values as $value) {
            do {
                $key = array_search($value, $array, $strict);
                if ($key !== false) {
                    unset($array[$key]);
                }
            } while ($key !== false);
        }
    }

    /**
     * Merges two array like array_merge_recursive but overrides when a key appears twice instead of making a value to
     * an array.
     *
     * @static
     *
     * @param array $array1 The first array.
     * @param array $array2 The second array.
     *
     * @return array
     */
    public static function array_merge_recursive_distinct(array $array1, array $array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = static::array_merge_recursive_distinct($merged[$key], $value);
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Converts an amount of bites into a human readable format up to Exabyte.
     *
     * @static
     *
     * @param int $bytes     The bytes to convert.
     * @param int $precision The amount of floating point numbers to round to.
     *
     * @return string
     */
    public static function bytesToSize($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB'];

        return @round($bytes / 1024 ** ($i = (int)floor(log($bytes, 1024))), $precision) . ' ' . $units[$i];
    }

    /**
     * Converts a string from snake case to camel case.
     *
     * @static
     *
     * @param string $string A snake case string.
     *
     * @return string
     */
    public static function toCamelCase($string)
    {
        return preg_replace_callback(
            '/_([a-z])/',
            function ($m) {
                return strtoupper($m[1]);
            },
            $string
        );
    }

    /**
     * Converts a string from camel case to snake case.
     *
     * @static
     *
     * @param string $string A camel case string.
     *
     * @return string
     */
    public static function fromCamelCase($string)
    {
        return preg_replace_callback(
            '/(.)([A-Z])/',
            function ($m) {
                return $m[1] . '_' . strtolower($m[2]);
            },
            $string
        );
    }

    /**
     * Gets data from an array or returns the given default if the key is not present in the array or the value is
     * empty.
     *
     * @static
     *
     * @param string     $needle   The needle to search in the array.
     * @param array      $haystack The array to search in.
     * @param null|mixed $default  The default value if the kes is not present or empty.
     *
     * @return null|mixed
     */
    public static function getData($needle, $haystack, $default = null)
    {
        return (array_key_exists($needle, $haystack) && !empty($haystack[$needle])) ? $haystack[$needle] : $default;
    }

    /**
     * Returns the given milliseconds in a human readable format from '0.{ms} s' up to '{h}:{m}:{s}.{ms}'.
     *
     * @static
     *
     * @param $milliseconds float The milliseconds to convert.
     *
     * @return string
     */
    public static function humanReadableTime($milliseconds)
    {
        $milliseconds = round($milliseconds, 4);
        if (strpos($milliseconds, '.') === false) {
            $milliseconds .= '.0';
        }
        list($s, $ms) = explode('.', $milliseconds);
        $time = '0.' . $ms . ' s';
        if ($s != 0) {
            $m = (int)($s / 60);
            $time = $s . '.' . $ms . ' s';

            if ($m != 0) {
                $s -= $m * 60;
                $h = (int)($m / 60);
                $time = $m . ':' . $s . '.' . $ms;

                if ($h != 0) {
                    $m -= $h * 60;
                    $time = $h . ':' . $m . ':' . $s . '.' . $ms;
                }
            }
        }

        return $time;
    }

    /**
     * Checks if the $haystack starts with the $needle.
     *
     * @static
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The needle to search for.
     *
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Checks if the $haystack ends with the $needle.
     *
     * @static
     *
     * @param string $haystack The string to search in.
     * @param string $needle   The needle to search for.
     *
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        return $needle === ''
            || (($temp = strlen($haystack) - strlen($needle)) >= 0
                && strpos(
                    $haystack,
                    $needle,
                    $temp
                ) !== false);
    }

    /**
     * Converts an object to an array recursively.
     *
     * @static
     *
     * @param Object|array $object
     *
     * @return array
     */
    public static function objectToArray($object)
    {
        $array = is_object($object) ? get_object_vars($object) : $object;
        foreach ($array as $key => $val) {
            $array[$key] = (is_array($val) || is_object($val)) ? self::objectToArray($val) : $val;
        }

        return $array;
    }

    /**
     * Similar to {@see array_splice()} but can handle associative arrays.
     *
     * @param array      $input            The array to alter.
     * @param string|int $offset           The offset either as an int counting from the beginning for positive numbers
     *                                     or the end for negative numbers.
     * @param int        $length           [optional] The amount of values after that to replace, up to the end of the
     *                                     array if omitted.
     * @param mixed      $replacementValue [optional] The replacement value.
     * @param mixed      $replacementKey   [optional] The replacement key.
     */
    public static function array_splice(
        array &$input,
        $offset,
        $length = null,
        $replacementValue = null,
        $replacementKey = null
    ) {
        $counter = 0;
        $removed = 0;
        $replaced = false;
        $pureNumeric = $replacementKey === null;
        $found = false;

        if ($offset < 0) {
            $offset = count($input) + $offset;
        }

        $newInput = [];
        foreach ($input as $key => $value) {
            if (!is_int($key)) {
                $pureNumeric = false;
            }

            if ((is_int($offset) && $counter === $offset) || (!is_int($offset) && $offset === $key)) {
                $found = true;

                if ($length === null) {
                    if ($replacementValue !== null) {
                        $length = 0;
                    } else {
                        $length = count($input);
                    }
                } elseif ($length < 0) {
                    $length = count($input) + $length - $counter;
                }
            }

            if ($found && $removed < $length) {
                $removed++;
                $counter++;
                continue;
            }

            if ($found && !$replaced && $replacementValue !== null) {
                if ($replacementKey !== null) {
                    if (is_array($replacementValue) && !empty($replacementValue)) {
                        foreach ($replacementValue as $index => $val) {
                            $newInput[$replacementKey[$index]] = $val;
                        }
                    } else {
                        $newInput[$replacementKey] = $replacementValue;
                    }
                } else {
                    if (is_array($replacementValue) && !empty($replacementValue)) {
                        foreach ($replacementValue as $val) {
                            $newInput[] = $val;
                        }
                    } else {
                        $newInput[] = $replacementValue;
                    }
                }
                $replaced = true;
            }

            $counter++;
            if (is_int($key)) {
                $newInput[] = $value;
            } else {
                $newInput[$key] = $value;
            }
        }

        if ($replacementValue !== null && !$replaced) {
            if ($replacementKey !== null) {
                if (is_array($replacementValue) && !empty($replacementValue)) {
                    foreach ($replacementValue as $index => $val) {
                        $newInput[$replacementKey[$index]] = $val;
                    }
                } else {
                    $newInput[$replacementKey] = $replacementValue;
                }
            } else {
                if (is_array($replacementValue) && !empty($replacementValue)) {
                    foreach ($replacementValue as $val) {
                        $newInput[] = $val;
                    }
                } else {
                    $newInput[] = $replacementValue;
                }
            }
        }

        if ($pureNumeric) {
            $input = array_values($newInput);
        } else {
            $input = $newInput;
        }
    }
}
