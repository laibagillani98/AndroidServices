<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TM\AndroidServices\Api;

interface PalletQueueServicesInterface
{
    
    /**
     * Fetch Order from Queue for Tablet.
     *
     * @param varchar $user Tablet User.
     * @param varchar $shop_order if get shop orders in queue.
     * @param varchar $tab_unique_id Tablet Id
     * @param varchar $reason reason for logging in
     * @param varchar $training_mode if trainee user on table return only small pallet orders
     * @param varchar $token device cloud token
     * @param varchar $token_type device type
     * @param varchar $order_type if its blocation type
     * @return string
     */
    public function getQueueOrder();

    /**
     * Log in Tablet.
     *
     * @param varchar $user Tablet User.
     * @param varchar $tab_unique_id Tablet Id
     * @param varchar $reason reason for logging in
     * @param varchar $token device cloud token
     * @param varchar $token_type device type
     * @return string
     */
    public function loginTablet();

    /**
     * Log out Tablet.
     *
     * @param varchar $user Tablet User.
     * @param varchar $loginid Log Id
     * @param varchar $reason reason for logging out
     * @param varchar $orderid if order open on screen and admin logout
     * @return string
     */
    public function logoutTablet();

    /**
     * Report Problen with Order on Tablet.
     *
     * @param varchar $user Tablet User.
     * @param varchar $loginid User login id.
     * @param varchar $tab_unique_id Tablet Id
     * @param varchar $problem selected problem by user
     * @param varchar $orderid reported order id 
     * @param varchar $order_no reported order no
     * @param float $weight order weight calculatedon fork
     * @param varchar $sku problem product
     * @param varchar $location problem product location
     * @param varchar $park if user wants to park order
     * @param varchar $order_data_json parked order data
     * @param varchar $item_data_json parked order items data
     * @param varchar $pause_time process time on Table for parked order
     * @param varchar $pause_status pause status for parked order
     * @param varchar $printer_no network printer selected for printing
     * @param varchar $zpldata network printer label to print
     * @return string
     */
    public function reportOrderProblem();

    /**
     * Complete Order on Tablet.
     *
     * @param varchar $user Tablet User.
     * @param varchar $zpldata label to print
     * @param varchar $loginid user login id
     * @param varchar $process_time time taken for completing this order
     * @param varchar $orderid order to complete
     * @param varchar $printer_no selected printer on network
     * @param varchar $no_of_prints to print on network printer
     * @return string
     */
    public function orderComplete();

    /**
     * Picked Items on Tablet.
     *
     * @param varchar $user Tablet User.
     * @param varchar $sku label to print
     * @param varchar $tablet_unique_id order to complete
     * @param varchar $order_no 
     * @param float $actual_weight on fork
     * @param float $calculated_weight saved in magento
     * @param varchar $tablet_time when picked
     * @param varchar $local_id for android
     * @return string
     */
    public function itemsPicked();

     /**
     * Get Order by scanning barcode.
     *
     * @param varchar $user Tablet User.
     * @param varchar $loginid user login id
     * @param varchar $order_no 
     * @return string
     */
    public function getScanOrder();

     /**
     * Get all Complete Orders.
     *
     * @param varchar $user Tablet User.
     * @param varchar $pageno get collection page no.
     * @param varchar $order_no 
     * @return string
     */
    public function getAllCompleteOrders();

    /**
     * Get Completed Order Data.
     * @param varchar $order_no 
     * @return string
     */
    public function viewCompleteOrder();

    /**
     * print Completed Order.
     * @param varchar $data zpl data label to print
     * @param varchar $printer printer no to print label on
     * @return string
     */
    public function printCompleteOrder();

    /**
     * set Pallet Scan Time.
     * @param varchar $order_id 
     * @param varchar $timer pallet scan time in milli seconds
     * @return string
     */
    public function setPalletScanTime();

    /**
     * POST for orderDetails api
     * @param varchar $user Tablet User.
     * @param varchar $order_no reported order no
     * @return string
     */
    public function orderDetails();

    /**
     * POST for orderDetails api
     * @param varchar $user Tablet User.
     * @param varchar $order_no reported order no
     * @param varchar $return_products returned products
     * @param varchar $return_image returned image of products
     * @param varchar $return_by retunrn user
     * @param varchar $action_type type of action
     * @return string
     */
    public function returnRecords();

    /**
     * POST for putAways api
     * @param varchar $user Tablet User.
     * @return string
     */
    public function putAways();

    /**
     * POST for llopchecks api
     * @param varchar $llop_number number of the LLOP set up in the tablet settings.
     * @param varchar $llop_user user that was logged in when the check is performed.
     * @param varchar $hydraulic_system check reason in string.
     * @param varchar $wheels check reason in string.
     * @param varchar $forks check reason in string.
     * @param varchar $battery_charge check reason in string.
     * @param varchar $capacity_plate check reason in string.
     * @param varchar $gauges check reason in string.
     * @param varchar $brakes check reason in string.
     * @param varchar $steering check reason in string.
     * @param varchar $horn check reason in string.
     * @param varchar $lights check reason in string.
     * @param varchar $overall_condition check reason in string.
     * @return string
     */
    public function llopChecks();

   /**
     * GET for llopcheck config value api
     * @return string
     */
    public function getLLopConfig();


    /**
     * dhl api call.
     * @param varchar $order_id 
     * @param varchar $user
     * @param varchar $dhl_tablet
     * @return string
     */
    public function dhlApiCall();


    /**
     * POST for othervehiclechecks api
     * @param varchar $vehicle_info number of the LLOP set up in the tablet settings.
     * @param varchar $user user that was logged in when the check is performed.
     * @param varchar $hydraulic_system check reason in string.
     * @param varchar $wheels check reason in string.
     * @param varchar $forks check reason in string.
     * @param varchar $battery_charge check reason in string.
     * @param varchar $capacity_plate check reason in string.
     * @param varchar $gauges check reason in string.
     * @param varchar $brakes check reason in string.
     * @param varchar $steering check reason in string.
     * @param varchar $horn check reason in string.
     * @param varchar $lights check reason in string.
     * @param varchar $overall_condition check reason in string.
     * @return string
     */
    public function otherChecks();

    /**
     * POST for othervehiclechecks api
     * @param varchar $user
     * @param varchar $orderid
     * @param varchar $shipping_method
     * @param varchar $no_of_scan.
     * @param varchar $next_combine_pick.
     * @param varchar $is_local_connected.
     * @param varchar $combine_pick
     * @return string
     */
    public function CompleteCombinePick();

     /**
     * POST for othervehiclechecks api
     * @param varchar $user
     * @param varchar $orderno
     * @param varchar $sku
     * @param varchar $reason.
     * @return string
     */
    public function ReportBatchItem();

     /**
     * POST for generating Pallet for shop orders
     * @param varchar $pallet_no
     * @param float $weight
     * @param varchar $orders
     * @param varchar $zpldata
     * @param int $printer
     * @param int $store_id
     * @param varchar $user
     * @param varchar $completed_at
     * @return string
     */
    public function GenerateShopPallet();

    /**
     * POST for shop users
     * @param varchar $store
     * @return string
     */
    public function ShopUsers();
    
    /**
     * POST for get loaded Orders
      * @return string
     */
    public function GetLoadedOrders();

     /**
     * POST for unloading Pallets
     * @param varchar $data
     * @param varchar $user
     * @return string
     */
    public function UnloadingPallets();
    
    /**
     * POST for othervehiclechecks api
     * @param varchar $user
     * @param varchar $batch_no
     * @return string
     */
    public function parkBatch();


     /**
     * POST to check IP Address
     * @param varchar $ipaddress
     * @return string
     */
    public function CheckIPAddress();

}