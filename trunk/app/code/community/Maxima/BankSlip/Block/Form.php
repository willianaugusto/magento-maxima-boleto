<?php

class Maxima_BankSlip_Block_Form extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('maxima/bankslip/form.phtml');
    }
    
    
    /**
     * 
     * Pega os valores da configuracao do modulo
     * 
     */
    
    public function getConfigData($config)
	{
    	return Mage::getStoreConfig('payment/Maxima_BankSlip/' . $config);
	}
}
