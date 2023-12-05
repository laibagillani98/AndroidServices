<?php

namespace TM\AndroidServices\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

	public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
	{
		$installer = $setup;
		$installer->startSetup();
		if (!$installer->tableExists('order_checking_app')) {
			$table = $installer->getConnection()->newTable(
				$installer->getTable('order_checking_app')
			)
				->addColumn(
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
				)
				->addColumn(
					'order_no',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					['nullable => false'],
					'Order No'
				)
				->addColumn(
					'sku',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'sku'
				)
				->addColumn(
					'problem',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Problem with Product'
				)
				->addColumn(
					'reported_by',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Checking User'
				)
				->addColumn(
					'status',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					11,
					[],
					'Problem Status'
				)
				->addColumn(
					'submitted_image',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Posted Image'
				)
				->addColumn(
					'note',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Note by Checker'
				)
				->addColumn(
					'reported_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
					null,
					['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
					'Reported At'
				)
				->setComment('Post Table');
			$installer->getConnection()->createTable($table);

			$installer->getConnection()->addIndex(
				$installer->getTable('order_checking_app'),
				$setup->getIdxName(
					$installer->getTable('order_checking_app'),
					['order_no', 'sku', 'problem', 'reported_by'],
					\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
				),
				['order_no', 'sku', 'problem', 'reported_by'],
				\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
			);
		}
		$installer->endSetup();
		$connection = $installer->getConnection();
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
}