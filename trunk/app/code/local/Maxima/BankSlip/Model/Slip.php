<?php

class Maxima_BankSlip_Model_Slip extends Mage_Core_Model_Abstract
{
	/**
	 * Initialize resource mode
	 *
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->_init('Maxima_BankSlip/slip');
	}
	
	
	public function getCollection()
	{
		$collection = parent::getCollection();
		
		// adiciona os campos de nome dos enderecos de entrega e fatura
		$collection->getSelect()->joinLeft
		(
			array
			(
				'order_table' => $collection->getTable('sales/order')
			),
			"(main_table.order_id = order_table.entity_id)", 
			array
			(
				"order_table.increment_id",
				"order_table.grand_total"
			),
			null
		);
		
		return $collection;
	}
	
	
	private function _isSlipPayment($order)
	{
		// confere se a entidade de pedido estah instanciada
		if(!$order || !$order->getId())
		{
			return false;
		}
		
		// pega a entidade do pagamento
		$payment = $order->getPayment();
		if(!$payment || !$payment->getId())
		{
			return false;
		}
		
		// confere se o metodo utilizado foi boleto bancario
		if(!$payment->getMethodInstance() || $payment->getMethodInstance()->getCode() != "Maxima_BankSlip")
		{
			return false;
		}
		
		return true;
	}
	
	
	
	
	public function loadByOrder($order)
	{
		// realiza conferencia basica
		if(!$this->_isSlipPayment($order))
		{
			return $this;
		}
		
		// confere se existe instancia no banco para este pedido
		$readConn = $this->getResource()->getReadConnection();
		$result = $readConn->fetchRow("SELECT slip_id FROM maxima_bankslip_slip WHERE status in ('N', 'P') AND order_id = " . $order->getId());
		
		// em caso positivo, carrega o boleto e o retorna
		if(count($result) == 1)
		{
			return $this->load($result['slip_id']);
		}
		else
		{
			return $this;
		}
	}
	
	
	public function createNewSlip($order, $bankCode)
	{
		// confere se nao ha um boleto ativo,
		// independente de estar vencido ou nao
		$slip = $this->loadByOrder($order);
		
		if($slip->getId())
		{
			return $slip;
		}
		
		// insere nova entrada no banco e retorn a nova instancia
		$writeConn = $this->getResource()->getWriteConnection();
		
		$sql = "INSERT INTO maxima_bankslip_slip " . 
					"(order_id, created_at, bank, status) " . 
				"VALUES " .
					"(" . $order->getId() . ", NOW(), '" . $bankCode . "', 'N')";
		
		if($writeConn->exec($sql))
		{
			return $this->load($writeConn->lastInsertId());
		}
		else
		{
			return $this;
		}
	}
	
	
	public function getReturnFileInformation()
	{
		// busca inforcao no banco e retorna em forma de vetor
		$readConn = $this->getResource()->getReadConnection();
		
		$sql = "SELECT name, date, type " . 
				"FROM maxima_bankslip_file " . 
				"WHERE id = " . $this->getReturnFile();
		
		$result =  $readConn->fetchAll($sql);
		return $result[0];
	}
}
