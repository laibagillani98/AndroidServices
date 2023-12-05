<?php

namespace TM\AndroidServices\Block\Adminhtml\ShopQueue;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory as Pallexcollection;

class ShopCompletedOrders extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        Pallexcollection $palletCollection,
        \TM\AndroidServices\Model\ResourceModel\MbPalletNumbers\CollectionFactory $ShopPalletCollection,
        \TM\AndroidServices\Helper\TabletQueue $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_palletCollection = $palletCollection;
        $this->shopPalletCollection = $ShopPalletCollection;
        $this->helper = $helper;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('shopcompletedordersgrid');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {

        $shopPalletCollection = $this->shopPalletCollection->create();
        // $shopPalletCollection->addFieldToFilter('pallet_status', ['neq' => NULL]);
        $shopPalletCollection->addFieldToFilter('pallet_status', ['notnull' => true]);

        $this->setCollection($shopPalletCollection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
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
            'orders',
            [
                'header' => __('Order Numbers'),
                'type' => 'text',
                'index' => 'orders',
                'filter_index' => 'orders',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\ShopQueue\Renderer\Orders'
            ]
        );

        $this->addColumn(
            'store_id',
            [
                'header' => __('Store'),
                'index' => 'store_id',
                'type' => 'text',
                'sortable'  => false,
                'filter_condition_callback' => [$this, '_customFilterCallback'],
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\ShopQueue\Renderer\Stores'
            ]
        );

        $this->addColumn(
            'tablet_user',
            [
                'header' => __('User'),
                'index' => 'tablet_user',
                'type' => 'text',
                'filter_index' => 'tablet_user',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'pallet_completed_at',
            [
                'header' => __('Completed At'),
                'index' => 'pallet_completed_at',
                'filter_index' => 'pallet_completed_at',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'pallet_weight',
            [
                'header' => __('Pallet Weight'),
                'type' => 'decimal',
                'index' => 'pallet_weight',
                'sortable' => true,
                ]
        ); 

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/shop_completed_order_grid', ['_current' => true]);
    }
    protected function _customFilterCallback($collection, $column)
{
    if (!$value = $column->getFilter()->getValue()) {
        return $this;
    }
    //  $filter = $this->helper->getStoreIds($value,0,1);
     $stores = $this->_storeManager->getStores();
     $storeName = '~' . preg_quote($value, '~') . '~i';
     $store_id = [];
     foreach ($stores as $store) {
        if (preg_match($storeName, $store["name"])){
            $store_id[] = $store["store_id"];
        }
      }
      if($store_id){ 
       $collection->addFieldToFilter('store_id', ['in' => $store_id]);
      }
    return $this;
}
}

?>