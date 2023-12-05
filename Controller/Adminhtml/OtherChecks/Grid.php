<?php

namespace TM\AndroidServices\Controller\Adminhtml\OtherChecks;

class Grid extends \Magento\Backend\App\Action
{
    
    
    protected $resultJsonFactory;
    protected $layoutFactory;

    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
         $this->resultJsonFactory = $resultJsonFactory;
         $this->layoutFactory = $layoutFactory;
        parent::__construct($context);
    }
    public function execute()
    {
         $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax())  
        {
            $test=Array
            (
                'Block' => $this->layoutFactory->create()->createBlock('TM\AndroidServices\Block\Adminhtml\OtherChecks\OtherChecks\Grid')->toHtml()
            );
            return $result->setData($test);
        }
        
    }
    
    
    
}