<?php

namespace TM\AndroidServices\Controller\Adminhtml\Gridactions;

use Magento\Framework\Controller\ResultFactory;

class WriteOff extends \Magento\Backend\App\Action
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
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	)
	{
		parent::__construct($context);
		$this->_resultFactory = $resultFactory;
		$this->_webservices = $webservices;
		$this->authSession = $authSession;
		$this->json = $json;
		$this->messageManager = $messageManager;
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		$record_id = $this->getRequest()->getParam('id');
		
		try {
			
			$user = $this->authSession->getUser()->getData('username');
			$result = $this->_webservices->completeBrokenTiles($record_id,$user,1);
			$return = $this->json->unserialize($result);
			
			if ($return['status']) {
				$this->messageManager->addSuccess($return['message']);
			}else{
				$this->messageManager->addError($return['message']);
			}
			
		} catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());
			$this->helper->ErrorLog($user,"WriteOff",$e->getMessage(),"record id:".$record_id);
		}

		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);    
		$resultRedirect->setUrl($this->_redirect->getRefererUrl()); 
		return $resultRedirect;

	}


}