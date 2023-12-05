<?php

namespace TM\AndroidServices\Controller\Adminhtml\Gridactions;

use Magento\Framework\Controller\ResultFactory;
use TM\AndroidServices\Helper\Data;
use TM\AndroidServices\Helper\TabletQueue;

class AssignUser extends \Magento\Backend\App\Action
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
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime
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
    }

    public function execute()
    {
        $order_id = $this->getRequest()->getParam('id');
        $user = $this->getRequest()->getParam('user');
        $completed_date = $this->datetime->gmtDate();
        $adminUser = $this->authSession->getUser()->getData('username');
        try {
            $PalletModel = $this->palletOrder->create();
            $PalletOrderModel = $PalletModel->load( $order_id, 'op_order_id' );
            $PalletOrderModel->setTabletUser($user);
            $PalletOrderModel->setInQueue(TabletQueue::TABLET_QUEUE_STATUS_INQUEUE);
            $PalletOrderModel->setIsPicked(1);
            $PalletOrderModel->save();
            $return_array = array("response"=>true);
        } catch (\Exception $e) {
            $return_array = array("response"=>false);
            //$this->messageManager->addError($e->getMessage());
            $this->helper->ErrorLog($adminUser,"AssignUser",$e->getMessage(),"order id:".$order_id);
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['success' => $return_array]);

    }


}