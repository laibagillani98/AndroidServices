<?php
 
namespace TM\AndroidServices\Model;
 
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use TM\AndroidServices\Api\Data\SkuHistoryInterface;
use TM\AndroidServices\Api\SkuHistoryRepositoryInterface;
use TM\AndroidServices\Model\ResourceModel\SkuHistory\CollectionFactory as SkuHistoryCollectionFactory;
use TM\AndroidServices\Model\ResourceModel\SkuHistory\Collection;
 
class SkuHistoryRepository implements SkuHistoryRepositoryInterface
{
    /**
     * @var SkuHistoryFactory
     */
    private $SkuHistoryFactory;
 
    /**
     * @var SkuHistoryCollectionFactory
     */
    private $SkuHistoryCollectionFactory;
 
    /**
     * @var SkuHistorySearchResultInterfaceFactory
     */
    private $searchResultFactory;
    protected $searchResultsFactory;
 
    public function __construct(
        SkuHistoryFactory $SkuHistoryFactory,
        SkuHistoryCollectionFactory $SkuHistoryCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->SkuHistoryFactory = $SkuHistoryFactory;
        $this->SkuHistoryCollectionFactory = $SkuHistoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getById($id)
	{
	    $SkuHistory = $this->SkuHistoryFactory->create();
	    $SkuHistory->getResource()->load($SkuHistory, $id);
	    if (! $SkuHistory->getId()) {
	        throw new NoSuchEntityException(__('Unable to find SkuHistory with ID "%1"', $id));
	    }
	    return $SkuHistory;
	}
	 
	public function save(SkuHistoryInterface $SkuHistory)
	{
	    $SkuHistory->getResource()->save($SkuHistory);
	    return $SkuHistory;
	}
	 
	public function delete(SkuHistoryInterface $SkuHistory)
	{
	    $SkuHistory->getResource()->delete($SkuHistory);
	}
 
    // ... getById, save and delete methods listed above ...


    /**
     * @inheritDoc
     */
 
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->SkuHistoryCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[] = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }
}