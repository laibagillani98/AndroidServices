<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Helper\TabletQueue;

class ReturnRecord extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        TabletQueue $helper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('returnrecordgrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->helper->returnOrderCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        $this->addColumn(
            'return_order_id',
            [
                'header' => __('Order Number'),
                'type' => 'int',
                'index' => 'return_order_id',
                'filter_index' => 'return_order_id',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'return_date',
            [
                'header' => __('Return Date'),
                'index' => 'return_date',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'return_products',
            [
                'header' => __('Return Products'),
                'type' => 'text',
                'index' => 'return_products',
                'sortable'  => false,
            ]
        );

        $this->addColumn(
            'return_image',
            [
                'header' => __('Photo'),
                'index' => 'return_image',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\Image'
            ]
        );

        $this->addColumn(
            'return_by',
            [
                'header' => __('Returned By'),
                'index' => 'return_by',
                'type' => 'text',
                'sortable'  => false
            ]
        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/return_records', ['_current' => true]);
    }
}

?>