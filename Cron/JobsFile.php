<?php

namespace TM\AndroidServices\Cron;

class JobsFile
{

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Status\History $history,
        \Magento\Sales\Model\OrderFactory $ordermodel,
        \TM\PalletQueue\Model\OrderInvoiceFactory $OrderInvoiceFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_history = $history;
        $this->logger = $logger;
        $this->ordermodel = $ordermodel;
        $this->_palletOrderFactory = $OrderInvoiceFactory;
    }

    public function execute()
    {
            $collection = $this->_orderCollectionFactory->create()->addFieldToFilter('status', 'being_wrapped')->addFieldToFilter('increment_id', '71658098');
            foreach ($collection->getData() as $order ){
                try {
                    $historyData = $this->_history->getCollection()->addFieldToFilter('status', 'being_wrapped')->addFieldToFilter('parent_id', $order['entity_id'])->getFirstItem()->getData();
                    $statusChangeAt = $historyData['created_at'];

                    $t1 = strtotime( $statusChangeAt );
                    $t2 = strtotime( date("Y-m-d H:i:s") );
                    $diff = $t2 - $t1;

                    $hoursPassed = $diff / ( 60 * 60 );

                    if($hoursPassed > 1){
                        $OrderModel = $this->ordermodel->create()->load( $historyData['parent_id'] );
                        $OrderModel->setStatus("problem_order");
                        $OrderModel->addStatusHistoryComment('Order status changed to Problem Order by system Being Wrapped for more than 1 hour');
                        $OrderModel->save();
                        
                        
                        $id = $this->_palletOrderFactory->create()->checkEntityId($OrderModel->getId(), $OrderModel->getStoreId());
                        
                        if($id)
                        {
                            $data = [];
                            $data['reason_order'] = "Order is in Being Wrapped status for more than 1 hour";
                            $data['reason_processor'] = "System";
                            $this->_palletOrderFactory->create()->addData($data)->setId($id)->save(); 
                        }
                        
                        
                        
                    }
                }catch (\Exception $e){
                    
                }
            }
        

    }
}
