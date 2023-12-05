<?php

namespace TM\AndroidServices\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Sales\Model\Order\StatusFactory;
use Magento\Sales\Model\ResourceModel\Order\StatusFactory as StatusResourceFactory;

class UpgradeSchema implements UpgradeSchemaInterface
 {
    /*
        CUSTOM_STATUS_CODE,CUSTOM_STATE_CODE and CUSTOM_STATUS_LABEL
    */

    const CUSTOM_STATUS_CODE = 'checked';
    const CUSTOM_STATE_CODE = 'processing';
    const CUSTOM_STATUS_LABEL = 'Checked';

    protected $statusFactory;
    protected $statusResourceFactory;

    public function __construct(
        StatusFactory $statusFactory,
        StatusResourceFactory $statusResourceFactory
    ) {
        $this->_statusFactory = $statusFactory;
        $this->_statusResourceFactory = $statusResourceFactory;
    }

    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        
        $installer = $setup;

        $installer->startSetup();
        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '1.0.2', '<')) {

          if ($connection->tableColumnExists('sales_order', 'is_clearance') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('sales_order'),
                        'is_clearance',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 0,
                            'comment' => 'is_clearance'
                        ]
                    );
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.3', '<')) {

          if ($connection->tableColumnExists('mb_order_processing_pallet', 'in_queue') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'in_queue',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 1,
                            'comment' => 'tablet queue status'
                        ]
                    );
            }

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'tablet_user') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'tablet_user',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'tablet user'
                        ]
                    );
            }

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'tablet_status') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'tablet_status',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'order status on tablet'
                        ]
                    );
            }

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'process_time') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'process_time',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'order time on tablet'
                        ]
                    );
            }

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'pallet_size') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'pallet_size',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'pallet for order'
                        ]
                    );
            }
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'is_shop_order') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'is_shop_order',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 0,
                            'comment' => 'is_shop_order'
                        ]
                    );
            }

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'is_collected') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'is_collected',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 0,
                            'comment' => 'is_collected'
                        ]
                    );
            }

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'is_pick_created') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'is_pick_created',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 0,
                            'comment' => 'is_pick_created'
                        ]
                    );
            }

            if ($connection->tableColumnExists('sales_order_item', 'is_shop_pick') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('sales_order_item'),
                        'is_shop_pick',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 0,
                            'comment' => 'is_shop_pick'
                        ]
                    );
            }
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {

          if ($connection->tableColumnExists('mb_order_processing_pallet', 'pallet_scan') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'pallet_scan',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'palet on fork time'
                        ]
                    );
            }
        }

        if (version_compare($context->getVersion(), '1.0.8', '<')) {

            if ($connection->tableColumnExists('order_checking_app', 'order_id') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_checking_app'),
                        'order_id',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 11,
                            'default' => NULL,
                            'comment' => 'Order Id'
                        ]
                    );
            }
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            $statusAlready = $this->_statusFactory->create()->load("checked");
            $data = $statusAlready->getData();
            if(isset($data["status"])){
                $statusAlready->delete();
            }
            $statusResource = $this->_statusResourceFactory->create();
            $status = $this->_statusFactory->create();
            $status->setData([
                'status' => self::CUSTOM_STATUS_CODE,
                'label' => self::CUSTOM_STATUS_LABEL,
            ]);
            try {
                $statusResource->save($status);
            } catch (AlreadyExistsException $exception) {
                return $exception->getMessage();
            }

        }
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            if ($connection->tableColumnExists('mb_order_processing_pallet', 'is_pick_created')) {
                $setup->getConnection()->dropColumn($setup->getTable('mb_order_processing_pallet'), 'is_pick_created');
            }
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $statusAlready = $this->_statusFactory->create()->load("tablet_trial");
            $data = $statusAlready->getData();
            if(isset($data["status"])){
                $statusAlready->delete();
            }
            $statusResource = $this->_statusResourceFactory->create();
            $status = $this->_statusFactory->create();
            $status->setData([
                'status' => 'tablet_trial',
                'label' => "Tablet Trial",
            ]);
            try {
                $statusResource->save($status);
                $status->assignState(self::CUSTOM_STATE_CODE, false, false);
            } catch (AlreadyExistsException $exception) {
                return $exception->getMessage();
            }
        }
        
        
        
        
        if (version_compare($context->getVersion(), '1.1.2', '<')) {

          if ($connection->tableColumnExists('mb_order_processing_pallet', 'is_checked') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'is_checked',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 0,
                            'comment' => 'if order is checked from android'
                        ]
                    );
            }
        }

        if (version_compare($context->getVersion(), '1.1.3', '<')) {

            $table = $installer->getConnection()->newTable(
            $installer->getTable('return_record_app')
            )->addColumn(
                'return_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => TRUE, 'unsigned' => TRUE, 'nullable' => FALSE, 'primary' => TRUE],
                'Autoincremental ID'
            )->addColumn(
                'return_order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => FALSE],
                'return order id'
            )->addColumn(
                'return_products',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => FALSE],
                'returned products json'
            )->addColumn(
                'return_image',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => FALSE],
                'image uploaded while return'
            )->addColumn(
                'return_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => FALSE, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'order return date'
            )->addColumn(
                'return_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => FALSE],
                'return by'
            )->setComment( 'Hand Held Return Records' );

            $installer->getConnection()->createTable($table);
            
            $connection = $installer->getConnection();

        }
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            // 0 for Pending Returns , 1 for Return Records , 2 for Ready For Putaways table
            if ($connection->tableColumnExists('return_record_app', 'action') === false) {
                  $connection
                      ->addColumn(
                          $setup->getTable('return_record_app'),
                          'action',
                          [
                              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                              'length' => 1,
                              'default' => 0,
                              'comment' => '0 for “Pending Returns” and 1 for “Return Records” table'
                          ]
                      );
              }
            
              if ($connection->tableColumnExists('return_record_app', 'damaged_status') === false) {
                    $connection
                        ->addColumn(
                            $setup->getTable('return_record_app'),
                            'damaged_status',
                            [
                                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                                'length' => 255,
                                'default' => NULL,
                                'comment' => 'Damaged Status'
                            ]
                        );
                }
          }


 

          if (version_compare($context->getVersion(), '1.1.7', '<')) {
              $tableName = $installer->getTable('order_checks_app');
              if($connection->isTableExists($tableName) != true){

                  $table = $installer->getConnection()->newTable(
                      $installer->getTable('order_checks_app')
                  )->addColumn(
                      'check_id',
                      \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                      null,
                      [
                          'identity' => true,
                          'nullable' => false,
                          'primary'  => true,
                          'unsigned' => true,
                      ],
                      'Checking ID'
                  )->addColumn(
                      'order_no',
                      \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                      255,
                      ['nullable => false'],
                      'Order No'
                  )->addColumn(
                      'checked_by',
                      \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                      255,
                      ['nullable => false'],
                      'Checked By'
                  )->addColumn(
                      'check_start_time',
                      \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                      null,
                      ['nullable' => FALSE],
                      'Check Start Time'
                  )->addColumn(
                      'check_end_time',
                      \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                      null,
                      ['nullable' => FALSE],
                      'Check End Time'
                  )->addColumn(
                      'check_status',
                      \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                      11,
                      [],
                      '0/1'
                  )->addColumn(
                      'problem',
                      \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                      255,
                      ['nullable => false'],
                      'any reported issue with order'
                  ) ->setComment( 'Tablet App Order Checks(Previous order_checking_app is now Product Check)' );

                  $installer->getConnection()->createTable($table);

                  $connection = $installer->getConnection();

              }
          }

        if (version_compare($context->getVersion(), '1.1.5', '<')) {

            
            if ($connection->tableColumnExists('return_record_app', 'return_reason') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('return_record_app'),
                        'return_reason',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 255,
                            'default' => NULL,
                            'comment' => 'Return Reason'
                        ]
                    );
            }

        }


        if (version_compare($context->getVersion(), '1.1.8', '<')) {
            $tableName = $installer->getTable('llop_checks_app');
            if($connection->isTableExists($tableName) != true) {
                $table = $installer->getConnection()->newTable(
                    $installer->getTable('llop_checks_app')
                )->addColumn(
                    'llop_check_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Checking ID'
                )->addColumn(
                    'llop_check_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Check Date'
                )->addColumn(
                    'llop_number',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    255,
                    [],
                    'LLOP Number'
                )->addColumn(
                    'llop_user',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'LLOP User'
                )->addColumn(
                    'hydraulic_system',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Hydraulic System'
                )->addColumn(
                    'wheels',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Wheels'
                )->addColumn(
                    'forks',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Forks'
                )->addColumn(
                    'battery_charge',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Battery Charge'
                )->addColumn(
                    'capacity_plate',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Capacity plate'
                )->addColumn(
                    'gauges',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Gauges'
                )->addColumn(
                    'brakes',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Brakes'
                )->addColumn(
                    'steering',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Steering'
                )->addColumn(
                    'horn',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Horn'
                )->addColumn(
                    'lights',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Lights'
                )->addColumn(
                    'overall_condition',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Overall condition'
                )->setComment('LLOP Checks');

                $installer->getConnection()->createTable($table);

                $connection = $installer->getConnection();
            }

        }

        if (version_compare($context->getVersion(), '1.1.7', '<')) {
            if ($connection->tableColumnExists('return_record_app', 'return_order_id') === true) {

                $setup->getConnection()->modifyColumn(
                  $setup->getTable( 'return_record_app' ),
                 'return_order_id',
                  [
                      'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                      'comment' => 'Order Increment ID'
                   ]
                );
                }

              if ($connection->tableColumnExists('return_record_app', 'return_products') === true) {

                    $setup->getConnection()->modifyColumn(
                      $setup->getTable( 'return_record_app' ),
                     'return_products',
                      [
                          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
                      ]
                    );
                    }
    
        }

        if (version_compare($context->getVersion(), '1.1.8', '<')) {
            $table = $installer->getConnection()->newTable(
            $installer->getTable('other_checks_app')
            )->addColumn(
                'other_checks_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
            [
                'identity' => true,
                'nullable' => false,
                'primary'  => true,
                'unsigned' => true,
            ],
            'Checking ID'
            )->addColumn(
                'other_checks_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Check Date'
            )->addColumn(
                'vehicle_info',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Vehicle Information'
            )->addColumn(
                'user',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'User'
            ) ->addColumn(
                'hydraulic_system',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Hydraulic System'
            ) ->addColumn(
                'wheels',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Wheels'
            ) ->addColumn(
                'forks',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Forks'
            ) ->addColumn(
                'battery_charge',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Battery Charge'
            ) ->addColumn(
                'capacity_plate',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Capacity plate'
            ) ->addColumn(
                'gauges',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Gauges'
            ) ->addColumn(
                'brakes',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Brakes'
            ) ->addColumn(
                'steering',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Steering'
            ) ->addColumn(
                'horn',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Horn'
            ) ->addColumn(
                'lights',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Lights'
            ) ->addColumn(
                'overall_condition',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                [],
                'Overall condition'
            )   ->setComment( 'LLOP Checks' );

            $installer->getConnection()->createTable($table);
            $connection = $installer->getConnection();
        }

        if (version_compare($context->getVersion(), '1.1.9', '<')) {

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'is_blocation') === false) {
                  $connection
                      ->addColumn(
                          $setup->getTable('mb_order_processing_pallet'),
                          'is_blocation',
                          [
                              'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                              'length' => 1,
                              'default' => 0,
                              'comment' => 'if its b location order'
                          ]
                      );
            }
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {

          if ($connection->tableColumnExists('order_processing_small', 'in_queue') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'in_queue',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 1,
                            'comment' => 'tablet queue status dhl'
                        ]
                    );
            }

            if ($connection->tableColumnExists('order_processing_small', 'tablet_user') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'tablet_user',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'tablet user'
                        ]
                    );
            }

            if ($connection->tableColumnExists('order_processing_small', 'tablet_status') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'tablet_status',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'order status on tablet'
                        ]
                    );
            }

            if ($connection->tableColumnExists('order_processing_small', 'process_time') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'process_time',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'order time on tablet'
                        ]
                    );
            }

            if ($connection->tableColumnExists('order_processing_small', 'is_locked') === false)
            {
                $connection->addColumn(
                    $setup->getTable('order_processing_small'),'is_locked',
                        [
                            'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                            'nullable'  => false,
                            'after'     => null, // column name to insert new column after
                            'comment'   => '1 for locked 0 for unlocked',
                            'default'   => 0
                        ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.2.2', '<')) {

            if ($connection->tableColumnExists('login_order_history', 'tab_shipping') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('login_order_history'),
                        'tab_shipping',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'order status on tablet'
                        ]
                    );
            }

        }

        if (version_compare($context->getVersion(), '1.2.3', '<')) {

            $table = $installer->getConnection()->newTable(
            $installer->getTable('dhl_batch_no')
            )->addColumn(
                'batch_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => TRUE, 'unsigned' => TRUE, 'nullable' => FALSE, 'primary' => TRUE],
                'Autoincremental ID'
            )->addColumn(
                'batch_no',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => FALSE],
                'Batch Numbers'
            )->setComment( 'BatchNo of Combined Picks' );

            $installer->getConnection()->createTable($table);
            $connection = $installer->getConnection();
   
            if ($connection->tableColumnExists('order_processing_small', 'batch_queue_status') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'batch_queue_status',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 11,
                            'default' => NULL,
                            'comment' => '1 Ready For Tablet, 2 OnTablet, 3 Paused, 4 Completed on Tablet'
                        ]
                    );
            }  
            if ($connection->tableColumnExists('order_processing_small', 'scan_no') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'scan_no',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 11,
                            'default' => 0,
                            'comment' => 'scan no to keep check of batch order items'
                        ]
                    );
                    
            }
            if ($connection->tableColumnExists('order_processing_small', 'combined_pick_generated_at') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'combined_pick_generated_at',
                        [              
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            'default' => NULL,
                            'comment' => 'The time and date when the Combined Pick was created'
                        ]
                    );
                    
            }
            if ($connection->tableColumnExists('order_processing_small', 'combined_pick_completed_at') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'combined_pick_completed_at',
                        [              
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            'default' => NULL,
                            'comment' => 'The time and date when the Combined Pick was completed'
                        ]
                    );
                    
            }          
        }

        if (version_compare($context->getVersion(), '1.2.4', '<')) {

            if ($connection->tableColumnExists('mb_order_processing_pallet', 'is_blocation') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('mb_order_processing_pallet'),
                        'is_blocation',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'default' => 0,
                            'comment' => 'if its b location order'
                        ]
                    );
            }
        }
        if (version_compare($context->getVersion(), '1.2.5', '<')) {

            if ($connection->tableColumnExists('order_processing_small', 'out_of_stock') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('order_processing_small'),
                        'out_of_stock',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                            'length' => 100,
                            'default' => NULL,
                            'comment' => 'out of stock skus on tablet'
                        ]
                    );
            }

        }

        if (version_compare($context->getVersion(), '1.2.7', '<')) {
          if ($connection->tableColumnExists('sales_order_item', 'pick_count') === false) {
              $connection
                  ->addColumn(
                      $setup->getTable('sales_order_item'),
                      'pick_count',
                      [
                          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                          'length' => 10,
                          'default' => 0,
                          'comment' => 'pick_count'
                      ]
                  );
          }
          if ($connection->tableColumnExists('tm_woodpanel', 'count') === false) {
            $connection
                ->addColumn(
                    $setup->getTable('tm_woodpanel'),
                    'count',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 10,
                        'default' => 0,
                        'comment' => 'count'
                    ]
                );
        }
      }

      if (version_compare($context->getVersion(), '1.2.8', '<')) {
        if ($connection->tableColumnExists('sales_order_item', 'caliber_check') === false) {
            $connection
                ->addColumn(
                    $setup->getTable('sales_order_item'),
                    'caliber_check',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'length' => 1,
                        'default' => 0,
                        'comment' => 'caliber_check'
                    ]
                );
        }
      }
      
      if (version_compare($context->getVersion(), '1.2.9', '<')) {

          if ($connection->tableColumnExists('order_checking_app', 'check_status') === true) {

              $setup->getConnection()->modifyColumn(
                $setup->getTable( 'order_checking_app' ),
               'check_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'comment' => '0 for unchecked,1 for checked,2 for Problem,3 for Partially,4 for Loading orders'
                 ]
              );
            }
            if ($connection->tableColumnExists('order_checks_app', 'order_type') === false) {
              $connection
                  ->addColumn(
                      $setup->getTable('order_checks_app'),
                      'order_type',
                      [
                          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                          'length' => 255,
                          'default' => NULL,
                          'comment' => 'Order status type'
                      ]
                  );
            }
        
            if ($connection->tableColumnExists('mb_order_processing_pallet', 'pallet_number') === false) {
              $connection
                  ->addColumn(
                      $setup->getTable('mb_order_processing_pallet'),
                      'pallet_number',
                      [
                          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                          'length' => 100,
                          'default' => NULL,
                          'comment' => 'shop order pallet number'
                      ]
                  ); 
          }

          $table = $installer->getConnection()->newTable(
              $installer->getTable('mb_pallet_numbers')
              )->addColumn(
                  'pallet_id',
                  \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                  11,
                  ['identity' => TRUE, 'unsigned' => TRUE, 'nullable' => FALSE, 'primary' => TRUE],
                  'Autoincremental ID'
              )->addColumn(
                  'pallet_number',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  100,
                  ['nullable' => FALSE],
                  'Pallet Number'
              )->addColumn(
                  'pallet_weight',
                  \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                  [12, 4],
                  ['nullable' => true, 'unsigned' => TRUE]
              )->addColumn(
                  'pallet_completed_at',
                  \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                  'Pallet Completed At'
              )->addColumn(
                  'store_id',
                  \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                  'Store Id'
              )->addColumn(
                  'tablet_user',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  100,
                  ['nullable' => TRUE],
                  'Tablet User'
              )->addColumn(
                  'orders',
                  \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                  '2M',
                  ['nullable' => TRUE],
                  'Pallet Orders'
              )->addColumn(
                  'pallet_status',
                  \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                  'Pallet'  // 0 for normal, 1 for checked, 2 in transit, 3 for missing, 4 for partial ,5 for unloaded
              )->setComment( 'Pallet of Shop Orders');
  
              $installer->getConnection()->createTable($table);
              $connection = $installer->getConnection();
        }

        if (version_compare($context->getVersion(), '1.2.9', '<')) {
          if ($connection->tableColumnExists('order_checks_app', 'signature') === false) {
              $connection
                  ->addColumn(
                      $setup->getTable('order_checks_app'),
                      'signature',
                      [
                          'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                          'length' => 255,
                          'default' => NULL,
                          'comment' => 'Order Signature image'
                      ]
                  );
            }
        }
     

         $installer->endSetup();
    }
}