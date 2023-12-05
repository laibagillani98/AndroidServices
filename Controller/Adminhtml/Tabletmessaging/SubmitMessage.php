<?php

namespace TM\AndroidServices\Controller\Adminhtml\Tabletmessaging;

use Magento\Framework\Controller\ResultFactory;

class SubmitMessage extends \Magento\Backend\App\Action
{
    protected $resultPageFactory = false;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ResultFactory $resultFactory,
        \TM\AndroidServices\Helper\TabletQueue $tabletHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \TM\AndroidServices\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \TM\AndroidServices\Model\TabletMessagesFactory $messagesfactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->tabletHelper = $tabletHelper;
        $this->resultFactory = $resultFactory;
        $this->authSession = $authSession;
        $this->messagesfactory = $messagesfactory;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $action = $post['message_type'];
        if ($action == "messageusers"){
            $users = $post['reciepient'];
            $message_type = "Message";
        }elseif ($action == "announcement"){
            $users = $this->tabletHelper->getpalletUsers();
            $message_type = "Announcement";
        }

        $failedUsers = array();
        $sentBy = $this->authSession->getUser()->getData('username');
        $sentAt = date("Y-m-d H:i:s");
        $message = $post["message"];
        $messageData = array("message" => $message,"sentat" => $sentAt , "type" => $message_type);
        foreach ($users as $user){
            try {
                $dbarray =array();
                $orderOnScreen = $this->tabletHelper->onScreenOrder($user);
                if ($orderOnScreen['count']){
                    $workstation = $orderOnScreen['workstation'];
                    $isSent = false;
                    $token = $orderOnScreen['tab_token'];
                    if($orderOnScreen['tab_token_type'] == "fcm"){
                        $isSent = $this->helper->sendNotificationFcm($messageData,$token);
                    }elseif ($orderOnScreen['tab_token_type'] == "pushy"){
                        $isSent = $this->helper->sendNotificationPushy($messageData,$token);
                    }
                    if ($isSent === true){
                        $dbarray = array("reciepient" => $user, "tablet_unique_id" => $workstation, "message_type" => $message_type ,"message" => $message , "is_sent" => 1 ,"sent_at" => $sentAt ,
                            "sent_by" => $sentBy);
                    }else{
                        $failedUsers[] = $user;
                    }
                }else{
                    if ($action == "messageusers"){
                        $dbarray = array("reciepient" => $user , "tablet_unique_id" => "", "message_type" => $message_type ,"message" => $message , "is_sent" => 0 ,"sent_at" => $sentAt ,
                            "sent_by" => $sentBy);
                    }
                }
                if(count($dbarray)){
                    $messagesModel = $this->messagesfactory->create();
                    $messagesModel->addData($dbarray);
                    $messagesModel->save();
                }

            }catch (\Exception $e){
                $this->helper->ErrorLog($user,"submit_message",$e->getMessage(),"message:".$message."---sent at:".$sentAt);
                $failedUsers[] = $user;
            }
        }
        if (count($failedUsers)){
            $failed_users = implode(",",$failedUsers);
            $return_array = array("response"=>false);
            $this->messageManager->addError("Message Not sent to these users:".$failed_users);
        }else{
            $return_array = array("response"=>true);
            $this->messageManager->addSuccess("Messages Sucessfully Sent");
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
//        $resultJson = $this->resultJsonFactory->create();
//        return $resultJson->setData(['success' => $return_array]);
//        echo "<pre>";
//        print_r($users);
//        exit;
    }


}