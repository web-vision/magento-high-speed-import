<?php
/**
 * A class to provide enum functionality to PHP.
 * Just extend this class and make a subclass for each value.
 *
 * abstract class Month extends Cemes_Pattern_Enum {}
 * class JAN extends Month { var $value = 1; }
 * class FEB extends Month {}
 * class MAR extends Month {}
 *
 * @package Cemes-Framework
 * @version 1.0.0
 * @author Tim Werdin
 */

abstract class Cemes_Pattern_Enum {
    protected static $instances = array();
    protected $value = null;

    protected static $classesWalked = false;
    protected static $typeCounters = array();
    protected static $classIntValues = array();

    final private function __construct() {}

    /**
     * @throws Exception
     */
    final protected static function walkClasses() {
        if(!self::$classesWalked) {
            foreach(get_declared_classes() as $class) {
                if(is_subclass_of($class, "Cemes_Pattern_Enum") && $class !== "Cemes_Pattern_Enum") {
                    $parent = get_parent_class($class);
                    if($parent == "Cemes_Pattern_Enum") continue;
                    if(!array_key_exists($parent, self::$typeCounters)) {
                        self::$typeCounters[$parent] = 0;
                    }
                    $obj = new $class;
                    $objval = $obj->value;
                    if(!is_null($objval)) {
                        if(is_numeric($objval)) {
                            $nextval = $objval + 1;
                            if($nextval <= self::$typeCounters[$parent]) {
                                throw new Exception("Specified enum member value is impossible");
                            }
                        }
                        self::$classIntValues[$class] = $objval;
                        self::$typeCounters[$parent] = (isset($nextval)) ? $nextval : self::$typeCounters[$parent] + 1;
                    } else {
                        self::$classIntValues[$class] = self::$typeCounters[$parent]++;
                    }
                }
            }
            self::$classesWalked = true;
        }
    }

    final public function __toString() {
        $class = get_class($this);
        $parent = get_parent_class($class);
        if($parent != 'Cemes_Pattern_Enum' && !array_key_exists($parent, self::$typeCounters)) {
            self::$classesWalked = false;
        }
        self::walkClasses();
        return (string) self::$classIntValues[$class];
    }

    final public static function get($name) {
        if(is_subclass_of($name, "Cemes_Pattern_Enum")) {
            if(array_key_exists($name, self::$instances)) {
                return self::$instances[$name];
            } else {
                return self::$instances[$name] = new $name();
            }
        } else {
            return null;
        }
    }

    final public static function __callStatic($name, $args) {
        return self::get($name);
    }

    final public static function iterator($enum_type) {
        return new EnumIterator($enum_type);
    }
}