<?php
namespace vc\helper;

class ConstantHelper
{
    public static function getConstants($class, $ignorePrefixes = array(), $filterPrefix = null)
    {
        $oClass = new \ReflectionClass($class);
        $constants = array();
        foreach ($oClass->getConstants() as $key => $value) {
            $ignore = false;
            if (!empty($ignorePrefixes)) {
                foreach ($ignorePrefixes as $prefix) {
                    if (strpos($key, $prefix) === 0) {
                        $ignore = true;
                    }
                }
            }
            if ($ignore === false) {
                if ($filterPrefix === null || strpos($key, $filterPrefix) === 0) {
                    if ($filterPrefix !== null) {
                        $key = substr($key, strlen($filterPrefix));
                    }
                    $constants[$value] = $key;
                }
            }
        }
        return $constants;
    }
}
