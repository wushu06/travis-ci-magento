<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Model\ResourceModel\Attribute;

use Magento\Eav\Model\Config;
use Magento\Eav\Model\EntityFactory as EavEntityFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection as EavCollection;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Psr\Log\LoggerInterface;
use Elementary\EmployeesManager\Setup\CustomerEmployeeSetup;

class Collection extends EavCollection
{
    /**
     * Entity factory
     *
     * @var EavEntityFactory
     */
    protected $_eavEntityFactory;

    /**
     * [__construct description]
     * @param EntityFactory          $entityFactory    [description]
     * @param LoggerInterface        $logger           [description]
     * @param FetchStrategyInterface $fetchStrategy    [description]
     * @param ManagerInterface       $eventManager     [description]
     * @param Config                 $eavConfig        [description]
     * @param EavEntityFactory       $eavEntityFactory [description]
     * @param AdapterInterface|null  $connection       [description]
     * @param AbstractDb|null        $resource         [description]
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        Config $eavConfig,
        EavEntityFactory $eavEntityFactory,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_eavEntityFactory = $eavEntityFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $eavConfig, $connection, $resource);
    }

    /**
     * Main select object initialization.
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(
            ['main_table' => $this->getResource()->getMainTable()]
        )->where(
            'main_table.entity_type_id=?',
            $this->_eavEntityFactory->create()->setType(CustomerEmployeeSetup::ENTITY_TYPE_CODE)->getTypeId()
        )->join(
            ['additional_table' => $this->getTable(CustomerEmployeeSetup::EAV_ENTITY_TYPE_CODE . '_eav_attribute')],
            'additional_table.attribute_id = main_table.attribute_id'
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function getFilterAttributesOnly()
    {
        $this->getSelect()->where('additional_table.is_filterable', 1);
        return $this;
    }

    /**
     * @param int $status
     * @return $this
     */
    public function addVisibilityFilter($status = 1)
    {
        $this->getSelect()->where('additional_table.is_visible', $status);
        return $this;
    }

    /**
     * Specify attribute entity type filter
     *
     * @param int $typeId
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setEntityTypeFilter($typeId)
    {
        return $this;
    }
}