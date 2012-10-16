<?php
class Maxima_BankSlip_IndexController extends Mage_Core_Controller_Front_Action
{
	public function viewAction()
	{
		// pega o pedido de compra
		$orderIncrementId = $this->getRequest()->getParam('order');
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		$slip = Mage::getModel('Maxima_BankSlip/slip')->loadByOrder($order);
		
		if(!$order || !$order->getId() || !$slip || !$slip->getId())
		{
			$this->getResponse()->setHeader('HTTP/1.1','404 Not Found');
			$this->getResponse()->setHeader('Status','404 File not found');
			$pageId = Mage::getStoreConfig('web/default/cms_no_route');
			if (!Mage::helper('cms/page')->renderPage($this, $pageId))
			{
				$this->_forward('defaultNoRoute');
			}
		}
		else
		{
			// renderiza layout
			$this->loadLayout();
			$this->getLayout()->getBlock('Maxima_BankSlip.view')->setOrder($order);
			$this->getLayout()->getBlock('Maxima_BankSlip.view')->setSlip($slip);
			$this->renderLayout();
		}
	}
	
	public function successAction()
	{
		// cria a instancia
		$orderId = Mage::getSingleton('checkout/session')->getLastOrderId();
		$order = Mage::getModel('sales/order')->load($orderId);
		$bankCode = Mage::getStoreConfig('payment/Maxima_BankSlip/bank');
		$slip = Mage::getModel('Maxima_BankSlip/slip')->createNewSlip($order, $bankCode);
		
		// envia email de nova compra
		$order->sendNewOrderEmail();
		$order->setEmailSent(true);
		$order->save();
		
		// renderiza layout
		$this->loadLayout();     
		$this->renderLayout();
	}
} 
