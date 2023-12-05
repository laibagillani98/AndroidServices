<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Helper\TabletQueue;

class ShopOrders extends Extended
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
        $this->setId('shopqueuegrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->helper->shopOrdersCollection(1,0,true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
        // $this->addColumn(
        //     'id',
        //     [
        //         'header_css_class' => 'a-center',
        //         'type' => 'checkbox',
        //         'name' => 'id',
        //         'align' => 'center',
        //         'index' => 'id',
        //     ]
        // );
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
            'is_picked',
            [
                'header' => __('Pick Created'),
                'type' => 'options',
                'index' => 'is_picked',
                'filter_index' => 'is_picked',
                'sortable'  => false,
                'options'   => array('0' => 'pick no created', '1' => 'pick created'),
            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Order Status'),
                'type' => 'text',
                'index' => 'status',
                'filter_index' => 'order.status',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created Date'),
                'index' => 'created_at',
                'type' => 'datetime',
                'filter_index' => 'order.created_at',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'weight',
            [
                'header' => __('Order Weight'),
                'index' => 'weight',
                'filter_index' => 'order.weight',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\Weight'
            ]
        );

        $this->addColumn(
            'tablet_user',
            [
                'header' => __('Picker'),
                'index' => 'tablet_user',
                'type' => 'text',
                'sortable'  => false
                //'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\PalletUsers',
            ]
        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/shop_orders', ['_current' => true]);
    }
}

?>