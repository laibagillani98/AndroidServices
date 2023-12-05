<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TM\AndroidServices\Api;

interface WebServicesInterface
{
    /**
     * Report Broken Tiles from Tablet.
     *
     * @param varchar $user Reporting User.
     * @param varchar $tablet_unique_id Tablet Id.
     * @param varchar $location
     * @param varchar $sku
     * @param varchar $quantity broken qty.
     * @param varchar $printer_no printer to print.
     * @param varchar $zpldata label to print.
     * @return string
     */
    public function addBrokenTiles();

    /**
     * Report Broken Tiles from Tablet.
     *
     * @param varchar $user Completing User.
     * @param varchar $record_id Broken Tile record to resolve
     * @return string
     */
    public function completeBrokenTiles();

    /**
     * get order no from Android.
     *
     * @param mixed $orderno Order No.
     * @return string
     * @throws \Exception The specified cart does not exist.
     */

    public function orderChecking();

    /**
     * report product from Android.
     *
     * @param varchar $order_no Order No.
     * @param varchar $order_id Order Id.
     * @param varchar $sku Checking Product.
     * @param varchar $problem Repoted Problem.
     * @param varchar $status if problem or correct.
     * @param varchar $reported_by reporting user.
     * @param string $image Submitted Image.
     * @param varchar $note note by reporter.
     * @param varchar $index for android. 
     * @return string
     * @throws \Exception The specified cart does not exist.
     */

    public function reporting();

    /**
     * Checked order from Android.
     *
     * @param varchar $user Checking User.
     * @param varchar $orderid Order id.
     * @return string
     */
    public function checked();

    /**
     * All Pallet Users for Android.
     *
     * @return string
     */
    public function allusers();

    /**
     * Check avaiable network printers.
     *
     * @return string
     */
    public function checkPrinters();

    /**
     * Require action Broken Tiles.
     *
     * @return string
     */
    public function ReportedBrokenTiles();
    
    /**
     * Require action Broken Tiles.
     *
     * @return string
     */
    public function pickerList();

    /**
     * get authorized users from TM configuration.
     * @return array
     */
    public function getauthorizedusers();
    
    /**
     * SKU No from Android.
     *
     * @param varchar $sku
     * @return string
     */
    public function getSKUDetails();

    /**
     * Order Picked called by tablet.
     *
     * @param varchar $user Checking User.
     * @param varchar $orderid Order id.
     * @param boolean $is_locked Order lock status.
     * @return string
     */
    public function orderPicked();

    /**
     * user Authentication called by tablet.
     *
     * @param varchar $user_id Checking User.
     * @return string
     */
    public function UserAuthentication();

    /**
     * wooden panels called by HH.
      * @return string
     */
    public function GetWoodenPanels();

      /**
     * check items from called by HH.
     *
     * @param int $order_id order Id.
     * @param varchar $user user.
     * @param int $count item count.
     * @param varchar $shipping_method 
     * @param int $is_completed Order is completed if.
     * @param varchar $incrementId order Iincrement id.
     * @param int $store_id Store Id.
 
     * @return string
     */
    public function CheckWoodenPanels();

     /**
     * reprint items from called by HH.
     *
     * @param varchar $incrementId order Iincrement id.
     * @param int $count item count.
     * @param varchar $user user.
     * @param int $store_id Store Id.
     * @param varchar $shipping_method 
     * @return string
     */
    public function ReprintWoodenPanels();

}