<?php

namespace User\Model;

// Add these import statements
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class PlinkCustomer {

    public $PLC_ID;
    public $PLC_CUSTNO;
    public $PLC_EMAILS;
    public $PLC_DFT_UOM;
    public $PLC_DFT_SHIPTO;
    public $PLC_DFT_SHIP_METHOD;
    public $PLC_STATUS;

    public function exchangeArray($data) {
        $this->PLC_ID = (!empty($data['PLC_ID'])) ? $data['PLC_ID'] : null;
        $this->PLC_CUSTNO = (!empty($data['PLC_CUSTNO'])) ? $data['PLC_CUSTNO'] : null;
        $this->PLC_EMAILS = (!empty($data['PLC_EMAILS'])) ? $data['PLC_EMAILS'] : null;
        $this->PLC_DFT_UOM = (!empty($data['PLC_DFT_UOM'])) ? $data['PLC_DFT_UOM'] : null;
        $this->PLC_DFT_SHIPTO = (!empty($data['PLC_DFT_SHIPTO'])) ? $data['PLC_DFT_SHIPTO'] : null;
        $this->PLC_DFT_SHIP_METHOD = (!empty($data['PLC_DFT_SHIP_METHOD'])) ? $data['PLC_DFT_SHIP_METHOD'] : null;
        $this->PLC_STATUS = (!empty($data['PLC_STATUS'])) ? $data['PLC_STATUS'] : null;
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