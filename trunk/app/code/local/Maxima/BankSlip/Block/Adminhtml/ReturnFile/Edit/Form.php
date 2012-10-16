<?php

class Maxima_BankSlip_Block_Adminhtml_ReturnFile_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
	/**
	* Preparing form
	*
	* @return Mage_Adminhtml_Block_Widget_Form
	*/
	protected function _prepareForm()
	{
		$form = new Varien_Data_Form
		(
			array
			(
				'id' 			=> 'edit_form',
				'action' 		=> $this->getUrl('*/*/save'),
				'method' 		=> 'post',
				'enctype' 		=> 'multipart/form-data'
			)
		);
		
		$form->setUseContainer(true);
		$this->setForm($form);
		
		$helper = Mage::helper('Maxima_BankSlip');
		
		$fieldset = $form->addFieldset('fileform', array
		(
			'legend' => $helper->__('Send new file'),
			'class' => 'fieldset-wide'
		));
		
		$fieldset->addField('cnabfile', 'file', array
		(
			'name' => 'cnabfile',
			'label' => $helper->__('File'),
			'required'  => true,
		));
		
		if (Mage::registry('maxima_banksplit_returnfile'))
		{
			$form->setValues(Mage::registry('maxima_banksplit_returnfile')->getData());
		}
		
		return parent::_prepareForm();
	}
}
