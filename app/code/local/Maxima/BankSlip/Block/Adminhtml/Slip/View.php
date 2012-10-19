<?php

class Maxima_BankSlip_Block_Adminhtml_Slip_View extends Mage_Core_Block_Template
{
    public function _toHtml()
    {
        if( Mage::registry('bankslip_slip_data') && Mage::registry('bankslip_slip_data')->getId() ) 
        {
            return Mage::helper('Maxima_BankSlip')->__("Bank Slip #%s", $this->htmlEscape(Mage::registry('bankslip_slip_data')->getId()));
        } 
        else 
        {
            return Mage::helper('Maxima_BankSlip')->__('Bank Slip');
        }
    }
}