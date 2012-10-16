<?php

class Maxima_BankSlip_Block_Adminhtml_Form_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_blockGroup = 'Maxima_BankSlip';
		$this->_controller = 'adminhtml_form';
		$this->_headerText = Mage::helper('Maxima_BankSlip')->__('Edit Form');
	}
}
