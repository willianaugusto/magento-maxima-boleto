<?php

class Maxima_BankSlip_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getConfig($config)
	{
		return Mage::getStoreConfig('payment/Maxima_BankSlip/' . $config);
	}
}
