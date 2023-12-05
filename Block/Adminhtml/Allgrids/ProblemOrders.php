<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Helper\Data as HelperData;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\CollectionFactory as SkuHistoryCollectionFactory;

class ProblemOrders extends Extended
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
        $this->setId('problemorders');
        $this->setDefaultSort('reported_at');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->SkuHistoryCollectionFactory->create();
        $collection->addFieldToFilter('type', HelperData::SKU_REPORT_TYPE_PROBLEM)->addFieldToFilter('problem_status', HelperData::PROBLEM_STATUS_UNRESOLVED);
        $collection->join(array('order' =>'sales_order'), 'main_table.order_no= order.increment_id',
            array('order.dispatch_date','order.status')
        );
        $collection->join(array('pallet' =>'mb_order_processing_pallet'), 'main_table.order_no= pallet.op_increment_id',
            array('pallet.tablet_user','pallet.op_order_id')
        );
        
        $collection->addFieldToFilter('order.status', array("nin"=>array("complete","delivered_complete","closed")));
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
            'status',
            [
                'header' => __('Order Status'),
                'type' => 'text',
                'index' => 'status',
                'filter_index' => 'order.status'
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
            'tablet_user',
            [
                'header' => __('Assigned User'),
                'index' => 'tablet_user',
                'type' => 'text',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\PalletUsers'
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'sortable'  => false,
                'index' => 'record_id',
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\ProblemAction',
                'column_css_class' => 'problem_action_col'
            ]
        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/problem_orders', ['_current' => true]);
    }
}

?>