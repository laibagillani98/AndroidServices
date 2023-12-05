<?php

namespace TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer;

use Magento\Framework\DataObject;

class Orders extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Model\OrderFactory $ordermodel,
        \TM\Sampleprocessing\Model\ResourceModel\Sampleprocessing\CollectionFactory $dhlCollection,
        \TM\Python\Helper\Data $pythonHelper
    ) {
        $this->_helper = $helper;
        $this->scopeConfig = $scopeConfig;
        $this->_ordermodel = $ordermodel;
        $this->dhlCollection = $dhlCollection;
        $this->pythonHelper = $pythonHelper;
    }

    public function render(DataObject $row)
    {
        $username = $this->_getValue($row);
        $batch_number = $row->getData("batch_number");

        $dhlCollection = $this->dhlCollection->create();
        // $dhlCollection->addFieldToFilter('batch_queue_status', array("in"=>array(2)));
        $dhlCollection->addFieldToFilter('batch_number', $batch_number); 
        $dhlCollection = $this->_helper->applySalesJoin($dhlCollection);
        $dhlCollection->setOrder("status","ASC");
          if($dhlCollection){
            if($dhlCollection->getData()){

            $html = '<ul class="order-li-wrapper">';
$i = 1;

            foreach ($dhlCollection->getData() as $orders) {
                $_order = $this->_ordermodel->create()->load($orders["op_order_id"]);
                $_allItems = $_order->getAllItems();
                $consignmentId = $orders["consingment_id"];
                foreach($_allItems as $_item){
                    if($this->pythonHelper->isEnablePython() ){
                        if($orders['warehouse'] == 0){
                            if($_item->getWarehouse() == 1){
                                $consignmentId = $orders["consingment_id"];
                            }
                            if ($_item->getWarehouse() == 2){
                                $consignmentId = $orders["py_consingment_id"];
                            }
                        }
                        elseif($orders['warehouse'] == 1){
                            $consignmentId = $orders["consingment_id"];
                        }
                        elseif($orders['warehouse'] == 2){
                            $consignmentId = $orders["py_consingment_id"];
                        }
                    }

                    $_isproblem = "no_problem";
                    if(!in_array($orders["status"],array("pick_created","a_picking"))){
                        $_isproblem = "problem";
                    }
                    
                    if(in_array($orders["status"],array("complete"))){
                        $_isproblem = "ok";
                    }
                    $movement_numbers = 'No of Scans: '. $orders["scan_no"];
                    
                    if($orders["status"] == "movement_created" && $orders["easywms_movements"]){
                        $movement_numbers = "<br>" . str_replace(",","<br>",$orders["easywms_movements"]);
                    }
                    
                    $html .= '<li class="' . $_isproblem . '" > ' . $i . " - " . $_item->getSku() . " -- " . $orders["increment_id"]. ' (' .$orders["status"]. ')' . ' (' .$consignmentId. ')' .' -- '. $movement_numbers .' </li>';
                $i++;
                }
                
            }
            $html .= '</ul>';
    
            return $html;
          } 
        }
         return '';
    }
}