<?php

	class Maxima_BankSlip_Model_Slip_Status
	{
		
		public function toOptionArray()
		{
			return array
			(
				array
				(
					"value"		=> "N",
					"label"		=> Mage::helper('Maxima_BankSlip')->__("New")
				),
				array
				(
					"value"		=> "P",
					"label"		=> Mage::helper('Maxima_BankSlip')->__("Payed")
				),
				array
				(
					"value"		=> "C",
					"label"		=> Mage::helper('Maxima_BankSlip')->__("Canceled")
				)
			);
		}
		
		public function toArray()
		{
			return array
			(
				"N"		=> Mage::helper('Maxima_BankSlip')->__("New"),
				"P"		=> Mage::helper('Maxima_BankSlip')->__("Payed"),
				"C"		=> Mage::helper('Maxima_BankSlip')->__("Canceled")
			);
		}
	}
