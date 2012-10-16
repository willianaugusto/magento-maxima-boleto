<?php

class Maxima_BankSlip_Model_Mysql4_Slip extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('Maxima_BankSlip/slip', 'slip_id');
    }
    
    public function getWriteConnection()
    {
		return Mage::getSingleton('core/resource')->getConnection('core_write');
    }
}