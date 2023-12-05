<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;

use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;

class BreakageAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $record_id = $this->_getValue($row);
        $relsoveAction = $this->urlBuilder->getUrl('*/breakages/resolve/id/'.$record_id);
        return '<button class="comment_dialog" id="'.$record_id.'" data-action="resolve" type="button">Resolve</button>';

    }
}