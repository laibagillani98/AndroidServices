<?php 

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TM\AndroidServices\Model;

use mysql_xdevapi\Exception;
use TM\AndroidServices\Helper\TabletQueue;
use TM\AndroidServices\Helper\Data;
use Dev14\Replacements\Model\ReplacementsFactory;
use TM\EasyWms\Helper\NewConfig;
use TM\Sampleprocessing\Helper\DHL\Data as DhlHelper;
use TM\Sampleprocessing\Controller\Adminhtml\Order\Complete;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

class PalletQueueServices implements \TM\AndroidServices\Api\PalletQueueServicesInterface
{
    public $request;
    const ORDER_TABLE = 'sales_creditmemo';
    const RETURN_TABLE = 'return_record_app';
    
    public function __construct(
        \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory $palletCollection,
        \TM\AndroidServices\Model\ResourceModel\TabletMessages\CollectionFactory $messagescollection,
        \TM\AndroidServices\Api\SkuHistoryRepositoryInterface $skuHistoryRepository,
        \TM\AndroidServices\Model\TabletMessagesFactory $messagesfactory,
        \TM\AndroidServices\Api\Data\SkuHistoryInterface $skuHistoryinterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \TM\PalletQueue\Model\OrderInvoiceFactory $palletOrder,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteria,
        \Magento\Framework\App\ResourceConnection $resource,
        Data $webserviceHelper,
        \Magento\Framework\Webapi\Rest\Request $request,
        \Magento\Sales\Model\Order $ordermodel,
        TabletQueue $helper,
        \TM\AndroidServices\Model\ReturnOrderFactory $returnOrder,
        \TM\AndroidServices\Model\LLopChecksFactory $LLopChecks,
        \TM\AndroidServices\Model\OtherChecksFactory $OtherChecks,
        ReplacementsFactory $replacements,
        \Magento\Sales\Api\Data\OrderInterface $OrderInterface,
        \TM\Sampleprocessing\Model\ResourceModel\Sampleprocessing\CollectionFactory $dhlCollection,
        DhlHelper $dhlHelper,
        Complete $dhlComplete,
        \TM\Base\Helper\Local $localHelper,
        \TM\Sampleprocessing\Block\Adminhtml\Post\Index $dhlIndex,
        \TM\AndroidServices\Model\TabletLoginHistoryFactory $loginHistory,
        \TM\Sampleprocessing\Model\SampleprocessingFactory $sampleprocessingFactory,
        \TM\Sampleprocessing\Helper\NavHelper $NavHelper,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \TM\AndroidServices\Model\MbPalletNumbersFactory $mbPalletNumbers,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository,
        \Magento\User\Model\ResourceModel\User\CollectionFactory $userCollectionFactory,       
        \TM\AndroidServices\Model\SalesItems $salesItems,       
        PsrLoggerInterface $logger,
        \TM\Python\Helper\Data $pythonHelper
    ) {
        $this->_palletCollection = $palletCollection;
        $this->_skuHistoryRepository = $skuHistoryRepository;
        $this->messagescollection = $messagescollection;
        $this->messagesfactory = $messagesfactory;
        $this->_skuHistoryinterface = $skuHistoryinterface;
        $this->_webserviceHelper = $webserviceHelper;
        $this->_palletOrder = $palletOrder;
        $this->scopeConfig = $scopeConfig;
        $this->_resource = $resource;
        $this->datetime = $datetime;
        $this->request = $request;
        $this->helper = $helper;
        $this->_criteria = $criteria;
        $this->ordermodel = $ordermodel;
        $this->returnOrder = $returnOrder;
        $this->replacements = $replacements;
        $this->OrderInterface = $OrderInterface;
        $this->LLopChecks = $LLopChecks;
        $this->dhlHelper = $dhlHelper;
        $this->dhlComplete = $dhlComplete;
        $this->localHelper = $localHelper;
        $this->dhlCollection = $dhlCollection;
        $this->OtherChecks = $OtherChecks;
        $this->dhlIndex = $dhlIndex;
        $this->_loginHistory = $loginHistory;
        $this->_sampleprocessingFactory = $sampleprocessingFactory;
        $this->NavHelper = $NavHelper;
        $this->orderFactory = $orderFactory;
        $this->mbPalletNumbers = $mbPalletNumbers;
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->userCollectionFactory = $userCollectionFactory;
        $this->logger = $logger;
        $this->salesItems = $salesItems;
        $this->pythonHelper = $pythonHelper;
    }

    public function getQueueOrder(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $shipping_method = $params['shipping_method'];
        $reason = $params['reason'];
        $token_type = $params['token_type'];
        $token = $params['token'];
        $combine_pick = $params['combine_pick'];
        //$sent_at = date("Y-m-d H:i:s");
        $shop_orders = $params['shop_order'];
        $tab_unique_id = $params['tab_unique_id'];
        $order_type = $params['order_type'];
        $orderDataforTablet = array();
        $status = false;
        $ifParked = 0;
        $logId = 0;
        $storeId = null;
        $storeName = null;
        $newPallet = null;
        $check = false;

        if(isset($params['current_store'])){
            $current_store =  $params['current_store'];
          }
        $shop_orders = $this->_webserviceHelper->getConfiguration("tablet_config/general/shop_orders");
        $ifTrainingUser = $this->helper->checkTrainingUser($user);
        $ifOpen = $this->helper->onScreenOrder($user,$reason);
        $checkEasyWms = $this->scopeConfig->getValue('tablet_config/general/dhl_batch_easywms');

        $directory = $this->localHelper->getLocalDirectory( "AndroidServices", 1 );
        $fileName = "logincheck.txt";
        $file = $directory . '/' . $fileName;
        $fileExist = is_file($file);

        if ($fileExist) {
            $myfile = fopen($file, "r") or die("Unable to open file!");
            $data = fread($myfile,1);
            if($data == '1'){
                sleep(3);
            }else{
            $myfile = fopen($file, 'wb');
            fwrite($myfile, '1');
            }
            fclose($myfile);
        }else {
            $myfile = fopen($file, 'wb');
            fwrite($myfile, '1');
            fclose($myfile);
         }
       
        $assignedShopUser =  $this->helper->checkShopUser($user);
        $warehouse = '';
        $textwarehouse = '';
        $isEnablePython = $this->pythonHelper->isEnablePython();
        if ($isEnablePython) {
            $device_ip = '';
            if (isset($params['device_ip'])) {
                $device_ip = $params['device_ip'];
            }
            $warehouse = $this->pythonHelper->getWareHouse($device_ip);
            if ($warehouse == 1) {
                $textwarehouse = 'H2';
            } elseif ($warehouse == 2) {
                $textwarehouse = 'PW';
            }
        }

         if($combine_pick){

            //check for login user pause pick
             $dhlCollection = $this->helper->pendingDHLBatch($user);
             if($dhlCollection){
              $Batch_number = $dhlCollection[0]["batch_number"];
              $dhlCollection = $this->helper->pendingUnassignedDHLBatchWith($Batch_number);
              $setUser = $this->_webserviceHelper->ChangeBatchUser($dhlCollection, $user);
              $orderDataforTablet = $this->helper->getCombinePickData($dhlCollection,$user,0,$warehouse);
                if (empty($orderDataforTablet)) {
                    $status = "false";
                    $message ="No Item of Current Warehosue in Batch";
                    $return = array("status" => $status, "message" => $message);
                    echo json_encode($return ?? []);exit;
                }
              $sortedBatch =  $this->helper->getSortedBatch($orderDataforTablet);

                if ($Batch_number && $checkEasyWms){
                    $this->helper->UpdateBatchEasyWms(NewConfig::BATCH_IN_PROGRESS,$Batch_number);
                }

              $status = "true";
              $message ="Successfully returning paused combine pick";
              if ($fileExist) {
                $myfile = fopen($file, "w") or die("Unable to open file!");
                $txt = "0";
                fwrite($myfile, $txt);
                fclose($myfile);
            }
              $return = array("status" => $status, "combine_pick_no" => $Batch_number , "message" => $message, "order" => $sortedBatch, "warehouse" => $textwarehouse);
              echo json_encode($return ?? []);exit;
           }
           //can use this
           //$dhlCollection = $this->helper->pendingUnassignedDHLBatch($user);
           $dhlCollection = false;
           if($dhlCollection){
            $Batch_number = $dhlCollection[0]["batch_number"];
            $setUser = $this->_webserviceHelper->ChangeBatchUser($dhlCollection, $user);
            $orderDataforTablet = $this->helper->getCombinePickData($dhlCollection,$user,0,$warehouse);
            if (empty($orderDataforTablet)) {
                $status = "false";
                $message ="No Item of Current Warehosue in Batch";
                $return = array("status" => $status, "message" => $message);
                echo json_encode($return ?? []);exit;
            }
            $sortedBatch =  $this->helper->getSortedBatch($orderDataforTablet);

            $status = "true";
            $message ="Assigning paused combine pick";
            if ($fileExist) {
                $myfile = fopen($file, "w") or die("Unable to open file!");
                $txt = "0";
                fwrite($myfile, $txt);
                fclose($myfile);
            }
            $return = array("status" => $status, "combine_pick_no" => $Batch_number , "message" => $message, "order" => $sortedBatch, "warehouse" => $textwarehouse );
            echo json_encode($return ?? []);exit;
           }


           $currentDHL = $this->dhlIndex->getSmallOrderDispatchDate();
           if($checkEasyWms){
             $dhlCollection = $this->helper->generateDHLBatch($currentDHL,0,1,false,$warehouse);
             if($dhlCollection && ($dhlCollection->getData())){
//             $batchNo = $dhlCollection->getData()[0]["batch_number"];
                $batchNo = $dhlCollection->getFirstItem()->getBatchNumber();
             $setCombineBatch = $this->_webserviceHelper->SetCombineBatch($dhlCollection->getData(), $user, 0, $textwarehouse);
             $orderDataforTablet = $this->helper->getCombinePickData($dhlCollection->getData(),$user,0,$warehouse);
                if (empty($orderDataforTablet)) {
                    $status = "false";
                    $message ="No Item of Current Warehosue in Batch";
                    $return = array("status" => $status, "message" => $message);
                    echo json_encode($return ?? []);exit;
                }
             $sortedBatch =  $this->helper->getSortedBatch($orderDataforTablet);
             if ($batchNo){
                 $this->helper->UpdateBatchEasyWms(NewConfig::BATCH_IN_PROGRESS,$batchNo);
             }

             $status = "true";
             $message ="Successfully returning combine pick";
             $return = array("status" => $status, "combine_pick_no" => $batchNo , "message" => $message, "order" => $sortedBatch, "warehouse" => $textwarehouse);
             //echo json_encode($return ?? []);exit;

             }else{
              $status = "false";
              $message ="No Batch synced in EasyWMS";
              $return = array("status" => $status, "message" => $message);
              //echo json_encode($return ?? []);exit;
             }
             if ($fileExist) {
                $myfile = fopen($file, "w") or die("Unable to open file!");
                $txt = "0";
                fwrite($myfile, $txt);
                fclose($myfile);
            }
            echo json_encode($return ?? []);exit;
          
           }else{
            //generatig new combine pick
            $dhl_batchlimit = $this->scopeConfig->getValue('tablet_config/general/dhl_batch_limit');
            $dhlCollection = $this->helper->generateDHLBatch ($currentDHL,$dhl_batchlimit,0,false,$warehouse);
            //   sync to nav
            $sales_order_table = $this->_resource->getTableName('sales_order');
            $navCollection = $dhlCollection->join(array('sales_order' => $sales_order_table),
            'main_table.op_order_id = sales_order.entity_id');

            if($dhlCollection->getData()){
              $batchNo = $this->_webserviceHelper->DHLBatch($dhlCollection->getData(), $user);
              $navResponse = $this->NavHelper->sendDataToNav($navCollection->getData(), $batchNo,true);
             //onsuccess it returns null, on error response will be returned
             if($navResponse){
                $status = "false";
                $message = $navResponse;
                if ($fileExist) {
                    $myfile = fopen($file, "w") or die("Unable to open file!");
                    $txt = "0";
                    fwrite($myfile, $txt);
                    fclose($myfile);
                }
                $return = array("status" => $status, "combine_pick_no" => $batchNo , "message" => $message);
                echo json_encode($return ?? []);exit;
              } 
              $setCombineBatch = $this->_webserviceHelper->SetCombineBatch($dhlCollection->getData(), $user, $batchNo, $textwarehouse);
              $orderDataforTablet = $this->helper->getCombinePickData($dhlCollection->getData(),$user,0,$warehouse);
              $sortedBatch =  $this->helper->getSortedBatch($orderDataforTablet);

              $status = "true";
              $message ="Successfully returning combine pick";
              if ($fileExist) {
                $myfile = fopen($file, "w") or die("Unable to open file!");
                $txt = "0";
                fwrite($myfile, $txt);
                fclose($myfile);
            }
              $return = array("status" => $status, "combine_pick_no" => $batchNo , "message" => $message, "order" => $sortedBatch);
              echo json_encode($return ?? []);exit;
             } 
            }
          $status = "false";
          $message ="No order available for Combine pick";
          if ($fileExist) {
            $myfile = fopen($file, "w") or die("Unable to open file!");
            $txt = "0";
            fwrite($myfile, $txt);
            fclose($myfile);
        }
          $return = array("status" => $status, "message" => $message);
          echo json_encode($return ?? []);exit;
        }
        if ($ifOpen['count'] && empty($assignedShopUser)) {
            $status = false;
            $message = "Order Already assingned on ".$ifOpen['workstation'];
        }else{
            try{
                $connection = $this->_resource->getConnection();
                $connection->beginTransaction();
                if ($shipping_method == "DHL") {

                    $dhlCollection = $this->dhlNoCombinePick();

                    if (count($dhlCollection->getData())) {
                        $logCreated = false;
                        foreach ($dhlCollection->getData() as $topOrderData) {

                            $order_id = $topOrderData['op_order_id'];

                            $ifSet = $this->helper->setOrderOnScreen($order_id,TabletQueue::TABLET_QUEUE_STATUS_ONSCREEN,"In Process",$user,'',$shipping_method);
                            if($logCreated == false){
                                $logId = $this->helper->logOrderHistory(null,$user,$tab_unique_id,TabletQueue::TABLET_QUEUE_STATUS_ONSCREEN,$reason,$token,$token_type,"DHL");
                                $logCreated = true;
                            }

                            if ($ifSet && $logId) {

                                if ($topOrderData['in_queue'] == TabletQueue::TABLET_QUEUE_STATUS_WAS_PARKED_ORDER) {
                                    $ifParked = 1;
                                    $orderDataforTablet = $this->helper->getParkedOrderData($order_no);
                                }else{
                                    $orderDataforTablet[] = $this->helper->getOrderData($topOrderData,$user);
                                }
                                
                                if(!$orderDataforTablet){
                                    $connection->rollBack();
                                    $status = false;
                                    $message = "Error Please Try Again 1";
                                }else{
                                    $status = true;
                                    $message = "Data Sent";
                                    $this->tabletOrderData($user);
                                    $connection->commit();
                                }

                            }else{
                                $connection->rollBack();
                                $status = false;
                                $message = "Error Please Try Again 2";
                            }
                        }
                    } else{
                        $status = false;
                        $message = "No Order`s in Queue";
                    }
                    if ($fileExist) {
                        $myfile = fopen($file, "w") or die("Unable to open file!");
                        $txt = "0";
                        fwrite($myfile, $txt);
                        fclose($myfile);
                    }
                    $return = array("status" => $status, "message" => $message , "order" => $orderDataforTablet, "parked" => $ifParked , "loginid" => $logId );
                    echo json_encode($return ?? []);exit;

                } else{
                    if(isset($current_store)){ 
                        if(in_array($current_store, $assignedShopUser)){
                       //checkpython
                        $collection = $this->helper->OrdersCollection(0,$user,1,0,0,$current_store,false);
                        if (!$collection->getSize()){
                            $collection->clear();
                            $collection = $this->helper->OrdersCollection(0,'',1,0,0,$current_store,false);
                        }
                        if ($collection->getSize()){
                            $topOrderData = $collection->getFirstItem()->getData();
                            if(isset($topOrderData['op_order_id'])){
                                $storeId = $current_store;
                            }
                        }
                      }else{
                        $check = true;
                      }
                   }
                    else {
                        $totalIndices = count($assignedShopUser);
                        if($totalIndices > 0){
                            foreach ($assignedShopUser as $store ) {
                                $collection = $this->helper->OrdersCollection(0,$user,1,0,0,$store,false);
                                if (!$collection->getSize()){
                                    $collection->clear();
                                    $collection = $this->helper->OrdersCollection(0,'',1,0,0,$store,false);
                                }
                                if ($collection->getSize()){
                                    $topOrderData = $collection->getFirstItem()->getData();
                                    if(isset($topOrderData['op_order_id'])){
                                        $storeName = $this->helper->getStoreNameById($store);
                                        $storeId = $store;
                                        $newPallet =  $this->helper->GenerateShopPallet();
                                        break;
                                    }
                                }
                            }
                        } 
                        if ((!isset($topOrderData) ) && $shipping_method == "Kerbside" ){
     
                            if ($order_type == 'BLocation') {
                                $topOrderData = $this->helper->PlalletQueueCollection($shop_orders,$user, $ifTrainingUser,1,$warehouse);
                            } else {
                                $topOrderData = $this->helper->PlalletQueueCollection($shop_orders,$user, $ifTrainingUser,"",$warehouse);
                            }
                        }
                    }
                    if(isset($topOrderData['op_order_id'])){

                        $order_id = $topOrderData['op_order_id'];
                        $order_no = $topOrderData['op_increment_id'];

                        $ifSet = $this->helper->setOrderOnScreen($order_id,TabletQueue::TABLET_QUEUE_STATUS_ONSCREEN,"In Process",$user,'',$shipping_method,$warehouse);
                        $logId = $this->helper->logOrderHistory($order_id,$user,$tab_unique_id,TabletQueue::TABLET_QUEUE_STATUS_ONSCREEN,$reason,$token,$token_type,$shipping_method);
                
                        if ($ifSet && $logId) {
                            // pythoncheck
                            if ($topOrderData['in_queue'] == TabletQueue::TABLET_QUEUE_STATUS_WAS_PARKED_ORDER && $warehouse != 2) {
                                $ifParked = 1;
                                $orderDataforTablet = $this->helper->getParkedOrderData($order_no);
                            }else if ($topOrderData['python_queue'] == TabletQueue::TABLET_QUEUE_STATUS_WAS_PARKED_ORDER && $warehouse == 2) {
                                $ifParked = 1;
                                $orderDataforTablet = $this->helper->getParkedOrderData($order_no);
                            } else{
                                // $orderDataforTablet = $this->helper->getOrderData($topOrderData,$user);
                                $orderDataforTablet = $this->helper->getOrderData($topOrderData,$user,$shipping_method, $warehouse);
                            }

                            if(!$orderDataforTablet){
                                $connection->rollBack();
                                $status = false;
                                $message = "Error Please Try Again 3";
                            }else{
                                $status = true;
                                $message = "Data Sent";
                                $this->tabletOrderData($user);
                                $connection->commit();
                            }

                        }else{
                            $connection->rollBack();
                            $status = false;
                            $message = "Error Please Try Again 4";
                        }

                    }else{
                        $connection->rollBack();
                        $status = false;
                        if($check){
                            $message = "User removed from current store";
                        }else{
                        $message = "No Order`s in Queue";
                        }
                    }
                }
            }catch(\Exception $e){
                $connection->rollBack();
                $status = false;
                $message = "Model Error, Please Try Again".$e->getMessage();
                $this->_webserviceHelper->ErrorLog($user,"getQueueOrder",$e->getMessage(),"pallet tab unique id:".$tab_unique_id);
            }
        }
        if ($fileExist) {
            $myfile = fopen($file, "w") or die("Unable to open file!");
            $txt = "0";
            fwrite($myfile, $txt);
            fclose($myfile);
        }
        $return = array("status" => $status , "message" => $message , "order" => $orderDataforTablet , "storeId" => $storeId , "storeName" => $storeName ,"newPallet" => $newPallet, "parked" => $ifParked , "loginid" => $logId, "warehouse" => $textwarehouse );
            echo json_encode($return ?? []);exit;
    }

    public function loginTablet(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $reason = $params['reason'];
        $token_type = $params['token_type'];
        $token = $params['token'];
        $tab_unique_id = $params['tab_unique_id'];
        $shipping_method = $params['shipping_method'];

        $logId = $this->helper->logOrderHistory(0,$user,$tab_unique_id,"",$reason,$token,$token_type,$shipping_method);
        if ($logId) {
            $status = true;
            $message = "Logged in Sucessfully";
        }else{
            $status = false;
            $message = "Model Error, Please Try Again 5";
        }
        $return = array("status" => $status , "message" => $message, "loginid" => $logId );
        echo json_encode($return ?? []);exit;
    }

    public function logoutTablet(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];

        //on logout put batch in pending
        if(isset($params['batch_no'])){
            $dhlCollection = $this->dhlCollection->create();
            $dhlCollection->addFieldToFilter('batch_number',$params['batch_no']); // filter by 
            $dhlCollection->addFieldToFilter('batch_queue_status',2); // filter by 

            if($dhlCollection->getData()){
                foreach ($dhlCollection->getData() as $order) {
                  $Model = $this->_sampleprocessingFactory->create();
                   $Model->load($order["op_order_id"], 'op_order_id');
                   $Model->setBatchQueueStatus(3);
                   $Model->save();
                }
              }
          $status = true;
          $message = "Logged out Sucessfudlly";
        }else{
        $reason = $params['reason'];
        $log_id = $params['loginid'];
        $HistoryModel = $this->_loginHistory->create()->load($log_id);

        $shipping_method = $HistoryModel->getTabShipping() ?? "";
        if ($shipping_method == 'DHL') {
            $tab_unique_id = $params['tab_unique_id'];
        } else {
            $order_id = $params['orderid'];
        }
        $shop_order = 0;
        $ifSent = 0;
        $ifSet = true;

        $connection = $this->_resource->getConnection();
        $connection->beginTransaction();


        if ($shipping_method == 'DHL') {
            $ifHistorySet = $this->helper->logHistoryEnd($log_id,TabletQueue::TABLET_QUEUE_STATUS_INQUEUE,$tab_unique_id,$shipping_method);
        } else {
            $ifHistorySet = $this->helper->logHistoryEnd($log_id,TabletQueue::TABLET_QUEUE_STATUS_INQUEUE,$order_id,$shipping_method);
        }

        
        if($ifHistorySet){
            if(preg_match("/\bKerbside\b/i", $shipping_method)){
                if ($order_id) {
                    if($reason != "Super Admin Logout"){
                          $warehouse = '';
                          if ($this->pythonHelper->isEnablePython()) {
                              $device_ip = $params['device_ip'];
                              $warehouse = $this->pythonHelper->getWareHouse($device_ip);
                          }
                         $ifSet = $this->helper->setOrderOnScreen($order_id,TabletQueue::TABLET_QUEUE_STATUS_INQUEUE,$reason,$user,"",$shipping_method,$warehouse); 
                    }else{
                        $ifSet = true;
                    }
                      
                    
                    
                    
                    if ($ifSet) {

                        $collection = $this->_palletCollection->create()->getJoinOrderById($order_id);
                        $orderData = $collection->getFirstItem()->getData();
                        if ($orderData['is_shop_order']) {
                            $shop_order = 1;
                            $customer_name = $orderData['customer_firstname']." ".$orderData['customer_lastname'];
                            $waiting_time = date("H:i:s",strtotime($orderData['op_invoice_at']));
                            $ifSent = $this->_webserviceHelper->sendNotification( ["action" => "shoporder","orderno" => $orderData['increment_id'] ,'name' => $customer_name,"customer_waiting" => $waiting_time,"current_time" => $this->datetime->date("H:i:s")]);
                        }
                    }
                }
            }
            if ($shipping_method == 'DHL') {
                if ((!$tab_unique_id) || ($tab_unique_id)) {
                    $status = true;
                    $message = "Logged out Sucessfully";
                    $connection->commit();
                }else{
                    $status = false;
                    $message = "Model Error, Please Try Again";
                    $connection->rollBack();
                }
            } else {
                if ((!$order_id) || ($order_id && $ifSet && !$shop_order) || ($order_id && $ifSet && $shop_order && $ifSent)) {
                    $status = true;
                    $message = "Logged out Sucessfully";
                    $connection->commit();
                }else{
                    $status = false;
                    $message = "Model Error, Please Try Again";
                    $connection->rollBack();
                }
            }
        }else{
            $status = false;
            $message = "Model Error, Please Try Again";
            $connection->rollBack();
        }
      } 
       $return = array("status" => $status , "message" => $message );
        echo json_encode($return ?? []);exit;
     
    }

    public function reportOrderProblem(){
        $ifOrderParked = false;
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $tab_unique_id = $params['tab_unique_id'];
        $reported_at = $this->datetime->date("Y-m-d H:i:s");

        $order_id = $params['orderid'];
        $order_no = $params['order_no'];
        $skus = $params['sku'];
        $locations = $params['location'];

        $dbarray = array();
        $dbarray_mutiple = array();
        
        $dbarray['problem_status'] = Data::PROBLEM_STATUS_UNRESOLVED;  
        $dbarray['type'] = Data::SKU_REPORT_TYPE_PROBLEM;       
        $dbarray['order_no'] = $order_no;
        $dbarray['tablet_unique_id'] = $tab_unique_id;
        $dbarray['problem'] = $params['problem'];
        $dbarray['reported_by'] = $user;
        $dbarray['reported_at'] = $reported_at;
        if(count($skus)){
          foreach ($skus as $key => $value) {
            $dbarray['quantity'] = $value;
            $dbarray['sku'] = $key;
            $dbarray['location'] = $locations[$key];
      
            $dbarray_mutiple[] = $dbarray;
          }
        }else{
          $dbarray_mutiple[] = $dbarray;
        }
        try{
            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName('picked_problem_broken_sku_table');
            $connection->beginTransaction();

            $connection->insertMultiple($tableName, $dbarray_mutiple);
            $parkOrder = 0;
            if (isset($params['park']) && $params['park']) {
                $weight = $params['weight'];
                $parkOrder = $params['park'];
                $ifOrderParked = $this->helper->setParkOrder($params['order_data_json'],$params['item_data_json'],$order_no,$weight,$params['pause_status'],$user,$tab_unique_id);
                $orderQueueStatus = TabletQueue::TABLET_QUEUE_STATUS_PROBLEM_PARKED_ORDER;
            }else{
                $orderQueueStatus = TabletQueue::TABLET_QUEUE_STATUS_PROBLEM_NOT_PARKED;
            }

            $problem_status = "Problem";
            if (isset($params['problem']) && $params['problem'] == "Skipped") {
                $problem_status = "Skipped";
            }

            $ifQueueUpdated = $this->helper->setOrderOnScreen($order_id,$orderQueueStatus,$problem_status,"",$params['pause_time']);

            $ifHistorySet = $this->helper->logHistoryEnd($params['loginid'],$orderQueueStatus,$order_id);

            if ($problem_status == "Skipped") {
                $status = true;
                $message = "Problem Skipped Reported Sucessfully";
                $connection->commit();
                $return = array("status" => $status , "message" => $message );
                echo json_encode($return ?? []);exit;
            }

            $printer_no = $params['printer_no'];
            $zpldata = $params['zpldata'];
           
            if (($ifOrderParked && $ifQueueUpdated && $ifHistorySet) || (!$parkOrder && $ifQueueUpdated && $ifHistorySet)) {
                $isPrinted = 0;
                if($printer_no){
                    $printingData = $this->_webserviceHelper->printZpl($printer_no,$zpldata);
                    if ($printingData['success']) {
                        $isPrinted = 1;
                    }
                }

                if($isPrinted || !$printer_no){
                    $status = true;
                    $message = "Problem Reported Sucessfully";
                    $connection->commit();
                }else{
                    $status = false;
                    $message = "Printer, Error Please Try Again";
                    $connection->rollBack();
                }
                
            }else{
                $status = false;
                $message = "Model Error, Please Try Again";
                $connection->rollBack();
            } 
            

        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $connection->rollBack();
            $this->_webserviceHelper->ErrorLog($user,"reportOrderProblem",$e->getMessage(),"tab unique id:".$tab_unique_id);
        }

        $return = array("status" => $status , "message" => $message );
        echo json_encode($return ?? []);exit;

    }

    public function dhlApiCall()
    {
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $incrementId = $params['orderid'];
        $dhl_tablet = $params['dhl_tablet'];

        $warehouse = '';
        if ($isEnablePython) {
            $device_ip = '';
            if (isset($params['device_ip'])) {
                $device_ip = $params['device_ip'];
            }
            $warehouse = $this->pythonHelper->getWareHouse($device_ip);
        }

        $order = $this->ordermodel->loadByIncrementId($incrementId);

        $status = true;
        $message = "DHL Api Call";
        $collectionJobNumber = '';
        $response = $this->dhlHelper->DhlApiCalls($order,1,'',$dhl_tablet,false,false,false,false,$warehouse);


        if (isset($response['consingmentId'])) {
            $shipmentId = $response['consingmentId'];
        }

        if (isset($response['Errors'])) {
            $status = false;
            $message = $response['Errors'];
        }

        $return = array("status" => $status , "message" => $message, 'shipmentId' => $shipmentId );
        echo json_encode($return ?? []);exit;
    }

    public function orderComplete(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        // $printer_no = $params['printer_no'];
        $order_id = $params['orderid'];
        $shipping_method = $params['shipping_method'];
        $reported_at = $this->datetime->date("Y-m-d H:i:s");

        if (isset($params['caliber_sku']) && !empty($params['caliber_sku'])) {
            $caliber_sku = $params['caliber_sku'];
            try {
                $this->salesItems->updateCaliber($order_id, $caliber_sku, 1);
             }catch(\Exception $e){
                $return = array("status" => false,"message" => "Caliber Check Failed".$e->getMessage());
                echo json_encode($return ?? []);exit;
              }
        } 

        if ($shipping_method == 'DHL') {
            $local_connected = $params['is_local_connected'];

            $order = $this->ordermodel->load($order_id);

            $shipmentId = $params['shipmentId'];
            $status = true;
            $message = "DHL Order Completed Successfully";

            $directory = $this->localHelper->getLocalDirectory( "dhlshippinglabels", $order->getStoreId() );
            $fileName = $order->getIncrementId() . "-" . "dhl-shippinglabel" . ".prn";
            $file = $directory . '/' . $fileName;

            $dhl_printed = false;

            if ($local_connected) {
                if (file_exists($file)) {
                    $dhl_printed = true;
                    $status = true;
                    $message = "DHL Order Completed Successfully With local Printer";
                } else {
                    $status = false;
                    $message = "Shipment Id Empty";
                }
            } else {
                $printer_no = $params['printer_no'];

                if ($printer_no && file_exists($file)) {
                    $dhl_printed = true;
                    $this->_webserviceHelper->printZpl($printer_no,file_get_contents($file));
                } else {
                    $status = false;
                    $message = "Printer Offline or file not Found";
                }
            }

            $collection = $this->dhlCollection->create();
            $collection->addFieldToFilter( 'op_order_id', $order_id );

            if( count( $collection->getData() ) > 0 )
            {
                $tab = true;
                $data = $collection->getData();
                if ($dhl_printed) {
                    $this->dhlComplete->setQueuedStatus($data[0]['op_queue_id'],$user,'',$shipmentId,$tab);
                    $this->dhlComplete->createOrderShippment($order_id, $data[0]['op_queue_id'],$user,'tablet');
                }
            }  else {
                $status = false;
                $message = "Order Not Found";
            }

            if (file_exists($file)) {
                $zpl_data = file_get_contents($file);
            } else {
                $status = false;
                $message = "File not Found";
                $zpl_data = "";
            }

            $return = array("status" => $status , 'zpl_data' => $zpl_data, "message" => $message );
            echo json_encode($return ?? []);exit;
        } 
      else {
            $no_of_prints = $params['no_of_prints'];
            $printer_no = $params['printer_no'];
        }
        if(isset($params['problem_item'])){
            if($params['problem_item'] == 1){
            $order = $this->ordermodel->load($order_id);
            $order = $this->orderFactory->create()->loadByIncrementId($order->getIncrementId());
            $order->setStatus("problem_order");
            $order->addStatusToHistory($order->getStatus(), 'Order is added to Problem Orders - Processed from Tablet');
            $order->save();
            $isPrinted = 0;
                if ($printer_no) {
                    $i = 0;
                    while ($i < $no_of_prints){
                        $printingData = $this->_webserviceHelper->printZpl($printer_no,$params['zpldata']);
                        $i++;
                        $isPrinted = $i;
                    }
                }
                if ($isPrinted == $no_of_prints || !$printer_no) {
                    $status = true;
                    $message = "Order Reported as Problem Sucessfully";
                }else{
                    $status = false;
                    $message = "Printer, Error Please Try Again";
                }
            $return = array("status" => $status , "message" => $message );
             echo json_encode($return ?? []);exit;
         }
       }
        try{
            $connection = $this->_resource->getConnection();
            $connection->beginTransaction();
           
            //  no need to send warehouse bcz oncomplete we dont change user
            $ifQueueUpdated = $this->helper->setOrderOnScreen($order_id,TabletQueue::TABLET_QUEUE_STATUS_COMPLETE,"Completed on Tablet",$user,$params['process_time'],$shipping_method);

            $ifHistorySet = $this->helper->logHistoryEnd($params['loginid'],TabletQueue::TABLET_QUEUE_STATUS_COMPLETE,$order_id,$shipping_method);

            if ($ifQueueUpdated && $ifHistorySet) {
                $isPrinted = 0;
                if ($printer_no) {
                    $i = 0;
                    while ($i < $no_of_prints){
                        $printingData = $this->_webserviceHelper->printZpl($printer_no,$params['zpldata']);
                        $i++;
                        $isPrinted = $i;
                    }
                }

                if ($isPrinted == $no_of_prints || !$printer_no) {
                    $status = true;
                    $message = "Order Completed Sucessfully";
                    $connection->commit(); 
                }else{
                    $status = false;
                    $message = "Printer, Error Please Try Again";
                    $connection->rollBack();
                }

            }else{
                $status = false;
                $message = "Model Error, Please Try Again";
                $connection->rollBack();
            } 
            
        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $connection->rollBack();
            $this->_webserviceHelper->ErrorLog($user,"orderComplete",$e->getMessage(),"order id:".$order_id);
        }

        $return = array("status" => $status , "message" => $message );
        echo json_encode($return ?? []);exit;

    }

    public function itemsPicked(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $params['type'] = Data::SKU_REPORT_TYPE_PICKED;
        $params['reported_at'] = $this->datetime->date("Y-m-d H:i:s");
        $params['reported_by'] = $user;
        try{
            $this->_skuHistoryinterface->addData($params);
            $savedObj = $this->_skuHistoryRepository->save($this->_skuHistoryinterface);
            if ($savedObj->getRecordId()) {
                $status = true;
                $message = "Item Picked Sucessfully";
            }else{
                $status = false;
                $message = "Model Error, Please Try Again";
            }
            
        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $this->_webserviceHelper->ErrorLog($user,"itemsPicked",$e->getMessage(),"order id:".$params['order_no'].",sku:".$params['sku']);
        }

        $return = array("status" => $status , "message" => $message , "local_id" => $params['local_id']);
        echo json_encode($return ?? []);exit;
        
    }

    public function getScanOrder(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $order_no = $params['order_no'];
        $log_id = $params['loginid'];
        $orderDataforTablet = array();

        try{
            $ignoreStatusesconfig = $this->scopeConfig->getValue('config_section/allorders/ignore_order_status');
            $neglected_order_statuses = [];
            if($ignoreStatusesconfig){
              $neglected_order_statuses = explode(',', $this->scopeConfig->getValue('config_section/allorders/ignore_order_status'));  
            }
            

            $Collection = $this->_palletCollection->create()->getJoinOrderbyNo($order_no);
            if(count($neglected_order_statuses)){
              $Collection->addFieldToFilter("order.status",array("nin" => $neglected_order_statuses));  
            }
            
            $orderData = $Collection->getFirstItem()->getData();
            
            if ($orderData) {
                if($orderData['in_queue'] == TabletQueue::TABLET_QUEUE_STATUS_ONSCREEN){
                    $status = false;
                    $message = "Order is Being Picked by another User";
                }elseif ($orderData['in_queue'] == TabletQueue::TABLET_QUEUE_STATUS_COMPLETE) {
                    $status = false;
                    $message = "Order Already Picked";
                }else{
                    $connection = $this->_resource->getConnection();
                    $connection->beginTransaction();

                    $order_id = $orderData['op_order_id'];
                    $ifSetonScreen = $this->helper->setOrderOnScreen($order_id,TabletQueue::TABLET_QUEUE_STATUS_ONSCREEN,"In Process",$user);

                    $ifHistorySet = $this->helper->logHistoryEnd($log_id,TabletQueue::TABLET_QUEUE_STATUS_ONSCREEN,$order_id);

                    $orderDataforTablet = $this->helper->getOrderData($orderData,$user);

                    if ($ifSetonScreen && $ifHistorySet && $orderDataforTablet) {
                        $status = true;
                        $message = "Data Sent";
                        $connection->commit(); 
                    }else{
                        $status = false;
                        $message = "Model Error, Please Try Again";
                        $connection->rollBack();
                    }

                }
                
            }else{
                $status = false;
                $message = "Order not Ready for Picking";
            }

        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $this->_webserviceHelper->ErrorLog($user,"getScanOrder",$e->getMessage(),"order no:".$params['order_no']);
        }

        $return = array("status" => $status , "message" => $message ,"order" => $orderDataforTablet);
        echo json_encode($return ?? []);exit;

    }

    public function getAllCompleteOrders(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $order_no = $params['order_no'];
        $pageno = $params['pageno'];

        try{
            $orderDataArray = array();
            $collection = $this->_palletCollection->create()->getCompletedOrders($order_no,$pageno,$user);
            $allCompletedOrders = $collection->getData();
            if (count($allCompletedOrders)) {
                
                foreach ($allCompletedOrders as $key => $data) {
                    $dispatch_date = "";
                    if($data['dispatch_date']){
                        $dispatch_date = date("d-m-Y", strtotime($data['dispatch_date']) );
                    }
                    
                    $milli_seconds = $data['process_time'];
                    $process_time = $this->helper->convertToMins($milli_seconds);

                    if($data['is_shop_order']){
                      $dispatch_date = "Shop Order";
                    } 
                    $orderDataArray[] = array(
                          'Order_Id' => $data['op_order_id'],
                          'Order_No' => $data['increment_id'],
                          'Picked_By' => $data['tablet_user'],
                          'Dispatch_Date' => $dispatch_date,
                          'Picked_At' => $data['end_time'],
                          'Weight' => $data['weight'],
                          'No_of_Items' => $data['op_total_items'],
                          'Process_Time' => $process_time,
                    );

                }
                $status = true;
                $message = "Completed Orders List";
            }else{
                $status = false;
                $message = "No Matching Orders";
            }
        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $this->_webserviceHelper->ErrorLog($user,"getAllCompleteOrders",$e->getMessage(),"");
        }

        $return = array("status" => $status , "message" => $message , "orders" => $orderDataArray);
        echo json_encode($return ?? []);exit;
    }

    public function viewCompleteOrder(){
        $params = $this->request->getBodyParams();
        $order_no = $params['order_no'];
        $message = "";
        try{
            $orderDataforTablet = array();
            $collection = $this->_palletCollection->create()->getCompletedOrders($order_no);
            $orderData = $collection->getFirstItem()->getData();
            if ($orderData) {
                $orderDataforTablet = $this->helper->getOrderData($orderData);

                $status = true;
                $message = "Completed Order";
            }else{
                $status = false;
                $message = "No Matching Orders";
            }
        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $this->_webserviceHelper->ErrorLog("rrr","getAllCompleteOrders",$e->getMessage(),"");
        }

        $return = array("status" => $status , "message" => $message , "order" => $orderDataforTablet);
        echo json_encode($return ?? []);exit;
    }

    public function printCompleteOrder(){
        $params = $this->request->getBodyParams();
        $printer_no = $params['printer'];
        $zpldata = $params['data'];

        $printingData = $this->_webserviceHelper->printZpl($printer_no,$zpldata);
        if ($printingData['success']) {
            $status = true;
            $message = "Printed Sucessfully";
        }else{
            $status = false;
            $message = "Printing Error, Please Try Again";
        }

        $return = array("status" => $status , "message" => $message );
        echo json_encode($return ?? []);exit;
    }

    public function setPalletScanTime(){
        $params = $this->request->getBodyParams();
        $milli_seconds = $params['timer'];
        $order_id = $params['order_id'];

        try{
            $palletModel = $this->_palletOrder->create()->load($order_id, 'op_order_id');
            $palletModel->setPalletScan($milli_seconds);    
            $palletModel->save();
            $status = true;
            $message = "Pallet Scan Time is Set";
        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $this->helper->ErrorLog("","setPalletScanTime",$e->getMessage(),"pallet order id:".$order_id);
            return false;
        }

        $return = array("status" => $status , "message" => $message );
        echo json_encode($return ?? []);exit;

    }

    public function orderDetails()
    {
        
        $creditMemo = 0;

        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $order_no = $params['orderno'];

        $connection  = $this->_resource->getConnection();
        $tableName = $connection->getTableName(self::RETURN_TABLE);
        $query = $connection->select()
          ->from($tableName,['return_id','return_by'])
          ->where('return_order_id = ?', $order_no);
        $fetchOrder = $connection->fetchRow($query);
         
        if($fetchOrder){
            $return = array("status" => true , "orderDetail" => " Order Already Returned");
         }
        else{
        $connection  = $this->_resource->getConnection();
        $tableName = $connection->getTableName(self::ORDER_TABLE);
        $query = $connection->select()
          ->from($tableName,['entity_id','tax_amount'])
          ->where('order_id = ?', $order_no);
        $fetchData = $connection->fetchRow($query);

        if($fetchData){
            $creditMemo = 1;
        }
 
        try{ 
            // $order = $this->ordermodel->load($order_no);
            $order = $this->OrderInterface->loadByIncrementId($order_no);

            $orderCommentHistory = $order->getStatusHistoryCollection();

            $orderComment = [];
            foreach ($orderCommentHistory as $status) {
                if ($status->getComment()) {
                    $orderComment[] = array(
                        'comment'         => $status->getComment(),
                        'date'          => $status->getCreatedAt(),
                    );
                }
            }

            if ($order->getId())
            {
                foreach ($order->getAllItems() as $item) {
                    $items[] = array(
                        'name'         => $item->getName(),
                        'sku'          => $item->getSku(),
                        'size'         => $item->getSize(),
                        'creditmemo'   => $creditMemo,
                        'ordered_qty'  => (int)$item->getQtyOrdered()
                    );
                }
                
                $return = array("status" => true , "orderDetail" => $items, "ordercomment" => $orderComment);
            }else {
                $return = array("status" => false , "orderDetail" => "Order Not Found");
            }
        }catch(exception $e){
            $return = array("status" => false , "message" => "Failed::".$e->getMessage());
        }
    }
        echo json_encode($return ?? []); exit;
    }

    public function returnRecords()
    {
        $image[] = $this->request->getFiles();

        if (isset($image[0]['image'])) {
             $params = $this->request->getParams();
        }else{
             $params = $this->request->getBodyParams();
        }
        $order_id = $params['return_order_id'];
        $return_prods = $params['return_products'];
        $return_by = $params['return_by'];
        // $action_type = $params['action_type'];

        $printer_no = $params['printer_no'];
        $zpldata = $params['zpldata'];

        $current_time = date('Y-m-d H:i:s');

        // $cleanStr = str_replace( array('[',']','"','\\',',','sku=','qty=') , ''  , $return_prods );
        // $finalStr = str_replace( array(' ','') , 'x'  , $cleanStr );
         if (isset($image[0]['image'])) {
         for($i=0; $i < count($image[0]['image']); $i++)
         {
             $imageName[$i] = $image[0]['image'][$i]['name'];
             $dir = 'returnimage';
             $this->_webserviceHelper->UploadMultipleImage($image[0]['image'][$i],$dir);
         }
          
        }
        $imageName = json_encode($imageName ?? []);
   
         try{
            $Model = $this->returnOrder->create();
            $OrderReturnModel = $Model->load($order_id, 'return_order_id');
           
             if($OrderReturnModel){
             $printingData = $this->_webserviceHelper->printZpl($printer_no,$zpldata);
             if ($printingData['success']) {
              $Printermessage = "Printed Sucessfully";
             }else{
              $Printermessage = "Printing Error, Please Try Again";
             }
              if ($OrderReturnModel->getReturnId()) {
                $OldModel = $this->returnOrder->create()->load($OrderReturnModel->getReturnId());
                $OldModel->setReturnProducts($return_prods);
                $OldModel->setReturnImage($imageName);
                $OldModel->setReturnDate($current_time);
                // $OldModel->setDamagedStatus($action_type);
                $OldModel->setReturnBy($return_by);
                $OldModel->save();

            } else {
                $returnModel = $this->returnOrder->create();
                $returnModel->setReturnOrderId($order_id);
                $returnModel->setReturnProducts($return_prods);
                // $returnModel->setDamagedStatus($action_type);
                $returnModel->setReturnImage($imageName);
                $returnModel->setReturnDate($current_time);
                $returnModel->setAction(0);
                $returnModel->setReturnBy($return_by);
                $returnModel->save();
            }
            $status = true;
            $message = "Order Return Successfully";
          }
           
        }catch(\Exception $e){
            $status = false;
            $message = "Model Error, Please Try Again";
            $Printermessage = "Model Error, Please Try Again";
            return false;
        }
       
         $return = array("status" => $status , "message" => $message, "Printermessage" => $Printermessage );
         echo json_encode($return ?? []);exit;
    }

    public function putAways()
    {
        $params = $this->request->getBodyParams();
        $user = $params['user'];

        try{

            $collection = $this->replacements->create()->getCollection();
            $collection->addFieldToFilter("fd_created_at",array("from"=>date("Y-m-d 00:00:00"),"to"=>date("Y-m-d 23:59:59"),"date"=>true));
            $collection->addFieldToFilter('fd_type', 1); //type for refund
            $collection->addFieldToFilter('is_stock_return', 1);

            if (count($collection->getData())) {

                foreach ($collection->getData() as $item) {
                    $items[] = array(
                        'increment_id'  => $item['fd_entity_number'],
                        'order_id'      => $item['entity_id']
                    );
                }
                
                $return = array("status" => true , "orderDetail" => $items);
            }else {
                $return = array("status" => false , "orderDetail" => "Order Not Found");
            }
        }catch(exception $e){
            $return = array("status" => false , "message" => "Failed::".$e->getMessage());
        }
        
        echo json_encode($return ?? []); exit;
    }
    public function llopChecks()
    {
        $params = $this->request->getBodyParams();
        $llop_number = $params['llop_number'];
        $llop_user = $params['llop_user'];
        $hydraulic_system = $params['hydraulic_system'];
        $wheels = $params['wheels'];
        $forks = $params['forks'];
        $battery_charge = $params['battery_charge'];
        $capacity_plate = $params['capacity_plate'];
        $gauges = $params['gauges'];
        $brakes = $params['brakes'];
        $steering = $params['steering'];
        $horn = $params['horn'];
        $lights = $params['lights'];
        $overall_condition = $params['overall_condition'];

        $current_time = date('Y-m-d H:i:s');
       
         try {
            $LLopChecksModel = $this->LLopChecks->create();
            $LLopChecksModel->setLlopCheckDate($current_time);
            $LLopChecksModel->setLlopNumber($llop_number);
            $LLopChecksModel->setLlopUser($llop_user);
            $LLopChecksModel->setHydraulicSystem($hydraulic_system);
            $LLopChecksModel->setWheels($wheels);
            $LLopChecksModel->setForks($forks);
            $LLopChecksModel->setBatteryCharge($battery_charge);
            $LLopChecksModel->setCapacityPlate($capacity_plate);
            $LLopChecksModel->setGauges($gauges);
            $LLopChecksModel->setBrakes($brakes);
            $LLopChecksModel->setSteering($steering);
            $LLopChecksModel->setHorn($horn);
            $LLopChecksModel->setLights($lights);
            $LLopChecksModel->setOverallCondition($overall_condition);
            $LLopChecksModel->save();
            $status = true;
            $message = "LLOP safety checks Successful";
         } catch (\Exception $e) {
            $status = false;
            $message = "Model Error, Please Try Again";
            return false;
        }   
            $return = array("status" => $status , "message" => $message);
            echo json_encode($return ?? []);exit;

    }
    public function getLLopConfig(){
          $llopcheck = "";
          $llopcheck = $this->_webserviceHelper->getConfiguration('tablet_config/general/llop_checks');
          if($llopcheck == ""){
            $status = false;
            $llop_check = "Model Error, Please Try Again";
          }
          else{
            $status = true;
            $llop_check =  $llopcheck;
          }
         
          $return = array("status" => $status , "llop_check" => $llop_check);
          echo json_encode($return ?? []);exit;
      }
   
      public function otherChecks()
      {
          $params = $this->request->getBodyParams();
          $vehicle_info = $params['vehicle_info'];
          $user = $params['user'];
          $hydraulic_system = $params['hydraulic_system'];
          $wheels = $params['wheels'];
          $forks = $params['forks'];
          $battery_charge = $params['battery_charge'];
          $capacity_plate = $params['capacity_plate'];
          $gauges = $params['gauges'];
          $brakes = $params['brakes'];
          $steering = $params['steering'];
          $horn = $params['horn'];
          $lights = $params['lights'];
          $overall_condition = $params['overall_condition'];
  
          $current_time = date('Y-m-d H:i:s');
         
           try {

              $OtherChecksModel = $this->OtherChecks->create();
              $OtherChecksModel->setOtherChecksDate($current_time);
              $OtherChecksModel->setVehicleInfo($vehicle_info);
              $OtherChecksModel->setUser($user);
              $OtherChecksModel->setHydraulicSystem($hydraulic_system);
              $OtherChecksModel->setWheels($wheels);
              $OtherChecksModel->setForks($forks);
              $OtherChecksModel->setBatteryCharge($battery_charge);
              $OtherChecksModel->setCapacityPlate($capacity_plate);
              $OtherChecksModel->setGauges($gauges);
              $OtherChecksModel->setBrakes($brakes);
              $OtherChecksModel->setSteering($steering);
              $OtherChecksModel->setHorn($horn);
              $OtherChecksModel->setLights($lights);
              $OtherChecksModel->setOverallCondition($overall_condition);
              $OtherChecksModel->save();
              $status = true;
              $message = "Other safety checks Successful";
           } catch (\Exception $e) {
              $status = false;
              $message = "Model Error, Please Try Again";
              return false;
          }   
              $return = array("status" => $status , "message" => $message);
              echo json_encode($return ?? []);exit;
      }

      public function CompleteCombinePick(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $order_id = $params['order_id'];
        $shipping_method = $params['shipping_method'];
        $no_of_scan = $params['no_of_scan'];
        $is_completed = $params['is_completed'];
        $shipmentId = $params['shipmentId'];
        $batch_no = $params['batch_no'];
        $reported_at = $this->datetime->date("Y-m-d H:i:s");
        $dhl_printed = false;
        $warehouse = "";
        if ($this->pythonHelper->isEnablePython()) {
            $device_ip = '';
            if (isset($params['device_ip'])) {
                $device_ip = $params['device_ip'];
            }
            $warehouse = $this->pythonHelper->getWareHouse($device_ip);
            if ($warehouse == 1) {
                $textwarehouse = 'H2';
            } elseif ($warehouse == 2) {
                $textwarehouse = 'PW';
            }
        }
        if ($shipping_method == 'DHL') {
            $order = $this->ordermodel->load($order_id);
            $directory = $this->localHelper->getLocalDirectory( "dhlshippinglabels", $order->getStoreId() );
            if ($this->pythonHelper->isEnablePython()) {
                if ($warehouse == 2) {
                    $fileName = $order->getIncrementId() . "-" . "dhl-shippinglabel-py-" .$no_of_scan. ".prn";
                } else {
                    $fileName = $order->getIncrementId() . "-" . "dhl-shippinglabel-" .$no_of_scan. ".prn";
                }

            } else {
                $fileName = $order->getIncrementId() . "-" . "dhl-shippinglabel-" .$no_of_scan. ".prn";
            }
            $file = $directory . '/' . $fileName;

                if (file_exists($file)) {
                    $status = true;
                    // $items_count = count($order->getAllVisibleItems());
                    $items_count = 0;  
                    foreach ($order->getAllItems() as $item) {
                        $items_count += $item->getQtyOrdered();
                    }
                    $items_count = intval($items_count);
                    if($no_of_scan == $items_count){
                       $dhl_printed = true;
                    }else{
                       $message = "Item Picked Successfully With local Printer";
                    }
                    $zpl_data = file_get_contents($file);
               
                //do something to save scan
                if ($no_of_scan){
                    $this->dhlComplete->updateScanNo($order_id,$no_of_scan);
                }
                if ($dhl_printed){
                    $collection = $this->dhlCollection->create();
                    $collection->addFieldToFilter( 'op_order_id', $order_id );
      
                    if( count( $collection->getData() ) > 0 )
                    {
                        $tab = true;
                        $data = $collection->getData();
                        $this->dhlComplete->setQueuedStatus($data[0]['op_queue_id'],$user,'',$shipmentId,$tab);
                        $this->dhlComplete->createOrderShippment($order_id, $data[0]['op_queue_id'],$user,'tablet',$warehouse);
                        $message = "DHL Order Completed Successfully With local Printer";
                    }  else {
                        $status = true;
                        $message = "Order Not Found Returning ZPL";
                    }
                }
                if($this->pythonHelper->isEnablePython()) {
                    if($order['warehouse'] == 0) {
                        $checkEasyWms = $this->scopeConfig->getValue('tablet_config/general/dhl_batch_easywms');
                        if ($checkEasyWms) {
                            $this->helper->UpdateBatchEasyWms(NewConfig::BATCH_READY_TO_PICK, $batch_no);
                        }
                        $status = "true";
                        $message = "Batch Partially Completed Successfully";
                    }
                    else{
                        if($is_completed == 1){ //just complete batch no need of next batch
                            $completeBatch= $this->helper->CompleteBatchOrders($batch_no);
                            $checkEasyWms = $this->scopeConfig->getValue('tablet_config/general/dhl_batch_easywms');
                            if($checkEasyWms){
                                $this->helper->UpdateBatchEasyWms(NewConfig::BATCH_C0MPLETED,$batch_no);
                            }
                            $status = "true";
                            $message ="Batch Completed Successfully";
                            // $return = array("status" => $status,'zpl_data' => $zpl_data , "message" => $message);
                            // echo json_encode($return);exit;
                        }
                    }
                }
                else{
                    if($is_completed == 1){ //just complete batch no need of next batch
                        $completeBatch= $this->helper->CompleteBatchOrders($batch_no);
                        $checkEasyWms = $this->scopeConfig->getValue('tablet_config/general/dhl_batch_easywms');
                        if($checkEasyWms){
                            $this->helper->UpdateBatchEasyWms(NewConfig::BATCH_C0MPLETED,$batch_no);
                        }
                        $status = "true";
                        $message ="Batch Completed Successfully";
                        // $return = array("status" => $status,'zpl_data' => $zpl_data , "message" => $message);
                        // echo json_encode($return);exit;
                    }
                }

            } else {
                $status = false;
                $message = "Shipping Label Doesnot exits $fileName"; 
                $zpl_data = "";
            }
            $return = array("status" => $status , 'zpl_data' => $zpl_data, "message" => $message );
            echo json_encode($return ?? []);exit;
        }  
    }
    
    public function parkBatch(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $batch_no = $params['batch_no'];
        
        $orders = "";
        $collection = $this->dhlCollection->create();
        $collection->addFieldToFilter( 'batch_number', $batch_no );
        $collection = $this->_webserviceHelper->applySalesJoinData($collection);
        $collection->addFieldToFilter('status', array("in"=>array("pick_created","a_picking","movement_created")));
        
        foreach($collection as $_order){
            $orders = $orders . " - " . $_order->getIncrementId();
            $Model = $this->_sampleprocessingFactory->create();
            $Model->load($_order->getOpOrderId(), 'op_order_id');
            $Model->setBatchQueueStatus(1);
            $Model->setTabletUser("");
            $Model->save();
            
            if($_order->getStatus() == "a_picking"){
                
                $Comment="Order Moved Back from Tablet by user : $user";
            $order = $this->OrderInterface->loadByIncrementId($_order->getIncrementId());
            
            $order->setStatus("pick_created");
            $order->addStatusToHistory($order->getStatus(), $Comment);
            $order->save();
            }
            
            
        }
        $this->helper->UpdateBatchEasyWms(NewConfig::BATCH_READY_FROM_EASYWMS,$batch_no);
        $reported_at = $this->datetime->date("Y-m-d H:i:s");
        $status = true;
        $message = "Batch No $batch_no is Parked by User $user $orders";
        $return = array("status" => $status , "message" => $message );
        echo json_encode($return ?? []);exit; 
    }

    public function ReportBatchItem(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $orderIncrementId = $params['orderno'];
        $sku = $params['sku'];
        $reason = $params['reason'];
        $is_completed = $params['is_completed'];
        $batch_no = $params['batch_no'];
        // $orderIncrementId = $this->getRequest()->getParam('ordernumber');
        $outOfStockSku = $sku;
     

        try {
            if (isset($orderIncrementId)) {
                $order = $this->OrderInterface->loadByIncrementId($orderIncrementId);
                // $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
                $Comment='Order Partially  Shipped - Following Sku is out of stock: ';
                $Comment = $Comment." ".$sku."--No Stock ";
                $order->addStatusToHistory($order->getStatus(), $Comment);
                $order->setStatus("to_call");
                $order->save();
 
                $dhlCollection = $this->dhlCollection->create();
                $dhlCollection->addFieldToFilter('op_order_id', $order->getId());
                $dhlCollection->addFieldToFilter( 'out_of_stock', ['notnull' => true]);
                $productData = $dhlCollection->getData();

               if (!empty($productData)) {
                $firstProduct = reset($productData);
                $productSku = $firstProduct['out_of_stock'];
                $outOfStockSku = $outOfStockSku . ','. $productSku;
              }
                $sample_processing = $this->_sampleprocessingFactory->create()->load($order->getId(), 'op_order_id');
                if ($sample_processing->getOpQueueId()) {
                    $sample_processing->setOpHoldReason("No Stock");
                    $sample_processing->setIsProblem("1");
                    $sample_processing->setOutOfStock($outOfStockSku);
                    $sample_processing->save();
                }
            }
          if($is_completed){ //just complete batch no need of next batch
              $completeBatch= $this->helper->CompleteBatchOrders($batch_no);
              $checkEasyWms = $this->scopeConfig->getValue('tablet_config/general/dhl_batch_easywms');
              if($checkEasyWms){
                  $this->helper->UpdateBatchEasyWms(NewConfig::BATCH_C0MPLETED,$batch_no);
              }
            //   $status = "true";
            //   $message ="Batch Completed Successfully";
              // $return = array("status" => $status,'zpl_data' => $zpl_data , "message" => $message);
              // echo json_encode($return);exit;
            } 
         $status = true;
         $message = "Problem Reported Successfully";
        } catch (\Exception $exception) {
            $this->logger->critical("Exception while update problem order:" . $exception->getMessage(), array('AndroidServicesModelPalletQueueServices::ReportBatchItem'));
          $status = false;
          $message = "Problem Reporting Failed";
        }
        $return = array("status" => $status ,"message" => $message );
        echo json_encode($return ?? []);exit;
    }

    public function GenerateShopPallet(){
        $params = $this->request->getBodyParams();
        $user = $params['user'];
        $pallet_no = $params['pallet_no'];
        $weight = $params['weight'];
        $orders = $params['orders'];
        $is_pallet_generated = $params['is_pallet_generated'];

        $store_id = $params['store_id'];
        $completed_at = $this->datetime->date("Y-m-d H:i:s");
        $printer_no = $params['printer'];
        try {
                $isPrinted = 0;
                if($printer_no){
                    $zpldata = $params['zpldata'];
                    $printingData = $this->_webserviceHelper->printZpl($printer_no,$zpldata);
                    if ($printingData['success']) {
                        $isPrinted = 1;
                    }
                }
                if($isPrinted == 0 && $printer_no != 0){
                    $status = false;
                    $message = "Printer, Error Please Try Again";
                    $return = array("status" => $status ,"message" => $message);
                    echo json_encode($return ?? []); exit;
                }
 
      $updatePalletNumber = $this->mbPalletNumbers->create()->load($pallet_no, 'pallet_number');
 
        $ordersString = serialize($orders); 
       
        if ($updatePalletNumber->getPalletId()) {

            $updatePalletNumber->setPalletWeight($weight);
            $updatePalletNumber->setPalletCompletedAt($completed_at);
            $updatePalletNumber->setStoreId($store_id);
            $updatePalletNumber->setTabletUser($user);
            $updatePalletNumber->setOrders($ordersString);
            $updatePalletNumber->setPalletStatus(0);
            $updatePalletNumber->save();
        }else{

            $newPalletNumbers = $this->mbPalletNumbers->create();
            $newPalletNumbers->setPalletNumber($pallet_no);
            $newPalletNumbers->setPalletWeight($weight);
            $newPalletNumbers->setPalletCompletedAt($completed_at);
            $newPalletNumbers->setStoreId($store_id);
            $newPalletNumbers->setTabletUser($user);
            $newPalletNumbers->setOrders($ordersString);
            $newPalletNumbers->setPalletStatus(0);
            $newPalletNumbers->save();
        }
        // $orderString = trim($orders, "[]'"); // Remove the square brackets and single quotes
        // $items = explode("','", $orderString); 
        foreach ($orders as $orderno) {
            $palletModel = $this->_palletOrder->create()->load($orderno, 'op_increment_id');
            $palletModel->setPalletNumber($pallet_no);    
            $palletModel->save(); 
        }
      
        $newPallet = null;
        if($is_pallet_generated){
         $newPallet =  $this->helper->GenerateShopPallet();
        }

        $status = true;
        $message = "Pallet Successfully generated";
        $return = array("status" => $status ,"message" => $message,"newPallet" => $newPallet );
        echo json_encode($return ?? []);exit;
       }catch (\Exception $exception) {
            // $this->logger->info("Exception while update problem order " . $exception->getMessage());
          $status = false;
          $message = ("Problem Pallet Failed".$exception->getMessage());
          $return = array("status" => $status ,"message" => $message );
          echo json_encode($return ?? []);exit;
        }
        
    }

    public function ShopUsers() {
        $params = $this->request->getBodyParams();
        $user = $params['username'];
        $store = $params['store'];
    
        $config = "tablet_config/husky_settings/showroom_expediteusers";
        $shopexpediate_users = $this->_webserviceHelper->getConfiguration($config);
        $dataArray = json_decode($shopexpediate_users, true);
    
        $foundMatchingUser = false;
        foreach ($dataArray as $item) {
            if ($item['select_store'] === $store && $item['showroom_user'] === $user) {
                $foundMatchingUser = true;
                break;
            }
        }
    
        if ($foundMatchingUser) {
            $userCollection = $this->userCollectionFactory->create();
            $userCollection->addFieldToFilter('username', $user);
    
            if ($userCollection->getSize() > 0) {
                $userItem = $userCollection->getFirstItem();
                $result = [
                    'username' => $userItem->getUserName(),
                    'name' => $userItem->getFirstname() . ' ' . $userItem->getLastname(),
                ];
                $status = true;
                $message = "Successful";
            } else {
                $status = false;
                $message = "User Name not found for this user";
            }
        } else {
            $status = false;
            $message = "No Users Selected for this store";
        }
    
        $return = [
            "status" => $status,
            "message" => $message,
            "users" => $result ?? [],
        ];
    
        echo json_encode($return);
        exit;
    }

    public function GetLoadedOrders(){  
        $params = $this->request->getBodyParams();
        $store = $params['store_id'];
//send collection on basisi of this id
        $collection = $this->mbPalletNumbers->create()->getCollection();
        $collection->addFieldToFilter('pallet_status', 2);
        $collection->addFieldToFilter('store_id', $store);
        if ($collection->getSize() > 0) {
          $totalPallets = $collection->getSize();
          $itemData = [];
            foreach ($collection as $item) {
              $unserializedOrders = unserialize($item->getOrders());
  
              $itemData[] = [
               'palletNo' => $item->getPalletNumber(),
               'orders' => $unserializedOrders,
               'totalOrders' => count($unserializedOrders)
              ];
            
           }
          $return = array("status" => true ,"message" => "Returning Pallet Data", "Data" => $itemData, "totalPallets" => $totalPallets);
        } 
        else{
            $return = array("status" => false ,"message" => "No Pallets Available");
        }     
        echo json_encode($return ?? []);exit;

    }
    public function UnloadingPallets(){
        $params = $this->request->getBodyParams();
        $dataArray = $params['data'];
        $user = $params['user'];

    //  pallet_status 0 for normal, 1 for checked, 2 in transit, 3 for missing, 4 for partial ,5 for unloaded
            foreach ($dataArray as $dataItem) {
                $palletNo = $dataItem['palletNo'];

                $updatePalletNumber = $this->mbPalletNumbers->create()->load($palletNo, 'pallet_number');

                if ($updatePalletNumber->getPalletId()) {
                    $updatePalletNumber->setPalletStatus($dataItem['palletStatus']);
                    $updatePalletNumber->save();
                }

                if (isset($dataItem['orders']) && is_array($dataItem['orders'])) {
                    foreach ($dataItem['orders'] as $order) {

                        $orderNo = $order['orderNo'];
                        $status = $order['orderStatus'];

                        $orderLoad = $this->orderFactory->create()->loadByIncrementId($orderNo);
                        if ($orderLoad->getId()) {
                            $order_id = $orderLoad->getId();
                        $history = $this->historyFactory->create();

                        $message = "Order is Unloading by $user on Handheld";
                        $history->setParentId($order_id)
                                ->setComment($message)
                                ->setIsCustomerNotified(false)
                                ->setEntityName('order')
                                ->setStatus($status);
                        try {
                            $this->historyRepository->save($history);
                            $updateStatus =  $this->_webserviceHelper->updateOrderAttribute($order_id,$status);
                            if($updateStatus){
                                $return = array("status" => false , 'message' => "Something went wrong while updating order : $order_id");
                            }
                            else{
                                $return = array("status" => true , 'message' => "Successfully unloaded");
                            }
                      
                        }catch (\Exception $exception){
                              $return = array("status" => false , 'message' => "Something went wrong while updating order". $exception->getMessage());
                        }
                      }
                    }
                }
                else{
                    $return = array("status" => false , 'message' => "No orders in pallet");
                }
            }
        echo json_encode($return ?? []);exit;
    }

    public function CheckIPAddress(){   
        $params = $this->request->getBodyParams();
        $userIpAddress = $params['ipaddress'];

        $shopexpediate_users = $this->_webserviceHelper->getConfiguration('tablet_config/husky_settings/showroom_ipaddress');
        $ipShowroomMap = json_decode($shopexpediate_users ?? '', true);

        if(!empty($ipShowroomMap)){
         foreach ($ipShowroomMap as $map) {
             if ($map['ip_address'] === $userIpAddress) {
                 $showroom = $map['showroom'];
                 $storeId = $this->helper->getStoreIds($showroom);
                 $data = array(
                    'name'      => $showroom,
                    'id'       => $storeId
                );
                 $return = array("status" => true ,"message" => "Returning Showroom Name", "Data" => $data);
                 break;
            }
            else{
                $return = array("status" => false ,"message" => "No Showroom Found against IP");
            }
          }
        }else{
           $return = array("status" => false ,"message" => "No IP Addresses found in Magento ",);
        }
        echo json_encode($return ?? []);exit;
    }

    public function dhlNoCombinePick()
    {
        $currentDHL = $this->dhlIndex->getSmallOrderDispatchDate();
        $dhlCollection = $this->dhlCollection->create();
        $dhlCollection->addFieldToFilter('is_dhl', 1);
        // $dhlCollection->addFieldToFilter( 'op_queued', array("in"=>array(1,2)));
        $dhlCollection = $this->helper->applySalesJoin($dhlCollection);

        $dhlCollection->addFieldToFilter('sales.status', array("in" => array('pick_created')));

        $dhlCollection->addFieldToFilter( 'sales.total_item_count', array('lteq' => 1));

        $dhlCollection->addFieldToFilter("sales.dispatch_date",array("lteq"=>$currentDHL));

        // $dhlCollection->setOrder('sales.dispatch_date', 'ASC');
        $dhlCollection->setOrder('sales.created_at', 'ASC');

        return $dhlCollection;
    }


    public function tabletOrderData($user='')
    {
        $collectionMessages = $this->messagescollection->create()->addFieldToFilter("is_sent",0)->addFieldToFilter("reciepient",$user);
        $messagesData = $collectionMessages->getData();
        if(count($messagesData ?? [])){

            foreach ($messagesData as $admin_message){
                $messageData = array("message" => $admin_message['message'],"sentat" => $admin_message['sent_at'],"type" => $admin_message['message_type']);
                if($token_type == "fcm"){
                    $isSent = $this->_webserviceHelper->sendNotificationFcm($messageData,$token);
                }elseif($token_type == "pushy"){
                    $isSent = $this->_webserviceHelper->sendNotificationPushy($messageData,$token);
                }

                if($isSent === true){
                    try{
                        $messagesModel = $this->messagesfactory->create();
                        $messagesModel->load($admin_message['message_id'])->setIsSent(1)->save();
                    }catch(\Exception $e){
                        $this->_webserviceHelper->ErrorLog($user,"getQueueOrder",$e->getMessage(),"Message model update message_id:".$admin_message['message_id']);
                    }
                }
            }
        }
    }
}
 