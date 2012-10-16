<?php

class Maxima_BankSlip_Block_Adminhtml_ReturnFile_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{
		parent::__construct();
		$this->_blockGroup = 'Maxima_BankSlip';
		$this->_controller = 'adminhtml_returnFile';
		$this->_headerText = Mage::helper('Maxima_BankSlip')->__('Return Files');
	}
}
