<?php

namespace TM\AndroidServices\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderChecks extends AbstractDb
{


    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('order_checks_app', 'check_id');
    }


}