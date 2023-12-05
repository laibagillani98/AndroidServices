<?php
namespace TM\AndroidServices\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\App\ResourceConnection;

class SalesItems extends AbstractModel
{
    protected $resourceConnection;

    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->resourceConnection = $resourceConnection;
    }

    public function updatePickCount($order_id, $sku, $pick_count)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('sales_order_item');

        $data = ['pick_count' => $pick_count];
        $where = ['order_id = ?' => $order_id, 'sku = ?' => $sku];

        try {
            $connection->update($tableName, $data, $where);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error inserting data into the sales_order_item table: %1', $e->getMessage()));
        }
    }
    public function updateCaliber($order_id, $skus, $caliber_check)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('sales_order_item');

        $data = ['caliber_check' => $caliber_check];
        $where = ['order_id = ?' => $order_id, 'sku IN (?)' => $skus];

        try {
            $connection->update($tableName, $data, $where);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error inserting data into the sales_order_item table: %1', $e->getMessage()));
        }
    }
 
 
}
