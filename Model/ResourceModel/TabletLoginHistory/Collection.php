<?php
namespace TM\AndroidServices\Model\ResourceModel\TabletLoginHistory;

use TM\AndroidServices\Model\TabletLoginHistory as Model;
use TM\AndroidServices\Model\ResourceModel\TabletLoginHistory as ResourceModel;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}