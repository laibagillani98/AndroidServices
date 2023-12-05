<?php
namespace TM\AndroidServices\Block\Adminhtml\Tabletmessaging;

class SendMessage extends \Magento\Framework\View\Element\Template
{
    public $request;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \TM\AndroidServices\Helper\TabletQueue $tabletHelper,
        //\Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\Data\Form\FormKey $formKey,
        //\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->request = $request;
//        $this->scopeConfig = $scopeConfig;
//        $this->json = $json;
        $this->formKey = $formKey;
        $this->tabletHelper = $tabletHelper;
        parent::__construct($context, $data);
    }

    public function getMessageTypeConfig(){

        $data = array("announcement" => "Announcement" , "messageusers" => "Message");
        return $data;
        //echo "<pre>";print_r($data);die("dasd");
    }

    public function tabletUsers(){
        $allusers = $this->tabletHelper->getpalletUsers();
        return $allusers;
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
