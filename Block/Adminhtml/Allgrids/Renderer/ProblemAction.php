<?php
 
namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;
 
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
 
class ProblemAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $relsoveAction = $this->urlBuilder->getUrl('*/gridactions/resolve/id/'.$record_id);
        $toCallAction = $this->urlBuilder->getUrl('*/gridactions/puttocall/id/'.$record_id);
        return '<button class="comment_dialog" id="'.$record_id.'" data-action="resolve" type="button">Resolve</button>&nbsp;&nbsp;<button class="comment_dialog" id="'.$record_id.'" data-action="puttocall" type="button">Put to Call</button>';

    }
}