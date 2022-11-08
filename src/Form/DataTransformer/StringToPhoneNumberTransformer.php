<?php

namespace App\Form\DataTransformer;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use Symfony\Component\Form\DataTransformerInterface;

class StringToPhoneNumberTransformer implements DataTransformerInterface 
{

    // The 'libphonenumber.phone_number_util' service 
    private $phoneNumberUtil; 
    
    public function __construct($util)
    {
        $this->phoneNumberUtil = $util; 
    }

    /**
     * Transforms a string (number) to an object (PhoneNumber)
     *
     * @param  string $phoneNumberString
     * @return PhoneNumber
     */
    public function transform($phoneNumberString)
    {
        if ($phoneNumberString != "")
        {
            return $this->phoneNumberUtil->parse($phoneNumberString, "US");
        }
        return new PhoneNumber;
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  PhoneNumber $phoneNumberObject
     * @return string
     */
    public function reverseTransform($phoneNumberObject)
    {
        if ($phoneNumberObject != null)
        {
            return $this->phoneNumberUtil->format($phoneNumberObject, PhoneNumberFormat::NATIONAL);
        }
        return '';
    }
}