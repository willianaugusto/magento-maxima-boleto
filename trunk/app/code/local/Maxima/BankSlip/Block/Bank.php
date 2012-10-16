<?php

class Maxima_BankSlip_Block_Bank extends Mage_Core_Block_Template
{
	private $_order;
	
	public function getSlipData()
	{
		$dadosboleto = array();
		$order = $this->getParentBlock()->getOrder();
		
		if($order && $order->getId())
		{
			$billRegionCode = Mage::getModel('directory/region')->load($order->getBillingAddress()->getData('region_id'))->getCode();
			$billComplement = $order->getBillingAddress()->getData('complement');
			$billComplement = ($billComplement) ? " (" . $billComplement . "), " : ", ";
			
			$supportEmail = Mage::getStoreConfig("trans_email/ident_support/email");
			
			// dados da configuracao
			$dadosboleto["identificacao"] 		= $this->getConfigData('company_name');
			$dadosboleto["cpf_cnpj"] 			= $this->getConfigData('company_registration');
			$dadosboleto["endereco"] 			= $this->getConfigData('company_address');
			$dadosboleto["cidade_uf"] 			= $this->getConfigData('company_city_state');
			$dadosboleto["cedente"] 			= $this->getConfigData('company_official_name');
			$dias_de_prazo_para_pagamento 		= $this->getConfigData('payment_term');
			$taxa_boleto 						= $this->getConfigData('slip_cost');
			
			$data_venc 							= date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
			$valor_cobrado 						= $order->getGrandTotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
			$valor_cobrado 						= str_replace(",", ".",$valor_cobrado);
			$valor_boleto 						= number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

			$dadosboleto["nosso_numero"] 		= $this->_formatSlipNumber($order->getId());
			$dadosboleto["numero_documento"] 	= $order->getIncrementId();	// Num do pedido ou do documento
			$dadosboleto["data_vencimento"] 	= $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
			$dadosboleto["data_documento"] 		= $this->_formatDate($order->getCreatedAt()); // Data de emissão do Boleto
			$dadosboleto["data_processamento"] 	= $this->_formatDate($order->getCreatedAt()); // Data de processamento do boleto (opcional)
			$dadosboleto["valor_boleto"] 		= $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

			// DADOS DO SEU CLIENTE
			$dadosboleto["sacado"] 				= $order->getCustomerName();
			$dadosboleto["endereco1"] 			= $order->getBillingAddress()->getData('street') . " - " . 
												  $order->getBillingAddress()->getData('number') . 
												  $billComplement . 
												  $order->getBillingAddress()->getData('district');
												  
			$dadosboleto["endereco2"] 			= $order->getBillingAddress()->getData('city') . " - " . 
												  $billRegionCode . " - CEP " . 
												  $order->getBillingAddress()->getData('postcode');

			// INFORMACOES PARA O CLIENTE
			$dadosboleto["demonstrativo1"] 		= $this->__("Payment of an order realized in ") . Mage::getBaseUrl();
			$dadosboleto["demonstrativo2"] 		= $this->__("Slip tax - ") . Mage::helper('core')->currency($taxa_boleto, true, false);

			// INSTRUÇÕES PARA O CAIXA
			$dadosboleto["instrucoes1"] 		= $this->__("- Mr Bank Teller, please charge late payment penalty of 2% of the value after the expiration date");
			$dadosboleto["instrucoes2"] 		= $this->__("- Recieve at most 10 days after the expiration date");
			$dadosboleto["instrucoes3"] 		= $supportEmail ? $this->__("- Questions, ask ")  . $supportEmail : "";
			$dadosboleto["instrucoes4"] 		= "";

			// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
			$dadosboleto["quantidade"] 			= "1";
			$dadosboleto["valor_unitario"] 		= $valor_cobrado;
			$dadosboleto["aceite"] 				= "N";		
			$dadosboleto["especie"] 			= Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() ;
			$dadosboleto["especie_doc"] 		= "DM";
			
			
			// DADOS DA SUA CONTA - DADOS DA CONTA
			$dadosboleto["agencia"] 			= $this->getConfigData('agency'); // Num da agencia, sem digito
			$dadosboleto["conta"] 				= $this->getConfigData('account'); 	// Num da conta, sem digito
			$dadosboleto["conta_dv"] 			= $this->getConfigData('account_vd'); 	// Digito Verificador
		}
		
		return $dadosboleto;
	}
	
	
	/**
	 * 
	 * Pega os valores da configuracao do modulo
	 * 
	 */
	
	public function getConfigData($config)
	{
		return Mage::getStoreConfig('payment/Maxima_BankSlip/' . $config);
	}
	
	
	/**
	 * 
	 * Formata id da compra para nosso numero
	 * 
	 */
	
	private function _formatSlipNumber($orderId)
	{
		while(strlen($orderId) < 5)
		{
			$orderId = "0" . $orderId;
		}
		
		return $orderId;
	}
	
	
	/**
	 * 
	 * Formata data, tomando o formato de data do Magento
	 * 
	 */
	
	private function _formatDate($date)
	{
		return (substr($date, 8, 2) . "/" . substr($date, 5, 2) . "/" . substr($date, 0, 4));
	}
}
 
