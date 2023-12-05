<?php

namespace TM\AndroidServices\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Reciepient extends Column
{

    /**
     * ProductDetail constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param OrderRepositoryInterface $orderRepository
     * @param Image $imageHelper
     * @param array $data
     */
    public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, array $components = [],
                                array $data = []
    )
    {

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$items)  {
                if($items['message_type'] == "Announcement"){
                    $items['reciepient'] = "";
                }
                //print_r($items);die("zzz");
            }
        }
        return $dataSource;
    }
}