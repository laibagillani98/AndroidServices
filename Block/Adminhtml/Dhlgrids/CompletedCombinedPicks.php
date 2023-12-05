<?php

namespace TM\AndroidServices\Block\Adminhtml\Dhlgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use TM\AndroidServices\Helper\TabletQueue;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\SkuHistory\Adjoin\CollectionFactory as Pallexcollection;

class CompletedCombinedPicks extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        TabletQueue $helper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        Pallexcollection $palletCollection,
        \TM\Sampleprocessing\Model\ResourceModel\Sampleprocessing\CollectionFactory $dhlCollection,
        \TM\Python\Helper\Data $pythonHelper,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_palletCollection = $palletCollection;
        $this->dhlCollection = $dhlCollection;
        $this->helper = $helper;
        $this->pythonHelper=$pythonHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(true);
        $this->setId('completed_combined');
        $this->setDefaultSort("combined_pick_completed_at");
        $this->setSaveParametersInSession(true);
    }
    
    protected function _prepareCollection()
    {

        $dhlCollection = $this->dhlCollection->create();

        $dhlCollection =$this->pythonHelper->addFieldToWareHouse($dhlCollection ,true);
        if($this->pythonHelper->isEnablePython()) {
            $dhlCollection->addFieldToFilter(
                ['py_consingment_id', 'consingment_id'],
                [
                    ['neq' => null],
                    ['neq' => null]
                ]
            );
            $dhlCollection->addFieldToFilter(
                ['tablet_user', 'python_user'],
                [
                    ['neq' => null],
                    ['neq' => null]
                ]
            );
        }
        else{
            $dhlCollection->addFieldToFilter('consingment_id', array('notnull' => true));
            $dhlCollection->addFieldToFilter('tablet_user', array('notnull' => true));
        }
        $dhlCollection->addFieldToFilter('batch_queue_status', array("in"=>array(4)));
        $dhlCollection->addFieldToFilter('batch_number', array('notnull' => true));
        $dhlCollection = $this->helper->applySalesJoin($dhlCollection);
        $dhlCollection->addFieldToFilter('sales.status', array("in" => array('complete')));
        $dhlCollection->getSelect()->group('batch_number');
        $this->setCollection($dhlCollection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {

        $this->addColumn(
            'batch_number',
            [
                'header' => __('Batch Number'),
                'type' => 'text',
                'index' => 'batch_number',
                'filter_index' => 'batch_number',
                'sortable'  => false
            ]
        );
        $this->addColumn(
            'order_numbers',
            [
                'header' => __('Order Numbers'),
                'type' => 'text',
                // 'index' => 'order_numbers',
                // 'filter_index' => 'increment_id',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer\Orders'
            ]
        );

        $this->addColumn(
            'picked_by',
            [
                'header' => __('Picked By'),
//                'index' => 'tablet_user',
                'type' => 'text',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer\PickedBy',
                'filter_condition_callback' => [$this, '_filterPickedBy'], // Add custom filter callback
//                'filter_index' => 'tablet_user',
            ]
        );

        $this->addColumn(
            'combined_pick_completed_at',
            [
                'header' => __('Picked Date'),
                'index' => 'combined_pick_completed_at',
                'type' => 'datetime',
                'filter_index' => 'combined_pick_completed_at',
                'sortable'  => false
            ]
        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/completed_combined_picks', ['_current' => true]);
    }
    protected function _filterPickedBy($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }

        $collection->addFieldToFilter(
              ['tablet_user', 'python_user'],
              [
                  ['like' => '%' . $value . '%'],
                  ['like' => '%' . $value . '%']
              ]
        );

        return $this;
    }

}

?>