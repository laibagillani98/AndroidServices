<?php
namespace TM\AndroidServices\Model\ResourceModel;

/**
 * Class TabletMessages
 */
class TabletMessages extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init('tablet_messages', 'message_id');
    }
}