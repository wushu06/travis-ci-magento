<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Setup\Updater\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class Updater
 *
 * @package Aheadworks\Rma\Setup\Updater\Schema
 */
class Updater
{
    /**
     * Update for 1.4.0 version
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function update140(SchemaSetupInterface $setup)
    {
        $this
            ->addAdditionalColumnsToRequestStatusTable($setup)
            ->createAwRmaActionTable($setup)
            ->addActionColumnToCustomFieldOptionTable($setup)
            ->createCustomFieldOptionActionStatusTable($setup)
            ->addColumnsToCustomFieldTable($setup);
        return $this;
    }

    /**
     * Add additional fields to request status table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addAdditionalColumnsToRequestStatusTable($setup)
    {
        $tableName = 'aw_rma_request_status';
        $this
            ->addColumnsToTable(
                $setup,
                [
                    [
                        'fieldName' => 'sort_order',
                        'config' => [
                            'type' => Table::TYPE_SMALLINT,
                            'nullable' => false,
                            'default' => 0,
                            'comment' => 'Status sort order'
                        ]
                    ]
                ],
                $tableName
            )->addColumnsToTable(
                $setup,
                [
                    [
                        'fieldName' => 'is_active',
                        'config' => [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => false,
                            'default' => true,
                            'comment' => 'Is status active'
                        ]
                    ]
                ],
                $tableName
            );

        return $this;
    }

    /**
     * Add columns to table
     *
     * @param SchemaSetupInterface $setup
     * @param array $columnsConfig
     * @param string $tableName
     * @return $this
     */
    private function addColumnsToTable($setup, $columnsConfig, $tableName)
    {
        $connection = $setup->getConnection();
        $tableName = $setup->getTable($tableName);
        foreach ($columnsConfig as $fieldConfig) {
            $fieldName = $fieldConfig['fieldName'];
            if ($connection->tableColumnExists($tableName, $fieldName)) {
                continue;
            }
            $connection->addColumn(
                $tableName,
                $fieldName,
                $fieldConfig['config']
            );
        }

        return $this;
    }

    /**
     * Create AW RMA action table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     */
    private function createAwRmaActionTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_custom_field_option_action'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Action ID'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Rma Action Title'
            )->addColumn(
                'operation',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Rma Action Operation'
            )->setComment('AW RMA Action Table');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add action field to custom field option table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addActionColumnToCustomFieldOptionTable($setup)
    {
        $tableName = 'aw_rma_custom_field_option';
        $this
            ->addColumnsToTable(
                $setup,
                [
                    [
                        'fieldName' => 'action_id',
                        'config' => [
                            'type' => Table::TYPE_INTEGER,
                            'nullable' => true,
                            'comment' => 'Selected Action for Custom Field Option'
                        ]
                    ]
                ],
                $tableName
            );

        return $this;
    }

    /**
     * Create AW RMA action table
     *
     * @param SchemaSetupInterface $installer
     * @return $this
     * @throws \Zend_Db_Exception
     */
    private function createCustomFieldOptionActionStatusTable(SchemaSetupInterface $installer)
    {
        $table = $installer->getConnection()
            ->newTable($installer->getTable('aw_rma_custom_field_option_action_status'))
            ->addColumn(
                'option_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Option Id'
            )->addColumn(
                'status_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Status Id'
            )->addForeignKey(
                $installer->getFkName(
                    'aw_rma_custom_field_option_action_status',
                    'option_id',
                    'aw_rma_custom_field_option',
                    'id'
                ),
                'option_id',
                $installer->getTable('aw_rma_custom_field_option'),
                'id',
                Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'aw_rma_custom_field_option_action_status',
                    'status_id',
                    'aw_rma_request_status',
                    'id'
                ),
                'status_id',
                $installer->getTable('aw_rma_request_status'),
                'id',
                Table::ACTION_CASCADE
            )
            ->setComment('AW RMA Custom Field Option Action Status Table');
        $installer->getConnection()->createTable($table);

        return $this;
    }

    /**
     * Add additional columns to custom field table
     *
     * @param SchemaSetupInterface $setup
     * @return $this
     */
    private function addColumnsToCustomFieldTable($setup)
    {
        $tableName = 'aw_rma_custom_field';
        $this
            ->addColumnsToTable(
                $setup,
                [
                    [
                        'fieldName' => 'is_active',
                        'config' => [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => false,
                            'default' => true,
                            'comment' => 'Is custom field active'
                        ]
                    ]
                ],
                $tableName
            )->addColumnsToTable(
                $setup,
                [
                    [
                        'fieldName' => 'is_included_in_report',
                        'config' => [
                            'type' => Table::TYPE_BOOLEAN,
                            'nullable' => false,
                            'default' => true,
                            'comment' => 'Is custom field is included in exported report'
                        ]
                    ]
                ],
                $tableName
            );

        return $this;
    }
}
