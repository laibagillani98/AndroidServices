<?php
namespace TM\AndroidServices\Model\ResourceModel;

/**
 * Class FuturePallet
 */
class FuturePallet extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init('order_processing_futurepallet', 'future_pallet_id');
    }
}