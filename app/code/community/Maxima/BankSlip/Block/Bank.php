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
			
			$data_venc 							= date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));
			$valor_cobrado 						= $order->getGrandTotal();
			$valor_cobrado 						= str_replace(",", ".",$valor_cobrado);
			$valor_boleto 						= number_format($valor_cobrado, 2, ',', '');

			$dadosboleto["nosso_numero"] 		= $this->_formatSlipNumber($order->getId());
			$dadosboleto["numero_documento"] 	= $order->getIncrementId();
			$dadosboleto["data_vencimento"] 	= $data_venc;
			$dadosboleto["data_documento"] 		= $this->_formatDate($order->getCreatedAt());
			$dadosboleto["data_processamento"] 	= $this->_formatDate($order->getCreatedAt());
			$dadosboleto["valor_boleto"] 		= $valor_boleto;

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
			$dadosboleto["demonstrativo1"] 		= $this->__("Pagamento por compra realizada em ") . Mage::getBaseUrl();
			$dadosboleto["demonstrativo2"] 		= "";

			// INSTRUÇÕES PARA O CAIXA
			$dadosboleto["instrucoes1"] 		= $this->__("- Sr Caixa, por favor cobre 2% do valor total como penalidade por atraso após a data de vencimento");
			$dadosboleto["instrucoes2"] 		= $this->__("- Aceitar no máximo até 10 dias após o vencimento");
			$dadosboleto["instrucoes3"] 		= $supportEmail ? $this->__("- Em caso de dúvidas, envie e-mail para ")  . $supportEmail : "";
			$dadosboleto["instrucoes4"] 		= "";

			// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
			$dadosboleto["quantidade"] 			= "1";
			$dadosboleto["valor_unitario"] 		= $valor_cobrado;
			$dadosboleto["aceite"] 				= "N";		
			$dadosboleto["especie"] 			= Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())->getSymbol() ;
			$dadosboleto["especie_doc"] 		= "DM";
			
			// DADOS DA SUA CONTA - DADOS DA CONTA
			$dadosboleto["agencia"] 			= $this->getConfigData('agency');
			$dadosboleto["conta"] 				= $this->getConfigData('account');
			$dadosboleto["conta_dv"] 			= $this->getConfigData('account_vd');
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
	
	protected function _formatSlipNumber($orderId)
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
	
	protected function _formatDate($date)
	{
		return (substr($date, 8, 2) . "/" . substr($date, 5, 2) . "/" . substr($date, 0, 4));
	}
	
	/**
	 * 
	 * Formata numeros utilizados no boleto, preenchendo com o caractere
	 * determinado por $insertValue, deixando o numero com o tamanho determinado
	 * pelo segund parametro
	 * 
	 */
	protected function _formatNumber($number, $size, $insertValue, $type = "default")
	{
		// para cada caso, retira a virgula e insere
		// o valor na posicao respectiva ao formato
		
		// padrao
		if ($type == "default")
		{
			$number = str_replace(",","",$number);
			
			while(strlen($number) < $size)
			{
				$number = $insertValue . $number;
			}
		}
		
		// valor
		if ($type == "value")
		{
			$number = str_replace(",","",$number);
			
			while(strlen($number) < $size)
			{
				$number = $insertValue . $number;
			}
		}
		
		// convenio
		if ($type == "agreement")
		{
			while(strlen($number)<$size)
			{
				$number = $number . $insertValue;
			}
		}
		
		return $number;
	}
	
	/**
	 * 
	 * Gera codigo de barras em HTML, utilizando imagens do modulo
	 * 
	 */
	protected function _generateBarCode($value)
	{
		$thin = 1 ;
		$large = 3 ;
		$height = 50 ;
		
		$barCodes = array();
		$barCodes[0] = "00110" ;
		$barCodes[1] = "10001" ;
		$barCodes[2] = "01001" ;
		$barCodes[3] = "11000" ;
		$barCodes[4] = "00101" ;
		$barCodes[5] = "10100" ;
		$barCodes[6] = "01100" ;
		$barCodes[7] = "00011" ;
		$barCodes[8] = "10010" ;
		$barCodes[9] = "01010" ;
		
		
		for($f1 = 9; $f1 >= 0; $f1--)
		{
			for($f2 = 9; $f2 >= 0; $f2--)
			{
				$f = ($f1 * 10) + $f2;
				$text = "";
				
				for($i = 1; $i < 6; $i++)
				{
					$text .=  substr($barCodes[$f1], ($i - 1), 1) . substr($barCodes[$f2], ($i - 1), 1);
				}
				
				$barCodes[$f] = $text;
			}
		}


		// imagens
		// guarda inicial
		$imagesText  = "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
					   "skin/frontend/default/default/images/maxima/bankslip/p.png' width='" . 
					   $thin . "' height='" . $height . "' border='0'>";
					   
		$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
					   "skin/frontend/default/default/images/maxima/bankslip/b.png' width='" . 
					   $thin . "' height='" . $height . "' border='0'>";
		
		
		$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) .
					   "skin/frontend/default/default/images/maxima/bankslip/p.png' width='" . 
					   $thin . "' height='" . $height . "' border='0'>";
					   
		$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
					   "skin/frontend/default/default/images/maxima/bankslip/b.png' width=" . 
					   $thin . "' height='" . $height . "' border='0'>";
		
		$text = $value;
		if((strlen($text) % 2) <> 0)
		{
			$text = "0" . $text;
		}

		// codigo de barra dos dados
		while(strlen($text) > 0)
		{
			$i = round($this->_leftSubstring($text,2));
			$text = $this->_rightSubstring($text,strlen($text)-2);
			$f = $barCodes[$i];
			for($i=1;$i<11;$i+=2)
			{
				if (substr($f,($i-1),1) == "0")
				{
					$f1 = $thin ;
				}
				else
				{
					$f1 = $large ;
				}
				
				$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
							   "skin/frontend/default/default/images/maxima/bankslip/p.png' width='" . 
							   $f1 . "' height='" . $height . "' border='0'>";
				
				
				if (substr($f,$i,1) == "0")
				{
					$f2 = $thin ;
				}
				else
				{
					$f2 = $large ;
				}
				
				$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
							   "skin/frontend/default/default/images/maxima/bankslip/b.png' width='" . 
							   $f2 . "' height='" . $height . "' border='0'>";
			}
		}

		// guarda final
		$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
							   "skin/frontend/default/default/images/maxima/bankslip/p.png' width='" . 
							   $large . "' height='" . $height . "' border='0'>";
		
		$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
							   "skin/frontend/default/default/images/maxima/bankslip/b.png' width='" . 
							   $thin . "' height='" . $height . "' border='0'>";
		
		$imagesText .= "<img src='" . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 
							   "skin/frontend/default/default/images/maxima/bankslip/p.png' width='1' " . 
							   "height='" . $height . "' border='0'>";
		
		return $imagesText;
	}
	
	/**
	 * 
	 * Gera a linha digitavel do boleto
	 * 
	 */
	protected function _generateBankNumber($number)
	{
		$part1 = substr($number, 0, 3);
		$part2 = $this->_module_11($number);
		
		return $part1 . "-" . $part2;
	}
	
	/**
	 * 
	 * Retorna parte da string, iniciando da esquerda
	 * 
	 */
	protected function _leftSubstring($str, $size)
	{
		return substr($str, 0, $size);
	}
	
	/**
	 * 
	 * Retorna parte da string, iniciando da direita
	 * 
	 */
	protected function _rightSubstring($str, $size)
	{
		return substr($str, strlen($str) - $size, $size);
	}
	
	/**
	 * 
	 * Retorna o fator de vencimento
	 * 
	 */
	protected function _maturityFactor($date)
	{
		$date = explode("/", $date);
		$year = $date[2];
		$month = $date[1];
		$day = $date[0];
		return(abs(($this->_dateToDays("1997","10","07")) - ($this->_dateToDays($year, $month, $day))));
	}
	
	/**
	 * 
	 * Contabiliza o numero de dias, dada uma data
	 * 
	 */
	protected function _dateToDays($year, $month, $day)
	{
		$century = substr($year, 0, 2);
		$year = substr($year, 2, 2);
		
		if ($month > 2)
		{
			$month -= 3;
		}
		else
		{
			$month += 9;
			if ($year)
			{
				$year--;
			}
			else
			{
				$year = 99;
				$century --;
			}
		}

		return (floor((146097 * $century) / 4) +
				floor((1461 * $year) / 4) +
				floor((153 * $month + 2) / 5) +
				$day + 1721119);
	}
}
 
