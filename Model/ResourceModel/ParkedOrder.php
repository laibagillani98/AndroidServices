<?php
namespace TM\AndroidServices\Model\ResourceModel;

/**
 * Class ParkedOrder
 */
class ParkedOrder extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init('tablet_parked_order', 'park_id');
    }
}