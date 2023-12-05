<?php
namespace TM\AndroidServices\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use TM\AndroidServices\Api\Data\SkuHistoryInterface;
use TM\AndroidServices\Model\ResourceModel\SkuHistory as ResourceModel;

/**
 * Class SkuHistory
 */
class SkuHistory extends AbstractModel implements
    SkuHistoryInterface,
    IdentityInterface
{
    const CACHE_TAG = 'picked_problem_broken_sku_table';

    /**
     * Init
     */
    protected function _construct() // phpcs:ignore PSR2.Methods.MethodDeclaration
    {
        $this->_init(ResourceModel::class);
    }

    /**
     * @inheritDoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getId()
    {
        return $this->getData('record_id');
    }
    public function setId($record_id)
    {
        return $this->setData('record_id', $record_id);
    }
    public function getTabletUniqueId()
    {
        return $this->getData('tablet_unique_id');
    }
    public function setTabletUniqueId($tablet_unique_id)
    {
        return $this->setData('tablet_unique_id', $tablet_unique_id);
    }
    public function getType()
    {
        return $this->getData('type');
    }
    public function setType($type)
    {
        return $this->setData('type', $type);
    }

    public function getOrderNo()
    {
        return $this->getData('order_no');
    }

    public function setOrderNo($order_no)
    {
        return $this->setData('order_no', $order_no);
    }

    public function getSku()
    {
        return $this->getData('sku');
    }

    public function setSku($sku)
    {
        return $this->setData('sku', $sku);
    }

    public function getLocation()
    {
        return $this->getData('location');
    }

    public function setLocation($location)
    {
        return $this->setData('location', $location);
    }

    public function getActualWeight()
    {
        return $this->getData('actual_weight');
    }

    public function setActualWeight($actual_weight)
    {
        return $this->setData('actual_weight', $actual_weight);
    }

    public function getCalculatedWeight()
    {
        return $this->getData('calculated_weight');
    }

    public function setCalculatedWeight($calculated_weight)
    {
        return $this->setData('calculated_weight', $calculated_weight);
    }

    public function getProblem()
    {
        return $this->getData('problem');
    }

    public function setProblem($problem)
    {
        return $this->setData('problem', $problem);
    }

    public function getQuantity()
    {
        return $this->getData('quantity');
    }

    public function setQuantity($quantity)
    {
        return $this->setData('quantity', $quantity);
    }

    public function getReportedBy()
    {
        return $this->getData('reported_by');
    }

    public function setReportedBy($reported_by)
    {
        return $this->setData('reported_by', $reported_by);
    }

    public function getReportedAt()
    {
        return $this->getData('reported_at');
    }

    public function setReportedAt($reported_at)
    {
        return $this->setData('reported_at', $reported_at);
    }

    public function getTabletTime()
    {
        return $this->getData('tablet_time');
    }

    public function setTabletTime($tablet_time)
    {
        return $this->setData('tablet_time', $tablet_time);
    }

    public function getResolvedBy()
    {
        return $this->getData('resolved_by');
    }

    public function setResolvedBy($resolved_by)
    {
        return $this->setData('resolved_by', $resolved_by);
    }

    public function getResolvedAt()
    {
        return $this->getData('resolved_at');
    }

    public function setResolvedAt($resolved_at)
    {
        return $this->setData('resolved_at', $resolved_at);
    }

    public function setOverrideWeight($override_weight)
    {
        return $this->setData('override_weight', $override_weight);
    }

    public function getOverrideWeight()
    {
        return $this->getData('override_weight');
    }

    public function getProblemStatus()
    {
        return $this->getData('problem_status');
    }

    public function setProblemStatus($problem_status)
    {
        return $this->setData('problem_status', $problem_status);
    }

    public function getImage()
    {
        return $this->getData('image');
    }

    public function setImage($image)
    {
        return $this->setData('image', $image);
    }

}