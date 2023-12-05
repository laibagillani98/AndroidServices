<?php
namespace TM\AndroidServices\Model\ResourceModel;
class ReturnOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('return_record_app', 'return_id');
	}
	
}