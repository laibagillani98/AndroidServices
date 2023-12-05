<?php
namespace TM\AndroidServices\Block\Adminhtml\ShopQueue\Renderer;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class CheckStatus extends AbstractRenderer
{
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        if ($value == 4) {
            return 'Received';
        }
        if ($value == 5) {
            return 'Loaded';
        }
        return $value; // Render the raw value for other cases
    }
}
