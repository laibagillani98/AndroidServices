<?php
namespace TM\AndroidServices\Model\ResourceModel;

/**
 * Class TabletLoginHistory
 */
class TabletLoginHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init('login_order_history', 'log_id');
    }
}