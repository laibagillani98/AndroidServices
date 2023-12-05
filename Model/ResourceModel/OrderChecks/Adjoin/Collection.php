<?php
namespace TM\AndroidServices\Model\ResourceModel\OrderChecks\Adjoin;

use Magento\Sales\Model\Order as Model;
use TM\AndroidServices\Model\ResourceModel\OrderChecking as ResourceModel;

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

    public function getOrders()
	{
	    $this->order_checking_app = "main_table";
	    $this->sales_order_table = $this->getTable("sales_order");
          $this->getSelect()
            ->join(array('order' =>$this->sales_order_table), $this->order_checking_app . '.order_no= order.increment_id',
                array('orderstatus' => 'order.status','order.store_id','checkstatus' => 'main_table.status'
                )
            );
	    return $this;

	}
    public function getCount($store_id)
	{
        $ProblemCollection = $this->getOrders();
        $ProblemCollection->addFieldToFilter("store_id",$store_id); 
        $ProblemCollection->addFieldToFilter("main_table.status",2); 
        $ProblemCollection->addFieldToFilter("order.status","showroom_problem"); 
        $recordCount = $ProblemCollection->getSize();
	    return $recordCount;
	}

	
}