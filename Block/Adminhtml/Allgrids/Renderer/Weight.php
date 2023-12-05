<?php
 
namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;
 
use Magento\Framework\DataObject;
 
class Weight extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper
    ) {
        $this->_helper = $helper;
    }
 
    public function render(DataObject $row)
    {
        $weight = $this->_getValue($row);
        $isPriority = $row->getData("easywms_priority");
        
        if($isPriority){
            $weight = round($weight , 2);
            $weight = "<div style='color:red'>$weight</div>";
        }
        
        
        if($weight){
            return $weight;
        }else{
            return "";
        }
        
    }
}