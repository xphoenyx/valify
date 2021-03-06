<?php

namespace examples;

use \valify\validators\AbstractValidator;

/**
 * This class is constructed only for investigation purposes,
 * to give a more clear image of how to implement own validators.
 * Using own validator, define validator name in rules as a namespace:
 * $rules = [
 *    ['email', '\\examples\\ExampleValidator']
 * ];
 *
 * Class ExampleValidator
 * @package examples
 */
class ExampleValidator extends AbstractValidator {

    /**
     * You can define as much properties as you need.
     * They are automatically set with values from
     * corresponding keys from rule.
     *
     * @var $ownProperty
     */
    public $ownProperty;

    /**
     * You may override parent constructor and
     * do here something before validator init() execution.
     * For example, you can redefine attribute or its value.
     * It is completely safe to remove this method from here -
     * parent constructor will be executed anyway.
     * NB! Defining constructor params and calling
     * parent constructor before your logic is required.
     * Also, although you are able to access 'attribute'
     * and 'value' parent properties here, it is highly recommended
     * to deal with them only in validateValue() method,
     * because sometimes their values may be modified before
     * method validateValue() is called.
     * See \valify\validators\AbstractValidator::setAttributeAndValue()
     * method description for more info.
     */
    function __construct() {
        parent::__construct();
        // Your code here
    }

    /**
     * You can override this method to do some job
     * right after validator constructor.
     * For example, you can define extra object properties,
     * or modify predefined ones.
     * It is completely safe to omit overriding this method -
     * parent init() method will be executed anyway.
     * NB! Parent init() method call after your logic is required.
     */
    public function init() {
        // Your code here
        parent::init();
    }

    /**
     * This method is required.
     * Do here your validation magic. You have an
     * access to the value you are going to validate.
     * Attribute name is omitted here, but if you
     * definitely need it, ask it from $this->attribute.
     *
     * @param $value
     */
    protected function validateValue($value) {
        // Set an error message with some params (you can call addError() as many times as you want):
        $this->addError('Example error; Called at: {time}', ['{time}'=>date('H:i')]);

        // Your validation code here
    }
}