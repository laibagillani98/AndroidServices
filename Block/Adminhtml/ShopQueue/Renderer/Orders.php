<?php
 
namespace TM\AndroidServices\Block\Adminhtml\ShopQueue\Renderer;
 
use Magento\Framework\DataObject;
 
class Orders extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    
    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper
    ) {
        $this->_helper = $helper;
    }
 
    public function render(DataObject $row)
    {
        $orders = $this->_getValue($row);
        
        if($orders){
            $orders = unserialize($orders??[]);

            $html = '<ul>';

            foreach ($orders as $item) {
                $html .= "<li>" . $item . "</li>";
            }
            $html .="</ul>";

            return $html;
         }
        else{
            return $this->_getValue($row);
        }
        
    }
}