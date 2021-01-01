<?php
namespace vc\lib;

class Assert
{
    public static function assertLongArray($field, &$array, $min, $max, $optionalValue)
    {
        if (empty($array) && $optionalValue) {
            return;
        }
        if (!is_array($array)) {
            throw new \vc\exception\AssertionException("Value '" . $field. "' is no array.");
        }
        if (count($array) == 0 && !$optionalValue) {
            throw new \vc\exception\AssertionException("Empty long-array-value '" . $field. "'.");
        }

        foreach ($array as $value) {
            self::assertLong($field, $value, $min, $max, $optionalValue);
        }
    }

    public static function assertLong($field, $value, $min, $max, $optionalValue)
    {
        self::assertDouble($field, $value, $min, $max, $optionalValue);
        if ($value % 1 > 0) {
            throw new \vc\exception\AssertionException(
                "Value '" . $value . "' in field '" . $field. "' no long value."
            );
        }
    }

    public static function assertDouble($field, $value, $min, $max, $optionalValue)
    {
        if (empty($value) || $value == "") {
            if (!$optionalValue) {
                throw new \vc\exception\AssertionException(
                    "Empty long-value '" . $field. "' (" . $value . ")." . ($value===0)
                );
            }
        } elseif (!is_numeric($value)) {
            throw new \vc\exception\AssertionException(
                "Value '" . $value . "' in field '" . $field. "' no numeric value."
            );
        } elseif ($value < $min) {
            throw new \vc\exception\AssertionException(
                "Value '" . $value . "' in field '" . $field. "' smaller than minimum (" . $min . ")."
            );
        } elseif ($value > $max) {
            throw new \vc\exception\AssertionException(
                "Value '" . $value . "' in field '" . $field. "' larger than maximum (" . $max . ")"
            );
        }
    }

    public static function assertArraySize($field, &$array, $size)
    {
        if (count($array) < $size) {
            throw new \vc\exception\AssertionException(
                "Array '" . $field. "' to small (" . count($array) . "<" . $size . ")."
            );
        }
    }

    public static function assertValuesInArray($field, $valueArray, $array, $optionalValue)
    {
        if (array_key_exists($field, $valueArray) && is_array($valueArray[$field])) {
            foreach ($valueArray[$field] as $value) {
                if (!array_key_exists($value, $array)) {
                    throw new \vc\exception\AssertionException(
                        "Value '" . $value . "' not found in array '" . $field. "'."
                    );
                }
            }
        } elseif (!$optionalValue) {
            throw new \vc\exception\AssertionException("Value '" . $field. "' missing in array.");
        }
    }

    public static function assertValueInArray($field, $value, $array, $optionalValue = false)
    {
        if (empty($value) || is_array($value) || count($value) == 0) {
            if (!$optionalValue) {
                throw new \vc\exception\AssertionException("Missing value '" . $field. "' (" . $value . ").");
            }
        } elseif (!in_array($value, $array)) {
            throw new \vc\exception\AssertionException("Value '" . $value . "' not found in array '" . $field. "'.");
        }
    }

    // Throws the exception if the value is invalid
    public static function invalidValue($field, $value)
    {
        throw new \vc\exception\AssertionException("Invalid value '" . $value . "' in field '" . $field. "'.");
    }
    
    public static function assertBeginsWith($string, $start)
    {
        if (stripos($string, $start) !== 0) {
            throw new \vc\exception\AssertionException("String " . $string . " doesn't begin with " . $start);
        }
    }
    
    public static function assertUnreachable()
    {
        throw new \vc\exception\AssertionException('Reached unreachable code');
    }
}
