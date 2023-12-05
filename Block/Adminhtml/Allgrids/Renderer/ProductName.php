<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;

use Magento\Framework\DataObject;
 
class ProductName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
	protected $_productRepository;

    public function __construct(
          \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
          $this->_productRepository = $productRepository;
    }

    public function render(DataObject $row)
    {
        $_product = $this->_productRepository->get($row->getSku());
        $html = '<p>';
        $html .= $_product->getName();
        $html .= '</p>';
        return $html;

    }
}