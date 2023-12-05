<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Helper\TabletQueue;
use TM\EasyWms\Helper\NewConfig;

class WaitingBatches extends Extended
{
    protected $registry;

    public function __construct(
        Context                $context,
        Data                   $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry               $registry,
        TabletQueue            $helper,
        array                  $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('waiting_batch_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = $this->helper->getWaitingDhlBatches($return_count = false);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $__options = array(
            NewConfig::BATCH_READY_FROM_EASYWMS => 'Ready From EasyWms',
            NewConfig:: BATCH_READY_TO_PICK => 'Ready To Pick',
            NewConfig::BATCH_ERROR_ON_HOLD => 'Error On Hold',
            NewConfig::BATCH_IN_PROGRESS => 'In Progress'
        );

//        $this->addColumn(
//            'entity_id',
//            [
//                'header' => __('ID'),
//                'type' => 'text',
//                'index' => 'entity_id',
//                'sortable' => false
//            ]
//        );

        $this->addColumn(
            'status',
            [
                'header' => __('Batch Status'),
                'type' => 'options',
                'options' => $__options,
                'index' => 'batch_status',
                'filter_index' => 'batch_status',
//                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\BatchStatus',
                'sortable' => false
            ]
        );
        $this->addColumn(
            'received_at',
            [
                'header' => __('Received Date'),
                'index' => 'received_at',
                'filter_index' => 'received_at',
                'type' => 'datetime',
                'sortable' => false
            ]
        );

        $this->addColumn(
            'batch_number',
            [
                'header' => __('Batch Number'),
                'index' => 'batch_number',
                'filter_index' => 'batch_number',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'order_details',
            [
                'header' => __('Order Details'),
                'type' => 'text',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer\Orders'
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/dhl_queue_batches', ['_current' => true]);
    }
}

?>