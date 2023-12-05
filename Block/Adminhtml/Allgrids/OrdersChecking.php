<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\ObjectManagerInterface;
use \TM\AndroidServices\Model\ResourceModel\OrderChecking\CollectionFactory;

class OrdersChecking extends Extended
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
        $this->setDefaultFilter(array('status' => 2));
        $this->setDefaultSort('reported_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->ordercheckingFactory->create();
        $collection->join(array('history' =>'login_order_history'),  'main_table.order_id= history.tab_order_id',
            array('history.tab_order_id','history.tab_order_status','history.start_time','history.end_time','history.user')
        );
        $collection->addFieldToFilter("tab_order_status",array("eq" => 0));
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
            'order_no',
            [
                'header' => __('Order Number'),
                'type' => 'text',
                'index' => 'order_no',
                'filter_index' => 'order_no',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'type' => 'text',
                'index' => 'sku',
                'filter_index' => 'sku',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'reported_by',
            [
                'header' => __('Checked By'),
                'index' => 'reported_by',
                'filter_index' => 'reported_by',
                'type' => 'text',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'user',
            [
                'header' => __('Picked By'),
                'index' => 'user',
                'filter_index' => 'user',
                'type' => 'text',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'end_time',
            [
                'header' => __('Picked At'),
                'index' => 'end_time',
                'type' => 'datetime',
                'filter_index' => 'history.end_time',
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

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'options'   => array('1' => 'checked', '2' => 'problem'),
                'type'      => 'options',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'note',
            [
                'header' => __('Note'),
                'index' => 'note',
                'type' => 'text',
                'filter_index' => 'note',
                'sortable'  => false
            ]
        );

        $this->addColumn(
            'submitted_image',
            [
                'header' => __('Submitted Image'),
                'index' => 'submitted_image',
                'type' => 'image',
                'sortable'  => false,
                'renderer' => 'TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer\Image',
            ]
        );
        
        $this->addColumn(
            'reported_at',
            [
                'header' => __('Reported At'),
                'index' => 'reported_at',
                'type' => 'datetime',
                'sortable'  => false
            ]
        );


        return parent::_prepareColumns();
    }
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ajaxgrids/type/order_checking', ['_current' => true]);
    }
}

?>