<?php

namespace TM\AndroidServices\Model\ResourceModel\OtherChecks;

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
        $this->_init('TM\AndroidServices\Model\OtherChecks', 'TM\AndroidServices\Model\ResourceModel\OtherChecks');
    }
}