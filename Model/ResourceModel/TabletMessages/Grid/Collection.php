<?php

namespace TM\AndroidServices\Model\ResourceModel\TabletMessages\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    protected $_idFieldName = 'id';
    const Mn_NOT_FIX = 1;
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
                      $mainTable = 'tablet_messages',
                      $resourceModel = 'TM\AndroidServices\Model\ResourceModel\TabletMessages',
                      $identifierName = null,
                      $connectionName = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel, $identifierName, $connectionName);
    }

    /**
     * @return Collection|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->columns(['reciepient' => new \Zend_Db_Expr('group_concat(reciepient)')])->group(array("sent_at"));
        return $this;
    }
}
