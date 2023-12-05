<?php

namespace TM\AndroidServices\Controller\Adminhtml\OtherChecks;

class Dashboard extends \Magento\Backend\App\Action
{
    
    protected $resultPageFactory;

    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

   /*  protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TM_Microconnect::microconnect_deliverydat_order');
    } */
    
    public function execute()
    {
        return $this->resultPageFactory->create();   
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('TM_AndroidServices::otherchecks_dashboard');
    }
}