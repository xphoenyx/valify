<?php

namespace valify;

class Validator {
    private $_errors = [];
    private $_rules = [];
    private $_data = [];
    private $_builtInValidators = [
        'required' => '\valify\validators\RequiredValidator',
        'string'   => '\valify\validators\StringValidator',
        'email'    => '\valify\validators\EmailValidator',
        'boolean'  => '\valify\validators\BooleanValidator',
        'compare'  => '\valify\validators\CompareValidator',
        'date'     => '\valify\validators\DateValidator',
        'default'  => '\valify\validators\DefaultValueValidator',
        'double'   => '\valify\validators\NumberValidator',
        'exist'    => '\valify\validators\ExistValidator',
        'file'     => '\valify\validators\FileValidator',
        'image'    => '\valify\validators\ImageValidator',
        'in'       => '\valify\validators\RangeValidator',
        'integer'  => '\valify\validators\NumberValidator',
        'match'    => '\valify\validators\RegularExpressionValidator',
        'number'   => '\valify\validators\NumberValidator',
        'url'      => '\valify\validators\UrlValidator',
    ];

    /**
     * You can perform a single validation by using this method.
     * Result of validate() method (boolean) will be returned.
     *
     * @param $name string - Name of validator
     * @param $value mixed - Value to validate. If array,
     * all keys are taken as attributes and values as values.
     * @param array $params array - Params for a validator
     * @return bool
     * @throws \Exception
     */
    function validateFor($name, $value, $params = []) {
        $rules = [];

        if( is_array($value) ) {
            foreach ($value as $attr => $val)
                $rules[] = [$attr, $name, $params];
        } else {
            $rules[] = [$name, $name, $params];
            $value = [$name => $value];
        }

        return $this->setRules($rules)->loadData($value)->validate();
    }

    /**
     * You can call this method multiple times. New rules
     * will be merged with already loaded ones.
     *
     * @param array $rules
     * @return $this
     */
    public function setRules($rules = []) {
        if( !is_array($rules) )
            throw new \InvalidArgumentException("Rules must be provided as an array");

        foreach ($rules as $rule) {
            if( !is_array($rule) )
                throw new \UnexpectedValueException("Every rule must be provided as an array");
        }

        //TODO Rules could be set in JSON

        $this->_rules = array_merge($this->_rules, $rules);
        return $this;
    }

    /**
     * You can call this method multiple times. New data
     * will be merged with already loaded one.
     *
     * @param array $data
     * @return $this
     */
    public function loadData($data = []) {
        if( !is_array($data) )
            throw new \InvalidArgumentException("Data must be provided as an array");

        //TODO Data could be set in JSON

        $this->_data = array_merge($this->_data, $data);
        return $this;
    }

    /**
     * @return bool
     */
    public function validate() {
        foreach ($this->_rules as $rule) {
            $attribute = array_shift($rule);
            $validatorName = array_shift($rule);

            if($validatorName) {
                if( is_string($attribute) ) {
                    $value = isset($this->_data[$attribute]) ? $this->_data[$attribute] : null;
                    $this->callValidator($validatorName, [$attribute => $value], $rule);
                } elseif( is_array($attribute) ) {
                    $safeData = array_intersect_key( $this->_data, array_flip($attribute) );
                    $this->callValidator($validatorName, $safeData, $rule);
                }
            }
        }

        return !$this->hasErrors();
    }

    /**
     * After using validate(), we can
     * just check, if there are any errors
     * @return bool
     */
    public function hasErrors() {
        return !empty($this->_errors);
    }

    /**
     * @return array
     */
    public function getErrors($attribute = null) {
        return isset($this->_errors[$attribute]) ? $this->_errors[$attribute] : $this->_errors;
    }

    /**
     * Get error of a particular attribute
     * @param $attribute
     * @return array|null
     */
    public function getError($attribute = null) {
        return isset($this->_errors[$attribute]) ? $this->_errors[$attribute][0] : null;
    }

    private function callValidator($validator, $data, $rule = []) {
        if( isset($this->_builtInValidators[$validator]) ) {
            $namespace = $this->_builtInValidators[$validator];
            $validator = new $namespace();
        } elseif( strpos($validator, '\\') !== false ) { # Validator name is matched as a namespace
            $validator = new $validator();

            if( !is_subclass_of($validator, '\valify\validators\AbstractValidator', false) )
                throw new \DomainException("Validator " . get_class($validator) . " must extend \\valify\\validators\\AbstractValidator class");
        }

        if( is_object($validator) ) {
            /** @var $validator validators\AbstractValidator */
            $validator = $this->setValidatorProperties($validator, $rule);

            foreach ($data as $attr => $val) {
                $validator->setAttributeAndValue($attr, $val);
                $validator->init();
                if( $validator->gotErrors() )
                    $this->setErrorStack($validator->fetchErrors());
            }
        } else {
            throw new \UnexpectedValueException("Validator " . get_class($validator) . " not found");
        }
    }

    private function setValidatorProperties($obj, $params) {
        foreach ($params as $prop => $value)
            $obj->$prop = $value;

        return $obj;
    }

    private function setErrorStack($errors) {
        foreach ($errors as $attr => $msgs)
            $this->_errors[$attr] = $msgs;
    }
}