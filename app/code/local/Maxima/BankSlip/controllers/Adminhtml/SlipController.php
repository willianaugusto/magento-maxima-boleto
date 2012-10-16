<?php

class Maxima_BankSlip_Adminhtml_SlipController extends Mage_Adminhtml_Controller_action
{

	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu('maxima/bankslip/list');
		
		return $this;
	}
 
	public function indexAction()
	{
		$this->_title($this->__('Bank Slips'));
		$this->_initAction()->renderLayout();
	}
}