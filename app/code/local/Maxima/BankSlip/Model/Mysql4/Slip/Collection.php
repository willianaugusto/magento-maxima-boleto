<?php

class Maxima_BankSlip_Model_Mysql4_Slip_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Maxima_BankSlip/slip');
    }
}