<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace TM\AndroidServices\Block\Adminhtml\OtherChecks\OtherChecks;


use Magento\Framework\App\RequestInterface;
use \TM\Base\Helper\Data;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $request;
    protected $date;
    protected $pageLayoutBuilder;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        RequestInterface $request,
         \Magento\Store\Ui\Component\Listing\Column\Store $storestatus,
         \TM\AndroidServices\Model\OtherChecksFactory $otherChecksFactory,
          \Magento\Framework\App\ResourceConnection $Resource,
        array $data = []
    ) {
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->date = $date;
        $this->_storestatus = $storestatus;
        $this->request = $request;
        $this->otherChecksFactory = $otherChecksFactory; 
        $this->_resource = $Resource;

        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultDir('ASC');
        $this->setId('other_checks');
        $this->setDefaultSort('identifier');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $otherChecksFactory = $this->otherChecksFactory->create()->getCollection();
        $this->setCollection($otherChecksFactory);
         return parent::_prepareCollection();
    }

  
    protected function _prepareColumns()
    {
           $this->addColumn(
            'other_checks_id',
            [
                'header' => __('ID'),
                'index'  => 'other_checks_id',
                'filter_index' => 'other_checks_id',
                'type'  => 'int',
                'sortable' => true,
             ]

        );
        
        $this->addColumn(
            'vehicle_info',
            [
                'header' => __('Vehicle Information'),
                'index'  => 'vehicle_info',
            ]

        );
        
        $this->addColumn(
            'other_checks_date',
            [
                'header' => __('Check Date'),
                'index'  => 'other_checks_date',
                'type' => 'datetime',
                'renderer'  => 'TM\ReturnForm\Block\Adminhtml\Renderer\Date'
            ]

        );
        
         $this->addColumn(
            'llop_user',
            [
                'header' => __('User'),
                'index'  => 'user',
                'filter_index'  => 'user',
            ]

        );

        $this->addColumn(
            'hydraulic_system',
            [
                'header' => __('Hydraulic System / leaks'),
                'index'  => 'hydraulic_system',
                'filter_index'  => 'hydraulic_system',
            ]

        );
        $this->addColumn(
            'wheels',
            [
                'header' => __('Wheels / tyres'),
                'index'  => 'wheels',
                'filter_index'  => 'wheels',
            ]

        );
        $this->addColumn(
            'forks',
            [
                'header' => __('Forks,tips,Carriage,mast'),
                'index'  => 'forks',
                'filter_index'  => 'forks',
            ]

        );
        $this->addColumn(
            'battery_charge',
            [
                'header' => __('Battery Charge'),
                'index'  => 'battery_charge',
                'filter_index'  => 'battery_charge',
            ]

        );
        $this->addColumn(
            'capacity_plate',
            [
                'header' => __('Capacity plate'),
                'index'  => 'capacity_plate',
                'filter_index'  => 'capacity_plate',
            ]

        );
        $this->addColumn(
            'gauges',
            [
                'header' => __('Gauges/instruments'),
                'index'  => 'gauges',
                'filter_index'  => 'gauges',
            ]

        );
        $this->addColumn(
            'brakes',
            [
                'header' => __('Brakes'),
                'index'  => 'brakes',
                'filter_index'  => 'brakes',
            ]

        );  
         $this->addColumn(
            'steering',
            [
                'header' => __('Steering'),
                'index'  => 'steering',
                'filter_index'  => 'steering',
            ]

        );  
         $this->addColumn(
            'horn',
            [
                'header' => __('Horn'),
                'index'  => 'horn',
                'filter_index'  => 'horn',
            ]

        );
           $this->addColumn(
            'lights',
            [
                'header' => __('Lights'),
                'index'  => 'lights',
                'filter_index'  => 'lights',
            ]

        );
         $this->addColumn(
         'overall_condition',
         [
             'header' => __('Overall condition'),
             'index'  => 'overall_condition',
             'filter_index'  => 'overall_condition',
         ]

      );
 
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/tempgrid', ['_current' => true]);
    }
    
}