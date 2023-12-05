<?php
namespace TM\AndroidServices\Block\Adminhtml\ShopQueue;

class Dashboard extends \Magento\Framework\View\Element\Template
{
    public $request;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->request = $request;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    public function getjsonData($data){
        $json = $this->json->serialize($data);
        return $json;
    }

    public function getShopQueueGridsConfigValue()
    {
        $configValue = '';
        $newArray = [];

        $configValue = $this->scopeConfig->getValue('tablet_config/husky_settings/shop_queue_grids', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($configValue){
          $configValueArray = explode(',', $configValue);
  
          foreach ($configValueArray as $value) {
              $title = $value; // The config value becomes the title
              $id = str_replace(' ', '_', strtolower($value)); // Remove spaces and replace with underscores
          
              $newArray[] = [
                  'title' => $title,
                  'id' => $id,
              ];
          }
       }
        return $newArray;
    }
}