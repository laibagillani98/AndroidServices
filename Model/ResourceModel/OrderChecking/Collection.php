<?php

namespace TM\AndroidServices\Model\ResourceModel\OrderChecking;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('TM\AndroidServices\Model\OrderChecking', 'TM\AndroidServices\Model\ResourceModel\OrderChecking');
    }
}