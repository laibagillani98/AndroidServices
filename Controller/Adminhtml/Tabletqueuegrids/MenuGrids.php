<?php

namespace TM\AndroidServices\Controller\Adminhtml\Tabletqueuegrids;

class MenuGrids extends \Magento\Backend\App\Action
{
	protected $resultPageFactory = false;
	protected $_authorization;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\TM\AndroidServices\Block\Adminhtml\ShopQueue\Dashboard $dashboard,
		\TM\AndroidServices\Block\Adminhtml\HuskyShopCollection\Dashboard $huskyDashboard,
		\TM\AndroidServices\Block\Adminhtml\HuskyReportedProblems\Dashboard $huskyProblems
	)
	{
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->dashboard = $dashboard;
		$this->huskyDashboard = $huskyDashboard;
		$this->huskyProblems = $huskyProblems;
	}

	public function execute()
	{
		$grid = $this->getRequest()->getParam('type'); 

		$shopQueue = $this->dashboard->getShopQueueGridsConfigValue();
		$huskyshopQueue = $this->huskyDashboard->getHuskyShopCollection();
		$huskyproblemQueue = $this->huskyProblems->getHuskyProblemCollection();

		$storeNames = array_map(function ($item) {
		   return $item['id'];
	   }, $shopQueue);

	    $shopCollection = array_map(function ($item) {
		  return $item['id'];
    	}, $shopQueue);

		$storeProblem = array_map(function ($item) {
			return $item['id'];
		}, $huskyproblemQueue);

		$storeChecks = array("receiving_checks","loading_checks");
		
		try {
			$this->_view->loadLayout();
			$resultPage = $this->resultPageFactory->create();
			switch ($grid) {
			  case "queue_orders":
			    $resultPage->getConfig()->getTitle()->prepend(__('Queued Orders Grid'));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\Queueorders');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\Queueorders'));
			    break;
			    case "shop_orders":
			    $resultPage->getConfig()->getTitle()->prepend((__('Shop Orders Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\ShopOrders');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\ShopOrders'));
			    break;
			  case "completed_orders":
			    $resultPage->getConfig()->getTitle()->prepend((__('Completed Orders Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\CompletedOrders');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\CompletedOrders'));
			    break;
			  case "picked_products":
			    $resultPage->getConfig()->getTitle()->prepend((__('Picked Products Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\PickedProducts');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\PickedProducts'));
			    break;
			  case "broken_tiles":
			    $resultPage->getConfig()->getTitle()->prepend((__('Broken Tiles Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\BrokenTiles');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\BrokenTiles'));
			    break;
			  case "problem_orders":
			    $resultPage->getConfig()->getTitle()->prepend((__('Problem Orders Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\ProblemOrders');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\ProblemOrders'));
			    break;
			    case "problem_orders_log":
			    $resultPage->getConfig()->getTitle()->prepend((__('Problem Orders Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\ProblemOrdersLog');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\ProblemOrdersLog'));
			    break;
			    case "order_checking":
			    $resultPage->getConfig()->getTitle()->prepend((__('Product Checking Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\OrdersChecking');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\OrdersChecking'));
			    break;
				case "order_checks":
				$resultPage->getConfig()->getTitle()->prepend((__('Order Checking Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\OrderChecks');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\OrderChecks'));
				break;
				case "b_location":
				$resultPage->getConfig()->getTitle()->prepend((__('B Location Orders Grid')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\BLocation');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\BLocation'));
				break;

				case "completed_combined_picks":
				$resultPage->getConfig()->getTitle()->prepend((__('Completed Combined Picks')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Dhlgrids\CompletedCombinedPicks');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Dhlgrids\CompletedCombinedPicks'));
				break;
				case "active_combined_picks":
				$resultPage->getConfig()->getTitle()->prepend((__('Active Combined Picks')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Dhlgrids\ActiveCombinedPicks');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Dhlgrids\ActiveCombinedPicks'));
				break;
				case "dhl_queue":
				$resultPage->getConfig()->getTitle()->prepend((__('DHL Queue')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Dhlgrids\DhlQueue');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Dhlgrids\DhlQueue'));
				break;
				case "breakages":
				$resultPage->getConfig()->getTitle()->prepend((__('Breakages')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\Breakages');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\Breakages'));
				break;
				case "breakages_log":
				$resultPage->getConfig()->getTitle()->prepend((__('Breakages Log')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\BreakagesLog');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\BreakagesLog'));
				break;
                case "active_orders":
			    $resultPage->getConfig()->getTitle()->prepend(__('OnTablet Orders Grid'));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\Activeorders');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\Allgrids\Activeorders'));
			    break;
				case in_array($grid, $storeNames):
				$resultPage->getConfig()->getTitle()->prepend(__('OnTablet Orders Grid'));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\ShopQueue\Showroom');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\ShopQueue\Showroom'));
				break;
				case "shop_completed_order_grid":
				$resultPage->getConfig()->getTitle()->prepend((__('Shop Completed Orders')));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\ShopQueue\ShopCompletedOrders');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\ShopQueue\ShopCompletedOrders'));
				break;
				case in_array($grid, $storeChecks):
				$resultPage->getConfig()->getTitle()->prepend(__('OnTablet Orders Grid'));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\ShopQueue\Checks');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\ShopQueue\Checks'));
				break;
				case in_array($grid, $shopCollection):
				$resultPage->getConfig()->getTitle()->prepend(__('Shop Collection Grid'));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\HuskyShopCollection\Showroom');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\HuskyShopCollection\Showroom'));
				break;
				case in_array($grid, $storeProblem):
				$resultPage->getConfig()->getTitle()->prepend(__('Reported Problems Grid'));
				$this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\HuskyReportedProblems\Showroom');
				$this->_addContent($this->_view->getLayout()->createBlock('TM\AndroidServices\Block\Adminhtml\HuskyReportedProblems\Showroom'));
				break;

			  default:
			    echo "Path Not Found";
			}

			$this->_view->renderLayout();
		} catch (Exception $e) {
			//die($e->getMessage());
		}
		//return $resultPage;

	}
}