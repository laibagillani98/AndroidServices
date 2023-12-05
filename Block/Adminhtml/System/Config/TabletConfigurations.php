<?php

namespace TM\AndroidServices\Block\Adminhtml\System\Config;

class TabletConfigurations extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Integration\Api\IntegrationServiceInterface $integrationService,
        \Magento\Integration\Api\OauthServiceInterface $oauthService,
        \TM\AndroidServices\Helper\Data $helper
    ) {
        $this->_storeManager = $storeManager;
        $this->_integrationService = $integrationService;
        $this->_oauthService = $oauthService;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $tokenNameServ = $this->_integrationService->findByName('TabletServices');
        $consumerId = $tokenNameServ->getConsumerId();
        $oauth = false;
        $AccessToken = "";
        if($consumerId){
          $oauth = $this->_oauthService->getAccessToken($consumerId);  
        }
        
        if($oauth){
           $AccessToken = $oauth->getToken(); 
        }

        $tmBmConfig = $this->_helper->getConfiguration('tablet_config/general/tm_bm_config');

        if ($tmBmConfig) {
            $site = 'TM';
        } else {
            $site = 'BM';
        }
        
        
        $tabPasswords = $this->_helper->getTabletPasswords();
        $array = array("baseurl" => $baseUrl, "token" => $AccessToken , "logout" => $tabPasswords['logoutPwd'], "scaleCheck" => $tabPasswords['scaleCheckPwd'] , "serverUrl" => $tabPasswords['settingsPwd'],"site" => $site);
        $jsonString = json_encode($array);

        return '<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl='.urlencode($jsonString).'&choe=UTF-8" 
            alt="Qr Code for Tablet Config" ><br><span>Please save Configuration First then Scan final QR</span>';
    }
}