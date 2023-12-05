<?php

namespace TM\AndroidServices\Block\Adminhtml\Allgrids\Renderer;

use Magento\Framework\DataObject;

class PalletUsers extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public function __construct(
        \TM\AndroidServices\Helper\TabletQueue $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_helper = $helper;
        $this->scopeConfig = $scopeConfig;
    }

    public function render(DataObject $row)
    {
        $username = $this->_getValue($row);

        $order_id = $row->getData("op_order_id");
        $allusers = $this->scopeConfig->getValue('config_section/tm_palletqueue/users',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $allusers_list = explode(",",$allusers);
        $options = '<option value=""></option>';
        foreach ($allusers_list as $user){
            $selected = "";
            if ($username == $user){
                $selected = "selected";
            }
            $options .= '<option '.$selected.' value="'.$user.'">'.$user.'</option>';
        }
        return '<select name="allTabletUsers" id="'.$order_id.'" class="selectUser"  >
                  '.$options.'
                </select>';

    }
}