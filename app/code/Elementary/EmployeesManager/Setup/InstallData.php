<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Elementary\EmployeesManager\Setup\CustomerEmployeeSetupFactory;

class InstallData implements InstallDataInterface
{
    /**
     * CustomerEmployee setup factory
     *
     * @var CustomerEmployeeSetupFactory
     */
    protected $customeremployeeSetupFactory;

    /**
     * Init
     *
     * @param CustomerEmployeeSetupFactory $customeremployeeSetupFactory
     */
    public function __construct(CustomerEmployeeSetupFactory $customeremployeeSetupFactory)
    {
        $this->customeremployeeSetupFactory = $customeremployeeSetupFactory;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerEmployeeSetup $customeremployeeSetup */
        $customeremployeeSetup = $this->customeremployeeSetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        $customeremployeeSetup->installEntities();
        $entities = $customeremployeeSetup->getDefaultEntities();
        foreach ($entities as $entityName => $entity) {
            $customeremployeeSetup->addEntityType($entityName, $entity);
        }

        $setup->endSetup();
    }
}