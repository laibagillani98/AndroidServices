<?php
namespace TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin;

use Magento\Sales\Model\Order as Model;
use TM\PalletQueue\Model\ResourceModel\OrderInvoice as ResourceModel;

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
	    $this->pallet_table = "main_table";
	    $this->sales_order_table = $this->getTable("sales_order");
        $this->getSelect()
            ->join(array('order' =>$this->sales_order_table), $this->pallet_table . '.op_order_id= order.entity_id',
                array('order.status','weight' => 'order.weight','order.customer_firstname','customer_lastname','delivery_note',
                    'order.dispatch_date','order.increment_id','order.created_at','order.shipping_date','order.shipping_description',
                    'order.easywms_priority', 'order.is_checked', 'order.store_id','order.is_wood_panel','order.warehouse'
                )
            );
	    return $this;
	    //die($this->getSelect()." vvv");    
	    //$this->getSelect()->where("payment_method=".$payment_method);
	}

	public function getJoinOrderById($order_id){
		$collection = $this->getOrders();
		$collection->addFieldToFilter("op_order_id",$order_id);
		return $collection;
	}

	public function getJoinOrderbyNo($order_no){
		$collection = $this->getOrders();
		if ($order_no) {
			$collection->addFieldToFilter("order.increment_id",$order_no);
		}
		
		return $collection;
	}

	public function getCompletedOrders($order_no,$pageno = 0,$user = "",$shop_order = 0){
		$this->login_order_history = $this->getTable("login_order_history");
		$collection = $this->getOrders();
		$collection->getSelect()
		->join(array('history' =>$this->login_order_history), $this->pallet_table . '.op_order_id= history.tab_order_id',
	        array('history.tab_order_id','history.tab_order_status','history.start_time','history.end_time')
	    )->where(
            'history.tab_order_status=?',
            0
        );

	    $collection->addFieldToFilter("in_queue",array("eq" => 0));
		$collection->addFieldToFilter("history.end_time",array("neq" =>"null"));

		if ($user != "") {
			$user = strtolower($user);
        	$collection->addFieldToFilter('main_table.tablet_user',array("eq" => $user));
		}
		
		if ($order_no) {
			$collection->addFieldToFilter('order.increment_id',array("like" => "%".$order_no."%"));
		}
		if ($shop_order) {
			// $collection->addFieldToFilter('is_shop_order', 1);
        }
		$collection->setOrder('history.end_time', 'Desc');
		if ($pageno) {
			$collection->setPageSize(50)->setCurPage($pageno);
		}
		
		//$collection->load();

		return $collection;
	}
}