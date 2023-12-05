<?php 

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TM\AndroidServices\Model;

class WebServices implements \TM\AndroidServices\Api\WebServicesInterface{

  /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $_orderRepository;

    public $request;

    public function __construct(
        \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory $palletCollection,
        \TM\AndroidServices\Api\SkuHistoryRepositoryInterface $skuHistoryRepository,
        \TM\AndroidServices\Api\Data\SkuHistoryInterface $skuHistoryinterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Webapi\Rest\Request $request,
        \TM\AndroidServices\Helper\Data $helper,
        \TM\AndroidServices\Model\OrderCheckingFactory $orderchecking,
        \TM\PalletQueue\Model\OrderInvoiceFactory $palletOrder,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteria,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Sales\Model\OrderFactory $ordermodel,
        \TM\AndroidServices\Model\TabletLoginHistoryFactory $loginHistory,
        \TM\AndroidServices\Model\OrderChecksFactory $orderchecks,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \TM\Sampleprocessing\Model\SampleprocessingFactory $sampleprocessingFactory,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \TM\AndroidServices\Helper\TabletQueue $tabletHelper,
        \TM\AndroidServices\Model\ResourceModel\MbPalletNumbers\CollectionFactory $mbPalletNumbers,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \TM\WoodPanel\Helper\Data $woodHelper,
        \TM\Base\Helper\Local $localHelper,
        \TM\AndroidServices\Model\SalesItems $salesItems,
        \TM\WoodPanel\Model\WoodFactory $woodFactory,
        \TM\Sampleprocessing\Block\Adminhtml\Post\Index $dhlIndex,
        \TM\Sampleprocessing\Helper\PrintItemlable $helperItemLabel,
        \TM\Python\Helper\Data $pythonHelper
    ) {
        $this->_palletCollection = $palletCollection;
        $this->_skuHistoryRepository = $skuHistoryRepository;
        $this->_skuHistoryinterface = $skuHistoryinterface;
        $this->scopeConfig = $scopeConfig;
        $this->datetime = $datetime;
        $this->request = $request;
        $this->helper = $helper;
        $this->json = $json;
        $this->_resource = $resource;
        $this->_criteria = $criteria;
        $this->orderchecking = $orderchecking;
        $this->palletOrder = $palletOrder;
        $this->ordermodel = $ordermodel;
        $this->convertOrder = $convertOrder;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->transactionFactory = $transactionFactory;
        $this->_productRepository = $productRepository;
        $this->_loginHistory = $loginHistory;
        $this->orderchecks = $orderchecks;
        $this->order = $order;
        $this->_sampleprocessingFactory = $sampleprocessingFactory;
        $this->userFactory = $userFactory;
        $this->historyFactory = $historyFactory; 
        $this->historyRepository = $historyRepository;
        $this->orderRepository = $orderRepository;
        $this->tabletHelper = $tabletHelper;
        $this->mbPalletNumbers = $mbPalletNumbers;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->woodHelper = $woodHelper;
        $this->localHelper = $localHelper;
        $this->salesItems = $salesItems;
        $this->woodFactory = $woodFactory;
        $this->dhlIndex = $dhlIndex;
        $this->helperItemLabel = $helperItemLabel;
        $this->pythonHelper = $pythonHelper;
    }

    public function addBrokenTiles()
    {
      $params = $this->request->getBodyParams();
      $user = $params['user'];
      $sku = $params['sku'];
      $location = $params['location'];
      $printer_no = $params['printer_no'];//printer selected by user
      // $broken_qty = $params['quantity'];
      $zpl_data = $params['zpldata'];
      $tablet_unique_id = $params['tablet_unique_id'];
      $reported_date = $this->datetime->gmtDate();
      $product = $this->_productRepository->get($sku);
      
      // $responseData = $this->setBrokenTilesActions($user,$sku,$broken_qty,$location,$reported_date,$zpl_data,$printer_no,$tablet_unique_id);
      $responseData = $this->setBrokenTilesActions($user,$sku,$location,$reported_date,$zpl_data,$printer_no,$tablet_unique_id);
      if($responseData['success']){
        $final_array = array("status" => true,"message" => "Reported Successfully");
      }else{
        if($responseData['fcm_error']){
          $message = "FCM Error, Please Try Again";
        }elseif($responseData['printing_error']){
          $message = "Printing Error, Please Try Again";
        }else{
          $message = "Model Error, Please Try Again";
        }
        $final_array = array("status" => false,"message" => $message);
      }

      echo json_encode($final_array ?? []);die();

    }

    // public function setBrokenTilesActions($user,$sku,$broken_qty,$location,$reported_date,$zpl_data,$printer_no,$tablet_unique_id){
      public function setBrokenTilesActions($user,$sku,$location,$reported_date,$zpl_data,$printer_no,$tablet_unique_id){
       try{
        $error_entity = "";
        $return = array();
        $PrintingError = 0;
        $modelError = 0;
        $fcmError = 0;
        //check if sku already reported and not resolved
        $searchCriteria = $this->_criteria->addFilter('sku', $sku, 'eq')->addFilter('type', \TM\AndroidServices\Helper\Data::SKU_REPORT_TYPE_BROKEN, 'eq')
            ->addFilter('problem_status', \TM\AndroidServices\Helper\Data::PROBLEM_STATUS_UNRESOLVED, "eq")->create();
        $items = $this->_skuHistoryRepository->getList($searchCriteria);

        foreach ($items->getItems() as $item) {
          $data = $item->getData();
        }
        //print_r($data);die("zzz");
        if(isset($data['record_id'])){ // if sku already reported add previous and new quantity
          $BrokenModel = $this->_skuHistoryRepository->getById($data['record_id']);
          // $broken_qty = $broken_qty + $data['quantity'];
        }else{
          $BrokenModel = $this->_skuHistoryinterface;
          $BrokenModel->setTabletUniqueId($tablet_unique_id);
          $BrokenModel->setSku($sku);
          $BrokenModel->setType(\TM\AndroidServices\Helper\Data::SKU_REPORT_TYPE_BROKEN);
          $BrokenModel->setLocation($location);
        }

        $BrokenModel->setReportedBy($user);
        $BrokenModel->setReportedAt($reported_date);
        // $BrokenModel->setQuantity($broken_qty);
        $BrokenModel->setProblemStatus(\TM\AndroidServices\Helper\Data::PROBLEM_STATUS_UNRESOLVED);
        
          $connection = $this->_resource->getConnection();
          $connection->beginTransaction();

          $savedObj = $this->_skuHistoryRepository->save($BrokenModel);
          $record_id = $savedObj->getRecordId();
          
          // $FcmData = array("action" => "add","record_id" => $record_id , "reported_by" => $user , "reported_date" => $reported_date , "location" => $location , "sku" => $sku , "broken_qty" => $broken_qty);
          $FcmData = array("action" => "add","record_id" => $record_id , "reported_by" => $user , "reported_date" => $reported_date , "location" => $location , "sku" => $sku );
          $FcmSent = $this->helper->sendNotification($FcmData); // send FCM notification for broken tiles reported
          $printing_data = array();
          if($FcmSent && $record_id && $printer_no){
            $printing_data = $this->helper->printZpl($printer_no,$zpl_data); //Get printer ip from configuration by $printer_no to print zpl label  
          }
          
          if(isset($printing_data['success'])){ // for printing on network printer
           
            if ($printing_data['success']) {
              $connection->commit();
            }else{
              $error_message = $printing_data['message'];
              $PrintingError = 1;
            }
          }elseif(!$printer_no && $FcmSent){ // if local printing 
            
              $connection->commit();
          }else{
            $fcmError = 1;
            $error_message = $FcmSent;
          }

      }catch(\Exception $e){
        $error_message = $e->getMessage();
        $modelError = 1;
      }

      //echo "responsw".$modelError." ".$fcmError." ".$PrintingError;die("zzz");
      if ($modelError || $fcmError || $PrintingError) {
        $connection->rollBack();
        if ($FcmSent) {
          $FcmRemoved = $this->helper->sendNotification( ["action" => "remove","record_id" => $record_id , "completed_by" => $user , "completed_date" => $reported_date] );
        }
        //error log
        $this->helper->ErrorLog($user,"setBrokenTilesActions",$error_message,"sku:".$sku);

        $return = array('success' => 0 , 'error' => 1 , 'printing_error' => $PrintingError, 'model_error' => $modelError, 'fcm_error' => $fcmError);
      }else{
        $return = array('success' => 1 , 'error' => 0 );
      }
      return $return;
    }

    public function ReportedBrokenTiles(){
      try{
        $error_message = "";
        // $searchCriteria = $this->_criteria->addFilter('type', \TM\AndroidServices\Helper\Data::SKU_REPORT_TYPE_BROKEN, 'eq')->addFilter('problem_status', \TM\AndroidServices\Helper\Data::PROBLEM_STATUS_UNRESOLVED, "eq")->create();
        // $items = $this->_skuHistoryRepository->getList($searchCriteria);
        $data_array = array();
        $itemsCollection = $this->helper->getReportedBroken();
        
        foreach ($itemsCollection as $item) {
            $data_array[] = array(
                  'Record_Id' => $item->getRecordId(),
                  'Sku' => $item->getSku(),
                  'Qty' => $item->getQuantity(),
                  'Location' => $item->getLocation(),
                  'Reported_By' => $item->getReportedBy(),
                  'Reported_At' => $item->getReportedAt(),
            );
        }
        if(count($data_array)){
          $status = true;
          $message = "Data Sent";
        }else{
          $status = false;
          $message = "No Broken tiles Reported";
        }
      }catch(\Exception $e){
        $status = false;
        $message = "Please Try Again";
        $error_message = $e->getMessage();
        $this->helper->ErrorLog("","Get Reported Broken Tiles",$error_message,"ReportedBrokenTiles");
      }
      
      $final_array = array("status" => $status,"message" => $message,"reported_tiles" => $data_array);
      echo json_encode($final_array ?? []);die();
    }

    public function completeBrokenTiles($record_id = 0,$user = "",$fromAdmin = 0){
      if(!$record_id){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $record_id = $params['record_id'];
      }
      
      $completed_date = $this->datetime->gmtDate();
      $ModelError = 0;
      $FcmError = 0;
      try{
        $this->connection = $this->_resource->getConnection();
        $this->connection->beginTransaction();

        $this->BrokenModel = $this->_skuHistoryRepository->getById($record_id);
        $this->BrokenModel->setActionTaken("Write Off");
        $this->BrokenModel->setResolvedBy($user);
        $this->BrokenModel->setResolvedAt($completed_date);
        $this->BrokenModel->setProblemStatus(\TM\AndroidServices\Helper\Data::PROBLEM_STATUS_RESOLVED);

        $savedObj = $this->_skuHistoryRepository->save($this->BrokenModel);

        $FcmData = array("action" => "remove","record_id" => $record_id , "resolved_by" => $user , "resolved_at" => $completed_date);
        $FcmSent = $this->helper->sendNotification($FcmData); // send FCM notification for broken tile completed by user
        
        if($FcmSent === true){ 
          $this->connection->commit();
          $status = true;
          $message = "Completed sucessfully";
        }else{
          $FcmError = 1;
          $error_message = $FcmSent;
          $message = "FCM Error, Please Try Again";
          $this->connection->rollBack();
        }
           
      }catch(\Exception $e){
        $this->connection->rollBack();
        $ModelError = 1;
        $error_message = $e->getMessage();
        $message = "Model Error, Please Try Again";
      }
      
      if($FcmError || $ModelError){
        $this->helper->ErrorLog($user,"Complete Broken Tile",$error_message,"record id:".$record_id);
      }
      $final_array = array("status" => $status,"message" => $message);
      if($fromAdmin){
        $json = $this->json->serialize($final_array);
        return $json;
      }
      
      echo json_encode($final_array ?? []);die();
    }

    public function orderChecking()
    {
      $params = $this->request->getBodyParams();
      $orderno = trim($params['orderno']);
      $blindCheck = $this->helper->getBlindCheckConfig();  
      $datetime = $this->helper->getCurrentDate('Y-m-d H:i:s');

        $warehouse = '';
        if ($this->pythonHelper->isEnablePython()) {
            $device_ip = '';
            if (isset($params['device_ip'])) {
                $device_ip = $params['device_ip'];
            }
            $warehouse = $this->pythonHelper->getWareHouse($device_ip);
            $order = $this->order->loadByIncrementId($orderno);
            $warehouseItems = $this->pythonHelper->getFilteredOrderItems($order,$warehouse);

            if ($warehouse != 0 && $warehouse != '' && $warehouseItems['count'] == 0) {
                $messageError = "No Items of Current WareHouse!";
                $final_array = array("status" => false,"message" => $messageError);
                echo json_encode($final_array); exit();
            }
            $allItems = $order->getAllItems();
            foreach ($allItems ??[] as $item) {
                if ($warehouse != 0 && $warehouse != '' && $warehouse == $item->getWarehouse()) {
                    if ($item->getChecked() == 1) {
                        $final_array = array(
                            'status'=>false,
                            'message'=>'Items For This WareHouse Already Checked'
                        );
                        echo json_encode($final_array); exit();
                    }
                }
            }
        }

 
      try{
        $substring = "SC";
        if (stripos($orderno, $substring) === 0) {
          if(isset($params['order_type'])){
           $order_type = $params['order_type'];
           if (preg_match('/SC/', $order_type)) {
            $PalletData = $this->helper->FormatPalletData($orderno,$blindCheck,$datetime);  
              if(gettype($PalletData) != "string"){
                $final_array = array("status" => true,"message" => "Data Sent","data" => $PalletData);
              }else{
                  $final_array = array("status" => false,"message" => $PalletData);
              }
         }else{
          $messageError = "Pallet cannot be scanned on ".$order_type;
          $final_array = array("status" => false,"message" => $messageError);
         }
         echo json_encode($final_array);
         exit();
       }  
      }
        if ($this->helper->getConfiguration('tablet_config/general/tablet_complete'))
        {
            $orderCollection = $this->_palletCollection->create()->getCompletedOrders($orderno);
            $OrderData = $orderCollection->getFirstItem()->getData();
        } else {
            $order = $this->order->loadByIncrementId($orderno);
            $orderType=$this->getOrderType($order->getId());
            if($orderType=="pallet"){
                $orderCollection = $this->_palletCollection->create()->getJoinOrderbyNo($orderno);
                $OrderData = $orderCollection->getFirstItem()->getData();
            }
            else if($orderType=="small"){
                $dhlOrder = $this->_sampleprocessingFactory->create()->getCollection();
                $dhlOrder->addFieldToFilter('op_order_id',$order->getId());
                $orderTable = $this->_resource->getTableName('sales_order');
                $dhlOrder->getSelect()
                    ->join(array('order' => $orderTable), 'main_table.op_order_id =order.entity_id',
                        array('order.status', 'weight' => 'order.weight', 'order.customer_firstname', 'customer_lastname', 'delivery_note',
                            'order.dispatch_date', 'order.increment_id', 'order.created_at', 'order.shipping_date', 'order.shipping_description'
                        ,'order.is_checked'
                        )
                    );
                $OrderData = $dhlOrder->getFirstItem()->getData();
            }
            else{
               $final_array = array("status" => false,"message" => "Order does not have Pallet/DHL Shipping Method");
               echo json_encode($final_array ?? []); die();
            }
            if ($OrderData) {
                $OrderData['end_time'] = '';
            }
        } 
         if ($OrderData){
          $orderProcessingType="";
          if(isset($params['order_type'])){
            $orderProcessingType=$params['order_type'];
          }

            if(!$OrderData['is_checked'] || $orderProcessingType == "loading"|| $orderProcessingType == "receiving"){
              $messageError = "";
              if(isset($params['order_type'])){
                $order_type = $params['order_type'];
                if (preg_match('/receiving/', $order_type)) {
                 
                    if ($OrderData['status'] !== 'order_received') {
                    $messageError = "Order is not received";
                  }
                } else if (preg_match('/loading/', $order_type)) {
                    if (!(preg_match('/in_store/', $OrderData['status']) ) && !(preg_match('/partially_shipped/', $OrderData['status']))) {
                     $messageError = "Order is not in shop";
                  }
                }else{
                  $messageError = "Order type is not valid";
                }
              }
              if($messageError){
                
                $final_array = array("status" => false,"message" => $messageError);
                echo json_encode($final_array);die();
              }

        // If order is unsuccessfully checked, make its items checks = 0 so they can be checked again (unsucc check)
          $OrderItemsData = $this->orderchecking->create()->getCollection()->addFieldToFilter('order_no', $orderno)->addFieldToFilter('status',array('in' => array(1,2)));
          foreach ($OrderItemsData->getData() as $value) {
          $OrderItemsModel = $this->orderchecking->create();
          $OrderItems = $OrderItemsModel->load($value['check_id'],'check_id');
          $OrderItems->setStatus(0);
          $OrderItems->save();
         }
     
          $OrderData['blindcheck']=  $blindCheck;
          $OrderData['datetime']=  $datetime; 
            $order_return_data = $this->helper->FormatOrderData($OrderData,$warehouse);
            if(gettype($order_return_data) != "string"){
                $final_array = array("status" => true,"message" => "Data Sent","data" => $order_return_data);
            }else{
                $final_array = array("status" => false,"message" => $order_return_data);
            }
              
            }else{
                $final_array = array("status" => false,"message" => "Order Already Checked");
            }
        }else{
            $order = $this->order->loadByIncrementId($orderno);
            if($order->getId()){
                $PalletOrderModel = $this->palletOrder->create()->load($order->getId(), 'op_order_id');
            if($PalletOrderModel->getOpOrderId()){
                
                if($order->getStatus() != "picked"){
                    $final_array = array("status" => false,"message" => "Order is not 'Picked' Current Status is " . $order->getStatus());
                }else{
                   $final_array = array("status" => false,"message" => "Order not Available for Checking"); 
                }
                
                
            }else{
                $final_array = array("status" => false,"message" => "Order does not have Pallet/DHL Shipping Method");
            }
            }else{
                $final_array = array("status" => false,"message" => "Order ($orderno) Does Not Exist");
            }
        }

      }catch(exception $e){
        $final_array = array("status" => false,"message" => "Exception: ".$e->getMessage());
      }
      echo json_encode($final_array ?? []);die();
    }

    public function getOrderType($orderId){
        $this->connection = $this->_resource->getConnection();
        $tableName = $this->_resource->getTableName('mb_order_processing_pallet');
        $select = $this->connection->select()
            ->from($tableName)
            ->where('op_order_id = ?', $orderId);
        $row = $this->connection->fetchRow($select);
        if ($row) {
           return "pallet";
        }else {
            $tableName = $this->_resource->getTableName('order_processing_small');
            $select = $this->connection->select()
                ->from($tableName)
                ->where('op_order_id = ?', $orderId);
            $row = $this->connection->fetchRow($select);
            if ($row) {
                return "small";
            }
        }
    }

    public function reporting(){

      $image = $this->request->getFiles();
      if (isset($image['image']['name'])) {
        $params = $this->request->getParams();
      }else{
        $params = $this->request->getBodyParams();
      }

      $user = $params['reported_by'];
      $problem = $params['problem'];
      $orderno = $params['order_no'];
      $sku = $params['sku'];
      $status = $params['status'];
      $note = $params['note'];
      $index = $params['index'];
      $OrderArray=[];

      if (is_array($orderno)) {
        $OrderArray=$orderno;
      }
      else{
        $OrderArray=[$orderno];
        if(isset($params['order_type'])){
          $order_type = $params['order_type'];
          if ((preg_match('/receiving/', $order_type)|| preg_match('/loading/', $order_type)) &&  $status == 2) {
            // dump("this check");
             $Orderstatus = "showroom_problem";
             $this->updateOrderAttribute($orderno,$Orderstatus);
             $history = $this->historyFactory->create();
             $message = "Problem Reported at Showroom in Item: " .$sku. " Problem: ". $problem . " Reported By " . $user . " at ". $order_type;
             $history->setParentId($params['order_id'])
             ->setComment($message)
             ->setIsCustomerNotified(false)
             ->setEntityName('order')
             ->setStatus($Orderstatus);
           $this->historyRepository->save($history);
          }
        }
      }
      $returnArray=[];
      
      foreach($OrderArray as $orderno ){
        $params['order_no']=$orderno;
        try{
            $expediting_data = $this->helper->expeditingExists($orderno,$sku);
            if(isset($expediting_data['status'])){
              $expediting_id = $expediting_data['id']; 
            }else{
              $expediting_id = 0;
            }
            
            $datetime = $this->helper->getCurrentDate('Y-m-d H:i:s');

            $this->connection = $this->_resource->getConnection();
            $this->connection->beginTransaction();
            if(isset($image['image']['name'])){
              $dir = 'orderChecking';
              $ifUploaded = $this->helper->UploadImage($image,$dir);
            }
            if($expediting_id){
                $report_image = '';
                if (isset($image['image']['name'])) {
                    $report_image = $image['image']['name'];
                }

                $return = $this->setStatus($expediting_id,$status,$datetime,$problem,$user,$note,$report_image);
             
              if($return){
                $final_array = array(
                  'status'=>true,
                  'message'=>'Data Added Successfully.',
                  'type'=>$status,
                  'index'=>$params['index']
                );
              }else{
                $final_array = array(
                      'status'=>false,
                      'message'=>'Try Again'
                  );
              }
            }else{
                $params['reported_at'] = $datetime;
                $ifUploaded = 0;

                if (isset($image['image']) && isset($image['image']['name'])) {
                    $params['submitted_image'] = $image['image']['name'];
                }
              
                $model = $this->orderchecking->create();
               
                $model->addData($params)->save();
                $final_array = array(
                    'status'=>true,
                    'message'=>'Data Added Successfully.',
                    'type'=>$status,
                    'index'=>$params['index']
                );
            }
            $this->connection->commit();
          }catch(exception $e){
            $final_array = array(
            'status'=>false,
            'message'=>'Exception: '.$e->getMessage()
            );
            $this->connection->rollBack();
          }
      }
      echo json_encode($final_array ?? []);die();
    }

    public function checked()
    {
      $image = $this->request->getFiles();
      if (isset($image['image']['name'])) {
        $params = $this->request->getParams();
      }else{
        $params = $this->request->getBodyParams();
      }
 
      $orderid = $params['orderid'];
      $order_no = $params['order_no'];
      $user = $params['user'];
      $start_time = $params['start_time'];
      $check_status = $params['check_status'];
      $problem = $params['problem'];
      $items = [];

    
      if(isset($params['items'])){
         $itemsParam = $params['items'];
         $items = json_decode($itemsParam);
      }
      $end_time = $this->helper->getCurrentDate('Y-m-d H:i:s');

      $orderDetails['order_no']          = $order_no;
      $orderDetails['checked_by']        = $user;
      $orderDetails['check_status']      = $check_status;
      $orderDetails['problem']           = $problem; 
      $orderDetails['check_start_time']  = $start_time;
      $orderDetails['check_end_time']    = $end_time;

        $warehouse = '';
        if ($this->pythonHelper->isEnablePython()) {
            $device_ip = '';
            if (isset($params['device_ip'])) {
                $device_ip = $params['device_ip'];
            }
            $warehouse = $this->pythonHelper->getWareHouse($device_ip);
        }
       
      try{
        $connection = $this->_resource->getConnection();
        $connection->beginTransaction();

        $substring = "SC";
          if (stripos($order_no, $substring) === 0) {
            $palletCollection = $this->mbPalletNumbers->create();
            $palletCollection->addFieldToFilter('pallet_number', $order_no);
            $palletModel = $palletCollection->getFirstItem();

            if ($palletModel->getId()) {
               $palletModel->setPalletStatus(1);
                $palletOrders = $palletModel->getOrders();
                $unserializedOrders = unserialize($palletOrders);
                foreach ($unserializedOrders as $orderno) {
                  $order = $this->ordermodel->create()->loadByIncrementId($orderno);
                  $final_array = $this->processNormalCheck($order, $check_status, $user, $orderDetails,$warehouse);
                }
                $palletModel->save();
              $connection->commit();
              }
              else{
                $final_array = array(
                  'status'=>false,
                  'message'=>'Pallet Not Found'
              );
              $connection->rollBack();  

              }
                echo json_encode($final_array ?? []);die();
          }
        $PalletOrderModel = $this->palletOrder->create()->load( $orderid, 'op_order_id' );
        if(count($PalletOrderModel->getData())){
          if(isset($params['order_status'])){

            $order_type = $params['order_status'];
            $orderDetails['order_type'] = $order_type;
  
            $order = $this->orderRepository->get($orderid);
            $status = $order->getStatus();

            if (preg_match('/receiving/', $order_type)) {
              $final_array = $this->processReceiving($params, $PalletOrderModel, $status,$orderDetails);
            }else if (preg_match('/loading/', $order_type)) {
              $final_array = $this->processLoading($params, $PalletOrderModel, $status, $image, $order, $items, $end_time);
            }

          }else{
           $order = $this->ordermodel->create()->load($orderid);
           $final_array = $this->processNormalCheck($order, $check_status, $user, $orderDetails,$warehouse);
           $connection->commit();
          }
        }else{
          $order = $this->ordermodel->create()->load($orderid);
          $final_array = $this->processNormalCheck($order, $check_status, $user, $orderDetails,$warehouse);
          $connection->commit();
        //   $final_array = array(
        //     'status'=>false,
        //     'message'=>'Order Not Found'
        // );
        // $connection->rollBack();   
      }
      }catch(\Exception $e){
        $final_array = array(
              'status'=>false,
              'message'=>'Exception: '.$e->getMessage()
          );
        $connection->rollBack();
      }
      echo json_encode($final_array ?? []);die();
    }

    public function setStatus($expediting_id,$status,$datetime,$problem,$user,$note = "",$image = ""){
      try{
        $model = $this->orderchecking->create();
        $ExpModel = $model->load($expediting_id);
        $ExpModel->setStatus($status);
        if($status == 2){
          $ExpModel->setSubmittedImage($image);
          $ExpModel->setProblem($problem);
          $ExpModel->setNote($note);
        }

        $ExpModel->setReportedBy($user);
        $ExpModel->setReportedAt($datetime);
        if($ExpModel->save()){
          return true;
        }else{
          return false;
        }
      }catch(exception $e){
        return false;
      }
    }

    public function allusers(){
      $allusers = $this->scopeConfig->getValue('config_section/tm_palletqueue/users',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      if($allusers){
        $users = explode(',', $allusers);
        $data = array();
        if(count($users))
        {
          $data['status']=true;
          $data['message']='Users found';
          $data['users'] = $users;
        }
        else
        {
          $data['status']=false;
          $data['message']='No User found';
          $data['users'] = $users;
        }
      }else{
        $data['status']=false;
        $data['message']='No User found';
        $data['users'] = [];
      }
      
      echo json_encode($data ?? []);die();
    }

    public function checkPrinters(){
      $printing_data = $this->helper->printZpl('','',1); //Get Available printers list from configuration

      if($printing_data['send_printer']){
        $return = array('status' => true , 'message' => 'All Available printers' , "printer" => $printing_data['all_printers']);
      }else{
        $return = array('status' => false , 'message' => 'No Printers Found, Please speak to IT Department' , "printer" => $printing_data['all_printers']);
      }
      echo json_encode($return ?? []);die();
    }

    public function pickerList()
    {
        $pickerListArray = [];
        $collection = $this->_loginHistory->create()->getCollection()->addFieldToFilter('tab_order_status', 2);
        if (count($collection->getData())) {
            foreach ($collection as $data) {
                $pickerListArray[] = [
                    'user' => $data['user'],
                    'tab_order_id' => $data['tab_order_id'],
                    'log_id' => $data['log_id']
                ];
            }
            $status = true;
            $message = 'Picker List';
        } else {
            $status = false;
            $message = 'Picker List Empty';
        }

        $return = array("status" => $status , "message" => $message , "list" => $pickerListArray);
        echo json_encode($return ?? []);exit;
    }
    public function getauthorizedusers()
    {
      $authorizedusers = $this->helper->getAuthorizedUsersConfig(); //Get avaiable user list from configuration
        $authorizedusers = strtolower($authorizedusers);
       if($authorizedusers){
        $authorizedusers = preg_split ("/\,/", $authorizedusers); 
        $return = array('status' => true , 'message' => 'All authorized users are' , "users" => $authorizedusers);
      }else{
        $return = array('status' => false , 'message' => 'No authorized user Found');
      }
      echo json_encode($return ?? []);die();
   
    }
    public function getSKUDetails()
    {
        $response = array();
        $params = $this->request->getBodyParams();
        $sku = $params['sku'];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $producInterface = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface');
        try{
            $product = $producInterface->get($sku);
        }
        catch(Throwable $e){
            $response["status"] = false;
            $response["message"] = "Product not found";
            echo json_encode($response ?? []);die();
        }
        $response["status"] = true;
        $response["description"] = $product->getName();
        // $response["name"] = $product->getName();
        echo json_encode($response ?? []);die();
    }

    public function orderPicked()
    {
        $response = array();
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $orderid = $params['orderid'];
        $lockStatus = $params['is_locked'];
        $order = $this->order->loadByIncrementId($orderid);
        $dhlOrder = $this->_sampleprocessingFactory->create()->load($order->getId(),'op_order_id');
        $dhlOrder->setIsLocked($lockStatus);
        $dhlOrder->save();
        $FcmData = array("locked" => $lockStatus ,"type"=>"dhl","reported_by" => $user ,   "order_id" => $orderid);
        $FcmSent = $this->helper->sendNotificationDHLOrder($FcmData);
        if($FcmSent === true){
            $response["status"] = true;
            $response["user"] = $user;
            $response["orderid"] = $orderid;
            $response["is_locked"] = $lockStatus;
        }
        else{
            $response["status"] = false;
            $response["error"] = $FcmSent;
        }
        echo json_encode($response ?? []);die();
    }

    public function UserAuthentication()
    {
        $params = $this->request->getBodyParams();
        $user_id = $params['user_id'];
        $is_admin = $params['is_admin'];

        $authorized_users = $this->scopeConfig->getValue('tablet_config/general/authorized_users',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $exp_users = explode(',', $authorized_users);

        $allusers = $this->scopeConfig->getValue('tablet_config/general/easywms_users',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $allDataArray = json_decode($allusers ?? "", true);

        foreach($allDataArray ?? [] as $data){

            $config_id = $data['card_data'];

            if ($config_id == $user_id) {
                $user = $this->userFactory->create()->load($data['card_user']);
                $user_name = $user->getUserName();

                if ($is_admin) {
                    if(in_array($user_name, $exp_users)){
                        $return = array('status' => true , 'user' => $user_name);
                        echo json_encode($return ?? []);die();
                    } else {
                        $return = array('status' => false , 'message' => 'No Authorized User Found');
                        echo json_encode($return ?? []);die();
                    }
                }

                $return = array('status' => true , 'user' => $user_name);

                echo json_encode($return ?? []);die();
            }
        }

        $return = array('status' => false , 'message' => 'No Authorized User Found');

        echo json_encode($return ?? []);die();
    }

    public function updateOrderAttribute($orderId,$status)
    {
        $connection  = $this->_resource->getConnection();
        try {
            $where = ['increment_id = ?' => (int)$orderId];
            $whereforGrid = ['increment_id = ?' => (int)$orderId];
            $data = ["status"=>$status];
            $tableName = $connection->getTableName("sales_order");
            $tableTwo = $connection->getTableName("sales_order_grid");
            $connection->update($tableName, $data, $where);
            $connection->update($tableTwo, $data, $whereforGrid);
        }catch (\Exception $exception){
            return $exception->getMessage();
        }
    }
      public function processReceiving($params, $PalletOrderModel, $status,$orderDetails)
      {
        $connection  = $this->_resource->getConnection();
        // $orderDetails
       if (preg_match('/order_received/', $status)) {
          $PalletOrderModel->setTabletStatus('Order Received on inshop-Checked');
          $PalletOrderModel->save();
   
          $status = "in_store";
          $history = $this->historyFactory->create();
          $this->updateOrderAttribute($params['order_no'],$status);
          $message = "";
          $message = $message . " Order Received at " . $params['showroom'] . " showroom By " . $params['user'];
   
          $history->setParentId($params['orderid'])
              ->setComment($message)
              ->setIsCustomerNotified(false)
              ->setEntityName('order')
              ->setStatus($status);
            try {
                $this->historyRepository->save($history);
                $final_array = array(
                  'status'=>true,
                  'message'=>$message
              );
              $model = $this->orderchecks->create();
              $model->addData($orderDetails)->save();
              $connection->commit();
            } catch (Exception $exception) {
                $final_array = array(
                  'status'=>false,
                  'message'=>'Receiving Failed'
              );
              $connection->rollBack();
            }
          }else{
           $final_array = array(
            'status'=>false,
            'message'=>'Receiving Order is not Received'
          );
          $connection->rollBack();
        }
        return $final_array ?? '';
      }
        public function processLoading($params, $PalletOrderModel, $status, $image, $order, $items, $end_time)
        {
        $connection  = $this->_resource->getConnection();

          if ((preg_match('/in_store/', $status) ) || (preg_match('/partially_shipped/', $status))) {

            if(isset($image['image']['name'])){
              $dir = 'orderSignature';
              $ifUploaded = $this->helper->UploadImage($image,$dir);
            }
            $signature_image = '';
            if (isset($image['image']['name'])) {
                $signature_image = $image['image']['name'];
            }

             $shipmentGenerated = $this->tabletHelper->GenerateShipment($order, $params['user'], $params['showroom'], $items);
             if ($shipmentGenerated == "complete") {
              // $PalletOrderModel->setIsChecked(1);
                // $order->setIsChecked(1);
                // $order->save();

                $PalletOrderModel->setTabletStatus('Loaded into User Vehicle-Checked');
                $PalletOrderModel->save();

                $model = $this->orderchecks->create()->load($params['order_no'], 'order_no');
                if (!$model->getCheckId()) {
                  $model->setData('order_no', $params['order_no']);
                }
                $model->setOrderType($params['order_status']);
                $model->setCheckStatus($params['check_status']);
                $model->setCheckedBy($params['user']);
                $model->setCheckEndTime($end_time);
                $model->setSignature($signature_image);
                $model->save();

                $final_array = array(
                 'status'=>true,
                 'message'=>"Order is Completed"
                );
                $connection->commit();

              }
              else if ($shipmentGenerated == "partially_shipped") {
                $PalletOrderModel->setTabletStatus('Partial Order Loaded into User Vehicle-Checked');
                $PalletOrderModel->save();

                $model = $this->orderchecks->create()->load($params['order_no'], 'order_no');

                if (!$model->getCheckId()) {
                  $model->setData('order_no', $params['order_no']);
                }   
                $model->setOrderType($params['order_status']);              
                $model->setCheckedBy($params['user']);
                $model->setCheckEndTime($end_time);
                $model->setSignature($signature_image);
                $model->save();

                $final_array = array(
                  'status'=>true,
                  'message'=>"Order is Partially Shipped"
                 );
                 $connection->commit();
              }
              else{
                 $final_array = array(
                  'status'=>false,
                  'message'=>"Shipment Failed"
                 );
                 $connection->rollBack();
                }
          }else{
             $final_array = array(
               'status'=>false,
               'message'=>'Loading Order is not in Shop'
             );
             $connection->rollBack();
          }
          return $final_array ?? '';
        }
        public function processNormalCheck($order, $check_status, $user,$orderDetails,$warehouse='')
        {
          // $PalletOrderModel = $this->palletOrder->create()->load($order->getId(), 'op_order_id' );
          $orderType=$this->getOrderType($order->getId());
          $allItems = $order->getAllItems();

          if($orderType){
              if($orderType=="pallet"){
                $PalletOrderModel = $this->palletOrder->create()->load($order->getId(), 'op_order_id' );
                $PalletOrderModel->setTabletStatus('Completed on Tablet-Checked');
                  $PalletOrderModel->save();
              }
            $check_status = (int) $check_status;
  
            $blindCheck = $this->helper->getBlindCheckConfig();  
            if($blindCheck){
                if($check_status == 0){
                    $order->setIsChecked(1);
                    $order->setStatus('checked');
                    $order->addStatusHistoryComment('Order Checked By '.$user);
                    $order->save();
                } else {
                    $order->setIsChecked(0);
                    $order->save();
                }
           }else{

                if ($this->pythonHelper->isEnablePython()) {
                    $allItemsChecked = true;
                    foreach ($allItems ??[] as $item) {
                        if ($warehouse != 0 && $warehouse != '' && $warehouse == $item->getWarehouse())
                        {
                            $item->setChecked(1);
                            $item->save();
                            $order->addStatusHistoryComment('SKU '.$item->getSku().' Checked By '.$user);
                            $order->setStatus('partially_checked');
                        } else {
                            if ($warehouse == 0 && $item->getChecked() == 1) {
                                $final_array = array(
                                    'status'=>false,
                                    'message'=>'Item Already Checked'
                                );
                                return $final_array ?? '';
                            } 
                        }
                    }

                    if ($warehouse != 0){
                        foreach ($allItems ??[] as $item) {
                            $isChecked = $item->getChecked();

                            if (!$isChecked) {
                                $allItemsChecked = false;
                                break;
                            }
                        }
                    }
                    if ($allItemsChecked) {
                        $order->setIsChecked(1);
                        $order->setStatus('checked');
                        $order->addStatusHistoryComment('Order Checked By '.$user);
                        $order->save();
                    } else {
                        // $order->setStatus('partially_checked');
                        $order->save();
                    }
                } else {
                    $order->setIsChecked(1);
                    $order->setStatus('checked');
                    $order->addStatusHistoryComment('Order Checked By '.$user);
                    $order->save();
                }

           }
            $model = $this->orderchecks->create();
            $model->addData($orderDetails)->save();
  
            $final_array = array(
                'status'=>true,
                'message'=>'Checked Successfully'
            );
            // $connection->commit();
          }else{
            $final_array = array(
                    'status'=>false,
                    'message'=>'Order Not Found'
                );

          } 
           return $final_array ?? '';
        }

    public function GetWoodenPanels(){

      $collection = $this->orderCollectionFactory->create();
      $collection->addFieldToSelect('*');
      $collection->addFieldToFilter('is_wood_panel', 1);
      $collection->addFieldToFilter('status', ['in' => ['processing','pick_created','a_picking']]);
      
      //$collection->addFieldToFilter('shipping_description', ['nlike' => "%Collection%"]);
      
      $currentDHL = $this->dhlIndex->getSmallOrderDispatchDate();
      $collection->addFieldToFilter('dispatch_date', ['lteq' => $currentDHL]);
      $itemData = [];
      $LabelsData = [];

     foreach ($collection as $order) {
     
      $woodModel = $this->woodFactory->create();
      $woodModel->load($order->getEntityId(), 'wood_order_id');
      
      $totalItems = 0;
      $qtyArray=array();
         $items = $order->getAllItems();
         foreach ($items as $item) {
          $product = $item->getProduct();
          
            $attributeName = $this->woodHelper->getAttributeSetName($item->getSku());
            
             if ($attributeName != 'Wood Panels') {
                 continue;
             }

             $is_sample = $this->woodHelper->isSample($item);
             if($is_sample){
                continue;
             }
             
             $itemId = $item->getProductId();
             if(isset($qtyArray[$itemId])){
                 $qtyArray[$itemId]=$qtyArray[$itemId]+intval($item->getQtyOrdered());
             }else{
                 $qtyArray[$itemId]=intval($item->getQtyOrdered());
             }
             $qtyOrdered = $qtyArray[$itemId]; // Get the quantity ordered for the item
             $scan_count = intval($item->getPickCount());

             if(in_array($item->getSku(),["802114","802108"])){
                 $totalItems =$totalItems+1;
             }else{
                 $boxQty = 2;
                 $fullboxes = floor($qtyOrdered / $boxQty);
                 $lossepieces = $qtyOrdered % $boxQty;
                 $totalItems = $totalItems + $fullboxes + $lossepieces;
             }
             if ($qtyOrdered == $scan_count) {
               continue;
              }

             if (!isset($itemData[$itemId])) {
                 $itemData[$itemId] = [
                     'item_id' => $itemId,
                     'product_id' => $item->getProductId(),
                     'product_name' => $item->getName(),
                     'product_sku' => $item->getSku(),
                     'location' => $product->getStockLocation(),
                     'order_data' => [
                       [
                        'order_number' => $order->getIncrementId(),
                        'qty_ordered' => $qtyOrdered,
                        'scan_count' => $scan_count,
                        'order_id' => $order->getEntityId(),
                        'store_id' => $order->getStoreId()
                       ]
                    ]
                 ];

             } else {
                 $itemData[$itemId]['order_data'][] = [
                'order_number' => $order->getIncrementId(),
                'qty_ordered' => $qtyOrdered,
                'scan_count' => $scan_count,
                'order_id' => $order->getEntityId(),
                'store_id' => $order->getStoreId()
              ];
             }
         }

           if (strpos( $order->getShippingMethod(), "Own_Transport") !== false) {
            $shipping_method = "Own Transport";
           }else if(strpos( $order->getShippingMethod(), "DPD") !== false) {
            $shipping_method = "DPD";
           }else{ 
            $shipping_method = $order->getShippingMethod();
           }

         $LabelsData[] = [
          'order_number' => $order->getIncrementId(),
          'total_labels' => $totalItems,
          'current_count' => $woodModel->getCount(),
          'shipping_method' => $shipping_method,
        ];
       }
       $ProductData = [];

       foreach ($itemData as $item) {
        $boxQTY = 2;
        if(in_array(trim($item['product_sku']),["802114","802108"])){
            $boxQTY = 0;
           }
       $ProductData[] = [
        'product_name' => $item['product_name'],
        'product_sku' => $item['product_sku'],
        'location' => $item['location'],
        'box_qty' => $boxQTY,        
        'order_data' => $item['order_data']
       ];

      }
      if($ProductData){
          $final_array = array("status" => true,"message" => "Returning Wooden Pallets","data" => $ProductData, "labelsData" => $LabelsData);
      }else{
        $final_array = array("status" => false,"message" => "No Wooden Pallets Found");
      }
        echo json_encode($final_array ?? []);die();
      }

      public function CheckWoodenPanels(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $order_id = $params['order_id'];
        $shipping_method = $params['shipping_method'];
        $count = $params['count'];
        $incrementId = $params['incrementId'];
        $store_id = $params['store_id'];
        $is_completed = $params['is_completed'];
        $sku = $params['sku'];
        $sku_count = $params['sku_count'];
        $total_labels = $params['total_labels'];
          $order = $this->ordermodel->create()->load($order_id);
        
        
        $enable_panther = $this->scopeConfig->getValue('small_order_processing/panther/panther_shipping',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if(preg_match("/\bPANTHER\b/i", $order->getShippingDescription()??"") || $enable_panther){
            $enable_panther = 1;
        }else{
            $enable_panther = 0;
        }
        
        if(preg_match("/\bCollection\b/i", $order->getShippingDescription()??""))
        {
            $directory = $this->localHelper->getLocalDirectory( "owntransportlabels", $store_id );
            $fileName = $incrementId . ".txt";
            $file = $directory . '/' . $fileName;
          
            if (!file_exists($file)) {
                $woodModel = $this->woodFactory->create();
                $woodModel->load($order_id, 'wood_order_id');
                if (!$woodModel->getId()) {
                    $woodModel->setData('wood_order_id', $order_id);
                    $woodModel->setQueueName("Collection");
                    $woodModel->setWoodIncrementId($incrementId);
                }
                if ($woodModel->getConsingmentId() === null) {
                    $woodModel->setConsingmentId($incrementId);
                    $woodModel->setWoodProcessedBy($user);
                } 
            
                $zpl = $this->helperItemLabel->smallCollectionLabel($order,1,1);
                $this->helper->generateZPL($zpl,$order);
                $woodModel->save();
            }
            if (file_exists($file)) {
                $error = $this->localHelper->sendDataForDirectPrint(file_get_contents($file),$store_id,"dpd_label",$incrementId,false,$user);
                if ($error){
                    $return = array("status" => false,"message" => "Printer Error",'refresh' => false);
                    echo json_encode($return ?? []);exit;
                } else {
                    $status = 'a_picking';
                    $message = "Order is being picked by $user on Handheld ";
                    $this->helper->updateOrderComment($status,$order_id,$message);
            
                    $this->salesItems->updatePickCount($order_id, $sku, $sku_count);
                    $woodModel = $this->woodFactory->create();
                    $woodModel->load($order_id, 'wood_order_id');
                    $woodModel->setData('count', $count);
                    $woodModel->save();
                    $return = array("status" => true,"message" => "Item Picked and printed Successfully", 'refresh' => false);
                }
                if($is_completed && ($total_labels == $count)){   
                    $return = $this->helper->GenerateWoodenOrderShipment($order_id,$user);
                } 
            }else{
                $return = array("status" => false,"message" => "Shipping Label Doesnot exits $fileName",'refresh' => false);
            }
        }
        elseif ( $shipping_method != "Own Transport") {
            $consignmnt =  $this->helper->GenerateDPDWoodenConsignment($order_id,$user,$enable_panther);
            if (isset($consignmnt)) {
                echo json_encode($consignmnt ?? []);exit;
            }

            if ($enable_panther) {
                $directory = $this->localHelper->getLocalDirectory( "pantherlabels", $store_id );
                $fileName = $incrementId . "/" . $incrementId. "-" .$count. ".prn";
            } else {
                $directory = $this->localHelper->getLocalDirectory( "dpdshippinglabels", $store_id );
                $fileName = $incrementId . "/" . $incrementId. "-" .$count. ".pdf";
            }
            $file = $directory . '/' . $fileName;
            if (file_exists($file)) {
                $error = $this->localHelper->sendDataForDirectPrint(file_get_contents($file),$store_id,"dpd_label",$incrementId,false,$user);
                if ($error){
                    $return = array("status" => false,"message" => "Printer Error",'refresh' => false);
                    echo json_encode($return ?? []);exit;
                }else {
                    $this->salesItems->updatePickCount($order_id, $sku, $sku_count);
                    $woodModel = $this->woodFactory->create();
                    $woodModel->load($order_id, 'wood_order_id');
                    $woodModel->setData('count', $count);
                    $woodModel->save();
                    $return = array("status" => true,"message" => "Item Picked and printed Successfully", 'refresh' => false);
                }
            } else {
                $return = array("status" => false,"message" => "Shipping Label Doesnot exits $fileName",'refresh' => false);
                echo json_encode($return ?? []);exit;
            }

            if($is_completed && ($total_labels == $count)){  
                $return = $this->helper->GenerateWoodenOrderShipment($order_id,$user,$enable_panther);
            }
            echo json_encode($return ?? []);exit;
        } else {
            $directory = $this->localHelper->getLocalDirectory( "owntransportlabels", $store_id );
            $fileName = $incrementId . ".txt";
            $file = $directory . '/' . $fileName;
          
            if (!file_exists($file)) {
                $woodModel = $this->woodFactory->create();
                $woodModel->load($order_id, 'wood_order_id');
                if (!$woodModel->getId()) {
                    $woodModel->setData('wood_order_id', $order_id);
                    $woodModel->setQueueName($shipping_method);
                    $woodModel->setWoodIncrementId($incrementId);
                    $woodModel->setDpdShipmentId($incrementId);
                }
                if ($woodModel->getConsingmentId() === null) {
                    $woodModel->setConsingmentId($incrementId);
                    $woodModel->setWoodProcessedBy($user);
                } 
            
                $zpl = $this->helperItemLabel->ownTransportLabel($order,1,1);
                $this->helper->generateZPL($zpl,$order);
                $woodModel->save();
        
            }
            if (file_exists($file)) {
                $error = $this->localHelper->sendDataForDirectPrint(file_get_contents($file),$store_id,"dpd_label",$incrementId,false,$user);
                if ($error){
                    $return = array("status" => false,"message" => "Printer Error",'refresh' => false);
                    echo json_encode($return ?? []);exit;
                }else {
                    $status = 'a_picking';
                    $message = "Order is being picked by $user on Handheld ";
                    $this->helper->updateOrderComment($status,$order_id,$message);
                    
                    $this->salesItems->updatePickCount($order_id, $sku, $sku_count);
                    $woodModel = $this->woodFactory->create();
                    $woodModel->load($order_id, 'wood_order_id');
                    $woodModel->setData('count', $count);
                    $woodModel->save();
                    $return = array("status" => true,"message" => "Item Picked and printed Successfully", 'refresh' => false);
                }
                if($is_completed && ($total_labels == $count)){   
                    $return = $this->helper->GenerateWoodenOrderShipment($order_id,$user,$enable_panther);
                } 
            }else{
                $return = array("status" => false,"message" => "Shipping Label Doesnot exits $fileName",'refresh' => false);
            }
        }
        echo json_encode($return ?? []);exit;
    }

    public function ReprintWoodenPanels(){
        $params = $this->request->getBodyParams();
        $count = $params['count'];
        $incrementId = $params['incrementId'];
        $shipping_method = $params['shipping_method'];
        $store_id = $params['store_id'];
        $user = $params['user'];
        $order = $this->ordermodel->create()->loadByIncrementId($incrementId);
        $enable_panther = $this->scopeConfig->getValue('small_order_processing/panther/panther_shipping',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        
        if(preg_match("/\bPANTHER\b/i", $order->getShippingDescription()??"") || $enable_panther){
            $enable_panther = 1;
        }else{
            $enable_panther = 0;
        }
        

        if ( $shipping_method == "Own Transport") {
            $directory = $this->localHelper->getLocalDirectory( "owntransportlabels", $store_id );
            $fileName = $incrementId . ".txt";
        } elseif($enable_panther) {
            $directory = $this->localHelper->getLocalDirectory( "pantherlabels", $store_id );
            $fileName = $incrementId . "/" . $incrementId. "-" .$count. ".prn";
        } else {
            $directory = $this->localHelper->getLocalDirectory( "dpdshippinglabels", $store_id );
            $fileName = $incrementId . "/" . $incrementId. "-" .$count. ".pdf";
        }
        $file = $directory . '/' . $fileName;

        if (file_exists($file)) {
            $error = $this->localHelper->sendDataForDirectPrint(file_get_contents($file),$store_id,"dpd_label",$incrementId,false,$user);
            if($error){
                $return = array("status" => false,"message" => "Printer Error");
            } else {
                $return = array("status" => true,"message" => "Printed Successfully");
            }

        } else {
            $return = array("status" => false,"message" => "Shipping Label Doesnot exits $fileName");
        }
        echo json_encode($return ?? []);
        exit;
    }
}