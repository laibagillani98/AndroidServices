<?php

namespace TM\AndroidServices\Block\Adminhtml\Dhlgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use TM\AndroidServices\Helper\TabletQueue;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory as Pallexcollection;

class DhlQueue extends Extended
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
        \TM\Sampleprocessing\Block\Adminhtml\Post\Index $dhlIndex,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_palletCollection = $palletCollection;
        $this->dhlCollection = $dhlCollection;
        $this->helper = $helper;
        $this->dhlIndex = $dhlIndex;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('dhl_queue_grid');
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $currentDHL = $this->dhlIndex->getSmallOrderDispatchDate();
        $dhlCollection = $this->helper->generateDHLBatch($currentDHL,0,0,true);
        $this->setCollection($dhlCollection);
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
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'op_order_id',
            [
                'header' => __('Products'),
                'index' => 'op_order_id',
                'type' => 'text',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer\Products'

            ]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Order Status'),
                'index' => 'status',
                'type' => 'text',
                'filter_index' => 'status',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'dispatch_date',
            [
                'header' => __('Dispatch Date'),
                'index' => 'dispatch_date',
                'filter_index' => 'dispatch_date',
                'type' => 'date',
                'sortable'  => false
            ]
        );
        
        
        $this->addColumn(
            'batch_number',
            [
                'header' => __('Batch Number'),
                'index' => 'batch_number',
                'type' => 'text',
                'filter_index' => 'batch_number',
                'sortable'  => false
            ]
        );                        

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/dhl_queue', ['_current' => true]);
    }
}

?>