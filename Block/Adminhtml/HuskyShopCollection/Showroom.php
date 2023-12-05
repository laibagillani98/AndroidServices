<?php

namespace TM\AndroidServices\Block\Adminhtml\HuskyShopCollection;
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
        $store_id;

        $id = str_replace('_', ' ',$store_type);
        $idColl = str_replace('collection', '', $id); // Remove 'collection' from the end of the string

        $idCap = ucwords($idColl);
        $store_id = $this->helper->getStoreIds($idCap);
      
         $collection = $this->helper->getHuskyShopCollection($store_id,0);
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
                'filter_index' => 'increment_id',
                'sortable'  => true
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Order Status'),
                'type' => 'text',
                'index' => 'status',
                'filter_index' => 'status',
                'sortable'  => false
            ]
        );
        $this->addColumn(
            'pallet_number',
            [
                'header' => __('Pallet Number'),
                'type' => 'text',
                'index' => 'pallet_number',
                'filter_index' => 'pallet_number',
                'sortable'  => true
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
            'dispatch_date',
            [
                'header' => __('Expected Delivery Date'),
                'index' => 'dispatch_date',
                'type' => 'date',
                'filter_index' => 'dispatch_date',
                'sortable'  => false
            ]
        ); 

        $this->addColumn(
            'shipping_date',
            [
                'header' => __('Expected Shipping Date'),
                'index' => 'shipping_date',
                'type' => 'date',
                'filter_index' => 'shipping_date',
                'sortable'  => false
            ]
        ); 
        

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        $store_type = $this->getRequest()->getParam('type');
        return $this->getUrl('*/*/ajaxgrids/type/'.$store_type, ['_current' => true]);
    }
}

?>