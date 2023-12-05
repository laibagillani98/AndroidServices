<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory as Pallexcollection;

class CompletedOrders extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        Pallexcollection $palletCollection,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_palletCollection = $palletCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('completedgrid');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->_palletCollection->create()->getCompletedOrders(0);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {

        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order Number'),
                'type' => 'text',
                'index' => 'increment_id',
                'filter_index' => 'order.increment_id',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'tablet_user',
            [
                'header' => __('Picked By'),
                'index' => 'tablet_user',
                'type' => 'text',
                'sortable'  => false,
                'filter_index' => 'tablet_user',
            ]
        );

        $this->addColumn(
            'end_time',
            [
                'header' => __('Picked Date'),
                'index' => 'end_time',
                'type' => 'datetime',
                'filter_index' => 'history.end_time',
                'sortable'  => false
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
            'process_time',
            [
                'header' => __('Time to Pick'),
                'type' => 'text',
                'index' => 'process_time',
                'filter_index' => 'process_time',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\TimeinMins'
            ]
        );

        $this->addColumn(
            'pallet_scan',
            [
                'header' => __('Time to Pallet'),
                'type' => 'text',
                'index' => 'pallet_scan',
                'filter_index' => 'pallet_scan',
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\TimeinMins',
                'sortable'  => false
            ]
        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/completed_orders', ['_current' => true]);
    }
}

?>