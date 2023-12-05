<?php
namespace TM\AndroidServices\Controller\Adminhtml\OtherChecks;

class Tempgrid extends \Magento\Backend\App\Action
{
    
    
    protected $resultPageFactory;

    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    public function execute()
    {
        return $this->resultPageFactory->create();
        
    }    
}