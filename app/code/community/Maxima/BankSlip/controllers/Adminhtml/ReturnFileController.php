<?php

class Maxima_BankSlip_Adminhtml_ReturnFileController extends Mage_Adminhtml_Controller_Action
{
	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu('maxima/bankslip/returnfile');
		
		return $this;
	}
	
	public function indexAction()
	{
		$this->_initAction()->renderLayout();
	}
	
	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			if(isset($_FILES['cnabfile']['name']) && $_FILES['cnabfile']['name'] != '')
			{
				try
				{
					$uploader = new Varien_File_Uploader('cnabfile');
					$uploader->save(Mage::getBaseDir('tmp'), Mage::getSingleton('admin/session')->getSessionId());
					
					$resource = Mage::getSingleton('core/resource');
					$readConn = $resource->getConnection('core_read');
					$writeConn = $resource->getConnection('core_write');
					
					// salva arquivo em diretorio temporario
					$uploadedFileName =  $_FILES['cnabfile']['name'];
					$newFileName = Mage::getBaseDir('tmp') . "/" . Mage::getSingleton('admin/session')->getSessionId();
					
					// le conteudo do arquivo
					$fileContent = file_get_contents($newFileName);
					$file = file($newFileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
					
					// descobre o formato do arquivo
					if(!is_array($file) && count($file) == 0)
					{
						throw new Exception("It was not possible to open the file.");
					}
					
					$pattern = strlen($file[0]);
					
					if($pattern !== 240 && $pattern != 400)
					{
						throw new Exception("The file format is incorrect.");
					}
					
					// insere registro no banco de dados
					$writeConn->exec
					(
						"INSERT INTO maxima_bankslip_file 
							(name, type, date, content) 
						VALUES 
							('" . $uploadedFileName . "', '" . $pattern . "', NOW(), '" . $fileContent . "')"
					);
					
					$fileId = $writeConn->lastInsertId();
					
					if($pattern == "200")
					{
						
					}
					else if($pattern == "400")
					{
						$proccessor = Mage::getModel('Maxima_BankSlip/cnab400', array
						(
							'file' => $file,
							'readConn' => $readConn,
							'writeConn' => $writeConn
						));
						
						
						$proccessor->analyse();
						$proccessor->registerTransactions($fileId);
						
						$warnings = $proccessor->getWarnings();
						
						foreach($warnings as $war)
						{
							Mage::getSingleton('adminhtml/session')->addNotice(Mage::helper('Maxima_BankSlip')->__($war));
						}
					}
					
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Maxima_BankSlip')->__('File successfully processed.'));
					
					
					
					$this->_redirect('*/*/');
				}
				catch (Exception $e)
				{
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Maxima_BankSlip')->__('Error: ' . $e->getMessage()));
					$this->_redirect('*/*/');
				}
			}
		}
    }
	
	
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('maxima/bankslip/list');
	}
}