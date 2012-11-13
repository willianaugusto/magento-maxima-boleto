<?php

class Maxima_BankSlip_Block_Itau extends Maxima_BankSlip_Block_Bank
{
	private $_order;
	
	public function getSlipData()
	{
		$dadosboleto = array();
		$order = $this->getParentBlock()->getOrder();
		
		if($order && $order->getId())
		{
			$dadosboleto = parent::getSlipData();

			// DADOS PERSONALIZADOS - ITAU
			$dadosboleto["carteira"] = $this->getConfigData('book');
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
		// campo 1
        $bank		= substr($line, 0, 3);
        $currency	= substr($line, 3, 1);
        $ccc		= substr($line, 19, 3);
		$ddnnum		= substr($line, 22, 2);
		$vDigit1	= $this->_module_10($bank . $currency . $ccc . $ddnnum);
		
		// campo 2
		$resnnum	= substr($line, 24, 6);
		$dac1		= substr($line, 30, 1);
		$dddag		= substr($line, 31, 3);
		$vDigit2	= $this->_module_10($resnnum.$dac1.$dddag);
		
		// campo 3
		$resag		= substr($line, 34, 1);
		$contadac	= substr($line, 35, 6);
		$zeros		= substr($line, 41, 3);
		$vDigit3	= $this->_module_10($resag.$contadac.$zeros);
		
		// campo 4
		$vDigit4	= substr($line, 4, 1);
		
		// campo 5
        $factor		= substr($line, 5, 4);
        $value		= substr($line, 9, 10);
		
        $field1 	= substr($bank.$currency.$ccc.$ddnnum.$vDigit1,0,5) . '.' . substr($bank.$currency.$ccc.$ddnnum.$vDigit1,5,5);
        $field2 	= substr($resnnum.$dac1.$dddag.$vDigit2,0,5) . '.' . substr($resnnum.$dac1.$dddag.$vDigit2,5,6);
        $field3 	= substr($resag.$contadac.$zeros.$vDigit3,0,5) . '.' . substr($resag.$contadac.$zeros.$vDigit3,5,6);
        $field4 	= $vDigit4;
        $field5 	= $factor . $value;
		
        return "$field1 $field2 $field3 $field4 $field5";
	}
	
	/**
	 * 
	 * Digito verificador do numero do codigo de barras
	 * 
	 */
	protected function _generateBarCodeVerificationDigit($number)
	{
		$rest = $this->_module_11($number, 9, 1);
		$digit = 11 - $rest;
		
		if ($digit == 0 || $digit == 1 || $digit == 10  || $digit == 11)
		{
			$verificationDigit = 1;
		}
		else
		{
			$verificationDigit = $digit;
		}
		
		return $verificationDigit;
	}
	
	/**
	 * 
	 * Utilizada para geracao do digito verificador
	 * 
	 */
	protected function _module_10($num)
	{
		$total = 0;
        $factor = 2;
		
		$partial = array();
		$numbers = array();
		
        for ($i = strlen($num); $i > 0; $i--)
		{
			$numbers[$i] = substr($num, $i - 1, 1);
            $temp = $numbers[$i] * $factor; 
            $temp0 = 0;
			
            foreach(preg_split('//', $temp, -1, PREG_SPLIT_NO_EMPTY) as $k => $v)
			{
				$temp0 += $v;
			}
            
			$partial[$i] = $temp0;
            $total += $partial[$i];
			
            if ($factor == 2)
			{
                $factor = 1;
            }
			else
			{
                $factor = 2;
            }
        }
		
        $rest = $total % 10;
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
		$partial = array();

		for ($i = strlen($num); $i > 0; $i--)
		{
			$numbers[$i] = substr($num, $i-1, 1);
			$partial[$i] = $numbers[$i] * $factor;
			$sum += $partial[$i];
			
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
			if ($digit == 10)
			{
				$digit = 0;
			}
			
			return $digit;
		}
		else if($r == 1)
		{
			$rest = $sum % 11;
			return $rest;
		}
	}
}
