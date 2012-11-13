<?php

class Maxima_BankSlip_Block_Bb extends Maxima_BankSlip_Block_Bank
{
	private $_order;
	
	public function getSlipData()
	{
		$dadosboleto = array();
		$order = $this->getParentBlock()->getOrder();
		
		if($order && $order->getId())
		{
			$dadosboleto = parent::getSlipData();

			// DADOS PERSONALIZADOS - BANCO DO BRASIL
			$dadosboleto["convenio"] 			= $this->getConfigData('agreement');
			$dadosboleto["contrato"] 			= $this->getConfigData('contract');
			$dadosboleto["carteira"] 			= $this->getConfigData('book');
			$dadosboleto["variacao_carteira"] 	= $this->getConfigData('book_variation');

			// TIPO DO BOLETO
			$dadosboleto["formatacao_convenio"] 	= "7";
			$dadosboleto["formatacao_nosso_numero"] = "2";
		}
		
		return $dadosboleto;
	}
	
	/**
	 * 
	 * Gera a linha digitavel do boleto
	 * 
	 */
	protected function _generateBarCodeNumber($line)
	{
		// Posição 	Conteudo
		// 1 a 3    Numero do banco
		// 4        Codigo da Moeda - 9 para Real
		// 5        Digito verificador do Codigo de Barras
		// 6 a 19   Valor (12 inteiros e 2 decimais)
		// 20 a 44  Campo Livre definido por cada banco

		// 1. Campo - composto pelo codigo do banco + codigo da moeda = as cinco primeiras posicoes
		// do campo livre e DV (modulo10) deste campo
		$p1 = substr($line, 0, 4);
		$p2 = substr($line, 19, 5);
		$p3 = $this->_module_10("$p1$p2");
		$p4 = "$p1$p2$p3";
		$p5 = substr($p4, 0, 5);
		$p6 = substr($p4, 5);
		$firstField = "$p5.$p6";

		// 2. Campo - composto pelas posiçoes 6 a 15 do campo livre
		// e DV (modulo10) deste campo
		$p1 = substr($line, 24, 10);
		$p2 = $this->_module_10($p1);
		$p3 = "$p1$p2";
		$p4 = substr($p3, 0, 5);
		$p5 = substr($p3, 5);
		$secondField = "$p4.$p5";

		// 3. Campo composto pelas posicoes 16 a 25 do campo livre
		// e livre e DV (modulo10) deste campo
		$p1 = substr($line, 34, 10);
		$p2 = $this->_module_10($p1);
		$p3 = "$p1$p2";
		$p4 = substr($p3, 0, 5);
		$p5 = substr($p3, 5);
		$thirdField = "$p4.$p5";

		// 4. Campo - digito verificador do codigo de barras
		$fourthField = substr($line, 4, 1);

		// 5. Campo composto pelo valor nominal pelo valor nominal do documento, sem
		// indicacao de zeros a esquerda e sem edicao (sem ponto e virgula). Quando se
		// tratar de valor zerado, a representacao deve ser 000 (tres zeros).
		$fifthField = substr($line, 5, 14);

		return "$firstField $secondField $thirdField $fourthField $fifthField"; 
	}
	
	/**
	 * 
	 * Utilizada para geracao do digito verificador
	 * 
	 */
	protected function _module_10($num)
	{ 
		$total = 0;
		$fator = 2;
		$numbers = array();
		
		for ($i = strlen($num); $i > 0; $i--)
		{
			$numbers[$i] = substr($num,$i-1,1);
			$temp[$i] = $numbers[$i] * $fator;
			$total .= $temp[$i];
			if ($fator == 2)
			{
				$fator = 1;
			}
			else
			{
				$fator = 2; 
			}
		}
		
		$sum = 0;
		for ($i = strlen($total); $i > 0; $i--)
		{
			$numbers[$i] = substr($total, $i-1, 1);
			$sum += $numbers[$i]; 
		}
		
		$rest = $sum % 10;
		$digit = 10 - $rest;
		if ($rest == 0)
		{
			$digit = 0;
		}

		return $digit;
	}
	
	/**
	 * 
	 * Utilizada para geracao do digito verificador
	 * 
	 */
	protected function _module_11($num, $base = 9, $r = 0)
	{
		$sum = 0;
		$factor = 2;
		$numbers = array();
		
		for ($i = strlen($num); $i > 0; $i--)
		{
			$numbers[$i] = substr($num, $i-1, 1);
			$temp[$i] = $numbers[$i] * $factor;
			$sum += $temp[$i];
			if ($factor == $base)
			{
				$factor = 1;
			}
			$factor++;
		}
		
		if ($r == 0)
		{
			$sum *= 10;
			$digit = $sum % 11;
			
			// corrigido
			if ($digit == 10)
			{
				$digit = "X";
			}

			if (strlen($num) == "43")
			{
				// entao estamos checando a linha digitavel
				if ($digit == "0" or $digit == "X" or $digit > 9)
				{
					$digit = 1;
				}
			}
			
			return $digit;
		} 
		else if ($r == 1)
		{
			return ($sum % 11);
		}
	}
}
