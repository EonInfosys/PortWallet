<?php

namespace EonInfosys\PortWallet\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'portwallet' payment table
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('portwallet')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'portwallet ID'
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['nullable' => false, 'default' => '0'],
            'Order ID'
        )->addColumn(
            'invoice',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => null],
            'portwallet invoice ID'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => true],
            'Invoice status'
        )->addColumn(
            'amount',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,null,
            ['nullable' => true],
            'amount'
        )->addColumn(
                'invoice_response',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null,
                ['nullable' => true],
                'invoice response'
        )->addColumn(
            'ipn_response',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,null,
            ['nullable' => true],
            'ipn response'
        )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                [],
                'Last updated date'
        );
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }
}
