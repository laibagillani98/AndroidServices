<?php
namespace TM\AndroidServices\Model\ResourceModel\ReturnOrder;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'return_id';
	protected $_eventPrefix = 'returnrecord_app_collection';
	protected $_eventObject = 'returnrecord_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('TM\AndroidServices\Model\ReturnOrder', 'TM\AndroidServices\Model\ResourceModel\ReturnOrder');
	}
}