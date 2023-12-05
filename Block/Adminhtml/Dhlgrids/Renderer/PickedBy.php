<?php

namespace TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer;

use Magento\Framework\DataObject;

class PickedBy extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $dhlCollection->addFieldToFilter('batch_number', $batch_number);
        $dhlCollection = $this->_helper->applySalesJoin($dhlCollection);
        $dhlCollection->setOrder("status", "ASC");

        $tabletUser = '';

        if ($dhlCollection && $dhlCollection->getSize() > 0) {
            foreach ($dhlCollection as $order) {
                if ($this->pythonHelper->isEnablePython()) {
                    if ($order->getWarehouse() == 0) {
                        if($order->getTabletUser() && $order->getPyTabletUser()) {
                            $tabletUser = "Py User: " . $order->getPyTabletUser() . " Tablet User: " . $order->getTabletUser();
                        }
                        elseif ($order->getPyTabletUser()) {
                            $tabletUser = "Py User: " . $order->getPyTabletUser();
                        }
                        elseif ($order->getTabletUser()) {
                            $tabletUser = "Tablet User: " . $order->getTabletUser();
                        }
                    }
                    if ($order->getWarehouse() == 1) {
                        if ($order->getTabletUser()) {
                            $tabletUser = "Tablet User: " . $order->getTabletUser();
                        }
                    }
                    if ($order->getWarehouse() == 2) {
                        if ($order->getPyTabletUser()) {
                            $tabletUser = "Py User: " . $order->getPyTabletUser();
                        }
                    }
                } else {
                    $tabletUser = $order->getTabletUser();
                }
            }
        }

        return $tabletUser;
    }
}
