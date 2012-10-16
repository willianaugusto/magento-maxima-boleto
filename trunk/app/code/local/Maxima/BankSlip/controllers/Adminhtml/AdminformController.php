<?php

class Maxima_BankSlip_Adminhtml_AdminformController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
		$this->loadLayout();
		//$this->_setActiveMenu('maxima/form');
		//$this->_addBreadcrumb(Mage::helper('Maxima_BankSlip')->__('Form'), Mage::helper('Maxima_BankSlip')->__('Form'));
		$this->renderLayout();
	}
	
	public function saveAction()
	{
		if ($data = $this->getRequest()->getPost())
		{
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '')
			{
				try
				{
					$uploader = new Varien_File_Uploader('filename');
					$uploader->save(Mage::getBaseDir('tmp'), Mage::getSingleton('admin/session')->getSessionId());
					
					$resource = Mage::getSingleton('core/resource');
					$readConn = $resource->getConnection('core_read');
					$writeConn = $resource->getConnection('core_write');
					
					// salva arquivo em diretorio temporario
					$uploadedFileName =  $_FILES['filename']['name'];
					$newFileName = Mage::getBaseDir('tmp') . "/" . Mage::getSingleton('admin/session')->getSessionId();
					
					// le conteudo do arquivo
					$fileContent = file_get_contents($newFileName);
					$file = file($newFileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
					
					// descobre o formato do arquivo
					if(!is_array($file) && count($file) == 0)
					{
						throw new Exception("Arquivo não se encontra nos formatos permitidos.");
					}
					
					$pattern = strlen($file[0]);
					
					if($pattern !== 240 && $pattern != 400)
					{
						throw new Exception("Arquivo não se encontra nos formatos permitidos.");
					}
					
					// insere registro no banco de dados
					$writeConn->exec
					(
						"INSERT INTO maxima_billet_file 
							(name, type, date, content) 
						VALUES 
							('" . $uploadedFileName . "', '" . $pattern . "', " . time() . ", '" . $fileContent . "')"
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
							'bankNumber' => $data['filebank'],
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
					
					Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('Maxima_BankSlip')->__('Arquivo processado com sucesso.'));
					
					
					
					$this->_redirect('*/*/');
				}
				catch (Exception $e)
				{
					Mage::getSingleton('adminhtml/session')->addError(Mage::helper('Maxima_BankSlip')->__('Erro ao tentar processar arquivo: ' . $e->getMessage()));
					$this->_redirect('*/*/');
				}
			}
		}
    }
	
	protected function _isAllowed()
	{
		return Mage::getSingleton('admin/session')->isAllowed('maxima/form');
	}
}