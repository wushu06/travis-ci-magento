<?php

namespace Elementary\Banner\Setup;

use Elementary\Banner\Api\Data;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade Schema
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     *
     * @param SchemaSetupInterface   $setup
     *
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        switch (true) {
            case !$context->getVersion():
            case version_compare($context->getVersion(), '1.0.2', '<'):
                $this->_addSlideCustomerGroupTable($setup);
                break;
        }
    }

    /**
     * Add Slide Group table
     *
     * @param SchemaSetupInterface $setup
     *
     * @return $this
     */
    protected function _addSlideCustomerGroupTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable(Data\SlideInterface::TABLE_CUSTOMER_GROUP)
        );

        $table->addColumn(Data\SlideInterface::SLIDE_ID, Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'primary' => true],
            'Block ID'
        );

        $table->addColumn(
            'customer_group',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Customer Group');

        $setup->getConnection()->createTable($table);
    }
}
