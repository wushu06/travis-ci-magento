<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class EavTablesSetup {
    /**
     * @var SchemaSetupInterface
     */
    protected $setup;

    /**
     * [__construct description]
     * @param SchemaSetupInterface $setup [description]
     */
    public function __construct(SchemaSetupInterface $setup) {
        $this->setup = $setup;
    }

    /**
     * create all eav tables
     */
    public function createEavTables($entityCode) {
        $this->createEAVMainTable($entityCode);
        $this->createEntityTable($entityCode, 'int', Table::TYPE_INTEGER);
        $this->createEntityTable($entityCode, 'varchar', Table::TYPE_TEXT, 255);
    }

    /**
     * create eav attributes tables and add foreign keys
     */
    protected function createEAVMainTable($entityCode) {
        $tableName = $entityCode . '_eav_attribute';
        $tableName = 'elementary_customeremployee_eav_attribute';

        $table = $this->setup->getConnection()->newTable(
            $this->setup->getTable($tableName)
        )->addColumn(
            'attribute_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Attribute Id'
        )->addColumn(
            'is_global',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Global'
        )->addColumn(
            'is_filterable',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Is Filterable'
        )->addColumn(
            'is_visible',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '1'],
            'Is Visible'
        )
            ->addColumn(
                'is_wysiwyg_enabled',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute uses WYSIWYG'
            )->addColumn(
                'validate_rules',
                Table::TYPE_TEXT,
                '64k',
                [],
                'Validate Rules'
            )->addColumn(
                'is_system',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Is System'
            )->addColumn(
                'sort_order',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Sort Order'
            )->addColumn(
                'data_model',
                Table::TYPE_TEXT,
                255,
                [],
                'Data Model'
            )->addForeignKey(
                $this->setup->getFkName($tableName, 'attribute_id', 'eav_attribute', 'attribute_id'),
                'attribute_id',
                $this->setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )->setComment(
                'RH CustomerEmployee Eav Attribute'
            );
        $this->setup->getConnection()->createTable($table);
    }

    /**
     * create eav entities tables and add foreign keys
     */
    protected function createEntityTable($entityCode, $type, $valueType, $valueLength = null) {
        $tableName = $entityCode . '_' . $type;

        $table = $this->setup->getConnection()
            ->newTable($this->setup->getTable($tableName))
            ->addColumn(
                'value_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Value ID'
            )
            ->addColumn(
                'entity_type_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity Type ID'
            )
            ->addColumn(
                'attribute_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Attribute ID'
            )
            ->addColumn(
                'store_id',
                Table::TYPE_SMALLINT,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Store ID'
            )
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'Entity ID'
            )
            ->addColumn(
                'value',
                $valueType,
                $valueLength,
                [],
                'Value'
            )
            ->addIndex(
                $this->setup->getIdxName(
                    $tableName,
                    ['entity_id', 'attribute_id', 'store_id', 'entity_type_id'],
                    AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['entity_id', 'attribute_id', 'store_id', 'entity_type_id'],
                ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
            )
            ->addIndex(
                $this->setup->getIdxName($tableName, ['entity_id']),
                ['entity_id']
            )
            ->addIndex(
                $this->setup->getIdxName($tableName, ['attribute_id']),
                ['attribute_id']
            )
            ->addIndex(
                $this->setup->getIdxName($tableName, ['store_id']),
                ['store_id']
            )
            ->addIndex(
                $this->setup->getIdxName($tableName, ['entity_type_id']),
                ['entity_type_id']
            )
            ->addForeignKey(
                $this->setup->getFkName(
                    $tableName,
                    'attribute_id',
                    'eav_attribute',
                    'attribute_id'
                ),
                'attribute_id',
                $this->setup->getTable('eav_attribute'),
                'attribute_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->setup->getFkName(
                    $tableName,
                    'entity_id',
                    $entityCode,
                    'entity_id'
                ),
                'entity_id',
                $this->setup->getTable($entityCode),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->setup->getFkName($tableName, 'store_id', 'store', 'store_id'),
                'store_id',
                $this->setup->getTable('store'),
                'store_id',
                Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $this->setup->getFkName($tableName, 'entity_type_id', 'eav_entity_type', 'entity_type_id'),
                'entity_type_id',
                $this->setup->getTable('eav_entity_type'),
                'entity_type_id',
                Table::ACTION_CASCADE
            )
            ->setComment($entityCode . ' ' . $type . 'Attribute Backend Table');
        $this->setup->getConnection()->createTable($table);
    }
}