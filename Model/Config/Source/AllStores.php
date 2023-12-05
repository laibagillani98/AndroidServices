<?php
namespace TM\AndroidServices\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

class AllStores implements ArrayInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve store options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $storeManagerDataList = $this->storeManager->getStores();
        $options = [];

        foreach ($storeManagerDataList as $store) {
            // $value = str_replace(' ', '_', strtolower($store->getName()));

            $options[] = [
                'value' => $store->getId(),
                'label' => $store->getName(),
            ];
        }

        return $options;
    }
}

