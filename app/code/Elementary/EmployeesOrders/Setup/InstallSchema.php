<?php
namespace Elementary\EmployeesOrders\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $this->createEmployeeOrderTable($setup);
        $installer->endSetup();
    }

    /**
     * install table for Employee Order
     *
     * @param SchemaSetupInterface $setup
     * @throws \Zend_Db_Exception
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function createEmployeeOrderTable(SchemaSetupInterface $setup)
    {
        if (!$setup->tableExists('elementary_employees_manager_employee_order')) {
            $table = $setup->getConnection()->newTable(
                $setup->getTable('elementary_employees_manager_employee_order')
            )
            ->addColumn(
                'employee_order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary'  => true,
                    'unsigned' => true,
                ],
                'Employee Order ID'
            )
            ->addColumn(
                'employee_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                ],
                'Employee Order Employee ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                ],
                'Employee Order Order ID'
            )
            ->addColumn(
                'item_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                ],
                'Employee Order Item ID'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT
                ],
                'Employee Order Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE
                ],
                'Employee Order Updated At'
            )
            ->setComment('Employee Order Table');
            $setup->getConnection()->createTable($table);

        }
    }
}
