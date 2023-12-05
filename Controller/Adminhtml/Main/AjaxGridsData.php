<?php

namespace TM\AndroidServices\Controller\Adminhtml\Main;

class AjaxGridsData extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;
    protected $_authorization;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \TM\AndroidServices\Model\ResourceModel\OrderChecks\Adjoin\CollectionFactory $checkCollection,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \TM\AndroidServices\Helper\TabletQueue $queuehelper,
        \TM\AndroidServices\Helper\Data $helper,
        \TM\AndroidServices\Block\Adminhtml\ShopQueue\Dashboard $shopQueue,
        \TM\AndroidServices\Block\Adminhtml\HuskyShopCollection\Dashboard $huskyDashboard,
        \TM\AndroidServices\Block\Adminhtml\HuskyReportedProblems\Dashboard $huskyProblems
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_queuehelper = $queuehelper;
        $this->_helper = $helper;
        $this->checkCollection = $checkCollection;
        $this->shopQueue = $shopQueue;
        $this->huskyDashboard = $huskyDashboard;
        $this->huskyProblems = $huskyProblems;
    }

    public function execute()
    {
        try {
            $queorders = $this->_queuehelper->filterOrdersByStoreAndShipping(1,true);

            // (0,'',0,0,0,0,$isGrid);
//     public function shopOrdersCollection($forGrid = 0,$blocation = 0,$isGrid=false){
//     public function getPendingCombinePickOrder($count = true , $user = "" , $grid = false, $isGrid=false){


            $ontabletorders = $this->_queuehelper->OrdersCollection(0,"",0,0,1,0,true);
            $shoporders = $this->_queuehelper->shopOrdersCollection(1,0,true);
            $returnorder = $this->_queuehelper->returnOrderCollection();
            $blocationorders = $this->_queuehelper->OrdersCollection(0,"",0,1,0,0,true);

            $dataArray = array(
                "brokencount" => $this->_helper->getBrokenCount(), //na
                "queuedcount" => $queorders, //done
                "ontabletcount" => count($ontabletorders->getData()), //done
                "problemcount" => $this->_helper->getProblemOrdersCount(),//n/a
                "shopcount" => count($shoporders->getData()), //done
                "checkingcount" => $this->_helper->getCheckingProblemCount(), //n/a
                "checkscount" => $this->_helper->getChecksProblemCount(), //naa
                "returncount" => $returnorder->count(), //na
                "blocationcount" => count($blocationorders->getData()),//done
                "brokentilescount" => 0, //$this->_helper->getBrokenTilesCount(),
                "DHL_Queue"=>$this->_queuehelper->getDhlQueueOrder(true,0,true), //done
                "DHLBatch_Queue"=>$this->_helper->getWaitingDhlBatches($return_count = true), //na
                "PendingCombinePick"=>$this->_helper->getPendingCombinePickOrder(), //done
                "ActiveCombinePick"=>$this->_helper->getActiveCombinePickOrder(true,true), //done
                "receivingcount" => $this->_helper->getReceivingChecksCount(), //NA
                "loadingcount" => $this->_helper->getLoadingChecksCount(), //NA
            ); 
              $getShopQueueGrids =  $this->shopQueue->getShopQueueGridsConfigValue();
              $huskyshopQueue = $this->huskyDashboard->getHuskyShopCollection();
              $huskyproblemQueue = $this->huskyProblems->getHuskyProblemCollection();

              $storeArray = [];
               foreach ($getShopQueueGrids as $value) {
                 $store_id = $this->_queuehelper->getStoreIds($value['title']);
                 $count = 0;
                 $storecount = $this->_queuehelper->OrdersCollection(0,"",0,0,0,$store_id,true);
                 if($storecount->getData()){
                  $count = count($storecount->getData());
                 }
                 $id = $value['id'];
                 $storeArray[] = ['count' => $count, 'id' => $id ];
               }

               $huskyShopCollection = [];
                foreach ($huskyshopQueue as $shopqueue) {
                 $store_id = $this->_queuehelper->getStoreIds($shopqueue['title']);
                 $count = $this->_queuehelper->getHuskyShopCollection($store_id,1);
                 $id = $shopqueue['id'];
                 $huskyShopCollection[] = ['count' => $count, 'id' => $id ];
               }

               $huskyProblem = [];
                foreach ($huskyproblemQueue as $shopqueue) {
                 $store_id = $this->_queuehelper->getStoreIds($shopqueue['title']);
                 $count = $this->checkCollection->create()->getCount($store_id);
                 $id = $shopqueue['id'];
                 $huskyProblem[] = ['count' => $count, 'id' => $id ];
               }
               
          } catch (\Exception $e) {
            echo $e->getMessage(); exit;
        }
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData(['success' => $dataArray,'showrooms' => $storeArray, 'HuskyShopCollection' => $huskyShopCollection, 'huskyProblem' => $huskyProblem]);
    }
}