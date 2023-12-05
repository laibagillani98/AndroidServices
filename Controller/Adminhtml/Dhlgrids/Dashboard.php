<?php

namespace TM\AndroidServices\Controller\Adminhtml\Dhlgrids;

class Dashboard extends \Magento\Backend\App\Action
{
	protected $resultPageFactory = false;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	)
	{
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		$this->_view->loadLayout();
		$resultPage = $this->resultPageFactory->create();
		//$resultPage->getConfig()->getTitle()->prepend((__('Warehouse Report from Android')));

		return $resultPage;
	}
	protected function _isAllowed()
	{
	 	return $this->_authorization->isAllowed('TM_AndroidServices::dhl_grids');
	}

} 