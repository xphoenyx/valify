<?php

namespace valify\validators;

abstract class AbstractValidator {
    public $allowEmpty = true;
    public $_data = [];
    public $message;
    protected $attribute;
    protected $value;
    private $_errors = [];

    /**
     * Although constructor is empty right now,
     * it may be useful in future.
     */
    function __construct() {}

    /**
     * Called by Validator class, when loading new validator.
     * For better performance, only a single validator object
     * is created for all values, that should be validated
     * through this validator. So, if object has already been
     * created, we only need to load attribute and value into it.
     *
     * @param $attribute
     * @param $value
     */
    public function setAttributeAndValue($attribute, $value) {
        $this->attribute = $attribute;
        $this->value     = $value;
    }

    /**
     * Called by Validator class to execute validation.
     * Yes, some errors may be set in the init() method
     */
    public function init() {
        if( !$this->gotErrors() && ( !$this->allowEmpty || ( $this->allowEmpty && !$this->isEmpty($this->value) ) ) ) {
            $this->validateValue($this->value);
        }
    }

    /**
     * Error message constructor. Attribute and value will always be
     * accessible in message string as {attribute} and {value} templates.
     * You can use own params to replace in $msg.
     * Usage:
     * $msg = "Length should be longer than {min} chars and not exceed {max} chars"
     * $params = ['{min}'=>3, '{max}'=>15]
     *
     * @param string $msg
     * @param array $params
     */
    protected function addError($msg, $params = []) {
        $params = array_merge([
            '{attribute}' => $this->attribute,
            '{value}'     => $this->value,
        ], $params);

        foreach ($params as $template => $value)
            $params[$template] = $this->isPrintable($value) ? $value : gettype($value);

        $msg = str_replace(array_keys($params), array_values($params), $msg);

        $this->_errors[$this->attribute][] = $msg;
    }

    /**
     * Fetching errors for a particular validator
     * @return array
     */
    public function fetchErrors() {
        return $this->_errors;
    }

    /**
     * Does current validator got any errors
     * @return bool
     */
    public function gotErrors() {
        return !empty($this->_errors);
    }

    public function isPrintable($value) {
        return ( is_string($value) || is_numeric($value) );
    }

    public function isEmpty($value) {
        $empty = true;

        if ( is_string($value) || is_numeric($value) ) {
            $empty = empty($value);
        } elseif( is_object($value) ) {
            $empty = $this->isEmpty( get_object_vars($value) );
        } elseif( is_array($value) ) {
            foreach ($value as $key => $val) {
                if( !$this->isEmpty($val) ) {
                    $empty = false;
                    break;
                }
            }
        }

        return $empty;
    }

    abstract protected function validateValue($value);
}