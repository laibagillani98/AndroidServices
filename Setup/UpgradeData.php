<?php

namespace TM\AndroidServices\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeData implements UpgradeDataInterface
{

    public function __construct(
        \Magento\Integration\Model\IntegrationFactory $intergration,
        \Magento\Integration\Model\OauthService $oauthservice,
        \Magento\Integration\Model\Oauth\Token $tokenService,
        \Magento\Integration\Model\AuthorizationService $authservice
    )
    {
        $this->intergration = $intergration;
        $this->tokenService = $tokenService;
        $this->oauthservice = $oauthservice;
        $this->authservice = $authservice;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $name = "TabletServices";
            $integrationExists = $this->intergration->create()->load($name,'name')->getData();
            if(empty($integrationExists)){
                $integrationData = array(
                    'name' => $name,
                    'email' => '',
                    'status' => '1',
                    'endpoint' => '',
                    'setup_type' => '0'
                );
                try{
                    // Code to create Integration
                    $integrationFactory = $this->intergration->create();
                    $integration = $integrationFactory->setData($integrationData);
                    $integration->save();
                    $integrationId = $integration->getId();
                    $consumerName = 'Integration' . $integrationId;

                    // Code to create consumer
                    $consumer = $this->oauthservice->createConsumer(['name' => $consumerName]);
                    $consumerId = $consumer->getId();
                    $integration->setConsumerId($consumer->getId());
                    $integration->save();

                    // Code to grant permission
                    $this->authservice->grantAllPermissions($integrationId);

                    // Code to Activate and Authorize
                    $this->tokenService->createVerifierToken($consumerId);
                    $this->tokenService->setType('access');
                    $this->tokenService->save();

                }catch(Exception $e){
                    echo 'Error : '.$e->getMessage();
                }
            }
        }
    }
}