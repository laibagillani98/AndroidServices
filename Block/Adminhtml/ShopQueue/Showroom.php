<?php

namespace TM\AndroidServices\Block\Adminhtml\ShopQueue;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Helper\TabletQueue;

class Showroom extends Extended
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
        $this->setId('showroom_grid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $store_type = $this->getRequest()->getParam('type');

        $id = str_replace('_', ' ',$store_type);
        $idCap = ucwords($id);
        $store_id = $this->helper->getStoreIds($idCap);

         $collection = $this->helper->OrdersCollection(0,"",0,0,0,$store_id);
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
            'shipping_description',
            [
                'header' => __('Shipping Information'),
                'index' => 'shipping_description',
                'type' => 'text'
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created Date'),
                'index' => 'created_at',
                'type' => 'datetime',
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
            'op_invoice_at',
            [
                'header' => __('Order Invoiced At'),
                'index' => 'op_invoice_at',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'shipping_date',
            [
                'header' => __('Order Delivery Date'),
                'index' => 'shipping_date',
                'type' => 'date',
                'filter_index' => 'order.shipping_date',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'delivery_note',
            [
                'header' => __('Customer Comments'),
                'index' => 'delivery_note',
                'type' => 'text',
                'filter_index' => 'order.delivery_note',
                'sortable'  => false
            ]
        );
        
        $this->addColumn(
            'pick_number',
            [
                'header' => __('Pick Number'),
                'index' => 'pick_number',
                'type' => 'text',
                'sortable'  => false
            ]
        );
        
        // $this->addColumn(
        //     'tablet_user',
        //     [
        //         'header' => __('Assigned User'),
        //         'index' => 'tablet_user',
        //         'type' => 'text',
        //         'sortable'  => false,
        //         'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\PalletUsers'
        //     ]
        // );
        
        

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        $store_type = $this->getRequest()->getParam('type');
        return $this->getUrl('*/*/ajaxgrids/type/'.$store_type, ['_current' => true]);
    }
}

?>