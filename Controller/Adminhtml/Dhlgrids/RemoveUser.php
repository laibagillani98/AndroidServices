<?php

namespace TM\AndroidServices\Controller\Adminhtml\Dhlgrids;

use Magento\Framework\Controller\ResultFactory;
use TM\AndroidServices\Helper\Data;
use TM\AndroidServices\Helper\TabletQueue;
use TM\EasyWms\Helper\NewConfig;

class RemoveUser extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        ResultFactory $resultFactory,
        \Magento\Backend\App\Action\Context $context,
        \TM\AndroidServices\Model\WebServices $webservices,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \TM\PalletQueue\Model\OrderInvoiceFactory $palletOrder,
        Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \TM\Sampleprocessing\Model\ResourceModel\Sampleprocessing\CollectionFactory $dhlCollection,
        \TM\EasyWms\Model\BatchOrderFactory                                         $batchOrderFactory,
        \TM\Sampleprocessing\Model\SampleprocessingFactory $sampleprocessingFactory
    )
    {
        parent::__construct($context);
        $this->_resultFactory = $resultFactory;
        $this->_webservices = $webservices;
        $this->authSession = $authSession;
        $this->helper = $helper;
        $this->datetime = $datetime;
        $this->palletOrder = $palletOrder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dhlCollection = $dhlCollection;
        $this->sampleprocessingFactory = $sampleprocessingFactory;
        $this->batchOrderFactory = $batchOrderFactory;
    }

    public function execute()
    {

        $batch_number = $this->getRequest()->getParam('id');
       if($batch_number){
        $dhlCollection = $this->dhlCollection->create();
        $dhlCollection->addFieldToFilter('batch_number', $batch_number); 
      
        foreach ($dhlCollection as $orders) {
            $op_queue_id = $orders['op_queue_id'];
            $ordersmall = $this->sampleprocessingFactory->create();
            $ordersmall->load($op_queue_id);
            $ordersmall->setTabletUser(NULL);
            $ordersmall->setBatchQueueStatus(1);
            $ordersmall->save();
            
            
            
          }
          
          $BatchesRecord = $this->batchOrderFactory->create()->getCollection()
          ->addFieldToFilter('batch_number', $batch_number);
          foreach ($BatchesRecord as $batch) {
                $batch->setBatchStatus(NewConfig::BATCH_READY_TO_PICK)->save();
            }
          
          $return_array = array("response"=>true);
      }else{
        $return_array = array("response"=>false);
      }
  
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['success' => $return_array]);

    }


}