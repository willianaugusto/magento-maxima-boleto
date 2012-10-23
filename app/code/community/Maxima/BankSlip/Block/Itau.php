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
			$dadosboleto["carteira"] 			= $this->getConfigData('book'); // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157
		}
		
		return $dadosboleto;
	}
}
