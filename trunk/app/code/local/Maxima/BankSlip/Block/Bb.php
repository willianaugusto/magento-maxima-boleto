<?php

/*
#################################################
	DESENVOLVIDO PARA CARTEIRA 18

	- Carteira 18 com Convenio de 8 digitos
	Nosso número: pode ser até 9 dígitos

	- Carteira 18 com Convenio de 7 digitos
	Nosso número: pode ser até 10 dígitos

	- Carteira 18 com Convenio de 6 digitos
	Nosso número:
	de 1 a 99999 para opcao de até 5 dígitos
	de 1 a 99999999999999999 para opcao de até 17 dígitos

#################################################
*/

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
			$dadosboleto["convenio"] 			= $this->getConfigData('agreement');  // Num do convênio - REGRA: 6 ou 7 ou 8 dígitos
			$dadosboleto["contrato"] 			= $this->getConfigData('contract'); // Num do seu contrato
			$dadosboleto["carteira"] 			= $this->getConfigData('book');
			$dadosboleto["variacao_carteira"] 	= $this->getConfigData('book_variation');  // Variação da Carteira, com traço (opcional)

			// TIPO DO BOLETO
			$dadosboleto["formatacao_convenio"] 	= "7"; // REGRA: 8 p/ Convênio c/ 8 dígitos, 7 p/ Convênio c/ 7 dígitos, ou 6 se Convênio c/ 6 dígitos
			$dadosboleto["formatacao_nosso_numero"] = "2"; // REGRA: Usado apenas p/ Convênio c/ 6 dígitos: informe 1 se for NossoNúmero de até 5 dígitos ou 2 para opção de até 17 dígitos
		}
		
		return $dadosboleto;
	}
}
