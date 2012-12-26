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
	
	public function viewAction()
	{
		$id     = $this->getRequest()->getParam('slip_id');
		$model  = Mage::getModel('Maxima_BankSlip/slip')->load($id);
		
		if ($model->getId())
		{
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			
			if (!empty($data))
			{
				$model->setData($data);
			}

			Mage::register('bankslip_slip_data', $model);
			
			$this->_initAction();
			$this->_addContent($this->getLayout()->createBlock('Maxima_BankSlip/adminhtml_slip_view'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('View Slip'), Mage::helper('adminhtml')->__('View Slip'));
			$this->renderLayout();
		}
		else
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Maxima_BankSlip')->__('Slip does not exist'));
			$this->_redirect('*/*/');
		}
	}
	
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('maxima/bankslip/list');
	}
}