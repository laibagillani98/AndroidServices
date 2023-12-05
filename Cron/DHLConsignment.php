<?php
namespace TM\AndroidServices\Cron;

class DHLConsignment
{

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Status\HistoryFactory $historyFactory,
        \Magento\Sales\Api\OrderStatusHistoryRepositoryInterface $historyRepository,
        \Magento\Sales\Model\OrderFactory $ordermodel,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \TM\PalletQueue\Model\OrderInvoiceFactory $OrderInvoiceFactory,
        \Psr\Log\LoggerInterface $logger,
        \TM\Sampleprocessing\Helper\DHL\Data $DhlHelper,
        \Magento\Sales\Model\Order $Order,
        \TM\Sampleprocessing\Model\SampleprocessingFactory $sampleprocessingFactory,
        \TM\Sampleprocessing\Block\Adminhtml\Post\Index $postBlock,
        \TM\EasyWms\Cron\EasyWmsCronBatchUpdate $eazyBatch,
        \TM\Python\Helper\Data $pythonHelper
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->historyFactory = $historyFactory;
        $this->logger = $logger;
        $this->ordermodel = $ordermodel;
        $this->_palletOrderFactory = $OrderInvoiceFactory;
        $this->dhlHelper = $DhlHelper;
        $this->orderLoad = $Order;
        $this->_sampleprocessingFactory = $sampleprocessingFactory;
        $this->historyRepository = $historyRepository;
        $this->orderFactory = $orderFactory;
        $this->_postBlock = $postBlock;
        $this->_eazyBatch = $eazyBatch;
        $this->pythonHelper=$pythonHelper;
    }
   
    public function execute()
    {
        $this->_eazyBatch->UpdateBatch();
        $this->_eazyBatch->UpdatePickingBatch();
        $currenthour = date("H");
        if($currenthour < 1 || $currenthour > 18){
            //return;
        }
        
            $currentDHL = $this->_postBlock->getSmallOrderDispatchDate();
            $collection = $this->_orderCollectionFactory->create();
            $collection->addFieldToFilter('shipping_description', array('like' => '%DHL%'));
            $collection->join(array('small' =>'order_processing_small'),  'main_table.entity_id= small.op_order_id',
            array('small.consingment_id', 'small.consolidation_order', 'small.batch_number'));
            if($this->pythonHelper->isEnablePython()) {
                $ordWarehouse = $collection->getColumnValues('warehouse');
                if ($ordWarehouse[0] == 2) {
                    $collection->addFieldToFilter('py_consingment_id', array('null' => true));
                } elseif ($ordWarehouse[0] == 1) {
                    $collection->addFieldToFilter('consingment_id', array('null' => true));
                } elseif ($ordWarehouse[0] == 0){
                    $collection->addFieldToFilter('consingment_id', array('null' => true));
                    $collection->addFieldToFilter('py_consingment_id', array('null' => true));
                }
            } else{
                $collection->addFieldToFilter('consingment_id', array('null' => true));
            }
            $collection->addFieldToFilter('consolidation_order', 0);
            $collection->addFieldToFilter('main_table.status', array("in"=>array("pick_created","tablet_trial","processing","movement_created")));
            $collection->addFieldToFilter("dispatch_date",array("lteq"=>$currentDHL))->setOrder("batch_number","DESC");
            $collection->setPageSize(7);

            // echo "<pre>";
            // print_r($collection->getData()); exit;
            $warehouse = '';
            $responsePY = [];
            foreach ($collection->getData() as $order ){
                $OrderModel = $this->orderLoad->load($order['entity_id']);
                
                $warehouse = $OrderModel->getWarehouse();
                // $items_count = count($OrderModel->getAllVisibleItems());
                $items_count = 0;
                $py_items = 0;
                foreach ($OrderModel->getAllItems() as $item) {

                    if ($this->pythonHelper->isEnablePython()) {
                        if ($warehouse != 0 && $warehouse == $item->getWarehouse()) {
                            $items_count++;
                        } elseif($warehouse == 0) {
                            if ($item->getWarehouse() == 1) {
                                $items_count++;
                                $warehousetxt = 'HG2';
                            } elseif ($item->getWarehouse() == 2) {
                                $py_items++;
                                $warehousetxt = 'PY';
                            }
                        }
                    } else {
                        $items_count += $item->getQtyOrdered();
                    }
                }
                $items_count = intval($items_count);
                $py_items = intval($py_items);
                // echo $py_items.'----'.$warehousetxt; exit;
                if ($this->pythonHelper->isEnablePython()) {
                    if (!empty($items_count) && !empty($py_items)) {
                        $response = $this->dhlHelper->DhlApiCalls($OrderModel,$items_count,'',true,true,false,false,false,$warehouse);
                        $responsePY = $this->dhlHelper->DhlApiCalls($OrderModel,$py_items,'',true,true,false,false,false,2);
                    } elseif (!empty($items_count)) {
                        $response = $this->dhlHelper->DhlApiCalls($OrderModel,$items_count,'',true,true,false,false,false,$warehouse);
                    }
                } else {
                    $response = $this->dhlHelper->DhlApiCalls($OrderModel,$items_count,'',true,true);
                }
                if (isset($response['consingmentId'])) {
                    try {
                      $shipmentId = $response['consingmentId'];
                      $ordersmall = $this->_sampleprocessingFactory->create();
                      $ordersmall->load($order['entity_id'],"op_order_id");
                        if ($this->pythonHelper->isEnablePython()) {
                            if ($warehouse == 0) {
                                $consignmentPY = $responsePY['consingmentId'];
                                $ordersmall->setPyConsingmentId($consignmentPY);
                                $ordersmall->setConsingmentId($shipmentId);
                                $this->updateOrderHistoryandstatus($OrderModel,"Generated DHL Consignment: $consignmentPY by system For Warehouse : PY");
                                $this->updateOrderHistoryandstatus($OrderModel,"Generated DHL Consignment: $shipmentId by system For Warehouse : HG2");
                            }
                            if ($warehouse == 2) {
                                $warehousetxt = 'PY';
                                $ordersmall->setPyConsingmentId($shipmentId);
                                $this->updateOrderHistoryandstatus($OrderModel,"Generated DHL Consignment: $shipmentId by system For Warehouse : ".$warehousetxt);
                            } elseif($warehouse == 1){
                                $warehousetxt = 'HG2';
                                $ordersmall->setConsingmentId($shipmentId);
                                $this->updateOrderHistoryandstatus($OrderModel,"Generated DHL Consignment: $shipmentId by system For Warehouse : ".$warehousetxt);
                            }
                        } else {
                            $ordersmall->setConsingmentId($shipmentId);
                            $this->updateOrderHistoryandstatus($OrderModel,"Generated DHL Consignment: $shipmentId by system");
                        }
                        $ordersmall->save();  
                    } catch (\Exception $e) {
                        
                    }
                 }
                 else if (isset($response['error'])) {
                   if ($response['error'] != "") {
                    $orderIncrementId = $order["increment_id"];
                    $problem = $response['error'];
                    try {
                        if (isset($orderIncrementId)) {
                            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
                            $order->addStatusToHistory($order->getStatus(), 'DHL Consignment Failed - Sending order to Problem Order' . $problem);
                            $order->setStatus("problem_order");
                            $order->save();
                            //CHM-AR
                            $sample_processing = $this->_sampleprocessingFactory->create()->load($order->getId(),'op_order_id');
                            if($sample_processing->getOpQueueId()){
                                $sample_processing->setOpHoldReason($problem);
                                $sample_processing->setIsProblem("1");
                                $sample_processing->save();
                            }
                        }
                    }catch (\Exception $exception){
                        
                    }                     
                 }}
         }
         
         $this->_eazyBatch->UpdateBatch();
    }
    public function updateOrderHistoryandstatus($order,$message = "")
    {
        $history = $this->historyFactory->create();
        $orderId = $order->getId();
        $status = $order->getStatus();
        $history->setParentId($orderId)
            ->setComment($message)
            ->setIsCustomerNotified(false)
            ->setEntityName('order')
            ->setStatus($status);
        try {
            $this->historyRepository->save($history);
        } catch (Exception $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }
 
}
?>
