<?php

namespace TM\AndroidServices\Block\Adminhtml\HuskyReportedProblems;
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
        \TM\AndroidServices\Model\ResourceModel\OrderChecks\Adjoin\CollectionFactory $checkCollection,
        Context $context,
        Data $backendHelper,
        ObjectManagerInterface $objectManager,
        Registry $registry,
        TabletQueue $helper,
        array $data = []
    ) {
        $this->checkCollection = $checkCollection;
        $this->_objectManager = $objectManager;
        $this->registry = $registry;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('showroom_grid');
        $this->setId('expediting_grid_id');
         $this->setDefaultSort('reported_at');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }
    protected function _prepareCollection()
    {
        $collection = $this->checkCollection->create()->getOrders();
         $store_type = $this->getRequest()->getParam('type');
        $store_id;
        $id = str_replace('_', ' ',$store_type);
        $idColl = str_replace('problem', '', $id); // Remove 'problem' from the end of the string
        $idCap = ucwords($idColl);
 
        $store_id = $this->helper->getStoreIds($idCap);
       
        $collection->addFieldToFilter("store_id",$store_id); 
        $collection->addFieldToFilter("main_table.status",2); 
        $collection->addFieldToFilter("order.status","showroom_problem"); 

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
                'sortable'  => false
            ]
        );
       
        $this->addColumn(
            'orderstatus',
            [
                'header' => __('Order Status'),
                'type' => 'text',
                'index' => 'orderstatus', // Change 'index' to 'orderstatus'
                'sortable' => false,
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
            'problem',
            [
                'header' => __('Problem'),
                'index' => 'problem',
                'filter_index' => 'problem',
                'type' => 'text',
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
            'note',
            [
                'header' => __('Note'),
                'index' => 'note',
                'type' => 'text',
                'filter_index' => 'note',
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