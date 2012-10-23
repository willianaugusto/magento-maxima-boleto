<?php


class Maxima_BankSlip_Model_Method_BankSlip extends Mage_Payment_Model_Method_Abstract
{
	protected $_code  = 'Maxima_BankSlip';
	protected $_formBlockType = 'Maxima_BankSlip/form';
	protected $_infoBlockType = 'Maxima_BankSlip/info';
	
	public function getOrderPlaceRedirectUrl()
	{
		return Mage::getUrl("bankslip/index/success");
	}
}
