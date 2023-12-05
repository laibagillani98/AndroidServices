<?php

namespace TM\AndroidServices\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class MbPalletNumbers extends AbstractDb
{


    /**
     * Initialize resource
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('mb_pallet_numbers', 'pallet_id');
    }


}