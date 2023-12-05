<?php
 
namespace TM\AndroidServices\Api;
 
use Magento\Framework\Api\SearchCriteriaInterface;
use TM\AndroidServices\Api\Data\SkuHistoryInterface;
 
interface SkuHistoryRepositoryInterface
{
    /**
     * @param int $id
     * @return \TM\AndroidServices\Api\Data\SkuHistoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);
 
    /**
     * @param \TM\AndroidServices\Api\Data\SkuHistoryInterface $skuhistory
     * @return \TM\AndroidServices\Api\Data\SkuHistoryInterface
     */
    public function save(SkuHistoryInterface $skuhistory);
 
    /**
     * @param \TM\AndroidServices\Api\Data\SkuHistoryInterface $skuhistory
     * @return void
     */
    public function delete(SkuHistoryInterface $skuhistory);
 
    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}