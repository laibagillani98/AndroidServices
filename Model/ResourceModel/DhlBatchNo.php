<?php

namespace TM\AndroidServices\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DhlBatchNo extends AbstractDb
{


    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('dhl_batch_no', 'batch_id');
    }


}