<?php
class Maxima_BankSlip_Block_Adminhtml_Slip extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_slip';
		$this->_blockGroup = 'Maxima_BankSlip';
		$this->_headerText = Mage::helper('Maxima_BankSlip')->__('Bank Slips');
		$this->_addButtonLabel = Mage::helper('Maxima_BankSlip')->__('Add Item');
		parent::__construct();
	}
}