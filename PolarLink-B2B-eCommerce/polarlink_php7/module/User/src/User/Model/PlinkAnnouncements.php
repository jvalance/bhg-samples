<?php

namespace User\Model;

// Add these import statements
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class PlinkAnnouncements {

    public $PLA_ID;
    public $PLA_CUSTNO;
    public $PLA_COMPANY;
    public $PLA_CUST_TYPE;
    public $PLA_START_DATE;
    public $PLA_END_DATE;
    public $PLA_MESSAGE;

    public function exchangeArray($data) {
        $this->PLA_ID = (!empty($data['PLA_ID'])) ? $data['PLA_ID'] : null;
        $this->PLA_CUSTNO = (!empty($data['PLA_CUSTNO'])) ? $data['PLA_CUSTNO'] : null;
        $this->PLA_COMPANY = (!empty($data['PLA_COMPANY'])) ? $data['PLA_COMPANY'] : null;
        $this->PLA_CUST_TYPE = (!empty($data['PLA_CUST_TYPE'])) ? $data['PLA_CUST_TYPE'] : null;
        $this->PLA_START_DATE = (!empty($data['PLA_START_DATE'])) ? $data['PLA_START_DATE'] : null;
        $this->PLA_END_DATE = (!empty($data['PLA_END_DATE'])) ? $data['PLA_END_DATE'] : null;
        $this->PLA_MESSAGE = (!empty($data['PLA_MESSAGE'])) ? $data['PLA_MESSAGE'] : null;
    }

    // Add the following method:
    public function getArrayCopy() {
        return get_object_vars($this);
    }

    // Add content to these methods:
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used");
    }

    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

}