<?php

namespace TM\AndroidServices\Controller\Adminhtml\Gridactions;

use Magento\Framework\Controller\ResultFactory;
use TM\AndroidServices\Helper\Data;
use TM\AndroidServices\Helper\TabletQueue;

class Puttocall extends \Magento\Backend\App\Action
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
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Api\SearchCriteriaBuilder $criteria,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \TM\PalletQueue\Model\OrderInvoiceFactory $palletOrder,
        \Magento\Sales\Model\OrderFactory $ordermodel,
        Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \TM\AndroidServices\Api\SkuHistoryRepositoryInterface $skuHistoryRepository,
        \TM\AndroidServices\Api\Data\SkuHistoryInterface $skuHistoryinterface
    )
    {
        parent::__construct($context);
        $this->_resultFactory = $resultFactory;
        $this->_webservices = $webservices;
        $this->authSession = $authSession;
        $this->json = $json;
        $this->helper = $helper;
        $this->ordermodel = $ordermodel;
        $this->_criteria = $criteria;
        $this->_resource = $resource;
        $this->datetime = $datetime;
        $this->palletOrder = $palletOrder;
        $this->messageManager = $messageManager;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_skuHistoryRepository = $skuHistoryRepository;
        $this->_skuHistoryinterface = $skuHistoryinterface;
    }

    public function execute()
    {
        $record_id = $this->getRequest()->getParam('id');
        $comment = $this->getRequest()->getParam('comment');
        $completed_date = $this->datetime->gmtDate();
        $user = $this->authSession->getUser()->getData('username');
        //die("record_id:".$record_id);
        try {
            $problemModel = $this->_skuHistoryRepository->getById($record_id);
            $order_no = $problemModel->getOrderNo();

            $searchCriteria = $this->_criteria->addFilter('order_no', $order_no, 'eq')->addFilter('type',Data::SKU_REPORT_TYPE_PROBLEM, 'eq')->addFilter('problem_status', Data::PROBLEM_STATUS_UNRESOLVED, "eq")->create();
            $items = $this->_skuHistoryRepository->getList($searchCriteria);

            $connection = $this->_resource->getConnection();
            $connection->beginTransaction();

            foreach ($items->getItems() as $item) {

                $problemModel->unsetData();
                $problemModel = $this->_skuHistoryRepository->getById($item->getData('record_id'));
                $problemModel->setActionTaken("Put to Call");
                $problemModel->setAdminComment($comment);
                $problemModel->setResolvedBy($user);
                $problemModel->setResolvedAt($completed_date);
                $problemModel->setProblemStatus(Data::PROBLEM_STATUS_RESOLVED);
                $savedObj = $this->_skuHistoryRepository->save($problemModel);

            }

            $PalletModel = $this->palletOrder->create();
            $PalletOrderModel = $PalletModel->load( $order_no, 'op_increment_id' );
            if ($PalletOrderModel->getInQueue() == TabletQueue::TABLET_QUEUE_STATUS_PROBLEM_PARKED_ORDER) {
                $PalletOrderModel->setInQueue(TabletQueue::TABLET_QUEUE_STATUS_WAS_PARKED_ORDER);
            }else{
                $PalletOrderModel->setInQueue(TabletQueue::TABLET_QUEUE_STATUS_INQUEUE);
            }
            $PalletOrderModel->setTabletUser("");
            $PalletOrderModel->save();

            $model = $this->ordermodel->create();
            $order = $model->loadByIncrementId($order_no);
            $order->setStatus("to_call");
            $order->addStatusHistoryComment('Order Put to Call by '.$user);
            $order->addStatusHistoryComment('Comment added: '.$comment." by ".$user);
            $order->save();

            $connection->commit();
            $return_array = array("response"=>true);
            $this->messageManager->addSuccess("Order Put to Call");

        } catch (\Exception $e) {
            $connection->rollBack();
            $this->messageManager->addError($e->getMessage());
            $return_array = array("response"=>false);
            $this->helper->ErrorLog($user,"Puttocall",$e->getMessage(),"record id:".$record_id);
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['success' => $return_array]);

    }


}