<?php
namespace TM\AndroidServices\Block\Adminhtml\Main;

class AllGrids extends \Magento\Framework\View\Element\Template
{
    public $request;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->request = $request;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    public function getjsonData($data){
        $json = $this->json->serialize($data);
        return $json;
    }
}
