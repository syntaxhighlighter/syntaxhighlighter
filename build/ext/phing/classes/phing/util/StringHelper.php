<?php

/**
 * String helper utility class.
 *
 * This class includes some Java-like functions for parsing strings,
 * as well as some functions for getting qualifiers / unqualifying phing-style
 * classpaths.  (e.g. "phing.util.StringHelper").
 *
 * @author Hans Lellelid <hans@xmpl.org>
 * @package phing.system.util
 */
class StringHelper {

    private static $TRUE_VALUES = array("on", "true", "t", "yes");
    private static $FALSE_VALUES = array("off", "false", "f", "no");
    
    /**
     * Replaces identifier tokens with corresponding text values in passed string.
     *
     * @params array $strings Array of strings to multiply. (If string is passed, will convert to array)
     * @params array $tokens The tokens to search for.
     * @params array $replacements The values with which to replace found tokens.
     * @return string
     */
    public static function multiply($strings, $tokens, $replacements) {
        $strings = (array) $strings;
        $results = array();
        foreach ($strings as $string) {
            $results[] = str_replace($tokens, $replacements, $string);
        }        
        return $results;
    }

    /**
     * Remove qualification to name.
     * E.g. eg.Cat -> Cat
     * @param string $qualifiedName
     * @param string $separator Character used to separate.
     */
    public static function unqualify($qualifiedName, $separator = '.') {
        // if false, then will be 0
        $pos = strrpos($qualifiedName, $separator);
        if ($pos === false) { 
            return $qualifiedName;  // there is no '.' in the qualifed name
        } else {
            return substr($qualifiedName, $pos + 1); // start just after '.'
        }
    }

    /** 
     * Converts a string to an indexed array of chars
     * There's really no reason for this to be used in PHP, since strings
     * are all accessible using the $string{0} notation.
     * @param string $string
     * @return array
     * @deprecated
     */
    public static function toCharArray($str) {
        $ret=array();
        $len=strlen($str);
        for ($i=0; $i < $len; $i++) {
            $ret[] = $str{$i};
        }
        return $ret;
    }
    
    /**
     * Get the qualifier part of a qualified name.
     * E.g. eg.Cat -> eg
     * @return string
     */    
    public static function qualifier($qualifiedName, $seperator = '.') {
        $pos = strrchr($qualifiedName, $seperator);
        if ($pos === false) {
            return '';
        } else {
            return substr($qualifiedName, 0, $pos);
        }
    }
    
    /**
     * @param array $columns String[]
     * @param string $prefix
     * @return array String[]
     */ 
    public static function prefix( $columns, $prefix) {
        if ($prefix == null) return $columns;
        $qualified = array();
        foreach($columns as $key => $column) {
            $qualified[$key] = $prefix . $column;
        }        
        return $qualified;
    }
    
    /**
     *
     * @return string
     */ 
    public static function root($qualifiedName, $separator = '.') {
        $loc = strpos($qualifiedName, $separator);
        return ($loc === false) ? $qualifiedName : substr($qualifiedName, 0, $loc);
    }
    
    /**
     * @return int
     */
    public static function hashCode($string) {
        return crc32($string);
    }
    
    /**
     * @return boolean
     */ 
    public static function booleanValue($s) {
        if (is_bool($s)) {
            return $s; // it's already boolean (not a string)
        }
        // otherwise assume it's something like "true" or "t"
        $trimmed = strtolower(trim($s));
        return (boolean) in_array($trimmed, self::$TRUE_VALUES);
    }

    /** tests if a string is a representative of a boolean */
    public static function isBoolean($s) {

        if (is_bool($s)) {
            return true; // it already is boolean
        }
        
        if ($s === "" || $s === null || !is_string($s)) {
            return false; // not a valid string for testing
        }

        $test = trim(strtolower($s));
        return (boolean) in_array($test, array_merge(self::$FALSE_VALUES, self::$TRUE_VALUES));
    }
        
    /**
     * Creates a key based on any number of passed params.
     * @return string
     */
    public static function key() {
        $args = func_get_args();
        return serialize($args);
    }    
    
    /** tests if a string starts with a given string */
    public static function startsWith($check, $string) {
        if ($check === "" || $check === $string) {
            return true;
        } else {
            return (strpos($string, $check) === 0) ? true : false;
        }
    }
    
    /** tests if a string ends with a given string */
    public static function endsWith($check, $string) {
        if ($check === "" || $check === $string) {
            return true;
        } else {
            return (strpos(strrev($string), strrev($check)) === 0) ? true : false;
        }
    }            

    /**
     * a natural way of getting a subtring, php's circular string buffer and strange
     * return values suck if you want to program strict as of C or friends 
     */
    public static function substring($string, $startpos, $endpos = -1) {
        $len    = strlen($string);
        $endpos = (int) (($endpos === -1) ? $len-1 : $endpos);
        if ($startpos > $len-1 || $startpos < 0) {
            trigger_error("substring(), Startindex out of bounds must be 0<n<$len", E_USER_ERROR);
        }
        if ($endpos > $len-1 || $endpos < $startpos) {
            trigger_error("substring(), Endindex out of bounds must be $startpos<n<".($len-1), E_USER_ERROR);
        }
        if ($startpos === $endpos) {
            return (string) $string{$startpos};
        } else {
            $len = $endpos-$startpos;
        }
        return substr($string, $startpos, $len+1);
    }

    /**
     * Does the value correspond to a slot variable?
     * @param string $value    
     */
    public static function isSlotVar($value) {
        $value = trim($value);
        if ($value === "") return false;
        return preg_match('/^%\{([\w\.\-]+)\}$/', $value);
    }
    
    /**
     * Extracts the variable name for a slot var in the format %{task.current_file}
     * @param string $var The var from build file.
     * @return string Extracted name part.
     */
    public static function slotVar($var) {
        return trim($var, '%{} ');
    }
    
}


