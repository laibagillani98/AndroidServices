<?php

namespace TM\AndroidServices\Controller\Adminhtml\Tabletqueuegrids;

class AjaxGrids extends \Magento\Backend\App\Action
{
	protected $resultPageFactory = false;
	protected $_authorization;

	public function __construct(
		\Magento\Backend\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\TM\AndroidServices\Block\Adminhtml\ShopQueue\Dashboard  $dashboard,
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
		}, $huskyshopQueue);
		
		$storeProblem = array_map(function ($item) {
			return $item['id'];
		}, $huskyproblemQueue);

		$storeChecks = array("receiving_checks","loading_checks");

		try {
			switch ($grid) {
			    case "queue_orders":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\Queueorders';
			    break;
			    case "shop_orders":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\ShopOrders';
			    break;
			    case "completed_orders":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\CompletedOrders';
			    break;
			    case "picked_products":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\PickedProducts';
			    break;
			    case "broken_tiles":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\BrokenTiles';
			    break;
			    case "problem_orders":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\ProblemOrders';
			    break;
			    case "problem_orders_log":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\ProblemOrdersLog';
			    break;
			    case "order_checking":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\OrdersChecking';
			    break;
				case "order_checks":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\OrderChecks';
				break;
				case "b_location":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\BLocation';
				break;
				case "completed_combined_picks":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\CompletedCombinedPicks';
				break;
				case "active_combined_picks":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\ActiveCombinedPicks';
				break;
				case "pending_combined_picks":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\PendingCombinedPicks';
				break;
				case "dhl_queue":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\DhlQueue';
				break;
                case "breakages":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\Breakages';
				break;
				case "breakages_log":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\BreakagesLog';
				break;
                case "active_orders":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\Activeorders';
			    break;
                case "dhl_queue_batches":
				$gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\WaitingBatches';
			    break;
				case "shop_completed_order_grid":
			    $gridType = 'TM\AndroidServices\Block\Adminhtml\ShopQueue\ShopCompletedOrders';
			    break;
				case in_array($grid, $storeNames):
				$gridType = 'TM\AndroidServices\Block\Adminhtml\ShopQueue\Showroom';
				break;
				case in_array($grid, $shopCollection):
				$gridType = 'TM\AndroidServices\Block\Adminhtml\HuskyShopCollection\Showroom';
				break;
				case in_array($grid, $storeProblem):
				$gridType = 'TM\AndroidServices\Block\Adminhtml\HuskyReportedProblems\Showroom';
				break;
				case in_array($grid, $storeChecks):
				$gridType = 'TM\AndroidServices\Block\Adminhtml\ShopQueue\Checks';
				break;
			  default:
                $gridType = 'TM\AndroidServices\Block\Adminhtml\Allgrids\Queueorders';
			    echo "Path Not Found";
			}
			
			$this->_view->loadLayout();
	        $this->getResponse()->setBody(
	            $this->_view->getLayout()->createBlock($gridType)->toHtml()
	        );
		} catch (Exception $e) {
		}
		//return $resultPage;
	}
}