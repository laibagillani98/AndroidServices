<?php
 
namespace TM\AndroidServices\Block\Adminhtml\Dhlgrids\Renderer;
 
use Magento\Framework\DataObject;
use Magento\Framework\UrlInterface;
 
class BatchAction extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
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
        $batch_number = $row->getData("batch_number");
      
        // $relsoveAction = $this->urlBuilder->getUrl('*/gridactions/resolve/id/'.$record_id);
        // $toCallAction = $this->urlBuilder->getUrl('*/gridactions/puttocall/id/'.$record_id);
        return '<button class="remove_batch" id="'.$batch_number.'" data-action="removeuser" type="button">Remove Assignment</button>';

    }
}