<?php
namespace TM\AndroidServices\Model\ResourceModel;

/**
 * Class SkuHistory
 */
class SkuHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init('picked_problem_broken_sku_table', 'record_id');
    }
}