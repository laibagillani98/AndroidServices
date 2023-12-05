<?php
 
namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;
 
use Magento\Framework\DataObject;
 
class TimeinMins extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper
    ) {
        $this->_helper = $helper;
    }
 
    public function render(DataObject $row)
    {
        $milli_secs = $this->_getValue($row);
        if ($milli_secs) {
            return $this->_helper->convertToMins($milli_secs);
        }else{
            return "";
        }
        
        
    }
}