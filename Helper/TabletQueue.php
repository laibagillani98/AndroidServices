<?php
namespace TM\AndroidServices\Helper;

use TM\EasyWms\Helper\NewConfig;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class TabletQueue extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $resource;
	protected $ordermodel;
	protected $scopeConfig;
    protected $orderCommentsFactory;

	const OP_QUEUE_STATUS_A = 1;
    const OP_QUEUE_STATUS_B = 3;
    const TABLET_QUEUE_STATUS_INQUEUE = 1;
    const TABLET_QUEUE_STATUS_ONSCREEN = 2;
    const TABLET_QUEUE_STATUS_COMPLETE = 0;
    const TABLET_QUEUE_STATUS_WAS_PARKED_ORDER = 5; //Orders Reported as problem back to queue for processing
    const TABLET_QUEUE_STATUS_PROBLEM_PARKED_ORDER = 4;
    const TABLET_QUEUE_STATUS_PROBLEM_NOT_PARKED = 3;
    // const TABLET_QUEUE_STATUS_FOR_PICKING = 6; //Orders with status picking

	public function __construct(
        \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory $palletCollection,
        \TM\AndroidServices\Api\SkuHistoryRepositoryInterface $skuHistoryRepository,
        \TM\AndroidServices\Api\Data\SkuHistoryInterface $skuHistoryinterface,
        \TM\PalletQueue\Model\OrderInvoiceFactory $palletModel,
        \TM\AndroidServices\Model\ParkedOrderFactory $parkedOrder,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\DataObjectFactory $objectFactory,
        \Magento\Framework\Webapi\Rest\Request $request,
        \TM\AndroidServices\Helper\Data $helper,
        \TM\Microconnect\Helper\Data $microconnectHelper,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteria,
        \Magento\Framework\App\ResourceConnection $resource,
        \TM\AndroidServices\Model\TabletLoginHistoryFactory $loginHistory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Sales\Model\OrderFactory $ordermodel,
        \TM\Base\Helper\Data $baseHelper,
        \TM\OrderComments\Model\OrderCommentsFactory $orderCommentsFactory,
        \TM\AndroidServices\Model\ReturnOrderFactory $returnOrder,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSet,
        \TM\Sampleprocessing\Model\SampleprocessingFactory $sampleprocessingFactory,
        \TM\Sampleprocessing\Model\ResourceModel\Sampleprocessing\CollectionFactory $dhlCollection,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \TM\Sampleprocessing\Block\Adminhtml\Post\Index $dhlIndex,
        \TM\AndroidServices\Model\SkuHistoryFactory $skuHistoryModel,
        // 
        \TM\EasyWms\Model\BatchOrderFactory $batchOrderFactory,
        \TM\AndroidServices\Model\ResourceModel\MbPalletNumbers\CollectionFactory $mbPalletNumbers,
        \Magento\Sales\Model\Convert\OrderFactory $convertOrderFactory,
        PsrLoggerInterface $logger,
        \TM\Python\Helper\Data $pythonHelper
     ) {
        $this->_skuHistoryRepository = $skuHistoryRepository;
        $this->_skuHistoryinterface = $skuHistoryinterface;
        $this->_palletCollection = $palletCollection;
        $this->_datetime = $datetime;
        $this->request = $request;
        $this->helper = $helper;
        $this->_palletModel = $palletModel;
        $this->_storeManager = $storeManager;
        $this->_parkedOrder = $parkedOrder;
        $this->objectFactory = $objectFactory;
        $this->_resource = $resource;
        $this->_criteria = $criteria;
        $this->_ordermodel = $ordermodel;
        $this->_loginHistory = $loginHistory;
        $this->_basehelper = $baseHelper;
        $this->_microconnectHelper = $microconnectHelper;
        $this->orderCommentsFactory = $orderCommentsFactory;
        $this->returnOrder = $returnOrder;
        $this->productFactory = $productFactory;
        $this->attributeSet = $attributeSet;
        $this->sampleprocessingFactory = $sampleprocessingFactory;
        $this->directory_list = $directory_list;
        $this->dhlCollection = $dhlCollection;
        $this->dhlIndex = $dhlIndex;
        $this->pythonHelper=$pythonHelper;
        $this->skuHistoryModel = $skuHistoryModel;
        $this->batchOrderFactory = $batchOrderFactory;
        $this->mbPalletNumbers = $mbPalletNumbers;
        $this->orderConverter = $convertOrderFactory->create();
        $this->logger = $logger;
     }

    public function PlalletQueueCollection($shop_orders,$user,$training_mode = 0,$blocation = 0,$warehouse = ''){
 
      $Collection = $this->objectFactory->create();
      $topOrderData = null;
        if($shop_orders){
          // pythoncheck
          $Collection = $this->shopOrdersCollection("",$blocation,false,$warehouse);
        }
        if(!$Collection->getSize() || !$shop_orders){
          $Collection = $this->OrdersCollection($training_mode,$user,1,$blocation,"","",false,$warehouse); //if collection order is assigned to user thn return anyways
         }
        if (!$Collection->getSize()){
          $Collection->clear();
          $Collection = $this->OrdersCollection($training_mode,"",1,$blocation,"","",false,$warehouse); 
          
          $storeIdString = $this->helper->getConfiguration('tablet_config/husky_settings/shop_queue_grids');
          $storeNames = explode(",", $storeIdString ?? "");
          $storeIds= [];
          foreach ($storeNames as $key) {
            $storeIds[] = $this->getStoreIds($key);
          }
          $ExcludedstoreIds = array_map('intval', $storeIds);
          foreach ($Collection as $order) {
            if (in_array($order->getStoreId(), $ExcludedstoreIds) && stripos($order->getOpShippingMethod(), 'collection') !== false) {
              $topOrderData = null;
              continue;  // Filter collection orders for stores excluded 
            }else{
              $topOrderData = $order->getData();
              break;
           } 
          }
           return $topOrderData;
        }
        if($Collection->getSize()){
           //code for logic for return that warehouse only
            //  if(!empty($warehouse)){
            //   foreach ($Collection as $order) {
            //       $warehouseItems = $this->pythonHelper->getFilteredOrderItems($order,$warehouse);
            //       if($warehouseItems['count'] != 0){
            //         return $order->getData();
            //       }
            //   }
            //  }
          return $Collection->getFirstItem()->getData();
        }
      return null;
    }


    public function OrdersCollection($training_mode,$user = "",$forQueue = 0,$blocation = 0,$onScreen = 0,$store_id = 0,$isGrid=false, $warehouse = '')
    {
      $collection = $this->_palletCollection->create()->getOrders();
      $collection=$this->pythonHelper->addFieldToWareHouse($collection,$isGrid);
      $includeAPicking = (int)$this->helper->getConfiguration('tablet_config/general/include_a_picking');
      if ($this->helper->getConfiguration('config_section/tm_palletqueue/disable_collection_printing') && !$store_id) {
      $collection->addFieldToFilter('shipping_description',array("nlike" => "%Collection%"));
      }
       if ($this->pythonHelper->isEnablePython() && $warehouse != '' && $warehouse != 0 ) {
        $collection->addFieldToFilter('order.warehouse', array('in' => [0, $warehouse]));
      } 
      if($onScreen){
        if($this->pythonHelper->isEnablePython() && $warehouse == 2){
         $collection->addFieldToFilter(['python_queue','is_picked'],[array("in" => array(self::TABLET_QUEUE_STATUS_ONSCREEN)),array("eq"=>0)]);
        }else{ 
         $collection->addFieldToFilter(['in_queue','is_picked'],[array("in" => array(self::TABLET_QUEUE_STATUS_ONSCREEN)),array("eq"=>0)]);
        }
        $collection->addFieldToFilter(['dispatch_date'],
            [
                array("to" => date("Y-m-d 23:59:59"), 'date' => true)
            ]);
        
      }else{
         if($this->pythonHelper->isEnablePython()  && $warehouse == 2){
           $collection->addFieldToFilter('python_queue',array("in" => array(self::TABLET_QUEUE_STATUS_INQUEUE,self::TABLET_QUEUE_STATUS_WAS_PARKED_ORDER)));
     }else{
          $collection->addFieldToFilter('in_queue',array("in" => array(self::TABLET_QUEUE_STATUS_INQUEUE,self::TABLET_QUEUE_STATUS_WAS_PARKED_ORDER)));
         }
       if ($this->helper->getConfiguration('config_section/tm_palletqueue/check_pick_created')) {
              //$collection->addFieldToFilter("is_picked", 1); // pick should be created in NAV
          }
      }
 
      if($blocation){
        $collection->addFieldToFilter('is_blocation',array("eq" => "1"));
      }
      else{
        $collection->addFieldToFilter('is_blocation',array("eq" => "0"));
      }
       if (!$this->helper->getConfiguration('tablet_config/general/tablet_trial')) {
            if(!$store_id){
           $collection->addFieldToFilter('is_shop_order', 0);
          }
      }else{
          $collection->addFieldToFilter('order.status', array("eq" => "tablet_trial"));
      }

      if ($training_mode) {
          $training_weight = round((float)$this->helper->getConfiguration('tablet_config/general/training_weight')??0,0);
          $collection->addFieldToFilter('weight', array("lt" => $training_weight));
           //$collection->addFieldToFilter('pallet_size', 'SPALLET');
      } 
      if ($forQueue) {
            if ($user != "") {
             if($this->pythonHelper->isEnablePython()  && $warehouse == 2){
                $collection->addFieldToFilter('python_user', $user);
              }else{            
                $collection->addFieldToFilter('tablet_user', $user);
              }
           if($this->pythonHelper->isEnablePython()){
            $collection->addFieldToFilter('order.status', array("in" => array('a_picking','partially_checked', 'pick_created','partial_a_picking','partial_pick_created','partial_nav_picked','partially_shipped')));
           }else {
                $collection->addFieldToFilter('order.status', array("in" => array('pick_created','a_picking')));
           }
            } else {
                // $collection->addFieldToFilter('tablet_user', array('null' => true));
               if($this->pythonHelper->isEnablePython() && $warehouse == 2){
                 $collection->addFieldToFilter('python_user', array('null' => true));
              }else{
                 $collection->addFieldToFilter('tablet_user', array('null' => true));
               }
               if($this->pythonHelper->isEnablePython()){
                   $collection->addFieldToFilter('order.status', array("in" => array('a_picking','partially_checked', 'pick_created','partial_a_picking','partial_pick_created','partial_nav_picked','partially_shipped')));
               }else {
                if($includeAPicking){
                     $collection->addFieldToFilter('order.status', array("in" => array('pick_created','a_picking'))); //a_picking orders with table_users = null
                 }
                 else{
                     $collection->addFieldToFilter('order.status', array("in" => array('pick_created')));
                 }   
               }
            }
            if(!$store_id){
            $collection->addFieldToFilter('is_shop_order', 0);
            }
    

      }else{ 
        if (!$this->helper->getConfiguration('tablet_config/general/tablet_trial')) {
          
          if($onScreen){
              if($this->pythonHelper->isEnablePython()){
                $collection->addFieldToFilter('order.status', array("in" => array('pick_created','a_picking','partially_checked','partial_a_picking','partial_nav_picked','partially_shipped')));
              }else {
                $collection->addFieldToFilter('order.status', array("in" => array('pick_created','a_picking')));
              }
          }else{
            if($this->pythonHelper->isEnablePython()){
                $collection->addFieldToFilter('order.status', array("in" => array('a_picking','pick_created','partially_checked','partial_a_picking','partial_nav_picked','partially_shipped')));
              }else {
               if($includeAPicking){
                  $collection->addFieldToFilter('order.status', array("in" => array('pick_created','a_picking')));
               }
               else{
                  $collection->addFieldToFilter('order.status', array("in" => array('pick_created')));
               }
              }
          if($this->pythonHelper->isEnablePython()  && $warehouse == 2){
            $collection->addFieldToFilter('python_queue', ['neq' => self::TABLET_QUEUE_STATUS_ONSCREEN]); //a_picking which are not currently being picked 
          }else{
            $collection->addFieldToFilter('in_queue', ['neq' => self::TABLET_QUEUE_STATUS_ONSCREEN]); //a_picking which are not currently being picked 
           }
          }
          if(!$store_id){
          $collection->addFieldToFilter('is_shop_order', 0);
          }
          
      }else{
          $collection->addFieldToFilter('order.status', array("eq" => "tablet_trial"));
      }
      } 
      
      if($this->helper->getConfiguration('tablet_config/general/depriortize_ot')){
      if(date("H") < "17"){
        $collection->addFieldToFilter(['dispatch_date','is_owntransport'],
            [
                array("to" => $this->dhlIndex->getSmallOrderDispatchDate() . " 23:59:59", 'date' => true),
                ['eq'=> 0]
            ]);
        
        
      }
      }
        if($store_id){
            $collection->addFieldToFilter('shipping_description',array("like" => "%Collection%"));
            $collection->addFieldToFilter("order.store_id",$store_id); 
         } 
           
      $collection->setOrder('order.dispatch_date', 'ASC');
      $collection->setOrder('easywms_priority', 'DESC');
      //$collection->setOrder('shipping_description', 'DESC');
      $collection->setOrder('is_owntransport', 'DESC');
      
      if($this->helper->getConfiguration('tablet_config/general/priortize_p2p')){
      $collection->setOrder('is_ptop', 'DESC');
      }
      
      $collection->setOrder('weight', 'DESC');
       //$collection->setOrder('order.created_at', 'ASC');
      return $collection;
    }
  
 
    public function shopOrdersCollection($forGrid = 0,$blocation = 0,$isGrid=false,$warehouse = ''){
       $today = $this->helper->getCurrentDate('d-m-Y');
      if ($forGrid) {
        $filter_array = array(self::TABLET_QUEUE_STATUS_INQUEUE,self::TABLET_QUEUE_STATUS_ONSCREEN);
      }else{
        $filter_array = array(self::TABLET_QUEUE_STATUS_INQUEUE);
      }

       if($this->pythonHelper->isEnablePython()  && $warehouse == 2){
        $queue_check = "python_queue";
        $filter_status = array('processing','printed','a_picking','pick_created','partially_checked','partial_a_picking','partial_nav_picked','partially_shipped');

       }else{
        $queue_check = "in_queue";
        $filter_status = array('processing','printed','a_picking','pick_created');

       }
      $collection = $this->_palletCollection->create()->getOrders();
      $collection=$this->pythonHelper->addFieldToWareHouse($collection,$isGrid);

      $collection
      ->addFieldToFilter($queue_check,array("in" => $filter_array))
      ->addFieldToFilter('is_shop_order', 1)
      ->addFieldToFilter('is_collected',array('neq' => 1))
      ->addFieldToFilter('order.status',array("in" => $filter_array)) // pick_created add need to discuss
      ->addFieldToFilter('order.dispatch_date',array('like' => '%'.$today.'%'))
      ->setOrder('created_at', 'ASC');
       if($blocation){
        $collection->addFieldToFilter('is_blocation',array("eq" => "1"));
      }
      else{
        $collection->addFieldToFilter('is_blocation',array("eq" => "0"));
      }
      if ($this->pythonHelper->isEnablePython() && $warehouse != 0 && $warehouse != '' ) {
         $collection->addFieldToFilter('order.warehouse', array('in' => [0, $warehouse]));
       }
      return $collection;
    }

    public function setOrderOnScreen($order_id,$in_queue,$status,$user = "",$process_time = 0,$shipping_method = '',$warehouse = ''){
      
    	try {
        $connection = $this->_resource->getConnection();
        $connection->beginTransaction();

            if($shipping_method == "DHL"){
                $Model = $this->sampleprocessingFactory->create();
            } else {
                $Model = $this->_palletModel->create();
            }
            $Model->load($order_id, 'op_order_id');
            
            if ($this->pythonHelper->isEnablePython()  && $warehouse == 2) {
                $queueParam = 'PythonQueue';
                $userParam = 'PythonUser';
            } else {
                $queueParam = 'InQueue';
                $userParam = 'TabletUser';
            }

            if ($in_queue == self::TABLET_QUEUE_STATUS_INQUEUE) {
                if ($Model->{"get{$queueParam}"}() != self::TABLET_QUEUE_STATUS_COMPLETE) {
                    $Model->{"set{$queueParam}"}($in_queue);
                }
            } else {
                $Model->{"set{$queueParam}"}($in_queue);
            }

            $Model->setTabletStatus($status);

            if ($in_queue == self::TABLET_QUEUE_STATUS_INQUEUE) {
                $Model->{"set{$userParam}"}(NULL);
            } else {
                $Model->{"set{$userParam}"}($user);
            }

            if ($process_time) {
              $Model->setProcessTime($process_time);
            }
            $Model->save();
            
            $OrderModel = $this->_ordermodel->create()->load($order_id);
            
            if($in_queue == self::TABLET_QUEUE_STATUS_ONSCREEN){
                if ($this->pythonHelper->isEnablePython()) {
                    $warehouse = $OrderModel->getWarehouse();
                    $ordStatus = $OrderModel->getStatus();
                    if ($warehouse == 0 && $ordStatus == 'pick_created') {
                        $OrderModel->setStatus("partial_a_picking");
                    }else{
                        $OrderModel->setStatus("a_picking");
                    }
                }else {
                    $OrderModel->setStatus("a_picking");
                }
                $OrderModel->addStatusHistoryComment('This order is started on Tablet by user : ' . $user);
                
            }elseif($in_queue == self::TABLET_QUEUE_STATUS_COMPLETE){
                $OrderModel->addStatusHistoryComment('This order is Completed on Tablet by user : ' . $user);
            }elseif($in_queue == self::TABLET_QUEUE_STATUS_INQUEUE){
                $OrderModel->setStatus("pick_created");
                $OrderModel->addStatusHistoryComment('This order is Put Back in Queue on Tablet by user : ' . $user . " Reason : " . $status);
            }elseif($in_queue == self::TABLET_QUEUE_STATUS_WAS_PARKED_ORDER){
                $OrderModel->setStatus("a_picking");
                $OrderModel->addStatusHistoryComment('This order is Parked on Tablet by user : ' . $user . " Reason : " . $status);
            }
            
            $OrderModel->save();
            $connection->commit();
            //$Model->getResource()->commit();
            
            return true;
    	} catch (\Exception $e) {
        $error_message = $e->getMessage();
    		$this->helper->ErrorLog($user,"getQueueOrder",$error_message,"pallet order id:".$order_id);
        $connection->rollBack();
        return false;
    	}

    }

    public function logOrderHistory($order_id,$user,$tab_unique_id,$tablet_status,$reason,$token,$token_type,$shippingMethod=null)
    {
      try{
        $dateNow = $this->helper->getCurrentDate('Y-m-d H:i:s');

        $HistoryModel = $this->_loginHistory->create();
        if($shippingMethod == "DHL"){
            $HistoryModel->setTabOrderId(0);
            $HistoryModel->setTabOrderStatus($tablet_status);
        } elseif ($order_id) {
          $HistoryModel->setTabOrderId($order_id);
          $HistoryModel->setTabOrderStatus($tablet_status);
        }
        $HistoryModel->setUser($user);
        $HistoryModel->setTabToken($token);
        $HistoryModel->setTabTokenType($token_type);
        $HistoryModel->setReason($reason);
        $HistoryModel->setStartTime($dateNow);
        $HistoryModel->setTabUniqueId($tab_unique_id);
        $HistoryModel->setTabShipping($shippingMethod);

        $HistoryModel->save();
        return $HistoryModel->getLogId();
      }catch(\Exception $e){
        $error_message = $e->getMessage();
        $this->helper->ErrorLog($user,"logOrderHistory",$error_message,"pallet tab unique id:".$tab_unique_id);
      }

    }

    public function logHistoryEnd($log_id,$status,$order_id = 0,$shipping_method = "")
    {
        $dateNow = $this->helper->getCurrentDate('Y-m-d H:i:s');
        try{
            $HistoryModel = $this->_loginHistory->create()->load($log_id);

            if ($shipping_method == "DHL") {
                $HistoryModel->setTabUniqueId($order_id);
                $HistoryModel->setTabOrderStatus($status);
            } else {
                if ($order_id) {
                    $HistoryModel->setTabOrderId($order_id);
                    $HistoryModel->setTabOrderStatus($status);
                }
            }
            $HistoryModel->setEndTime($dateNow);
            $HistoryModel->save();
            return true;
        }catch(\Exception $e){
            $this->helper->ErrorLog("","logHistoryEnd",$e->getMessage(),"log id:".$log_id);
            return false;
        }
    }

    public function onScreenOrder($user,$reason = "")
    {
      $collection = $this->_loginHistory->create()->getCollection()->addFieldToFilter('user', $user)->addFieldToFilter('tab_order_status', self::TABLET_QUEUE_STATUS_ONSCREEN);
      if ($reason == 'picking') {
        $return_array = array('count' => false);
        return $return_array;
      }
      $order = $collection->getFirstItem()->getData();
      if ($order) {
        $return_array = array('count' => true,'workstation' => $order['tab_unique_id'],'tab_order_id' => $order['tab_order_id'],"tab_token" => $order['tab_token'], "tab_token_type" => $order['tab_token_type']);
      }else{
        $return_array = array('count' => false);
      }
      return $return_array;
    }
   
    public function getOrderData($orderData,$user = "",$shipping_method = '', $warehouse = '')
    {
        $is_shop_order = '';
        $order_id = $orderData['op_order_id'];
        $orderno = $orderData['increment_id'];
        $customer_name = $orderData['customer_firstname']." ".$orderData['customer_lastname'];
        $customer_comment = $orderData['delivery_note'];
        $dispatch_date = $this->_datetime->date("d-m-Y", strtotime($orderData['dispatch_date']??""));
        $weight = round((float)$orderData['weight'] ??0, 2);
         
        
        $delivery_date = $orderData['shipping_date'];

        //join with picked_problem_broken_sku_table to return sku picked data
        $skuHistoryCollection = $this->skuHistoryModel->create()->getCollection();
        $skuHistoryCollection->addFieldToFilter('order_no', $orderData['increment_id']); 
  
        $skus_data = [];
         foreach ($skuHistoryCollection->getData() ?? [] as $key ) {
          $skus_data[] = ['sku' => $key['sku'], 'type' => $key['type']];
         }

        try{

            $mediaBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $OrderModel = $this->_ordermodel->create()->load( $order_id );
            
            $customerSpecialDeliveryInstructions = $this->customerSpecialDeliveryInstructions($orderData['increment_id']??[] , $OrderModel);
            
            $shipping_description = $this->_basehelper->getQueueName($OrderModel);
         
         
            $allItems = $OrderModel->getAllItems();
            $allItems = $this->_microconnectHelper->sortItems($allItems);
            $numberofitems = count($allItems);
            $items = array();
            $listedSkus = array();
            $isEnablePython = $this->pythonHelper->isEnablePython();
            $otherwarehouseItems = 0;
            foreach ($allItems as $item) {
           
             if ($warehouse != 0 && $isEnablePython && $warehouse != $item->getWarehouse())
               {
                   $qty = round((float)$item->getQtyOrdered()??0, 0);
                   $itemWeight = $qty * $item->getWeight();
                   $weight -= $itemWeight;
                   $otherwarehouseItems++;
                   continue;
               }
              
              $qty = round((float)$item->getQtyOrdered()??0, 0);
                if($item->getQtyRefunded() == $qty){
                 continue;
                }else{
                  $qty = $qty - $item->getQtyRefunded();
                }
                if(!$item->getIsShopPick()){
                    $options = $item->getProductOptions();
                    if (isset($options['additional_options'][0]['value'])){
                        $sampleType = $options['additional_options'][0]['value'];
                        if (!is_array($sampleType) && (str_contains($sampleType??"", 'Full') || str_contains($sampleType??"", 'Cut') || str_contains($sampleType, 'Half') || str_contains($sampleType??"", 'Quarter'))){
                            $ifCut = $sampleType;
                        }else{
                            $ifCut = "";
                        }
                    }else{
                        $ifCut = "";
                    }
                    $product = $item->getProduct();
                    // $qty = round($item->getQtyOrdered(), 0);
                    $price = round((float)$item->getPrice()??0, 2);
                    $image = $mediaBaseUrl.'catalog/product'.$product->getSmallImage();
                    $productWeight = round((float)$product->getWeight()??0, 2);
                  
                    $productTypeId = $product->getProductTypes();
                    $attributeCode = 'product_types';
                    $productfactory = $this->productFactory->create();
                    $isAttributeExist = $productfactory->getResource()->getAttribute($attributeCode); 
                    $optionText = '';
                    if ($isAttributeExist && $isAttributeExist->usesSource()) {
                        $optionText = $isAttributeExist->getSource()->getOptionText($productTypeId);
                    }

                    $attributeSet = $this->getAttributeSetName($product->getAttributeSetId());

                    if ($attributeSet == 'Wood') {
                        $optionText = $attributeSet;
                    }
                   
                    $boxQty = (int)$product->getBoxQty();

                    $pick_type = null;
                    foreach ($skus_data ?? [] as $key) {
                        if($key['sku'] == $item->getSku()){
                          $pick_type = $key['type'];
                        }
                    }
                      
                    if (!in_array($item->getSku(),$listedSkus)) {
                        $items[$item->getSku()] = array(
                            'name'          => $item->getName(),
                            'sku'           => $item->getSku(),
                            'Price'         => $price,
                            'quantity'      =>  $qty,
                            'productWeight' => $productWeight,
                            'image'         => $image,
                            'location'      => ($ifCut == "") ? $product->getStockLocation() : $product->getCutSampleLocation(),
                            'size'   => $product->getData('size'),
                            'stock_qty' => round($qty, 0),
                            'box_qty' => $boxQty,
                            'type' => $ifCut,
                            'product_type' => $optionText,
                            'is_picked' => $pick_type

                        );
                        $listedSkus[] = $item->getSku();
                    }else{
                        $items[$item->getSku()]["quantity"] = $items[$item->getSku()]["quantity"] + $qty;
                        $items[$item->getSku()]["stock_qty"] = $items[$item->getSku()]["stock_qty"] +$qty;
                    }
                }
            }
            $productsArray = array_values($items);

            if(preg_match("/\bKerbside\b/i", $shipping_method??"") || preg_match("/\bKerbside\b/i", $shipping_description??"") ||
              preg_match("/\Collection\b/i", $shipping_method??"") || preg_match("/\bCollection\b/i", $shipping_description??"" )){
                $palletsize = $orderData['pallet_size'];
                $is_shop_order = $orderData['is_shop_order'];
                $is_locked = ''; 
            } else {
                $palletsize = "";
                $is_locked = $orderData['is_locked'];
             }
            $return_array = array(
                'Order_Id' => $order_id,
                'Order_No' => $orderData['increment_id'],
                'is_shop_order' => $is_shop_order,
                'Customer_Name' => $customer_name,
                'Customer_Comment' => $customer_comment,
                'customerSpecialDeliveryInstructions' => $customerSpecialDeliveryInstructions,
                'Weight' => $weight,
                'No_of_Items' => ($numberofitems-$otherwarehouseItems),
                'items' => $productsArray,
                'pallet_size'=> $palletsize,
                'process_time'=> $orderData['process_time'],
                'Shipping_Date'=> $delivery_date,
                'shipping_method'=>$shipping_description,
                'Dispatch_Date' => $dispatch_date,
                'is_locked' => $is_locked
            );
            if ($user == "") {
                $return_array['process_by'] = $orderData['tablet_user'];
                $return_array['picked_date'] = $orderData['end_time'];
            }
            return $return_array;
        }catch(\Exception $e){
            $error_message = $e->getMessage();
            $this->helper->ErrorLog($user,"getOrderData",$error_message,"pallet order id:".$order_id);
            return false;
        }
    }

    /**
     * @param $incrementId
     * @return string
     */
    public function customerSpecialDeliveryInstructions($incrementId = null , $OrderModel = null)
    {
        return $this->helper->customerSpecialDeliveryInstructions($incrementId,$OrderModel);
    }

    public function setParkOrder($order_data_json,$item_data_json,$order_no,$weight,$pause_status,$user,$tablet_unique_id){
      $dbarray = array();
      $dbarray['order_no'] = $order_no;
      $dbarray['user'] = $user;
      $dbarray['tablet_unique_id'] = $tablet_unique_id;
      $dbarray['order_data_json'] = $order_data_json;
      $dbarray['item_data_json'] = $item_data_json;
      $dbarray['pause_status'] = $pause_status;
      $dbarray['pause_time_start'] = $this->_datetime->date("Y-m-d H:i:s");
      try{
        $parkedModel = $this->_parkedOrder->create();
        $parkedModel->addData($dbarray);
        $parkedModel->save();
        return true;
      }catch(\Exception $e){
        $error_message = $e->getMessage();
        $this->helper->ErrorLog($user,"setParkOrder",$error_message,"order no:".$orderno);
        return false;
      }
    }

    public function getParkedOrderData($order_no){
      $date = $this->_datetime->date("Y-m-d H:i:s");
      try{
        $parkedCollection = $this->_parkedOrder->create()->getCollection();
        $parkedCollection->addFieldToFilter('order_no', $order_no)->addFieldToFilter('pause_status', 1);
        $parkeddata = $parkedCollection->getFirstItem()->getData();
        if ($parkeddata['park_id']) {
          $ParkedOrderModel = $this->_parkedOrder->create()->load($parkeddata['park_id']);
          $ParkedOrderModel->setPauseStatus(0);
          $ParkedOrderModel->setPauseTimeStop($date);
          $ParkedOrderModel->save();
          return $parkeddata;
        }

      }catch(\Exception $e){
        $error_message = $e->getMessage();
        $this->helper->ErrorLog("","getParedkOrder",$error_message,"order no:".$order_no);
        return false;
      }
    }

    public function convertToMins($milli_seconds){
        $seconds = $milli_seconds/1000;

        if($seconds < 60){
            $process_time = round($seconds??0,0)."s";
        }

        if($seconds > 60){
            $mins = ($seconds/60);
            $minutes = floor($mins);
            $sec = $mins-$minutes;
            $secs = round($sec*60);
            $process_time = $minutes."m ".$secs."s";
        }
        return $process_time;
    }

    public function checkTrainingUser($user_name){
        $training_users = $this->helper->getConfiguration('tablet_config/general/training_users');
        $training_users_list = explode(",",$training_users??"");
        if (in_array($user_name,$training_users_list??[])){
            return true;
        }else{
            return false;
        }
        print_r($training_users_list);die("sss");
    }

    public function getpalletUsers(){
        $allusers = $this->helper->getConfiguration('config_section/tm_palletqueue/users');
        $users = array();
        if($allusers){
            $users = explode(',', $allusers??"");
        }
        return $users;
    }
     
    public function returnOrderCollection()
    {
        $collection = $this->returnOrder->create()->getCollection();

        return $collection;
    }

    public function getAttributeSetName($attribute_id)
    {
        $attributeSetRepository = $this->attributeSet->get($attribute_id);
        return $attributeSetRepository->getAttributeSetName();
    }

    public function applySalesJoin($collection)
    {
        return  $this->helper->applySalesJoinData($collection);
    }

    public function getCombinePickData($dhlCollection,$user = "",$_testing = 0,$warehouse = "")
    {
      $return_array = array();
      foreach ($dhlCollection as $orderData) {
        $_batchNumber = $orderData['batch_number'];
      $order_id = $orderData['op_order_id'];
      $scanNo = $orderData['scan_no'];
      $orderno = $orderData['increment_id'];
      $stock_skus = $orderData['out_of_stock'];

      try{
        $mediaBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $OrderModel = $this->_ordermodel->create()->load( $order_id );
        
        $_orderStatus = $OrderModel->getStatus();
        
        $movementPendingSkus = [];
        if($_orderStatus == "movement_created"){
            $easywms_movements = $OrderModel->getEasywmsMovements();
            if($easywms_movements){
                $easywms_movements = explode(",",$easywms_movements);
                if(count($easywms_movements)){
                    
                    foreach($easywms_movements as $skunumber){
                        
                        $exploded = explode(":",$skunumber);
                        if($exploded && isset($exploded) && isset($exploded[1])){
                           $movementPendingSkus[trim($exploded[1])] = trim($exploded[0]);
                        }else{
                           $movementPendingSkus[trim($exploded[1])] = "NA"; 
                        }
                        
                    }
                    
                }
            }
        }

        $isEnablePython = $this->pythonHelper->isEnablePython();

      $allItems = $OrderModel->getAllItems();
      $allItems = $this->_microconnectHelper->sortItems($allItems);
      $numberofitems = count($allItems??[]);

      foreach ($allItems as $item) {
        if ($warehouse != 0 && $isEnablePython && $warehouse != $item->getWarehouse())
        {
            continue;
        }
        $out_of_stock_skus = false;
        $options = $item->getProductOptions();
        if (isset($options['additional_options'][0]['value'])){
          $sampleType = $options['additional_options'][0]['value'];
          if (!is_array($sampleType) && (str_contains($sampleType??"", 'Full') || str_contains($sampleType??"", 'Cut') || str_contains($sampleType??"", 'Half') || str_contains($sampleType??"", 'Quarter'))){
              $ifCut = $sampleType;
          }else{
              $ifCut = "";
          }
      }else{
          $ifCut = "";
      }
      if($stock_skus ){
        $pattern = '/' .$item->getSku(). '/';
        if (preg_match($pattern??"", $stock_skus??"")) {
          $out_of_stock_skus = true;
        }
      }
      $product = $item->getProduct();
    
      $qty = round((float)$item->getQtyOrdered()??0, 0);
      //get pallet image
      if ($product->getData('pallet_wrap_screen') && file_exists($this->directory_list->getPath('media').'/catalog/product'.$product->getData('pallet_wrap_screen'))) {
        $image = $mediaBaseUrl.'catalog/product'.$product->getData('pallet_wrap_screen');
       }
      else{
        $image = $mediaBaseUrl.'catalog/product'.$product->getSmallImage();
      }
      
      
      $movement_number = "";
      
      if($_orderStatus == "movement_created"){
        if(isset($movementPendingSkus[$item->getSku()]) && $movementPendingSkus[$item->getSku()]){
        $movement_number = $movementPendingSkus[$item->getSku()];
      }else{
        $movement_number = "NA";
      }
      }

        $shipping_no = $orderData['consingment_id'];
        if ($OrderModel->getWarehouse() ==2) {
            $shipping_no = $orderData['py_consingment_id'];
        }
      
      
      
        $return_array[] = array(
          'Order_Id' => $order_id,
          'Order_No' => $orderData['increment_id'],
          'sku'      => $item->getSku(),
          'location'      => ($ifCut == "") ? $product->getStockLocation() : $product->getCutSampleLocation(),
          'quantity'      =>  $qty,
          'image'         => $image,
          'scan_no'    => $scanNo,
          'shipping_no' => $shipping_no,
          'is_out_of_stock' =>$out_of_stock_skus,
          'movement_number' =>$movement_number
        );
      }
      
      if(!$_testing){
        
        if($_orderStatus == "movement_created"){
            $orderStatus = "movement_created";
        }else{
            if ($this->pythonHelper->isEnablePython()) {
                $warehouse = $OrderModel->getWarehouse();
                $ordStatus = $OrderModel->getStatus();
                if ($warehouse == 0 && $ordStatus == 'pick_created') {
                    $orderStatus = "partial_a_picking";
                }else{
                    $orderStatus = "a_picking";
                }
            }else {
                $orderStatus = "a_picking";
            }
        }
        
        $orderIdss = $OrderModel->getId();
        $orderComments = 'This order is being picked by tablet user : ' . $user . " in Batch $_batchNumber";
        
        $_connection = $this->_resource->getConnection();
        if($_orderStatus != "movement_created"){
        $_connection->update($_connection->getTableName("sales_order"), ["status"=>$orderStatus], ['entity_id = ?' => $orderIdss]);
        $_connection->update($_connection->getTableName("sales_order_grid"), ["status"=>$orderStatus], ['entity_id = ?' => $orderIdss]);  
        }
        
        
        
        $insertColumnValues = ["parent_id"=> $orderIdss, "is_customer_notified"=>0,
                    "is_visible_on_front"=>0, "comment"=>$orderComments, "status"=>$orderStatus, "entity_name"=>"order", "is_own_transport"=>0];
        $tableName = $_connection->getTableName("sales_order_status_history");
        $result = $_connection->insert("$tableName", $insertColumnValues);

      }
      

    }catch(\Exception $e){
      $this->logger->critical("[Batch#: ".$_batchNumber.']-Exception:'.$e->getMessage(), array('AndroidServicesHelperTabletQueue::getCombinePickData'));
      // $this->helper->ErrorLog($user,"getOrderData",$error_message,"pallet order id:".$order_id);
      return false;
      }
    }
     return $return_array;
    }
    public function pendingDHLBatch($user , $grid = false){
      return $this->helper->getPendingCombinePickOrder(false,$user,$grid);

    }    

    public function pendingUnassignedDHLBatch($user){
      $dhlCollection = $this->dhlCollection->create();
      $dhlCollection->addFieldToFilter('tablet_user',array('null' => true));
      $dhlCollection->addFieldToFilter('batch_queue_status', array("in"=>array(1)));
      $dhlCollection->addFieldToFilter('batch_number', array('notnull' => true));
      $dhlCollection = $this->applySalesJoin($dhlCollection);
      $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true));
      $dhlCollection->addFieldToFilter('status', array('in' => array("a_picking","pick_created","movement_created")));
      if($dhlCollection->getData()){
        $groupCollection = clone $dhlCollection;  
        $groupCollection->getSelect()->group('batch_number');
        $firstBatchOrder = $groupCollection->getFirstItem()->getData()["batch_number"];
        $dhlCollection->addFieldToFilter('batch_number', $firstBatchOrder); //return first batch only
        return $dhlCollection->getData();
      }
      return;

    }
    
    
    public function pendingUnassignedDHLBatchWith($Batch_number){
      $dhlCollection = $this->dhlCollection->create();
      $dhlCollection->addFieldToFilter('batch_number', $Batch_number);
      $dhlCollection = $this->applySalesJoin($dhlCollection);
      $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true));
      $dhlCollection->addFieldToFilter('status', array('in' => array("a_picking","pick_created","movement_created")));
      if($dhlCollection->getData()){
        return $dhlCollection->getData();
      }
      return;

    }
    
    
    public function activeBatches(){
     return $this->helper->getActiveCombinePickOrder(false,true);
    }
    
    public function getConfiguration($path , $storeId = 0){
        $data = $this->helper->getConfiguration($path);
        return $data;
    }
        

    public function generateDHLBatch($currentDHL,$dhl_batchlimit,$EasyWMS = 0,$isGrid=false,$warehouse=''){
        
        $EasyWMS = (int)$this->helper->getConfiguration('tablet_config/general/dhl_batch_easywms');      
      
      if($EasyWMS){
        $dhlCollection = $this->getDhlQueueOrder(false,1,$isGrid,$warehouse);
      }
      else{
      $dhlCollection = $this->getDhlQueueOrder(false,0,$isGrid,$warehouse);
      $dhlCollection->setOrder('dispatch_date', 'ASC');
    }
      if($dhl_batchlimit){
        $dhlCollection->setPageSize($dhl_batchlimit);
      }
      
      return $dhlCollection;
    } 
    public function CompleteBatchOrders($batchNo){
      if($batchNo){
        $dhlCollection = $this->dhlCollection->create();
        $dhlCollection->addFieldToFilter('batch_number', $batchNo); 
        $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true)); 
        $dhlCollection = $this->applySalesJoin($dhlCollection);
        // $dhlCollection->addFieldToFilter('sales.status', array("in" => array('complete')));
      }
      if($dhlCollection){    
       if($dhlCollection->getData()){
               
        foreach ($dhlCollection->getData() as $order) {
           $Model = $this->sampleprocessingFactory->create();
           $Model->load($order["op_order_id"], 'op_order_id');
           $Model->setBatchQueueStatus(4);
           $Model->setCombinedPickCompletedAt(date('Y-m-d H:i:s'));
           $Model->save();
           }
        }
      }
    }
    public function getSortedBatch($orders){
      $even = array();
      $odd = array();
      $neither = array();
      foreach($orders as $order){
        if($order["location"]){
        $number = preg_replace("/[^0-9]/", "", $order["location"]??"");
        if($number % 2 == 0){
         $even[] = $order;

        } else{ 
         $odd[] = $order;

       }    
      }else{
        $neither[] = $order;   
         }
     }

    //  asc
     usort($even, function($a, $b) {
      return $a['location'] > $b['location'] ? 1 : -1;
    });
    //desc
     usort($odd, function($a, $b) {
      return $a['location'] < $b['location'] ? 1 : -1;
    });
    
    $concatenatedArray = array_merge($even, $odd, $neither);
    
    $newArray = [];
    $newSortingArray = [];
    foreach($concatenatedArray as $_rrr){
        $locationFirstChar = substr($_rrr["location"]??"", 0, 1);
        if(!$locationFirstChar){
            $locationFirstChar = "A";
        }
        $newArray[$locationFirstChar][] = $_rrr;
        $newSortingArray[$locationFirstChar] = $locationFirstChar;
    }

    array_multisort($newSortingArray, SORT_DESC, $newArray);
    $_finalArray = [];
    foreach($newArray as $letter => $newData){
        foreach($newData as $Data){
            $_finalArray[] = $Data;
        }
        
    }
    return $_finalArray;
     
    }
    function compareObjects($a, $b) {
      return $a['location'] - $b['location'];
  }


    public function getWaitingDhlBatches($return_count = false)
    {
       return $this->helper->getWaitingDhlBatches($return_count);
     }


    public function UpdateBatchEasyWms($status, $BatchNo)
    {
        $Model = $this->batchOrderFactory->create();
        $Model->load($BatchNo, 'batch_number');
        $Model->setBatchStatus($status);
        $Model->save();
     }

     public function getStoreIds($storeName = "", $store_id = 0){
      $stores = $this->_storeManager->getStores();
      $storeName = '/' .$storeName. '/';
  
      foreach ($stores as $store) {
        if (preg_match($storeName, $store["name"])){
          $store_id = $store["store_id"];
          break;
        }
      }
      return $store_id;
    }
    public function getStoreNameById($storeId){
      try {
        $store = $this->_storeManager->getStore($storeId);
        $storeName = $store->getName();
        return $storeName;
    } catch (\Exception $e) {
        return $e->getMessage();
      }
    }


    public function checkShopUser($user = "", $store = ""){

      $showrroms = $this->helper->getConfiguration('tablet_config/husky_settings/enable_showrooms');

      $allDataArray = json_decode($showrroms??"", true);
      $result = [];

      if (is_array($allDataArray)) {  
        foreach ($allDataArray as $item) {
            if ($item['showroom_user'] === $user) {
                $result[] = $item['select_store'];
            }
        }
      }
      return $result;
   }

     public function GenerateShopPallet()
     {
         $PalletNumbersCollection = $this->mbPalletNumbers->create();
         if($PalletNumbersCollection->getData()){
             $count = $PalletNumbersCollection->getSize();
             $totalCount = $count + 1;
             $palletNo = "SC00000" . $totalCount ;
            $dbarray['pallet_number'] = $palletNo;
         }else{
             $dbarray['pallet_number'] = 'SC000001';
             $palletNo = 'SC000001';
         }
 
         try {
             $connection = $this->_resource->getConnection();
             $tableName = $this->_resource->getTableName('mb_pallet_numbers');
             $connection->beginTransaction();
             $connection->insert($tableName, $dbarray);
             $connection->commit();
         }
         catch (\Throwable $th) {
             $connection->rollBack();
         }
         return $palletNo;
     }
     public function GenerateShipment($order,$userName,$showroomName,$items)
     {
             $shipment = $this->orderConverter->toShipment($order);
                $shippingSkus = "";
                foreach ($order->getAllItems() as $orderItem) {
                    if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual() ) {
                        continue;
                    }
                    
                    $qtyShipped = $orderItem->getQtyToShip();
                    
                    if(isset($items)){
                      $matchingItem = null;
                      $orderSku = $orderItem->getSku();
                    
                      foreach ($items as $item) {
                          if ($item->sku === $orderSku) {
                              $matchingItem = $item;
                              break;
                          }
                      }

                      if ($matchingItem) {
                          if ($matchingItem->status === 'picked') {
                              $qtyShipped = $orderItem->getQtyToShip();
                          } elseif ($matchingItem->status === 'partially') {
                              $qtyShipped = $matchingItem->quantity;
                          }
                      } else {
                        continue;
                      }
                    }

                    $shipmentItem = $this->orderConverter->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                    $shipment->addItem($shipmentItem);
                    $shippingSkus = $shippingSkus . $orderItem->getSku() . ",";
                }
                    $shipment->register();
                    $shipment->getOrder()->setIsInProcess(true);
                    try {
                        $shipment->save();
                        if ($order->canShip()) {
                          $ord_status = "partially_shipped";

                      } else {
                          $ord_status = "complete";
                      }
      
                        $shipment->getOrder()->addStatusToHistory($ord_status, 'Order loaded into the user’s vehicle at ' . $showroomName . ' showroom by ' . $userName);
                        $shipment->getOrder()->save();
                        $shipment->save();
                        return $ord_status;
                    } catch (\Exception $e) {
                        return false;
                    }


     }

     public function getHuskyShopCollection($store_id,$count = 0)
     {
      $collection = $this->_palletCollection->create()->getOrders();
      $collection->addFieldToFilter('order.status', array("nin" => array('complete', 'closed')));
      $collection->addFieldToFilter('shipping_description',array("like" => "%Collection%"));
      $collection->addFieldToFilter("store_id",$store_id); 
      if($count){
        $Datacount = count($collection->getData());
        return $Datacount;
      }
      else{
        return $collection;
      }
     }
     public function filterOrdersByStoreAndShipping($count = 0,$isGrid=false)
     {
      //     public function OrdersCollection($training_mode,$user = "",$forQueue = 0,$blocation = 0,$onScreen = 0,$store_id = 0,$isGrid=false)
    $warehouse = '';
    if($this->pythonHelper->isEnablePython()){
      $warehouse = $this->pythonHelper->getWareHouseForWorkStations();
     }


      $collection = $this->OrdersCollection(0,'',0,0,0,0,$isGrid,$warehouse);
      $originalCollection = clone $collection;

      $storeIdString = $this->helper->getConfiguration('tablet_config/husky_settings/shop_queue_grids');
      $storeNames = explode(",", $storeIdString ?? '');
      $storeIds= [];
      foreach ($storeNames as $key) {
     
        if($key!=null && $key!="")
        {
          $storeIds[] = $this->getStoreIds($key);
        }
        
      }

    
      $excludedStoreIds = array_map('intval', $storeIds);

      

      $filteredCollection = [];
      $conditionMet = false;
      foreach ($collection as $key => $order) {
        if (in_array($order->getStoreId(), $excludedStoreIds) && stripos($order->getOpShippingMethod(), 'collection') !== false) {
            $conditionMet = true;
            $filteredCollection[] = $order->getOpOrderId();
        }
      }      
      if ($conditionMet) {
       $originalCollection->addFieldToFilter('op_order_id', ['nin' => $filteredCollection]);
      }
      if($count){
        return count($originalCollection->getData());
      }
       return $originalCollection;
     }


     public function getDhlQueueOrder($count = true, $EasyWMS = 0,$isGrid=false,$warehouse = '')
     {
        $EasyWMS = (int)$this->helper->getConfiguration('tablet_config/general/dhl_batch_easywms');
        $storeids = "1,2,3,4,5,6,7,8";
        $queue = "SO";
        $type = "TAB";

        $dhlCollection = $this->dhlIndex
            ->getBatchPrinting($storeids,$queue,$type, 0, '', true,true,$isGrid);
        $dhlCollection->addFieldToFilter('is_dhl', 1);
        if ($this->pythonHelper->isEnablePython()) {
            $ordWarehouse = $dhlCollection->getColumnValues('warehouse');

            if ($ordWarehouse[0] == 0) {
                $dhlCollection->addFieldToFilter('consingment_id', array('neq' => null));
                $dhlCollection->addFieldToFilter('py_consingment_id', array('neq' => null));
                $dhlCollection->addFieldToFilter(
                    ['tablet_user', 'python_user'],
                    [
                        ['null' => true],
                        ['null' => true]
                    ]
                );
            }elseif ($ordWarehouse[0] == 2) {
                $dhlCollection->addFieldToFilter('py_consingment_id', array('notnull' => true));
               $dhlCollection->addFieldToFilter('python_user', ['null' => true]);
            } elseif ($ordWarehouse[0] == 1) {
                $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true));
                $dhlCollection->addFieldToFilter('tablet_user', array('null' => true));
            }
        } else {
            $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true));
            $dhlCollection->addFieldToFilter('tablet_user', array('null' => true));
        }

        if($EasyWMS){
            $dhlCollection->addFieldToFilter('main_table.batch_number', array('notnull' => true));
            $dhlCollection->addFieldToFilter('main_table.batch_number', array('like' => "M0%"));

            if ($this->pythonHelper->isEnablePython()) {
                $dhlCollection->addFieldToFilter('status', array("in"=>array("pick_created","a_picking","movement_created","partial_a_picking")));
            } else {

                $dhlCollection->addFieldToFilter('status', array("in"=>array("pick_created","a_picking","movement_created")));
            }

            $BatchesRecord = $this->batchOrderFactory->create()->getCollection()->addFieldToFilter('batch_status', [NewConfig::BATCH_READY_TO_PICK])->setOrder('received_at' , 'ASC');

            $firstBatchNumber = $BatchesRecord->getFirstItem()->getBatchNumber();

            $dhlCollection->addFieldToFilter('main_table.batch_number' , $firstBatchNumber);
        }else{
            $dhlCollection->addFieldToFilter('batch_number', array('null' => true));
        }
        if($count){
            return count($dhlCollection ??[]);
        }else{
            return $dhlCollection;
        }
    }
}
