<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TM\AndroidServices\Api;

interface FutureOrdersServicesInterface
{

    /**
     * Fetch all unassigned order order numbers.
     *
     * @return string
     */
    public function viewUnassignedOrders();

    /**
     * Fetch all users for future order processing.
     *
     * @return string
     */
    public function FuturePalletUsers();

    /**
     * Assign location to Future pallets.
     *
     * @param varchar $pallet_barcode order number and pallet no
     * @param varchar $pallet_no pallet number
     * @param varchar $user_name warehouse user
     * @param varchar $location
     * @param int $override_location change location of order
     * @param int $override_orderno change order on location
     * @return string
     */
    public function assignLocationFutureOrders();

    /**
     * Future Order Locatore.
     *
     * @param varchar $order_no
     * @return string
     */
    public function locateFutureOrder();

    /**
     * Remove Future Order.
     *
     * @param varchar $location
     * @param varchar $user_name
     * @return string
     */
    public function removeFutureOrder();

    /**
     * Future Orders List.
     *
     * @return string
     */
    public function FutureOrders();

}