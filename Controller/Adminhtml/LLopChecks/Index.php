<?php

/**

* Copyright © Magento, Inc. All rights reserved.

* See COPYING.txt for license details.

*/

namespace TM\AndroidServices\Controller\Adminhtml\LLopChecks;

use Magento\Backend\App\Action;

use Magento\Backend\App\Action\Context;

use Magento\Framework\View\Result\PageFactory;

/**

* Main page controller

*/

class Index extends Action {

/**

* @var PageFactory

*/

protected $resultPageFactory;

/**

* @param Context     $context

* @param PageFactory $resultPageFactory

*/

public function __construct(

Context $context,

PageFactory $resultPageFactory

) {

parent::__construct($context);

$this->resultPageFactory = $resultPageFactory;

}

/**

* @return \Magento\Framework\View\Result\PageFactory

*/

public function execute() {

 $resultPage = $this->resultPageFactory->create();

$resultPage->setActiveMenu('TM_LLopChecks::grid');

$resultPage->addBreadcrumb(__('Manage Grid View'), __('Manage Grid View'));

$resultPage->getConfig()->getTitle()->prepend(__('Manage Blog'));

return $resultPage;

}
}