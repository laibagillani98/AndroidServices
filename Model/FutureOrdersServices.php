<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TM\AndroidServices\Model;

use mysql_xdevapi\Exception;
use TM\AndroidServices\Helper\Data;

class FutureOrdersServices implements \TM\AndroidServices\Api\FutureOrdersServicesInterface
{
    public function __construct(Data $helper,
    \Magento\Sales\Model\OrderFactory $orderModel,
    \Magento\Framework\Webapi\Rest\Request $request,
    \TM\Tmunifi\Block\Adminhtml\Piccontainer $tmunificollection,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \TM\AndroidServices\Model\FuturePalletFactory $futurePalletModel,
    \TM\AndroidServices\Model\ResourceModel\FuturePallet $futurePalletRes,
    \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $ordersCollection,
    \TM\AndroidServices\Model\ResourceModel\FuturePallet\CollectionFactory $futurePalletCollection
    )
    {
        $this->helper = $helper;
        $this->request = $request;
        $this->_tmunificollection = $tmunificollection;
        $this->orderModel = $orderModel;
        $this->storeManager = $storeManager;
        $this->futurePalletRes = $futurePalletRes;
        $this->futurePalletModel = $futurePalletModel;
        $this->ordersCollection = $ordersCollection;
        $this->futurePalletCollection = $futurePalletCollection;
    }

    public function FuturePalletUsers()
    {
        try{
            $return_array = array();
            $users = $this->helper->getConfiguration("config_section/tm_palletqueue/users");
            $futurePalletUsers = explode(",",$users);
            if($futurePalletUsers)
            {
                $return_array['status']=true;
                $return_array['message']='Users found';
                $return_array['user'] = $futurePalletUsers;
            }
            else
            {
                $return_array['status']=false;
                $return_array['message']='No User found';
                $return_array['user'] = $futurePalletUsers;
            }

            $orders = $this->ordersCollection->create()->addAttributeToFilter('status', array('eq' => 'pick_for_future'))->getData();

            $assignedFutureOrders = $this->futurePalletCollection->create()->addFieldToFilter('active', array('eq' => 1))->getData();
            $assigned_orders = array();
            foreach ($assignedFutureOrders as $assignedOrder){
                $assigned_orders[] = $assignedOrder['order_number'];
            }
            $not_assigned_orders = array();
            foreach ($orders as $order){
                if (!in_array($order['increment_id'],$assigned_orders)){
                    $not_assigned_orders[] = $order['increment_id'];
                }
            }
            $total_unassigned = count($not_assigned_orders);
            $return_array['total_unassigned'] = $total_unassigned;
        }catch (\Exception $e){
            $return_array['status'] = false;
            $return_array['message'] = $e->getMessage();
        }

        echo json_encode($return_array);exit;
    }

    public function viewUnassignedOrders($mehtod_call = false)
    {
        try{
            $return_array = array();
            $return_array['total_unassigned'] = 0;
            $orders = $this->ordersCollection->create()->addAttributeToFilter('status', array('eq' => 'pick_for_future'))->getData();

            $assignedFutureOrders = $this->futurePalletCollection->create()->addFieldToFilter('active', array('eq' => 1))->getData();
            $assigned_orders = array();
            foreach ($assignedFutureOrders as $assignedOrder){
                $assigned_orders[] = $assignedOrder['order_number'];
            }
            //print_r($orders);die("xxx");
            $not_assigned_orders = array();
            foreach ($orders as $order){
                if (!in_array($order['increment_id'],$assigned_orders)){
                    $not_assigned_orders[] = $order['increment_id'];
                }
            }
            $total_unassigned = count($not_assigned_orders);
            if ($total_unassigned){
                $return_array['total_unassigned'] = $total_unassigned;
                $return_array['unassigned_orders'] = $not_assigned_orders;
                $return_array['status'] = true;
                $return_array['message'] = $total_unassigned.' Orders found';
            }else{
                $return_array['status'] = false;
                $return_array['message'] = 'No Unassigned Orders found';
            }

        }catch (\Exception $e){
            $return_array['status'] = false;
            $return_array['message'] = $e->getMessage();
        }
        if ($mehtod_call){
            return $return_array;
        }else{
            echo json_encode($return_array);exit;
        }

    }

    public function assignLocationFutureOrders(){
        $params = $this->request->getBodyParams();
        $user_name = $params['user_name'];
        $pallet_barcode = $params['pallet_barcode'];
        $location = $params['location'];
        $pallet_no = $params['pallet_no'];
        $order_no = $pallet_barcode;
        $override_location = $params['override_location'];
        $override_orderno = $params['override_orderno'];
        $dbarray = array();
        $alreadyActivated = false;
        $currentDateTime = date("Y-m-d H:i:s");
        $order = $this->ordersCollection->create()->addAttributeToFilter('increment_id', array('eq' => $order_no))->getData();
       
        if (!$order){
            $order_no = substr($pallet_barcode, 0, -2);
            $pallet_no = substr($pallet_barcode, -2);
            
            $order = $this->ordersCollection->create()->addAttributeToFilter('increment_id', array('eq' => $order_no))->getData();
          
          if (!$order){
            echo json_encode(array("status" => false , "message" => "Not a Future Order" ));
             exit;
           }
         }

        $assignedFutureOrders = $this->futurePalletCollection->create()->addFieldToFilter('active', array('eq' => 1))->getData();
        if ($assignedFutureOrders){
            foreach ($assignedFutureOrders as $assignedFutureOrder){
                //echo "assigned orderno:".$assignedFutureOrder['order_number']."---service orderno:".$order_no."---assigned location:".$assignedFutureOrder['location']."---service location".$location."<br>\n";
                
                if ($assignedFutureOrder['order_number'] == $order_no && $assignedFutureOrder['location'] == $location){
                    $alreadyActivated = true;
                }
                if ($assignedFutureOrder['order_number'] == $order_no && $assignedFutureOrder['location'] != $location && $pallet_no == $assignedFutureOrder['pallet_number']){
                    $model = $this->futurePalletModel->create()->load($assignedFutureOrder['future_pallet_id']);
                    if ($override_location){
                        $model->setActive(0)->setAction("Location override by ".$user_name." at ".$currentDateTime)->save();
                    }else{
                        echo json_encode(array("status" => false , "message" => "order already assinged" , "already_exist_location" => true));
                        exit;
                    }
                }
                if ($assignedFutureOrder['location'] == $location && $assignedFutureOrder['order_number'] != $order_no){
                    $model = $this->futurePalletModel->create()->load($assignedFutureOrder['future_pallet_id']);
                    if ($override_orderno){
                        $model->setActive(0)->setAction("Order override by ".$user_name." at ".$currentDateTime)->save();
                    }else{
                        echo json_encode(array("status" => false , "message" => "location already assinged" , "already_exist_orders" => true));
                        exit;
                    }
                }
            }
        }                       

        $order = $this->orderModel->create()->loadByIncrementId($order_no);
        $order_id = $order->getId();
        $dbarray['order_number'] = $order_no;
        $dbarray['order_id'] = $order_id;
        $dbarray['location'] = $location;
        $dbarray['user_name'] = $user_name;
        $dbarray['pallet_number'] = $pallet_no;
        $dbarray['action'] = "activated";
        $dbarray['date'] = $currentDateTime;
        if ($alreadyActivated){
            echo json_encode(array("status" => false , "message" => "Order Already Assigned to this Location"));exit;
        }else{
            $this->futurePalletModel->create()->addData($dbarray)->save();
            $unassignedOrders = $this->viewUnassignedOrders(true);
            echo json_encode(array(
                "status" => true ,
                "message" => "Order Number ".$order_no." has been assigned to location ".$location." Please scan your next Order" ,
                "total_unassigned" => $unassignedOrders['total_unassigned']));
            exit;
        }

    }

    public function locateFutureOrder(){
        $snapTime = date("Y-m-d H:i:s");
        $params = $this->request->getBodyParams();
        $order_no = $params['order_no'];
        $return_array = array();
        $locations = array();
        $order = $this->ordersCollection->create()->addAttributeToFilter('increment_id', array('eq' => $order_no))->getFirstItem()->getData();
         if (!$order){
             $pallet_no = substr($order_no, -2);
             $order_no = substr($order_no, 0, -2);
             $order = $this->ordersCollection->create()->addAttributeToFilter('increment_id', array('eq' => $order_no))->getFirstItem()->getData();
              if (!$order){
              $return_array = array("status" => false , "message" => "Not a Valid Order" );
              echo json_encode($return_array);exit;
            }
        }
            $order_id = $order['entity_id'];
            $assignedFutureOrders = $this->futurePalletCollection->create()->addFieldToFilter('active', array('eq' => 1))->addFieldToFilter('order_number', array('eq' => $order_no))->getData();
            if ($assignedFutureOrders){
                foreach ($assignedFutureOrders as $assignedFutureOrder){
                    $locations[] = $assignedFutureOrder['location'];
                }

                $snapCollection = $this->_tmunificollection->getCol($order_id);
                $snapData = ($snapCollection)? $snapCollection->getData():[];
                $snap = array();
                if ($snapData){
                    $currentStore = $this->storeManager->getStore();
                    $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                    foreach ($snapData as $snaps){
                        $snap[] = $mediaUrl.$snaps['folder_loc'].$snaps['snapshot']."?".$snaps['start_time'];
                    }

                }

                $return_array = array("status" => true ,
                    "message" => "Order Found" ,
                    "order_no" => $order_no ,
                    "location" => $locations,
                    "snap" => $snap ,
                    "order_status" => ($order['status'] == "pick_for_future") ? "Picked For Future Delivery" : "" ,
                    "id" => $assignedFutureOrder['future_pallet_id']);
            }else{
                $return_array = array("status" => false , "message" => "Order Not Assigned to a Location" );
            }

         echo json_encode($return_array);exit;
    }

    public function removeFutureOrder(){
        $Time = date("Y-m-d H:i:s");
        $params = $this->request->getBodyParams();
        $location = $params['location'];
        $user_name = $params['user_name'];
        $return_array = array();
        try {
            $assignedFutureOrder = $this->futurePalletCollection->create()->addFieldToFilter('active', array('eq' => 1))->addFieldToFilter('location', array('eq' => $location))->getFirstItem()->getData();
            if ($assignedFutureOrder) {
                $this->futurePalletModel->create()->load($assignedFutureOrder['future_pallet_id'])
                    ->setActive(0)
                    ->setDate($Time)
                    ->setUserName($user_name)
                    ->setAction("removed")
                    ->save();
                $return_array = array("status" => true , "message" => "Removed Successfully" );
            }else{
                $return_array = array("status" => false , "message" => "Invalid Location Scanned" );
            }

        }catch (\Exception $e){
            $return_array = array("status" => false , "message" => "Error: ".$e->getMessage() );
        }

        echo json_encode($return_array);exit;
    }

    public function FutureOrders(){
        $Collection = $this->ordersCollection->create();
        $today = date("Y-m-d");
        $Collection->addFieldToFilter('status', array('eq' => 'pick_for_future'))->addFieldToFilter('dispatch_date', array('eq' => $today));
        $orders = $Collection->getData();
        $ordersToDispatchData = array();
        if ($orders){
            foreach ($orders as $order){
                $order_nos[] = $order['increment_id'];
            }
            $assignedFutureOrders = $this->futurePalletCollection->create()->addFieldToFilter('active', array('eq' => 1))->addFieldToFilter('order_number', array('in' => $order_nos))->getData();
            if (count($assignedFutureOrders)){
                foreach ($assignedFutureOrders as $assignedFutureOrder){
                    $ordersToDispatchData['orderno'] = $assignedFutureOrder['order_number'];
                    $ordersToDispatchData['location'] = $assignedFutureOrder['location'];
                    $ordersToDispatchData['pallet_number'] = $assignedFutureOrder['pallet_number'];
                    $ordersToDispatch[] = $ordersToDispatchData;
                }
                $return_array = array("status" => true , "message" => "Future Orders To Dispatch Today" , "orders" => $ordersToDispatch);
            }else{
                $return_array = array("status" => false , "message" => "Future Orders To Dispatch Today Not Assigned Location" , "orders" => $order_nos);
            }
        }else{
            $return_array = array("status" => false , "message" => "No Future Orders To Dispatch Today");
        }

        echo json_encode($return_array);exit;
    }
}