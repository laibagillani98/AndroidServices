<?php

namespace TM\AndroidServices\Model;

use \Magento\Framework\Model\AbstractModel;

class OrderChecks extends AbstractModel
{


    /**
     * Initialize resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init('TM\AndroidServices\Model\ResourceModel\OrderChecks');
    }


}