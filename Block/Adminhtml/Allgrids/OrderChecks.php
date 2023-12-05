<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\OrderChecks\CollectionFactory;

class OrderChecks extends Extended
{
    protected $registry;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        CollectionFactory $ordercheckingFactory,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->ordercheckingFactory = $ordercheckingFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('expediting_grid_id');
        $this->setDefaultFilter(array('check_status' => 0));
         $this->setDefaultSort('check_end_time');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->ordercheckingFactory->create();
         // $collection->join(array('history' =>'login_order_history'),  'main_table.order_id= history.tab_order_id',
        //     array('history.tab_order_id','history.tab_order_status','history.start_time','history.end_time','history.user')
        // );
        // $collection->addFieldToFilter("tab_order_status",array("eq" => 0));
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    protected function _prepareColumns()
    {
      
        $this->addColumn(
            'order_no',
            [
                'header' => __('Order Number'),
                'type' => 'text',
                'index' => 'order_no',
                'filter_index' => 'order_no',
                'sortable'  => true
            ]
        );

        $this->addColumn(
            'checked_by',
            [
                'header' => __('Checked By'),
                'type' => 'text',
                'index' => 'checked_by',
                'filter_index' => 'checked_by',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'check_start_time',
            [
                'header' => __('Check Start Time'),
                'index' => 'check_start_time',
                'filter_index' => 'check_start_time',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'check_end_time',
            [
                'header' => __('Check End Time'),
                'index' => 'check_end_time',
                'filter_index' => 'check_end_time',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'check_status',
            [
                'header' => __('Check Status'),
                'index' => 'check_status',
                'options'   => array('0' => 'successful', '1' => 'unsuccessful'),
                'type'      => 'options',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'problem',
            [
                'header' => __('Problem'),
                'type' => 'text',
                'index' => 'problem',
                'filter_index' => 'problem',
                'sortable'  => false
            ]
        );
  
        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/order_checks', ['_current' => true]);
    }
}

?>