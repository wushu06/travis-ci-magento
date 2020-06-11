<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Aheadworks\Rma\Setup\Updater\Schema\Updater as SchemaUpdater;

/**
 * Class UpgradeSchema
 *
 * @package Aheadworks\Rma\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var UpgradeSchema120
     */
    private $upgradeSchema120;

    /**
     * @var SchemaUpdater
     */
    private $schemaUpdater;

    /**
     * @param UpgradeSchema120 $upgradeSchema120
     * @param SchemaUpdater $schemaUpdater
     */
    public function __construct(
        UpgradeSchema120 $upgradeSchema120,
        SchemaUpdater $schemaUpdater
    ) {
        $this->upgradeSchema120 = $upgradeSchema120;
        $this->schemaUpdater = $schemaUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.2.0', '<')) {
            $this->upgradeSchema120->upgrade($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.3.0', '<')) {
            $this
                ->createCannedResponseTables($setup)
                ->addIsInternalFieldToThreadMessageTable($setup)
                ->addCustomerIdForeignKeyToRequestTable($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '1.4.0', '<')) {
            $this->schemaUpdater->update140($setup);
        }

        $setup->endSetup();
    }

    /**
     * Create canned response tables
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @return $this
     */
    private function createCannedResponseTables(SchemaSetupInterface $setup)
    {
        /**
         * Create table 'aw_rma_canned_response'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_rma_canned_response'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Canned Response ID'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Canned Response Title'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Canned Response Time'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Canned Response Modification Time'
            )->addColumn(
                'is_active',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => true],
                'Is Canned Response Active'
            )->addIndex(
                $setup->getIdxName(
                    $setup->getTable('aw_rma_canned_response'),
                    ['title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['title'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->setComment('Canned Response');
        $setup->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_canned_response_text'
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('aw_rma_canned_response_text'))
            ->addColumn(
                'response_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Response ID'
            )->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'primary' => true],
                'Store ID'
            )->addColumn(
                'value',
                Table::TYPE_TEXT,
                '2M',
                ['nullable' => false],
                'Value'
            )->addIndex(
                $setup->getIdxName('aw_rma_canned_response_text', ['response_id']),
                ['response_id']
            )->addIndex(
                $setup->getIdxName('aw_rma_canned_response_text', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $setup->getFkName('aw_rma_canned_response_text', 'response_id', 'aw_rma_canned_response', 'id'),
                'response_id',
                $setup->getTable('aw_rma_canned_response'),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName('aw_rma_canned_response_text', 'store_id', 'store', 'store_id'),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('AW Rma Canned Response Text Table');
        $setup->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add is internal field to thread message table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addIsInternalFieldToThreadMessageTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable('aw_rma_thread_message');
        $fieldName = 'is_internal';
        if (!$connection->tableColumnExists($tableName, $fieldName)) {
            $connection->addColumn(
                $tableName,
                $fieldName,
                [
                    'type'     => Table::TYPE_BOOLEAN,
                    'default'  => 0,
                    'nullable' => false,
                    'comment'  => 'Is Internal Message',
                ]
            );
        }

        return $this;
    }

    /**
     * Add customer foreign key to 'aw_rma_request' table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addCustomerIdForeignKeyToRequestTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $connection->addForeignKey(
            $setup->getFkName('aw_rma_request', 'customer_id', 'customer_entity', 'entity_id'),
            $setup->getTable('aw_rma_request'),
            'customer_id',
            $setup->getTable('customer_entity'),
            'entity_id',
            Table::ACTION_SET_NULL,
            true
        );

        return $this;
    }
}
