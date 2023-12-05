<?php
 
namespace TM\AndroidServices\Api\Data;
 
 
interface SkuHistoryInterface
{
    /**
     * @return int
     */
    public function getId();
 
    /**
     * @param int $id
     * @return void
     */
    public function setId($id);
 
    /**
     * @return string
     */
    public function getTabletUniqueId();
 
    /**
     * @param string $tablet_unique_id
     * @return void
     */
    public function setTabletUniqueId($tablet_unique_id);
 
    /**
     * @return string
     */
    public function getType();
 
    /**
     * @param string $type
     * @return void
     */
    public function setType($type);
 
    /**
     * @return string
     */
    public function getOrderNo();
 
    /**
     * @param string $order_no
     * @return void
     */
    public function setOrderNo($order_no);

    /**
     * @return string
     */
    public function getSku();
 
    /**
     * @param string $sku
     * @return void
     */
    public function setSku($sku);

    /**
     * @return string
     */
    public function getLocation();
 
    /**
     * @param string $location
     * @return void
     */
    public function setLocation($location);

    /**
     * @return float
     */
    public function getActualWeight();
 
    /**
     * @param float $actual_weight
     * @return void
     */
    public function setActualWeight($actual_weight);

    /**
     * @return float
     */
    public function getCalculatedWeight();
 
    /**
     * @param float $calculated_weight
     * @return void
     */
    public function setCalculatedWeight($calculated_weight);

    /**
     * @return string
     */
    public function getProblem();
 
    /**
     * @param string $problem
     * @return void
     */
    public function setProblem($problem);

    /**
     * @return int
     */
    public function getQuantity();
 
    /**
     * @param int $quantity
     * @return void
     */
    public function setQuantity($quantity);

    /**
     * @return string
     */
    public function getReportedBy();
 
    /**
     * @param string $reported_by
     * @return void
     */
    public function setReportedBy($reported_by);

    /**
     * @return string
     */
    public function getReportedAt();
 
    /**
     * @param string $reported_at
     * @return void
     */
    public function setReportedAt($reported_at);

    /**
     * @return string
     */
    public function getTabletTime();
 
    /**
     * @param string $tablet_time
     * @return void
     */
    public function setTabletTime($tablet_time);

    /**
     * @return string
     */
    public function getResolvedBy();
 
    /**
     * @param string $resolved_by
     * @return void
     */
    public function setResolvedBy($resolved_by);

    /**
     * @return string
     */
    public function getResolvedAt();
 
    /**
     * @param string $resolved_at
     * @return void
     */
    public function setResolvedAt($resolved_at);

    /**
     * @return int
     */
    public function getOverrideWeight();
 
    /**
     * @param int $override_weight
     * @return void
     */
    public function setOverrideWeight($override_weight);

    /**
     * @return int
     */
    public function getProblemStatus();

    /**
     * @param int $problem_status
     * @return void
     */
    public function setProblemStatus($problem_status);

    /**
     * @return string
     */
    public function getImage();
 
    /**
     * @param string $image
     * @return void
     */
    public function setImage($image);

}