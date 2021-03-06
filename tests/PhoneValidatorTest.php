<?php

namespace tests;

use valify\Validator;

class PhoneValidatorTest extends \PHPUnit_Framework_TestCase {
    public function testIsEmptyValid()
    {
        $isValid = Validator::validateFor('phone', '')->isValid;

        $this->assertFalse($isValid);
    }

    public function testIsTooShortValid()
    {
        $isValid = Validator::validateFor('phone', '555')->isValid;

        $this->assertFalse($isValid);
    }

    public function testIsValidWithCountryCodeCheck()
    {
        $isValid = Validator::validateFor('phone', '55555555')->isValid;

        $this->assertFalse($isValid);
    }

    public function testIsValidWithoutCountryCodeCheck()
    {
        $isValid = Validator::validateFor('phone', '55555555', ['checkCountryCode'=>false])->isValid;

        $this->asserttrue($isValid);
    }

    public function testIsValidWitCountryCode()
    {
        $isValid = Validator::validateFor('phone', '+999 55555555')->isValid;

        $this->assertTrue($isValid);
    }
}
