<?php

namespace TM\AndroidServices\Block\Adminhtml\Order\View\Tab;


class Piccontainer extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    protected $_template = 'order/view/piccontainer.phtml';

    protected $coreRegistry = null;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \TM\Tmunifi\Block\Adminhtml\Piccontainer $Piccontainer,
        \Magento\Framework\Stdlib\DateTime\DateTime $DateTime,
        \TM\AndroidServices\Model\OrderChecksFactory $orderchecks,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->storeManager = $storeManager;
        $this->Piccontainer = $Piccontainer;
        $this->DateTime = $DateTime;
        $this->orderchecks = $orderchecks;
        parent::__construct($context, $data);
    }


    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    public function getSignatureImages()
    {
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        $current_order = $this->coreRegistry->registry('current_order')->getIncrementId();

         $collection = $this->orderchecks->create();
         $collection = $collection->getCollection();
         $collection = $collection->addFieldToFilter('order_no', array('eq' => $current_order));
         $collection = $collection->addFieldToFilter("order_type",array("eq" => "loading"));

         if($collection->getSize()>0)
         {
            foreach ($collection as  $col) 
            {
                $folder_loc = 'orderSignature/';
                $pic_a = $mediaUrl.$folder_loc.$col->getSignature();
                $time_a = $col->getCheckStartTime();
                $time_b = $col->getCheckEndTime();
                $user = $col->getCheckedBy();
            }
            return array($pic_a, $time_a,  $time_b, $user);
        }  
          else {
            echo "<span>Pallet Order not loaded</span>";
         }
    }

    public function getTabLabel()
    {
        return __('Signatures');
    }

    public function getTabTitle()
    {
        return __('Signatures');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }


}
