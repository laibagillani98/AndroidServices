<?php
 
namespace TM\AndroidServices\Block\Adminhtml\ShopQueue\Renderer;
 
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Stores extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper,
        StoreManagerInterface $storeManager
    ) {
        $this->_helper = $helper;
        $this->storeManager = $storeManager;
    }
 
    public function render(DataObject $row)
    {
        $storeid = $this->_getValue($row);
        
        if($storeid){
            try {
                $store = $this->storeManager->getStore($storeid);
                $storeName = $store->getName();
                return $storeName;
            } catch (\Exception $e) {
                return $this->_getValue($row);
            }
        }
        else{
            return $this->_getValue($row);
        }
        
    }
}