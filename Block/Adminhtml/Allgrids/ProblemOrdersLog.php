<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Helper\Data as HelperData;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\CollectionFactory as SkuHistoryCollectionFactory;

class ProblemOrdersLog extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        HelperData $brokenHelper,
        SkuHistoryCollectionFactory $SkuHistoryCollectionFactory,
        array $data = []
    ) { 
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_brokenHelper = $brokenHelper;
        $this->SkuHistoryCollectionFactory = $SkuHistoryCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('problemorderslog');
        $this->setDefaultSort('reported_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->SkuHistoryCollectionFactory->create();
        $collection->addFieldToFilter('type', HelperData::SKU_REPORT_TYPE_PROBLEM)->addFieldToFilter('problem_status', HelperData::PROBLEM_STATUS_RESOLVED);
        $collection->join(array('order' =>'sales_order'), 'main_table.order_no= order.increment_id',
            array('order.dispatch_date')
        );

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
                'filter_index' => 'order_no'
            ]
        );

        $this->addColumn(
            'dispatch_date',
            [
                'header' => __('Dispatch Date'),
                'index' => 'dispatch_date',
                'filter_index' => 'order.dispatch_date',
                'type' => 'date',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'reported_by',
            [
                'header' => __('Reported By'),
                'index' => 'reported_by',
                'type' => 'text',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'reported_at',
            [
                'header' => __('Reported At'),
                'index' => 'reported_at',
                'type' => 'datetime',
                'filter_index' => 'reported_at'
            ]
        );

        $this->addColumn(
            'problem',
            [
                'header' => __('Problem'),
                'type' => 'text',
                'index' => 'problem',
                'filter_index' => 'problem'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('Product SKU'),
                'index' => 'sku',
                'type' => 'text',
                'filter_index' => 'sku',
            ]
        );

        $this->addColumn(
            'location',
            [
                'header' => __(' Product Location'),
                'type' => 'text',
                'index' => 'location',
                'filter_index' => 'location'
            ]
        );


        $this->addColumn(
            'quantity',
            [
                'header' => __('Number of Pallets'),
                'type' => 'int',
                'index' => 'quantity',
                'filter_index' => 'quantity',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'action_taken',
            [
                'header' => __('Action'),
                'index' => 'action_taken',
                'type' => 'text',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'resolved_by',
            [
                'header' => __('Resolved By'),
                'index' => 'resolved_by',
                'type' => 'text',
                'filter_index' => 'resolved_by',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'resolved_at',
            [
                'header' => __('Resolved At'),
                'index' => 'resolved_at',
                'type' => 'datetime',
                'filter_index' => 'resolved_at'
            ]
        );

        $this->addColumn(
            'admin_comment',
            [
                'header' => __('Admin Comment'),
                'index' => 'admin_comment',
                'type' => 'text',
                'sortable'  => false
            ]
        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/problem_orders_log', ['_current' => true]);
    }
}

?>