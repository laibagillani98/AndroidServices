<?php

namespace TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer;

use Magento\Framework\DataObject;

class Products extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $ordermodel
    ) {
        $this->_helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->_ordermodel = $ordermodel;

    }

    public function render(DataObject $row)
    {
        $username = $this->_getValue($row);
          
        $order_id = $row->getData("op_order_id");
        $OrderModel = $this->_ordermodel->create()->load( $order_id );
        $allItems = $OrderModel->getAllItems();
        foreach ($allItems as $item) {
          $qty = round($item->getQtyOrdered(), 0);
            $return_array[] = array(
              'sku'      => $item->getSku(),
              'quantity'      =>  $qty
            );
          }
          if($return_array){

            $html = '<ul>';

            foreach ($return_array as $product) {
                $html .= '<li>Sku: ' . $product['sku'] . ', Qty: '. $product['quantity'] . '</li>';
            }
    
            $html .= '</ul>';
    
            return $html;
          }else{
            return '';
          }

    }
}