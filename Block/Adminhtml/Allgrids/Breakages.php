<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Helper\Data as HelperData;

class Breakages extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        HelperData $brokenHelper,
        array $data = []
    ) { 
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->_brokenHelper = $brokenHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('breakagestiles');
        $this->setDefaultSort('reported_at');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->_brokenHelper->getReportedBroken(0);
        // $collection->addFilterToMap('sku','main_table.sku');
        // $collection->addFilterToMap('problem_status', 1);

        // $collection->join(array('history' =>'sales_order_item'), 'main_table.sku= history.sku',
        // array('history.name','main_table.location','main_table.quantity', 'main_table.reported_by','main_table.reported_at','main_table.resolved_by','main_table.resolved_at','main_table.record_id'));
        $collection->getSelect()->group('record_id');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {

//        $this->addColumn(
//            'record_id',
//            [
//                'header' => __('ID'),
//                'type' => 'int',
//                'index' => 'record_id',
//                'filter_index' => 'record_id'
//            ]
//        );

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
                'header' => __('Product Location'),
                'type' => 'text',
                'index' => 'location',
                'filter_index' => 'location'
            ]
        );

        $this->addColumn(
            'reported_by',
            [
                'header' => __('Reported By'),
                'index' => 'reported_by',
                'type' => 'text',
                'sortable'  => false,
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
            'action',
            [
                'header' => __('Action'),
                'sortable'  => false,
                'index' => 'record_id',
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\BreakageAction',
            ]
        );

//        $this->addColumn(
//            'action',
//            [
//                'header' => __('Action'),
//                'width'     => '100px',
//                'type'      => 'action',
//                'getter'     => 'getId',
//                'actions'   => [
//                    [
//                        'caption' => __('Write Off'),
//                        'url' => ['base' => '*/gridactions/writeoff'],
//                        'field'   => 'id'   // pass id as parameter
//                    ]
//                ],
//                'filter'    => false,
//                'sortable'  => false,
//                'index' => 'record_id',
//                'is_system' => true
//            ]
//        );

        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/breakages', ['_current' => true]);
    }
}

?>