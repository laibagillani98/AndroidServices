<?php

namespace TM\AndroidServices\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LLopChecks extends AbstractDb
{


    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('llop_checks_app', 'llop_check_id');
    }


}