<?php
namespace TM\AndroidServices\Model\ResourceModel\FuturePallet;

use TM\AndroidServices\Model\FuturePallet as Model;
use TM\AndroidServices\Model\ResourceModel\FuturePallet as ResourceModel;

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