<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;

class WriteOff extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper,
        UrlInterface $urlBuilder
    ) {
        $this->_helper = $helper;
        $this->urlBuilder = $urlBuilder;
    }

    public function render(DataObject $row)
    {

        if (!$row->getData('resolved_by') || $row->getData('resolved_by') == 'NULL'){
            $record_id = $this->_getValue($row);
            $writeoffAction = $this->urlBuilder->getUrl('*/gridactions/writeoff/id/'.$record_id);
            return '<a href="' . $writeoffAction . '" >Write Off</a>';
        }

    }
}