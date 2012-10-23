<?php

class Maxima_BankSlip_Model_Cnab400
{
	public $fileName;
	
	private $_data;
	private $_transactions;
	
	private $_file;
	private $_readConn;
	private $_writeConn;
	
	private $_agNumber;
	private $_accNumber;
	private $_bankNumber;
	
	private $_warnings;
	
	public function __construct($params)
	{
		$this->_warnings = array();
		
		$this->_readConn = $params["readConn"];
		$this->_writeConn = $params["writeConn"];
		
		$this->_file = $params["file"];
		$this->_data = array();
		$this->_transactions = array();
		
		// pega dados da configuracao padrao
		// note que somente sao processados arquivos cnab da configuracao atual
		$bank = Mage::helper('Maxima_BankSlip')->getConfig('bank');
		
		$this->_bankNumber = Mage::getModel('Maxima_BankSlip/bank')->getNumber($bank);
		$this->_agNumber = Mage::helper('Maxima_BankSlip')->getConfig('agency');
		$this->_accNumber = Mage::helper('Maxima_BankSlip')->getConfig('account');
	}
	
	
	public function analyse()
	{
		$this->_verifyLineTypes();
		$this->_verifyHeader();
		$this->_verifyTrailer();
		
		$this->_proccessLines();
	}
	
	
	private function _verifyLineTypes()
	{
		$len = count($this->_file);
		
		if($len < 2)
		{
			throw new Exception("File does not have enough lines.");
		}
		
		// primeira linha = cabecalho
		if(substr($this->_file[0], 0, 1) != "0")
		{
			throw new Exception("First line has an incorrect format.");
		}
		
		// demais linhas = detalhe
		for($i = 1; $i < ($len - 1); $i++)
		{
			if(substr($this->_file[$i], 0, 1) != "1")
			{
				throw new Exception("Intermediary line ($i) has an incorrect format.");
			}
		}
		
		// ultima linha = trailer
		if(substr($this->_file[$len - 1], 0, 1) != "9")
		{
			throw new Exception("Last line has an incorrect format.");
		}
		
		return true;
	}
	
	
	private function _verifyHeader()
	{
		// confere se o arquivo é do tipo de retorno
		if(substr($this->_file[0], 1, 1) != "2")
		{
			throw new Exception("File type incorrect (required 'Return' type).");
		}
		
		// confere o tipo de serviço
		if(substr($this->_file[0], 9, 2) != "01")
		{
			throw new Exception("Incorrect service type.");
		}
		
		// confere os números da agência e da conta
		$len = strlen($this->_agNumber . $this->_accNumber);
		$zeros = "";
		
		while($len < 12)
		{
			$zeros .= "0";
			$len++;
		}
		
		$idNumber = $this->_agNumber . $zeros . $this->_accNumber;
		
		if(substr($this->_file[0], 26, 12) != $idNumber)
		{
			throw new Exception("Incorrect agency / account number .");
		}
		
		// confere o código do banco
		if(substr($this->_file[0], 76, 3) != $this->_bankNumber)
		{
			throw new Exception("Incorrect bank number.");
		}
		
		// coleta da data de processamento
		$this->data['date'] = substr($this->_file[0], 94, 6);
	}
	
	private function _verifyTrailer()
	{
		$lastPos = count($this->_file) - 1;
		
		// confere se o arquivo é do tipo de retorno
		if(substr($this->_file[$lastPos], 1, 1) != "2")
		{
			throw new Exception("File type incorrect (required 'Return' type).");
			return;
		}
		
		// confere o tipo de serviço
		if(substr($this->_file[$lastPos], 2, 2) != "01")
		{
		
		
			throw new Exception("Incorrect service type.");
			return;
		}
		
		// confere o código do banco
		if(substr($this->_file[$lastPos], 4, 3) != $this->_bankNumber)
		{
			throw new Exception("Incorrect bank number.");
			return;
		}
	}
	
	private function _proccessLines()
	{
		$len = count($this->_file);
		
		// processa linhas
		for($i = 1; $i < ($len - 1); $i++)
		{
			$transaction = array();
			
			$transaction['credit_date'] = substr($this->_file[$i], 110, 6);
			$transaction['doc_number'] = substr($this->_file[$i], 126, 9);
			$transaction['maturity_date'] = substr($this->_file[$i], 146, 6);
			$transaction['value'] = substr($this->_file[$i], 152, 13);
			$transaction['iof'] = substr($this->_file[$i], 214, 11);
			$transaction['tax'] = substr($this->_file[$i], 175, 13);
			$transaction['rebate'] = substr($this->_file[$i], 227, 13);
			$transaction['interest'] = substr($this->_file[$i], 266, 13);
			$transaction['value_paid'] = substr($this->_file[$i], 253, 13);
			
			
			// Banco do Brasil
			if($this->_bankNumber == "001")
			{
				$transaction['doc_number'] = substr($this->_file[$i], 62, 9);
			}
			// Santander
			if($this->_bankNumber == "033")
			{
				$transaction['credit_date'] = substr($this->_file[$i], 295, 6);
				//$transaction['doc_number'] = substr($this->_file[$i], 62, 8);
				
				// !!! conferir ao certo o caso do nosso numero
			}
			// itau
			if($this->_bankNumber == "341")
			{
				$transaction['doc_number'] = substr($this->_file[$i], 62, 8);
			}
			
			$this->_transactions[] = $transaction;
		}
	}
	
	
	public function registerTransactions($fileId)
	{
		foreach($this->_transactions as $transaction)
		{
			// pega a instancia do referido pedido
			$order = Mage::getModel('sales/order')->load(intval($transaction['doc_number']));
			if(!$order || !$order->getId())
			{
				$this->_warnings[] = "Cannot find the order for the bank slip number " . $transaction['doc_number'] . ".";
				continue;
			}
			
			// pega a instancia do boleto
			$slip = Mage::getModel('Maxima_BankSlip/slip')->loadByOrder($order);
			if(!$slip || !$slip->getId())
			{
				$this->_warnings[] = "The bank slip number " . $transaction['doc_number'] . " could not be found.";
				continue;
			}
			
			// confere se jah foi submetido
			if($slip->getStatus() == 'P')
			{
				$this->_warnings[] = "The bank slip number " . $transaction['doc_number'] . " has already been processed.";
				continue;
			}
			
			// converte valor para ponto flutuante
			$slipValue = floatval(substr($transaction['value_paid'], 0, 11) . "." . substr($transaction['value_paid'], 11, 2));
			
			// confere se o valor pago coincide com  valor da compra
			if($slipValue < $order->getData('grand_total'))
			{
				$this->_warnings[] = "The bank slip number " . $transaction['doc_number'] . " has a paid value small then the order value and was not processed.";
				continue;
			}
			
			// cria fatura no magento
			if($order->canInvoice() && !$order->hasInvoices())
			{
				$invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
				$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
				
				// envia email de confirmacao de fatura
				$invoice->sendEmail(true);
				$invoice->setEmailSent(true);
				$invoice->save();
			}
			
			// insere registro no banco
			$slip->setStatus("P");
			$slip->setReturnFile($fileId);
			$slip->save();
			
			// inclui o codigo do plano de pagamento para esta compra
			$sql = "SELECT
						p.id
					FROM
						maxima_integration_billing_code c, maxima_integration_billing_plan p
					WHERE
						c.mage_billing_method = 'Maxima_BankSlip' AND
						c.code = p.billing_code";
			
			$values = $this->_readConn->fetchRow($sql);
			
			if($values && count($values) == 1)
			{
				$payment = $order->getPayment();
				$payment->setAdditionalInformation('codplpag', $values['id']);
				$payment->save();
			}
		}
		
	}
	
	public function getWarnings()
	{
		return $this->_warnings;
	}
} 
