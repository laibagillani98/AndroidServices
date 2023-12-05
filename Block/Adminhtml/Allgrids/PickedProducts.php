<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\CollectionFactory as SkuHistoryCollectionFactory;
use \TM\AndroidServices\Helper\Data as HelperData;

class PickedProducts extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        SkuHistoryCollectionFactory $SkuHistoryCollectionFactory,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->SkuHistoryCollectionFactory = $SkuHistoryCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('pickedproducts');
        $this->setDefaultSort('reported_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->SkuHistoryCollectionFactory->create();
        $collection->addFieldToFilter('type', HelperData::SKU_REPORT_TYPE_PICKED);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {

        $this->addColumn(
            'record_id',
            [
                'header' => __('ID'),
                'type' => 'text',
                'index' => 'record_id',
                'filter_index' => 'record_id'
            ]
        );

        $this->addColumn(
            'tablet_unique_id',
            [
                'header' => __('Tablet Unique ID'),
                'type' => 'text',
                'index' => 'tablet_unique_id',
                'filter_index' => 'tablet_unique_id',
                'sortable'  => false
            ]
        );

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
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku',
                'type' => 'text',
                'filter_index' => 'sku',
            ]
        );


        $this->addColumn(
            'location',
            [
                'header' => __('Location'),
                'type' => 'text',
                'index' => 'location',
                'filter_index' => 'location'
            ]
        );

        $this->addColumn(
            'actual_weight',
            [
                'header' => __('Weight Registered'),
                'type' => 'decimal',
                'index' => 'actual_weight',
                'filter_index' => 'actual_weight',
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\Weight',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'calculated_weight',
            [
                'header' => __('Expected Weight'),
                'type' => 'decimal',
                'index' => 'calculated_weight',
                'filter_index' => 'calculated_weight',
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\Weight',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'override_weight',
            [
                'header' => __('Check Status'),
                'index' => 'override_weight',
                'options'   => array('0' => 'Checked', '1' => 'Overridden'),
                'type'      => 'options',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'reported_by',
            [
                'header' => __('Username'),
                'index' => 'reported_by',
                'type' => 'text',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'reported_at',
            [
                'header' => __('Date'),
                'index' => 'reported_at',
                'type' => 'datetime',
                'filter_index' => 'reported_at'
            ]
        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/picked_products', ['_current' => true]);
    }
}

?>