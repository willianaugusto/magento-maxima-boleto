<?php

class Maxima_BankSlip_Block_Adminhtml_Slip_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	public function __construct()
	{
		parent::__construct();
		$this->setId('bankslipSlipGrid');
		$this->setDefaultSort('slip_id');
		$this->setDefaultDir('ASC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel('Maxima_BankSlip/slip')->getCollection();
		
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	
	protected function _prepareColumns()
	{
		$this->addColumn('slip_id', array
		(
			'header'    => Mage::helper('Maxima_BankSlip')->__('Slip #'),
			'type'      => 'text',
			'width'     => '80px',
			'index'     => 'slip_id',
		));
		
		$this->addColumn('created_at', array(
			'header' => Mage::helper('Maxima_BankSlip')->__('Purchased On'),
			'index' => 'created_at',
			'type' => 'datetime',
			'width' => '140px',
		));
		
		$this->addColumn('increment_id', array(
			'header' => Mage::helper('Maxima_BankSlip')->__('Order'),
			'index' => 'increment_id',
			'type' => 'text',
		));
		
		$this->addColumn('bank', array(
			'header' => Mage::helper('Maxima_BankSlip')->__('Bank'),
			'index' => 'bank',
			'type'  => 'options',
			'options' => Mage::getSingleton('Maxima_BankSlip/bank')->toArray(),
		));
		
		$this->addColumn('status', array(
			'header' => Mage::helper('Maxima_BankSlip')->__('Status'),
			'index' => 'status',
			'type' => 'text',
			'type'  => 'options',
			'options' => Mage::getSingleton('Maxima_BankSlip/slip_status')->toArray(),
		));
		
		$this->addColumn('grand_total', array(
			'header' => Mage::helper('Maxima_BankSlip')->__('G.T. (Purchased)'),
			'index' => 'grand_total',
            'type'  => 'currency',
            'currency' => 'order_currency_code',
		));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('Maxima_BankSlip')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('Maxima_BankSlip')->__('XML'));
		
		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		return $this->getUrl('*/*/view', array('slip_id' => $row->getId()));
	}

}