<?php

namespace TM\AndroidServices\Block\Adminhtml\Dhlgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use TM\AndroidServices\Helper\TabletQueue;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory as Pallexcollection;

class ActiveCombinedPicks extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        Pallexcollection $palletCollection,
        TabletQueue $helper,
        \TM\Sampleprocessing\Model\ResourceModel\Sampleprocessing\CollectionFactory $dhlCollection,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_palletCollection = $palletCollection;
        $this->dhlCollection = $dhlCollection;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('active_combined');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $dhlCollection = $this->helper->activeBatches();
        $this->setCollection($dhlCollection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn(
            'batch_number',
            [
                'header' => __('Batch Number'),
                'type' => 'text',
                'index' => 'batch_number',
                'filter_index' => 'main_table.batch_number',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'batch_queue_status',
            [
                'header' => __('Pick Status'),
                'type' => 'text',
                'index' => 'batch_queue_status',
                'filter_index' => 'batch_queue_status',
                'sortable'  => false
            ]
        );
        $this->addColumn(
            'order_numbers',
            [
                'header' => __('Order Numbers'),
                'type' => 'text',
                // 'index' => 'order_numbers',
                // 'filter_index' => 'order.increment_id',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer\Orders'
            ]
        );
        $this->addColumn(
            'tablet_user',
            [
                'header' => __('Assigned User'),
                'index' => 'tablet_user',
                'type' => 'text',
                'sortable'  => false,
                'filter_index' => 'tablet_user',
            ]
        );

        $this->addColumn(
            'combined_pick_generated_at',
            [
                'header' => __('Assigned Date'),
                'index' => 'combined_pick_generated_at',
                'type' => 'datetime',
                'filter_index' => 'combined_pick_generated_at',
                'sortable'  => false
            ]
        );

        // $this->addColumn(
        //     'action',
        //     [
        //         'header' => __('Action'),
        //         'type' => 'text',
        //         'renderer' => 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer\BatchAction',
        //         'sortable'  => false
        //     ]
        // );
        
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/active_combined_picks', ['_current' => true]);
    }
}

?>