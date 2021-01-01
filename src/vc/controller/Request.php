<?php
namespace vc\controller;

class Request
{
    private $values;

    public function __construct($values)
    {
        $this->values = $values;
    }

    public function assertNumericParam($name, $optional = false, $min = null, $max = null)
    {
        if (array_key_exists($name, $this->values)) {
            if (is_numeric($this->values[$name])) {
                if ($min !== null && $this->values[$name] < $min) {
                    throw new \vc\exception\AssertionException(
                        'Field "' . $name . '" has a too small value.'
                    );
                }
                if ($max !== null && $this->values[$name] > $max) {
                    throw new \vc\exception\AssertionException(
                        'Field "' . $name . '" has a too big value.'
                    );
                }
            } else {
                throw new \vc\exception\AssertionException(
                    'Field "' . $name . '" is not numeric: ' . var_export($this->values[$name], true)
                );
            }
        } else {
            if ($optional === false) {
                throw new \vc\exception\AssertionException(
                    'Non-optional field "' . $name . '" is missing.'
                );
            }
        }
    }

    public function assertNumericArrayParam($name, $optional = false, $min = null, $max = null)
    {
        if (array_key_exists($name, $this->values)) {
            if (is_array($this->values[$name])) {
                foreach ($this->values[$name] as $value) {
                    if (is_numeric($value)) {
                        if ($min !== null && $value < $min) {
                            throw new \vc\exception\AssertionException(
                                'Field "' . $name . '" has a too small value.'
                            );
                        }
                        if ($max !== null && $value > $max) {
                            throw new \vc\exception\AssertionException(
                                'Field "' . $name . '" has a too big value.'
                            );
                        }
                    } else {
                        throw new \vc\exception\AssertionException(
                            'Field "' . $name . '" has a non-numeric value: ' . var_export($value, true)
                        );
                    }
                }
            } else {
                throw new \vc\exception\AssertionException(
                    'Field "' . $name . '" isn\t an array.'
                );
            }
        } else {
            if ($optional === false) {
                throw new \vc\exception\AssertionException(
                    'Non-optional field "' . $name . '" is missing.'
                );
            }
        }
    }

    public function assertValidParam($name, $allowedValues, $optional = false)
    {
        if (array_key_exists($name, $this->values)) {
            if (!in_array($this->values[$name], $allowedValues)) {
                throw new \vc\exception\AssertionException(
                    'Field "' . $name . '" doesn\'t have a valid value.'
                );
            }
        } else {
            if ($optional === false) {
                throw new \vc\exception\AssertionException(
                    'Non-optional field "' . $name . '" is missing.'
                );
            }
        }
    }

    public function assertValidArrayParam($name, $allowedValues, $optional = false)
    {
        if (array_key_exists($name, $this->values)) {
            if (is_array($this->values[$name])) {
                foreach ($this->values[$name] as $value) {
                    if (!in_array($value, $allowedValues)) {
                        throw new \vc\exception\AssertionException(
                            'Field "' . $name . '" contains a non-valid value: ' . var_export($value, true)
                        );
                    }
                }
            } else {
                throw new \vc\exception\AssertionException(
                    'Field "' . $name . '" isn\t an array.'
                );
            }
        } else {
            if ($optional === false) {
                throw new \vc\exception\AssertionException(
                    'Non-optional field "' . $name . '" is missing.'
                );
            }
        }
    }

    public function hasParameter($name)
    {
        return array_key_exists($name, $this->values);
    }

    public function hasArrayParameter($name)
    {
        return array_key_exists($name, $this->values) && is_array($this->values[$name]);
    }

    public function getText($name, $default = null)
    {
        if (array_key_exists($name, $this->values)) {
            return $this->values[$name];
        } else {
            return $default;
        }
    }

    public function getBoolean($name)
    {
        if (empty($this->values[$name])) {
            return false;
        } else {
            return true;
        }
    }

    public function getInt($name, $default = 0)
    {
        if (array_key_exists($name, $this->values)) {
            return intval($this->values[$name]);
        } else {
            return $default;
        }
    }

    public function getIntArray($name)
    {
        if (array_key_exists($name, $this->values) && is_array($this->values[$name])) {
            return array_map('intval', $this->values[$name]);
        } else {
            return array();
        }
    }

    public function getEmail($name)
    {
        if (array_key_exists($name, $this->values)) {
            return filter_var($this->values[$name], FILTER_VALIDATE_EMAIL);
        } else {
            return null;
        }

    }

    public function getTextArray($name)
    {
        if (array_key_exists($name, $this->values) && is_array($this->values[$name])) {
            return $this->values[$name];
        } else {
            return array();
        }
    }

    public function getValues()
    {
        return $this->values;
    }
}
