<?php

class Maxima_BankSlip_Block_Adminhtml_Form_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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
			'legend' => $helper->__('Enviar novo arquivo'),
			'class' => 'fieldset-wide'
		));
		
		
		// procura pelos bancos com boleto ativo
		$allowedBanks = $this->_getAllowedBanks();
		
		$fieldset->addField('filebank', 'select', array
		(
			'name' => 'filebank',
			'label' => $helper->__('Banco'),
			'required'  => true,
			"values" => $allowedBanks
		));
		
		
		$fieldset->addField('filename', 'file', array
		(
			'name' => 'filename',
			'label' => $helper->__('Arquivo'),
			'required'  => true,
		));
		
		
		if (Mage::registry('maxima_billet'))
		{
			$form->setValues(Mage::registry('maxima_banksplit')->getData());
		}
		
		return parent::_prepareForm();
	}
	
	
	private function _getAllowedBanks()
	{
		$resource = Mage::getSingleton('core/resource');
		$readConn = $resource->getConnection('core_read');
		
		$allowedBanks = array();
		
		$sql = "SELECT number, mage_billing_method, name FROM maxima_billet_bank ORDER BY name";
		$values = $readConn->fetchAll($sql);
		
		foreach($values as $row)
		{
			if(Mage::getStoreConfig('payment/' . $row['mage_billing_method'] . '/active') == "1")
				$allowedBanks[$row['number']] = $row['name'];
		}
		
		return $allowedBanks;
	}
}
