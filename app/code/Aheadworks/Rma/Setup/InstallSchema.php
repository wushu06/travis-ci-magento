<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Aheadworks\Rma\Setup\Updater\Schema\Updater as SchemaUpdater;

/**
 * Class InstallSchema
 *
 * @package Aheadworks\Rma\Setup
 */
class InstallSchema implements InstallSchemaInterface
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
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $this->addCustomFieldTables($installer)
            ->addRequestStatusTables($installer)
            ->addRequestTables($installer)
            ->addMessageTables($installer)
            ->addCannedResponseTables($installer);
        $this->upgradeSchema120->install($installer);
        $this->schemaUpdater->update140($installer);

        $installer->endSetup();
    }

    /**
     * Add custom field tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addCustomFieldTables(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_rma_custom_field'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_custom_field'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Custom Field Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'type',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Type'
            )
            ->addColumn(
                'refers',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Refers'
            )
            ->addColumn(
                'is_required',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Required'
            )
            ->addColumn(
                'is_display_in_label',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Display In Label'
            )
            ->setComment('RMA Custom Field');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_custom_field_option'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_custom_field_option'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Option Id'
            )
            ->addColumn(
                'field_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Custom Field Id'
            )
            ->addColumn(
                'sort_order',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Sort Order'
            )
            ->addColumn(
                'is_default',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Default'
            )
            ->addColumn(
                'enabled',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 1],
                'Enabled'
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_custom_field_option', 'field_id', 'aw_rma_custom_field', 'id'),
                'field_id',
                $installer->getTable('aw_rma_custom_field'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('RMA Custom Field Option');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_custom_field_option_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_custom_field_option_value'))
            ->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Option Id'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Value'
            )
            ->addForeignKey(
                $installer->getFkName(
                    'aw_rma_custom_field_option_value',
                    'option_id',
                    'aw_rma_custom_field_option',
                    'id'
                ),
                'option_id',
                $installer->getTable('aw_rma_custom_field_option'),
                'id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_custom_field_option_value', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->setComment('RMA Custom Field Option Value');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add request status tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addRequestStatusTables(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_rma_request_status'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_request_status'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Status Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'is_email_customer',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Email To Customer'
            )
            ->addColumn(
                'is_email_admin',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Email To Admin'
            )
            ->addColumn(
                'is_thread',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Is Thread'
            )
            ->setComment('RMA Request Status');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add request tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addRequestTables(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_rma_request'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_request'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Request Id'
            )
            ->addColumn(
                'increment_id',
                Table::TYPE_TEXT,
                100,
                ['nullable' => false],
                'Request Increment Id'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order ID'
            )
            ->addColumn(
                'payment_method',
                Table::TYPE_TEXT,
                255,
                [],
                'Payment Method'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Updated At'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Store ID'
            )
            ->addColumn(
                'last_reply_by',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Last Reply by'
            )
            ->addColumn(
                'status_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Status ID'
            )
            ->addColumn(
                'customer_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Customer ID'
            )
            ->addColumn(
                'customer_name',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer Name'
            )
            ->addColumn(
                'customer_email',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Customer Email'
            )
            ->addColumn(
                'print_label',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'Print Label'
            )
            ->addColumn(
                'external_link',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'External Link'
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_request', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_request', 'status_id', 'aw_rma_request_status', 'id'),
                'status_id',
                $installer->getTable('aw_rma_request_status'),
                'id',
                Table::ACTION_NO_ACTION
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_request', 'customer_id', 'customer_entity', 'entity_id'),
                'customer_id',
                $installer->getTable('customer_entity'),
                'entity_id',
                Table::ACTION_SET_NULL
            )
            ->setComment('RMA Request');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_request_custom_field_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_request_custom_field_value'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Entity Id'
            )
            ->addColumn(
                'field_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Field Id'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'Value'
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_request_custom_field_value', 'entity_id', 'aw_rma_request', 'id'),
                'entity_id',
                $installer->getTable('aw_rma_request'),
                'id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_request_custom_field_value', 'field_id', 'aw_rma_custom_field', 'id'),
                'field_id',
                $installer->getTable('aw_rma_custom_field'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('RMA Request Custom Field Value');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_request_item'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_request_item'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order Item Id'
            )
            ->addColumn(
                'request_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Request Id'
            )
            ->addColumn(
                'qty',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'default' => 0],
                'Qty'
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_request_item', 'request_id', 'aw_rma_request', 'id'),
                'request_id',
                $installer->getTable('aw_rma_request'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('RMA Request Item');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_request_item_custom_field_value'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_request_item_custom_field_value'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Entity Id'
            )
            ->addColumn(
                'field_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Field Id'
            )
            ->addColumn(
                'value',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'Value'
            )
            ->addForeignKey(
                $installer->getFkName(
                    'aw_rma_request_item_custom_field_value',
                    'entity_id',
                    'aw_rma_request_item',
                    'id'
                ),
                'entity_id',
                $installer->getTable('aw_rma_request_item'),
                'id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'aw_rma_request_item_custom_field_value',
                    'field_id',
                    $installer->getTable('aw_rma_custom_field'),
                    'id'
                ),
                'field_id',
                $installer->getTable('aw_rma_custom_field'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('RMA Request Item Custom Field Value');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add message tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function addMessageTables(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_rma_thread_message'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_thread_message'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Message Id'
            )
            ->addColumn(
                'request_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Request Id'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created At'
            )
            ->addColumn(
                'text',
                Table::TYPE_TEXT,
                4294967295,
                ['nullable' => false],
                'Message Text'
            )
            ->addColumn(
                'owner_type',
                Table::TYPE_SMALLINT,
                null,
                ['nullable' => false],
                'Owner Type'
            )
            ->addColumn(
                'owner_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Owner Id'
            )
            ->addColumn(
                'is_auto',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Auto'
            )
            ->addColumn(
                'is_internal',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                'Is Internal Message'
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_thread_message', 'request_id', 'aw_rma_request', 'id'),
                'request_id',
                $installer->getTable('aw_rma_request'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('RMA Thread Messages');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_thread_attachment'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_thread_attachment'))
            ->addColumn(
                'message_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true],
                'Message Id'
            )
            ->addColumn(
                'name',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'Name'
            )
            ->addColumn(
                'file_name',
                Table::TYPE_TEXT,
                Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'File Name On The Server'
            )
            ->addForeignKey(
                $installer->getFkName('aw_rma_thread_attachment', 'message_id', 'aw_rma_thread_message', 'id'),
                'message_id',
                $installer->getTable('aw_rma_thread_message'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('RMA Thread Attachments');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add canned response tables
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     */
    private function addCannedResponseTables(SchemaSetupInterface $installer)
    {
        /**
         * Create table 'aw_rma_canned_response'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_canned_response'))
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
                $installer->getIdxName(
                    $installer->getTable('aw_rma_canned_response'),
                    ['title'],
                    AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['title'],
                ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
            )->setComment('Canned Response');
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'aw_rma_canned_response_text'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_canned_response_text'))
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
                $installer->getIdxName('aw_rma_canned_response_text', ['response_id']),
                ['response_id']
            )->addIndex(
                $installer->getIdxName('aw_rma_canned_response_text', ['store_id']),
                ['store_id']
            )->addForeignKey(
                $installer->getFkName('aw_rma_canned_response_text', 'response_id', 'aw_rma_canned_response', 'id'),
                'response_id',
                $installer->getTable('aw_rma_canned_response'),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName('aw_rma_canned_response_text', 'store_id', 'store', 'store_id'),
                'store_id',
                $installer->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )->setComment('AW Rma Canned Response Text Table');
        $installer->getConnection()->createTable($table);
        
        return $this;
    }
}
