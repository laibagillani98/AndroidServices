<?php
namespace TM\AndroidServices\Model;
class ReturnOrder extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'returnrecord_app';

	protected $_cacheTag = 'returnrecord_app';

	protected $_eventPrefix = 'returnrecord_app';

	protected function _construct()
	{ 
		$this->_init('TM\AndroidServices\Model\ResourceModel\ReturnOrder');
	}
    public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];
		return $values;
	}
}