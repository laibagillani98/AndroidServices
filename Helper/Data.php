<?php
namespace TM\AndroidServices\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use TM\EasyWms\Helper\NewConfig;
use \setasign\Fpdi\Fpdi;

require_once __DIR__ . '/../../../../../lib/tm/pdfsplit/fpdi/src/autoload.php';

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_fileUploaderFactory;
    protected $_filesystem;
    protected $curlClient;

    const FCM_URL = "https://fcm.googleapis.com/fcm/send";
    const PUSHY_URL = "https://api.pushy.me/push";
    const PROBLEM_STATUS_RESOLVED = 0;
    const PROBLEM_STATUS_UNRESOLVED = 1;
    const SKU_REPORT_TYPE_BROKEN = 'broken';
    const SKU_REPORT_TYPE_PICKED = 'picked';
    const SKU_REPORT_TYPE_PROBLEM = 'problem';
    const PALLET_IS_CHECKED = 1;
    const PALLET_IN_TRANSIT = 2;
    const PALLET_MISSING = 3;
    const PALLET_PARTIALLY_SHIPPED = 4;
    const PALLET_UNLOADED = 5;

    public function __construct(
        \TM\AndroidServices\Model\ResourceModel\OrderChecking\CollectionFactory $ordercheckingFactory,
        \TM\AndroidServices\Model\ResourceModel\OrderChecks\CollectionFactory $orderchecksFactory,
        \TM\AndroidServices\Model\ResourceModel\DhlBatchNo\CollectionFactory $dhlBatchNo,
        \TM\AndroidServices\Model\ResourceModel\SkuHistory\CollectionFactory $skuCollection,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \TM\Microconnect\Model\WarehouseManagement\NavStatusUpdate $navservice,
        \Magento\Framework\Serialize\SerializerInterface $serialize,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \TM\Microconnect\Helper\Data $microconnectHelper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \TM\Microconnect\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \TM\OrderComments\Model\OrderCommentsFactory $orderCommentsFactory,
        \Magento\Sales\Model\OrderFactory $ordermodel,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \TM\Sampleprocessing\Model\SampleprocessingFactory $sampleprocessingFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \TM\Sampleprocessing\Model\ResourceModel\Sampleprocessing\CollectionFactory $dhlCollection,
        \TM\Sampleprocessing\Block\Adminhtml\Post\Index $dhlIndex,
        \TM\EasyWms\Model\BatchOrderFactory $batchOrderFactory,
        \TM\AndroidServices\Model\MbPalletNumbersFactory $mbPalletNumbers,
        \TM\WoodPanel\Model\WoodFactory $woodFactory,
        \TM\WoodPanel\Helper\Data $woodHelper,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory,
        \TM\Base\Helper\Local $localHelper,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository,
        \TM\Sampleprocessing\Helper\DPD\Data $dpdHelperData,
        \TM\Python\Helper\Data $pythonHelper,
        \TM\Sampleprocessing\Helper\PANTHER\Data $pantherHelper
    ) {
        $this->ordercheckingFactory = $ordercheckingFactory;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_microconnectHelper = $microconnectHelper;
        $this->_skuCollection = $skuCollection;
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->navservice = $navservice;
        $this->_filesystem = $filesystem;
        $this->_serialize = $serialize;
        $this->_ordermodel = $ordermodel;
        $this->_helper = $helper;
        $this->orderCommentsFactory = $orderCommentsFactory;
        $this->logger = $logger;
        $this->curlClient = $curl;
        $this->_datetime = $datetime;
        $this->orderchecksFactory = $orderchecksFactory;
        $this->dhlBatchNo = $dhlBatchNo;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->sampleprocessingFactory = $sampleprocessingFactory;
        $this->_resource = $resource;
        $this->dhlCollection = $dhlCollection;
        $this->dhlIndex = $dhlIndex;
        $this->batchOrderFactory = $batchOrderFactory;
        $this->mbPalletNumbers = $mbPalletNumbers;
        $this->woodFactory = $woodFactory;
        $this->woodHelper = $woodHelper;
        $this->convertOrder = $convertOrder;
        $this->_trackFactory = $trackFactory;
        $this->localHelper = $localHelper;
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->dpdHelperData = $dpdHelperData;
        $this->pantherHelper = $pantherHelper;
        $this->pythonHelper=$pythonHelper;
    }


    public function UploadImage($image,$dir)
    {
        try{
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $image['image']]);

            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

            $uploader->setAllowRenameFiles(false);

            $uploader->setFilesDispersion(false);

            if (!file_exists(DirectoryList::MEDIA.'/'.$dir)) {
                mkdir(DirectoryList::MEDIA.'/'.$dir, 0777, true);
            }

            $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($dir);

            $uploader->save($path);

            return true;

        }catch(\Exception $e){
            return false;
        }

    }
    public function UploadMultipleImage($image,$dir)
    {
        try{
            $uploader = $this->_fileUploaderFactory->create(['fileId' => $image]);

            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);

            $uploader->setAllowRenameFiles(false);

            $uploader->setFilesDispersion(false);

            if (!file_exists(DirectoryList::MEDIA.'/'.$dir)) {
                mkdir(DirectoryList::MEDIA.'/'.$dir, 0777, true);
            }

            $path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath($dir);

            $uploader->save($path);

            return true;

        }catch(\Exception $e){
            return false;
        }

    }
    public function getCurrentDate($format='')
    {
        $date = $this->_datetime->date($format);
        return $date;
    }

    public function FormatOrderData($OrderData,$warehouse = ''){
        $order_id = $OrderData['op_order_id'];
        $orderno = $OrderData['increment_id'];
        try{

            $order = $this->_ordermodel->create()->load( $order_id );
            $allItems = $order->getAllItems();
            $allItems = $this->_microconnectHelper->sortItems($allItems);
            $numberofitems = count($allItems ??[]);
            $items = array();
            $listedSkus = array();
            $refundedConfig = $this->getConfiguration('tablet_config/general/fully_refunded_products');
            foreach ($allItems ??[] as $item) {
                $qtyShipped = round($item->getQtyShipped(),0);
                $qtyToShip = round($item->getQtyToShip(),0);

                if ($qtyToShip <= 0) {
                    continue; // Skip items with quantity to ship 0, 0.0, or less than 0
                }
                
                if ($item->getProductType() == 'bundle' ) {
                    continue; // Skip bundle
                }

                if ($warehouse != 0 && $warehouse != '' && $warehouse != $item->getWarehouse())
                {
                    continue;
                }

                if ($warehouse == 0 && $item->getChecked() == 1) {
                    continue;
                }
                
                if(!$item->getIsShopPick()){
                    $exp_data = $this->expeditingExists($orderno,$item->getSku());
                    $exp_status = 0;
                    if($exp_data){
                        $exp_status = $exp_data['status'];
                    }
                    $qty = round($item->getQtyOrdered(), 0);
                    $product = $item->getProduct();
                    $boxQty = (int)$product->getBoxQty();
                    $width = (int)$product->getWidth();
                    $height = (int)$product->getHeight();

                    if($item->getQtyRefunded() == $qty){
                        $isRefunded = 1;
                    }else{
                        $qty = $qty - $item->getQtyRefunded();
                        $isRefunded = 0;
                    }
                    if($qtyShipped){
                        $qty = $qty - $qtyShipped;
                     }
                    
                    if (isset($refundedConfig) && $refundedConfig == 1 && $isRefunded == 1) {
                        continue;
                    }

                    $quantity = (int)$qty;

                    if($width && $height){
                        $size = $width."x".$height;
                    }else{
                        $size = "";
                    }
                    $productdata = $this->_productRepositoryFactory->create()->getById($item->getProductId());

                    $image_url = $productdata->getData('thumbnail');
                    $BaseUrl = rtrim($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),'/');
                    $image_base_url = $BaseUrl.'/catalog/product'.$image_url;

                    $attributeName = $this->woodHelper->getAttributeSetName($item->getSku());
                    if ($attributeName == 'Wood Panels') {
                        continue;
                    }
                    
                    if (!in_array($item->getSku(),$listedSkus)) {
                        $items[$item->getSku()] = array(
                            'name'      => $item->getName(),
                            'sku'       => trim($item->getSku()),
                            'quantity'  => $qty,
                            'is_refunded' => $isRefunded,
                            "boxes" => "Quantity: ".$quantity,
                            "size" => $size,
                            "status" => (!$exp_status) ? 0 : $exp_status,
                            "image_url" => $image_base_url,
                            "product_type" => $attributeName                                                        
                        );
                        $listedSkus[] = $item->getSku();
                    } else {
                        $items[$item->getSku()]["quantity"] = $items[$item->getSku()]["quantity"] + $qty;
                        $final_qty = explode(':', $items[$item->getSku()]["boxes"]);
                        $items[$item->getSku()]["boxes"] = "Quantity: ".($quantity + $final_qty[1]);
                    }
                }
            }
            $productsArray = array_values($items);
            $return_array = array(
                'Order_Id' => $order_id,
                'Order_No' => $orderno,
                'Picked_By' => $OrderData['tablet_user'],
                'No_of_Items' => $numberofitems,
                'Customer_Comment' => $OrderData['delivery_note'],
                'picking_comment' => $this->customerSpecialDeliveryInstructions($orderno , $order),
                'Picked_At' => $OrderData['end_time'],
                'items' => $productsArray,
                'blindcheck' => $OrderData['blindcheck'],
                'datetime' => $OrderData['datetime']
            );

            return $return_array;
        }catch(\Exception $e){
            return "Exception: ".$e->getMessage();
        }
    }
    public function customerSpecialDeliveryInstructions($incrementId = null , $OrderModel = null)
    {
        $orderComment = $this->orderCommentsFactory->create()->getCollection()->addFieldToFilter("order_id", ["eq"=>$incrementId])
            ->addFieldToFilter("is_picking", ["eq"=>1])->getFirstItem()->getData();

        $pickingComment = $orderComment["comments"]??"";
            
        $iswarehouse = $this->getConfiguration('order_comments/general/ordercomments_is_warehouse_enabled');
        if($OrderModel && $iswarehouse){
            $historyColl = $OrderModel->getStatusHistoryCollection();
            $historyColl->addFieldToFilter('is_warehouse', 1);
            
            if(count($historyColl->getData())){
                foreach ($historyColl->getData() as $data) {
                    $comments = $data['comment'];
                    if($pickingComment){
                        $pickingComment = $pickingComment . " , " . $comments;
                    }else{
                        $pickingComment = $comments;
                    }
                  $pickingComment = str_replace("WC","",$pickingComment);  
                }
            }
        }  
        return $pickingComment;

    }
    
    public function expeditingExists($orderno,$sku){
        
        $collection = $this->ordercheckingFactory->create();
        $collection->addFieldToFilter('order_no', $orderno)->addFieldToFilter('sku', $sku);
        $data = $collection->getFirstItem()->getData();

        if(isset($data['check_id'])){

            return array("id" => $data['check_id'],"status" => $data['status']);
        }else{

            return false;
        }
    }

    public function sendNotificationFcm($customData,$token)
    {
        $title = "Tile Mountain";
        $key = $this->getConfiguration('tablet_config/fcm_settings/server_key');
        $BaseUrl = rtrim($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),'/');
        $notification = array('title' =>$title , 'body' => $customData, 'sound' => 'default', 'badge' => '1','base_url' => $BaseUrl);
        $arrayToSend = array('to' => $token ,'priority'=>'high','data'=>$notification);
        $json = json_encode($arrayToSend??[]);

        $this->curlClient->addHeader('Content-Type','application/json');
        $this->curlClient->addHeader('Authorization','key='. $key);
        $this->curlClient->post(self::FCM_URL, $json);
        $response = $this->curlClient->getBody();
        $responsedecode = (array) json_decode($response??"");

        if (isset($responsedecode['message_id']) || isset($responsedecode['multicast_id']) ) {

            return true;
        }else{
            return $response;
            $this->ErrorLog("","sendNotification fcm",$responsedecode['results'],"");
        }
    }

    public function sendNotificationDHLOrder($customData = [],$token = '/topics/dhlorders')
    {
        $pushyResponse = $this->sendNotificationPushy($customData,$token);
        if($pushyResponse === true){
            $fcmResponse = $this->sendNotificationFcm($customData,$token);
            if ($fcmResponse === true){
                return true;
            }else{
                return $fcmResponse;
            }
            return true;
        }else{
            return $pushyResponse;
        }
    }

    public function sendNotification($customData = [],$token = '/topics/brokenTiles')
    {
        $pushyResponse = $this->sendNotificationPushy($customData,$token);
        if($pushyResponse === true){
            $fcmResponse = $this->sendNotificationFcm($customData,$token);
            if ($fcmResponse === true){
                return true;
            }else{
                return $fcmResponse;
            }
            return true;
        }else{
            return $pushyResponse;
        }
    }

    public function sendNotificationPushy($customData,$token)
    {
        $title = "Tile Mountain";
        $key = $this->getConfiguration('tablet_config/fcm_settings/server_key_pushy');

        $BaseUrl = rtrim($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB),'/');
        $notification = array('title' =>$title , 'body' => $customData, 'sound' => 'default', 'badge' => '1','base_url' => $BaseUrl);
        $arrayToSend = array('to' => $token ,'priority'=>'high','data'=>$notification);
        $json = json_encode($arrayToSend??[]);

        $this->curlClient->addHeader('Content-Type','application/json');
        //$this->curlClient->addHeader('Authorization','api_key='. $key);
        $url = self::PUSHY_URL."?api_key=".$key;
        $this->curlClient->post($url, $json);
        $response = $this->curlClient->getBody();
        $responsedecode = (array) json_decode($response??"");

        if (isset($responsedecode['success']) && $responsedecode['success'] == 1) {
            $this->sendNotificationFcm($customData,$token);
            return true;
        }else{
            $this->ErrorLog("","sendNotification pushy",$response,"");
            return $response;
        }
    }

    public function printZpl($printer_no,$zpl_data,$getPrinterslist = 0)
    {
        $printers_list = $this->getConfiguration('tablet_config/printer_settings/printers_option');
        $printers = $this->_serialize->unserialize($printers_list);
        $PrintersCount = count($printers??[]);
        $allPrinters = array();
        $success = 0;
        $send_printer = 1;
        $message = "";
        if($PrintersCount){
            foreach ($printers ??[] as $printer) {
                if($printer_no == $printer['workstation'] || $getPrinterslist){

                    $ip_address = $printer['ip_address'];
                    $port = $printer['port'];
                    $fp = @fsockopen($ip_address , $port, $errno, $errstr, 2);
                    if(!$fp){
                        $message = 'Unable to print, Try printing to another printer';
                    }else{
                        $allPrinters[] = $printer['workstation'];

                        if (!$getPrinterslist) {
                            $ifPrinted = fwrite($fp, $zpl_data, strlen($zpl_data));
                            if ($ifPrinted === false || !$ifPrinted) {
                                $message = 'Unable to print, Try printing to another printer';
                            }else{
                                $success = 1;
                                $message = "Printed sucessfully";
                            }
                        }
                        fclose($fp);
                    }
                }
            }
        }else{
            $send_printer = 0;
            $message = 'No Printers Found, Please speak to IT Department';
        }

        return array("success" => $success , "message" => $message , "send_printer" => $send_printer,'all_printers' => $allPrinters);
    }

    public function getReportedBroken($grid = 0){
        $collection = $this->_skuCollection->create();
        $collection->addFieldToFilter('type', self::SKU_REPORT_TYPE_BROKEN);
        if (!$grid) {
            $collection->addFieldToFilter('problem_status', self::PROBLEM_STATUS_UNRESOLVED);
        }

        return $collection;
    }

    public function getBrokenCount(){
        $collection = $this->getReportedBroken(0);
        return count($collection->getData()??[]);
    }
    public function getBrokenTilesCount(){

        $collection = $this->getReportedBroken(0);
        $collection->addFilterToMap('sku','main_table.sku');
        $collection->join(array('history' =>'sales_order_item'), 'main_table.sku= history.sku',
            array('history.name','main_table.location','main_table.quantity', 'main_table.reported_by','main_table.reported_at','main_table.resolved_by','main_table.resolved_at','main_table.record_id'));
        $collection->getSelect()->group('record_id');

        return count($collection->getData()??[]);
    }
    public function getProblemOrdersCount(){
        $collection = $this->_skuCollection->create();
        $collection->addFieldToFilter('type', self::SKU_REPORT_TYPE_PROBLEM)->addFieldToFilter('problem_status', self::PROBLEM_STATUS_UNRESOLVED);
        $collection->join(array('order' =>'sales_order'), 'main_table.order_no= order.increment_id',
            array('order.dispatch_date')
        );
        $collection->join(array('pallet' =>'mb_order_processing_pallet'), 'main_table.order_no= pallet.op_increment_id',
            array('pallet.tablet_user','pallet.op_order_id')
        );

        $collection->addFieldToFilter('order.status', array("nin"=>array("complete","delivered_complete","closed")));
        return count($collection->getData());
    }

    public function getCheckingProblemCount(){
        $collection = $this->ordercheckingFactory->create();
        // only this filtered data shows in grid so changing count also
        $collection->join(array('history' =>'login_order_history'),  'main_table.order_id= history.tab_order_id',
            array('history.tab_order_id','history.tab_order_status','history.start_time','history.end_time','history.user')
        );
        $collection->addFieldToFilter("tab_order_status",array("eq" => 0));
        $collection->addFieldToFilter("status",array("eq" => 2));

        return count($collection->getData()??[]);
    }
    public function getChecksProblemCount(){
        $collection = $this->orderchecksFactory->create();
        $collection->addFieldToFilter("check_status",array("eq" => 0));
        return count($collection->getData()??[]);
    }
    public function getTabletPasswords(){
        $logoutPwd = $this->getConfiguration('tablet_config/tablet_config_fields/admin_logout');
        $settingsPwd = $this->getConfiguration('tablet_config/tablet_config_fields/tablet_settings');
        $scaleCheckPwd = $this->getConfiguration('tablet_config/tablet_config_fields/scale_check');

        return array("logoutPwd" => $logoutPwd , "settingsPwd" => $settingsPwd , "scaleCheckPwd" => $scaleCheckPwd);
    }
    public function getAuthorizedUsersConfig(){
        $authusers = $this->scopeConfig->getValue('tablet_config/general/authorized_users', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $authusers;
    }
    public function getBlindCheckConfig(){
        $blindcheck = $this->scopeConfig->getValue('config_section/tm_palletqueue/blind_ordercheck', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $blindcheck;
    }
    public function getConfiguration($path , $storeId = 0){
        $data = $this->scopeConfig->getValue($path);
        return $data;
    }

    public function ErrorLog($user,$type,$message,$entity_no){

    }

    //CHM-AR check tablet fields enable
    public function isTabletEnable(){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $is_enabled = $this->scopeConfig->getValue('tablet_config/general/enable_tablet', $storeScope);
        return $is_enabled;
    }
    //e.o CHM-AR check tablet fields enable


    public function getBlocationCollection()
    {
        $EntityUrl = $this->getConfiguration('tablet_config/general/b_location_url');
        // $EntityUrl = 'https://51.140.51.89:13067/BC130NAV/WS/Tile%20Mountain/Page/FCMWarehousePicks';
        $crossReference = array();
        try{
            $objEntity = $this->navservice->getEntityObject($EntityUrl);
        }catch(\Throwable $e){
            return 'soap-error';
        }
        try{
            $NavResponse = $objEntity->ReadMultiple(array("filter"=>array(array("Field"=>"No","Criteria"=>"*BPI*")),"setSize"=>0))->ReadMultiple_Result;

            $topOrderData = $NavResponse->FCMWarehousePicks;
            if (array_key_exists("0",$topOrderData))
            {
                // have to make index 0 for live
                foreach ($topOrderData[17]->WhseActivityLines as $Data) {
                    if($Data){
                        foreach ($Data??[] as $value) {
                            return ($value->Source_No);
                        }
                    }
                }
            }
            else
            {
                return "No Order in queue";
            }
        } catch(\Throwable $e){
            return 'response-error';
        }
    }

    public function DHLBatch($collectionOrders,$user)
    {
        $dhlBatchCollection = $this->dhlBatchNo->create();
        if($dhlBatchCollection->getData()){
            $count = $dhlBatchCollection->getSize();
            $totalCount = 1000+$count;
            $batchNo = "DHL-" . $totalCount;
            $dbarray['batch_no'] = $batchNo;
        }else{
            $dbarray['batch_no'] = 'DHL-1000';
            $batchNo = 'DHL-1000';
        }

        try {
            $connection = $this->_resource->getConnection();
            $tableName = $this->_resource->getTableName('dhl_batch_no');
            $connection->beginTransaction();
            $connection->insert($tableName, $dbarray);
            $connection->commit();
        }
        catch (\Throwable $th) {
            $connection->rollBack();
        }
        return $batchNo;
    }

    public function SetCombineBatch($collectionOrders,$user, $batchNo, $textWarehouse = ""){

        foreach ($collectionOrders ??[] as $orders) {
            $op_queue_id = $orders['op_queue_id'];
            $ordersmall = $this->sampleprocessingFactory->create();
            $ordersmall->load($op_queue_id);
            if($batchNo){
                $ordersmall->setBatchNumber($batchNo);
                $ordersmall->setCombinedPickGeneratedAt(date('Y-m-d H:i:s'));
            }
            if($this->pythonHelper->isEnablePython()){
                if($textWarehouse == "PW"){
                    $ordersmall->setPythonUser($user);
                    $ordersmall->setBatchQueueStatus(2);
                }else{
                    $ordersmall->setTabletUser($user);
                    $ordersmall->setBatchQueueStatus(2);
                }
            }
            else{
                $ordersmall->setTabletUser($user);
                $ordersmall->setBatchQueueStatus(2);
            }

            $ordersmall->save();
        }
        return true;
    }

    public function ChangeBatchUser($collectionOrders,$user){
        foreach ($collectionOrders ??[] as $orders) {
            $op_queue_id = $orders['op_queue_id'];
            $ordersmall = $this->sampleprocessingFactory->create();
            $ordersmall->load($op_queue_id);
            $ordersmall->setTabletUser($user);
            $ordersmall->setBatchQueueStatus(2);
            $ordersmall->save();
        }
        return;
    }

    public function applySalesJoinData($collection)
    {
        $collection->getSelect()
            ->joinLeft(
                ['sales' => "sales_order"],
                'main_table.op_order_id= sales.entity_id',
                [
                    'sales.shipping_method', 'sales.status','
                    sales.customer_firstname', 'sales.customer_lastname',
                    'sales.increment_id','sales.dispatch_date','sales.shipping_date', 'sales.total_qty_ordered','sales.created_at','sales.weight','delivery_note','easywms_movements'
                ]);

        return $collection;
    }



    public function getPendingCombinePickOrder($count = true , $user = "" , $grid = false, $isGrid=false){

        $dhlCollection = $this->dhlCollection->create();
        $dhlCollection=$this->pythonHelper->addFieldToWareHouse($dhlCollection,$isGrid);
        if($user){
            $dhlCollection->addFieldToFilter('tablet_user', array("in" => array($user)));
            $dhlCollection->addFieldToFilter('batch_queue_status', array("in"=>array(2,3)))->setOrder('batch_number','ASC');
        }else{
            $dhlCollection->addFieldToFilter('tablet_user', array('notnull' => true));
            $dhlCollection->addFieldToFilter('batch_queue_status', array("in"=>array(3)));
        }

        $dhlCollection->addFieldToFilter('batch_number', array('notnull' => true));
        $dhlCollection = $this->applySalesJoinData($dhlCollection);
        $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true));
        $dhlCollection->addFieldToFilter('status', array('in' => array("a_picking","pick_created","movement_created")));

        if($grid || $count){
            $dhlCollection->getSelect()->group('batch_number');
            if($count){
                return count($dhlCollection ??[]);
            }
            return $dhlCollection;
        }

        if($dhlCollection->getData()){
            $groupCollection = clone $dhlCollection; // clone the original collection if same user has more than one pause batch rare condition though
            $groupCollection->getSelect()->group('batch_number');
            $firstBatchOrder = $groupCollection->getFirstItem()->getData()["batch_number"];
            $dhlCollection->addFieldToFilter('batch_number', $firstBatchOrder); //return first batch only
            return $dhlCollection->getData();
        }
        return;
    }
    public function getActiveCombinePickOrder($count = true,$isGrid=false) {

        $dhlCollection = $this->dhlCollection->create();
        $dhlCollection=$this->pythonHelper->addFieldToWareHouse($dhlCollection,$isGrid);
        $dhlCollection->addFieldToFilter('batch_queue_status', array("in"=>array(1,2)));
        $dhlCollection->addFieldToFilter('main_table.batch_number', array('notnull' => true));

        $EasyWMS = (int)$this->getConfiguration('tablet_config/general/dhl_batch_easywms');
        if($EasyWMS){
            $dhlCollection->addFieldToFilter('main_table.batch_number', array('like' => "M0%"));
            $dhlCollection->join(
                ['EasyTable' => "tm_easywms_batchordersfromnav"],
                'main_table.batch_number = EasyTable.batch_number',
                [
                    'EasyTable.batch_status'
                ])->addFieldToFilter('batch_status' , [NewConfig::BATCH_IN_PROGRESS]);
        }else{
            $dhlCollection->addFieldToFilter('batch_number', array('like' => "DHL-%"));
        }

        $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true));
        $dhlCollection->getSelect()->group('main_table.batch_number');
        if($count) {
            return count($dhlCollection);
        }else{
            return $dhlCollection;
        }
    }

    public function getWaitingDhlBatches($return_count = false)
    {
        $BatchesRecord = $this->batchOrderFactory->create()->getCollection()
            ->addFieldToFilter('batch_status', [NewConfig::BATCH_READY_FROM_EASYWMS, NewConfig::BATCH_ERROR_ON_HOLD,NewConfig::BATCH_READY_TO_PICK]);

        if ($return_count){
            return $BatchesRecord->getSize();
        }

        return $BatchesRecord;
    }
    public function getReceivingChecksCount(){
        $collection = $this->orderchecksFactory->create();
        $collection->addFieldToFilter("check_status",array("eq" => 0));
        $collection->addFieldToFilter("order_type",array("eq" => "receiving"));
        return count($collection->getData());  
        }
      public function getLoadingChecksCount(){
        $collection = $this->orderchecksFactory->create();
        $collection->addFieldToFilter("check_status",array("eq" => 0));
        $collection->addFieldToFilter("order_type",array("eq" => "loading"));
        return count($collection->getData());  
       }

       public function FormatPalletData($pallet_no,$blindCheck,$datetime){
        
            $items = array();
            $listedSkus = array();
            $orderNumbers = array();
            $invalidStatuses = [
                self::PALLET_IS_CHECKED,
                self::PALLET_IN_TRANSIT,
                self::PALLET_MISSING,
                self::PALLET_PARTIALLY_SHIPPED,
                self::PALLET_UNLOADED
            ];
            try {
                $palletCollection = $this->mbPalletNumbers->create()->getCollection();
                $palletCollection->addFieldToFilter('pallet_number', $pallet_no);
                $palletModel = $palletCollection->getFirstItem();
    
                if ($palletModel->getId()) {
                    $checkStatus = $palletModel->getPalletStatus();
                    if (in_array($checkStatus, $invalidStatuses)) {
                       return "Pallet already checked.";
                    }
                    $palletOrders = $palletModel->getOrders();
                    $unserializedOrders = unserialize($palletOrders);
                    foreach ($unserializedOrders as $orderno) {
                     $order = $this->_ordermodel->create()->loadByIncrementId($orderno);
                     if($order->getIsChecked()){
                      
                     }
                     $allItems = $order->getAllItems();

                     $allItems = $this->_microconnectHelper->sortItems($allItems);

                     $numberofitems = count($allItems ??[]);
                     foreach ($allItems ??[] as $item) {

                        $qtyShipped = round($item->getQtyShipped(),0);
                        $qtyToShip = round($item->getQtyToShip(),0);

                        if ($qtyToShip <= 0) {
                            continue; // Skip items with quantity to ship 0, 0.0, or less than 0
                        }
                        if(!$item->getIsShopPick()){
                            $exp_data = $this->expeditingExists($orderno,$item->getSku());
 
                            $exp_status = 0;
                            if($exp_data){
                                $exp_status = $exp_data['status'];
                            }
                            $qty = round($item->getQtyOrdered(), 0);
                            $product = $item->getProduct();
                            $boxQty = (int)$product->getBoxQty();
                            $width = (int)$product->getWidth();
                            $height = (int)$product->getHeight();
        
                            if($item->getQtyRefunded() == $qty){
                                $isRefunded = 1;
                            }else{
                                $qty = $qty - $item->getQtyRefunded();
                                $isRefunded = 0;
                            }
                            if($qtyShipped){
                                $qty = $qty - $qtyShipped;
                             }
                            $quantity = (int)$qty;
        
                            if($width && $height){
                                $size = $width."x".$height;
                            }else{
                                $size = "";
                            }
                            $productdata = $this->_productRepositoryFactory->create()->getById($item->getProductId());
        
                            $image_url = $productdata->getData('thumbnail');
                            $BaseUrl = rtrim($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),'/');
                            $image_base_url = $BaseUrl.'/catalog/product'.$image_url;

                            if (!in_array($item->getSku(),$listedSkus)) {
                                $items[$item->getSku()] = array(
                                    'name'      => $item->getName(),
                                    'sku'       => trim($item->getSku()),
                                    "orders"      => array($orderno), // Initialize an array with the current order
                                    'quantity'  => $qty,
                                    'is_refunded' => $isRefunded,
                                    "boxes" => "Quantity: ".$quantity,
                                    "size" => $size,
                                    "status" => (!$exp_status) ? 0 : $exp_status,
                                    "image_url" => $image_base_url
                                );
                                $listedSkus[] = $item->getSku();
                            } else {
                                $items[$item->getSku()]["quantity"] = $items[$item->getSku()]["quantity"] + $qty;
                                $final_qty = explode(':', $items[$item->getSku()]["boxes"]);
                                $items[$item->getSku()]["boxes"] = "Quantity: ".($quantity + $final_qty[1]);
                                $items[$item->getSku()]["orders"][] = $orderno;
                            }
                        }
                     }
                     $productsArray = array_values($items);
                     $return_array = array(
                         'Order_Id' => $pallet_no,
                         'Order_No' => null,
                         'Picked_By' => null,
                         'No_of_Items' => $numberofitems,
                         'Customer_Comment' => null,
                         'picking_comment' => null,
                         'Picked_At' => null,
                         'items' => $productsArray,
                         'blindcheck' => $blindCheck,
                         'datetime' => $datetime
                     );
         
                    }
                } else {
                    return "Pallet Not Found";
                }
          return $return_array;
      } catch (\Exception $e) {
        return "Exception ".$e->getMessage();
    }
            
    }

    public function GenerateWoodenOrderShipment($order_id,$user,$enable_panther = 0){

        $woodModel = $this->woodFactory->create();
        $woodModel->load($order_id, 'wood_order_id');
        $order = $this->_ordermodel->create()->load($order_id);

        if ($order->canShip()) {
  
          $shipment = $this->convertOrder->toShipment($order);
          foreach ($order->getAllItems() as $orderItem)
          {
              $attributeName = $this->woodHelper->getAttributeSetName($orderItem->getSku());
  
              if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual() || $attributeName != 'Wood Panels') {
                  continue;
              }
  
              $qtyShipped = $orderItem->getQtyToShip();
              $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
              $shipment->addItem($shipmentItem);
          }
          $shipment->register();
          $shipment->getOrder()->setIsInProcess(true);
  
           try {
                if ($enable_panther) {
                    $trackingDetail = array(
                        'carrier_code' => 'tracker5',
                        'title' => 'PANTHER',
                        'number' => $woodModel->getDpdShipmentId(),
                        'description' => ''
                    );
                } else {
                    $trackingDetail = array(
                        'carrier_code' => 'tracker2',
                        'title' => 'DPD',
                        'number' => $woodModel->getConsingmentId(),
                        'description' => $woodModel->getParcelNumbers()
                    );
                }
              $trackFlag = 1;

              try {
                  if ($trackFlag) {
                      $track = $this->_trackFactory->create()->addData($trackingDetail);
                      $shipment->addTrack($track)->save();
                  }
              }catch (LocalizedException $e) {
                  echo $e->getMessage();
              }
              $shipment->save();

              if ($order->canShip()) {
                  $ord_status = "partially_shipped";
              } else {
                  $ord_status = "complete";
              }
              $shipment->getOrder()->addStatusToHistory($ord_status, 'Order processed on HandHeld by ' . $user );
              $shipment->getOrder()->save();

          } catch (\Exception $e) {
              $return = array("status" => false,"message" => "Error Shipment Not Created: #".$e->getMessage(), 'refresh' => false);
              return $return;  
          }
            $return = array("status" => true,"message" => "Order Completed Successfully", 'refresh' => false);
            return $return;  
         } else {
             $return = array("status" => false,"message" => "Shipment Not Created Because It's already created or something went wrong",'refresh' => false);
             return $return;
        }
      }

    public function GenerateDPDWoodenConsignment($order_id,$user,$enable_panther = 0) {
       $woodModel = $this->woodFactory->create();
       $woodModel->load($order_id, 'wood_order_id');
       $order = $this->_ordermodel->create()->load($order_id);
       $shippingDescription = $order->getShippingDescription();
       $queuename = $this->woodHelper->getQueueName($shippingDescription);
        
       $valid_status = array('processing','pick_created','a_picking');
       // Check if order is completed
       if(!in_array($order->getStatus(),$valid_status))
       {
            $return = array("status" => false , 'message' => "Unable to Print Order as Order is in status ".$order->getStatus(),'refresh' => true);
            return $return;
       }
       if (!$woodModel->getId()) {
           $woodModel->setData('wood_order_id', $order_id);
           $woodModel->setQueueName($queuename);
           $woodModel->setWoodIncrementId($order->getIncrementId());
        }
        // If Consigment Id is not there means not consigment generated 
        if ($woodModel->getConsingmentId() == null) {
            $woodItem = true;

            $woodModel->setWoodProcessedBy($user);

            if ($enable_panther) {
                $responseApi = $this->pantherHelper->pantherApiCall($order,1,$woodItem,true);
                $woodModel->setConsingmentId($order->getIncrementId());
                if (isset($responseApi['SelfServiceId'])) {
                    $woodModel->setDpdShipmentId($responseApi['SelfServiceId']);
                }
                
                if (isset($responseApi['consingmentId'])) {
                    $woodModel->setConsingmentId($responseApi['consingmentId']);
                }
            } else {

                $responseApi = $this->dpdHelperData->DpdApiCalls($order,1,false,$woodItem,false,true);
                if (isset($responseApi['shipmentId'])) {
                    $woodModel->setDpdShipmentId($responseApi['shipmentId']);
                    $woodModel->setParcelNumbers($responseApi['parcelNumbers']);
                    $woodModel->setConsingmentId($responseApi['consingmentId']);
                }
            }
       
            $history = $this->historyFactory->create();

            if (isset($responseApi['consingmentId']))
            {
                try {
                    if ($enable_panther) {
                        $directory = $this->localHelper->getLocalDirectory( "pantherlabels", $order->getStoreId() );
                        $fileName = $order->getIncrementId();
                        $inputPath = $directory . '/' . $fileName. "-panther-label-1.prn";
                        $outputPath = $directory . '/' . $fileName;
                        if (file_exists($inputPath)) {
                            $this->splitZPLPages($inputPath, $outputPath, $fileName);
                        }
                    } else {

                        $directory = $this->localHelper->getLocalDirectory( "dpdshippinglabels", $order->getStoreId() );
                        $fileName = $order->getIncrementId();
                        $inputPath = $directory . '/' . $fileName. ".pdf";
                        $outputPath = $directory . '/' . $fileName;
             
                        if (file_exists($inputPath)) {
                            $this->splitPdfPages($inputPath, $outputPath, $fileName);
                        }
                    }

                    $status = 'a_picking';
                    $this->updateOrderAttribute($order_id,$status);
                    $message = "Order is being picked by $user on Handheld ";
                    $history->setParentId($order_id)
                    ->setComment($message)
                    ->setIsCustomerNotified(false)
                    ->setEntityName('order')
                    ->setStatus($status);
              
                    $this->historyRepository->save($history);
                } catch (Exception $exception) {
                
                    $return = array("status" => false , 'message' => $exception->getMessage(),'refresh' => false);
                    return $return;
                }
  
            }elseif(isset($responseApi['error'])){
                $status = 'problem_order';
                $this->updateOrderAttribute($order_id,$status);
                $message = $responseApi['error'];
      
                $history->setParentId($order_id)
                 ->setComment($message)
                 ->setIsCustomerNotified(false)
                 ->setEntityName('order')
                 ->setStatus($status);
                try {
                 $this->historyRepository->save($history);
                 $return = array("status" => false , 'message' => "Consignment Failed Sending Order to Problem".$responseApi['error'],'refresh' => true);
                } catch (Exception $exception) {
                    $return = array("status" => false , 'message' => $exception->getMessage(),'refresh' => false);
                }
                return $return;

            }else{
                $return = array("status" => false , 'message' => "DPD/Panther Consignment failed",'refresh' => false);
                return $return;
            }
        }
        else // If Consigment id is generated but label not printed 
        {
            $history = $this->historyFactory->create();
            if ($enable_panther) {
                $directory = $this->localHelper->getLocalDirectory( "pantherlabels", $order->getStoreId() );
                $fileName = $order->getIncrementId();
                $inputPath = $directory . '/' . $fileName. "-panther-label-1.prn";
                $outputPath = $directory . '/' . $fileName;
                if (file_exists($inputPath)) {
                    $this->splitZPLPages($inputPath, $outputPath, $fileName);
                } else {
                    // $selfServiceId =  $this->pantherHelper->getSelfServiceId($order,true);
                    // $woodModel->setDpdShipmentId($selfServiceId);
                    $this->pantherHelper->getLabel($order,true);
                    if (file_exists($inputPath)) {
                        $this->splitZPLPages($inputPath, $outputPath, $fileName);
                    } else {
                        $status = 'problem_order';
                        $this->updateOrderAttribute($order_id,$status);
                        $message = $responseApi['error'];
                
                        $history->setParentId($order_id)
                            ->setComment($message)
                            ->setIsCustomerNotified(false)
                            ->setEntityName('order')
                            ->setStatus($status);
                        try {
                            $this->historyRepository->save($history);
                            $return = array("status" => false , 'message' => "Not able to generate PANTHER Labels Sending Order to Problem",'refresh' => true);
                            return $return;
                        } catch (Exception $exception) {
                            $return = array("status" => false , 'message' => $exception->getMessage(),'refresh' => false);
                            return $return;
                        }
                    }
                }
            } else {
                $directory = $this->localHelper->getLocalDirectory( "dpdshippinglabels", $order->getStoreId() );
                $fileName = $order->getIncrementId();
                $inputPath = $directory . '/' . $fileName. ".pdf";
                $outputPath = $directory . '/' . $fileName;
                // If Label File exists
                if (file_exists($inputPath)) {
                    $this->splitPdfPages($inputPath, $outputPath, $fileName);
                }else{ //get label from dpd api
                    $shipmentId = $woodModel->getDpdShipmentId();
                    $parcelNumbers = $woodModel->getParcelNumbers();

                    $this->dpdHelperData->getLabel($shipmentId, $order, $parcelNumbers, false);
                    if (file_exists($inputPath)) {
                        $this->splitPdfPages($inputPath, $outputPath, $fileName);
                    }else{ //If unable to get labels on dpd api call sending order to problem order

                        $status = 'problem_order';
                        $this->updateOrderAttribute($order_id,$status);
                        $message = $responseApi['error'];
                
                        $history->setParentId($order_id)
                            ->setComment($message)
                            ->setIsCustomerNotified(false)
                            ->setEntityName('order')
                            ->setStatus($status);
                        try {
                            $this->historyRepository->save($history);
                            $return = array("status" => false , 'message' => "Not able to generate DPD Labels Sending Order to Problem",'refresh' => true);
                            return $return;
                        } catch (Exception $exception) {
                            $return = array("status" => false , 'message' => $exception->getMessage(),'refresh' => false);
                            return $return;
                        }
                    }
                }
            }
            
            $status = 'a_picking';
            $this->updateOrderAttribute($order_id,$status);
            $message = "Order is being picked by $user on Handheld ";
            $history->setParentId($order_id)
                ->setComment($message)
                ->setIsCustomerNotified(false)
                ->setEntityName('order')
                ->setStatus($status);
            try {
                $this->historyRepository->save($history);
            } catch (Exception $exception) {
                $return = array("status" => false , 'message' => $exception->getMessage(),'refresh' => false);
                return $return;
            }
        }
        $woodModel->save();
      
    }

     public function updateOrderAttribute($orderId,$status)
     {
         $connection  = $this->_resource->getConnection();
         try {
             $where = ['entity_id = ?' => (int)$orderId];
             $whereforGrid = ['entity_id = ?' => (int)$orderId];
             $data = ["status"=>$status];
             $tableName = $connection->getTableName("sales_order");
             $tableTwo = $connection->getTableName("sales_order_grid");
             $connection->update($tableName, $data, $where);
             $connection->update($tableTwo, $data, $whereforGrid);
         }catch (\Exception $exception){
             return $exception->getMessage();
         }
     }

     public function updateOrderComment($status,$order_id,$message){

        $history = $this->historyFactory->create();
        
        $this->updateOrderAttribute($order_id,$status);
         $history->setParentId($order_id)
            ->setComment($message)
            ->setIsCustomerNotified(false)
            ->setEntityName('order')
            ->setStatus($status);
            $this->historyRepository->save($history);
     }

     public function splitPdfPages($inputPath, $outputPath, $filename) {

           if (!is_dir($outputPath))
         {
             mkdir($outputPath, 0777, true);
         }
         $pdf = new FPDI();
         $pagecount = $pdf->setSourceFile($inputPath); // How many pages?
   
   
         // Split each page into a new PDF
         for ($i = 1; $i <= $pagecount; $i++) {
             $new_pdf = new FPDI();
              
             $new_pdf->AddPage('P', array(107,107.5),180);
             $new_pdf->setSourceFile($inputPath);
             $new_pdf->useTemplate($new_pdf->importPage($i));
   
           try {
                if (!is_dir($outputPath))
               {
                   mkdir($outputPath, 0777, true);
               }
                $new_filename = $outputPath . '/' . $filename . '-'. $i . '.pdf';
               $new_pdf->Output($new_filename, "F");
 
           } catch (Exception $e) {
               //echo 'Caught exception: ',  $e->getMessage(), "\n";
           }
       }
       $pdf->close();
  }
  
    public function generateZPL($zpl,$order){
       $directory = $this->localHelper->getLocalDirectory( "owntransportlabels", $order->getStoreId() );
       $fileName = $order->getIncrementId();
       $fullUrl = $directory . '/' . $fileName. ".txt";
       if (file_exists($fullUrl)) {
           unlink($fullUrl);
       }
       $fp = fopen($fullUrl, 'w');
       fwrite($fp, print_r($zpl, true));
       fclose($fp); 
    }

    public function splitZPLPages($inputPath, $outputPath, $filename)
    {
        if (!is_dir($outputPath)) {
            mkdir($outputPath, 0777, true);
        }
        $zplContent = file_get_contents($inputPath);
        // $labels = preg_split('/\^X[A-Z]/', $zplContent, -1, PREG_SPLIT_DELIM_CAPTURE);

        $delimiter = '^XZ';
        $labels = explode($delimiter, $zplContent);

        $j = 1;
        foreach ($labels as $i => $label) {
            if (empty($label)) {
                continue; // Skip empty labels
            }
            // $label = '^XA' . $label .'^XZ';

            $label = $label.'^XZ';

            $prnFilePath = $outputPath.'/'.$filename.'-'.$j++.'.prn';

            if ($prnFile = fopen($prnFilePath, 'w')) {
                fwrite($prnFile, $label);
                fclose($prnFile);
            } 
        }
    }
}