<?php

	class Maxima_BankSlip_Model_Bank
	{
		
		public function toOptionArray()
		{
			return array
			(
				array
				(
					"value"		=> "bb",
					"label"		=> "Banco do Brasil",
					"number"	=> "001"
				),
				array
				(
					"value"		=> "itau",
					"label"		=> "Itaú",
					"number"	=> "341"
				)
			);
		}
		
		public function toArray()
		{
			return array
			(
				"bb"	=> "Banco do Brasil",
				"itau"	=> "Itaú"
			);
		}
		
		public function getNumber($bankValue)
		{
			foreach($this->toOptionArray() as $bank)
			{
				if($bank['value'] == $bankValue)
				{
					return $bank['number'];
				}
			}
			
			return '';
		}
	}
