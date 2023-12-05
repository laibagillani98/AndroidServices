<?php

namespace TM\AndroidServices\Block\Adminhtml\ShopQueue;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\OrderChecks\CollectionFactory;

class Checks extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        CollectionFactory $ordercheckingFactory,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->ordercheckingFactory = $ordercheckingFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('orderchecks');
        $checks = $this->getRequest()->getParam('type');
        switch ($checks) {
            case "receiving_checks":
             $this->setDefaultFilter(array('check_status' => 4));
             break;
             case "loading_checks":
                $this->setDefaultFilter(array('check_status' => 5));
            break;
        }
        $this->setDefaultSort('check_end_time');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->ordercheckingFactory->create();
        $checks = $this->getRequest()->getParam('type');

        switch ($checks) {
            case "receiving_checks":
                $collection->addFieldToFilter("order_type",array("eq" => "receiving"));
                break;
            case "loading_checks":
                $collection->addFieldToFilter("order_type",array("eq" => "loading"));
                // $collection->addFieldToFilter("check_status",array("eq" => "4"));
                break;
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
      
        $this->addColumn(
            'order_no',
            [
                'header' => __('Order Number'),
                'type' => 'text',
                'index' => 'order_no',
                'filter_index' => 'order_no',
                'sortable'  => true
            ]
        );

        $this->addColumn(
            'checked_by',
            [
                'header' => __('Checked By'),
                'type' => 'text',
                'index' => 'checked_by',
                'filter_index' => 'checked_by',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'check_start_time',
            [
                'header' => __('Check Start Time'),
                'index' => 'check_start_time',
                'filter_index' => 'check_start_time',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'check_end_time',
            [
                'header' => __('Check End Time'),
                'index' => 'check_end_time',
                'filter_index' => 'check_end_time',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'check_status',
            [
                'header' => __('Check Status'),
                'index' => 'check_status',
                'type'      => 'text',
                'sortable'  => false,
                'filter' => false, // Disable filtering
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\ShopQueue\Renderer\CheckStatus'
            ]
        );

        $this->addColumn(
            'problem',
            [
                'header' => __('Problem'),
                'type' => 'text',
                'index' => 'problem',
                'filter_index' => 'problem',
                'sortable'  => false
            ]
        );
  
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        $checks = $this->getRequest()->getParam('type');
        return $this->getUrl('*/*/ajaxgrids/type/'.$checks, ['_current' => true]);
    }
}

?>