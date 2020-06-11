<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel;

use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as FrameworkAbstractCollection;

/**
 * Class AbstractCollection
 *
 * @package Aheadworks\Rma\Model\ResourceModel
 */
class AbstractCollection extends FrameworkAbstractCollection
{
    /**
     * @var int
     */
    protected $storeId;

    /**
     * @var array
     */
    private $linkageTableNames = [];

    /**
     * Attach relation table data to collection items
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @param string|array $columnNameRelationTable
     * @param string $fieldName
     * @param array $conditions
     * @return void
     */
    public function attachRelationTable(
        $tableName,
        $columnName,
        $linkageColumnName,
        $columnNameRelationTable,
        $fieldName,
        $conditions = []
    ) {
        $ids = $this->getColumnValues($columnName);
        if (count($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from([$tableName . '_table' => $this->getTable($tableName)])
                ->where($tableName . '_table.' . $linkageColumnName . ' IN (?)', $ids);
            foreach ($conditions as $condition) {
                $select->where(
                    $tableName . '_table.' . $condition['field'] . ' ' . $condition['condition'] . ' (?)',
                    $condition['value']
                );
            }
            /** @var \Magento\Framework\DataObject $item */
            foreach ($this as $item) {
                $resultIds = [];
                $id = $item->getData($columnName);
                foreach ($connection->fetchAll($select) as $data) {
                    if ($data[$linkageColumnName] == $id) {
                        if (is_array($columnNameRelationTable)) {
                            $fieldValue = [];
                            foreach ($columnNameRelationTable as $columnNameRelation) {
                                $fieldValue[$columnNameRelation] = $data[$columnNameRelation];
                            }
                            $resultIds[] = $fieldValue;
                        } else {
                            $resultIds[] = $data[$columnNameRelationTable];
                        }
                    }
                }
                $item->setData($fieldName, $resultIds);
            }
        }
    }

    /**
     * Join to linkage table if filter is applied
     *
     * @param string $tableName
     * @param string $columnName
     * @param string $linkageColumnName
     * @param string $columnFilter
     * @param string $fieldName
     * @param array $conditions
     * @param bool $addGroupBy
     * @param bool $notFilter
     * @return $this
     */
    public function joinLinkageTable(
        $tableName,
        $columnName,
        $linkageColumnName,
        $columnFilter,
        $fieldName,
        $conditions = [],
        $addGroupBy = true,
        $notFilter = false
    ) {
        if ($this->getFilter($columnFilter) || $notFilter) {
            $linkageTableName = $columnFilter . '_table';
            if (in_array($linkageTableName, $this->linkageTableNames)) {
                $this->addFilterToMap($columnFilter, $columnFilter . '_table.' . $fieldName);
                return $this;
            }

            $this->linkageTableNames[] = $linkageTableName;
            $select = $this->getSelect();
            $select->joinLeft(
                [$linkageTableName => $this->getTable($tableName)],
                'main_table.' . $columnName . ' = ' . $linkageTableName . '.' . $linkageColumnName,
                []
            );
            if ($addGroupBy) {
                $select->group('main_table.' . $columnName);
            }

            foreach ($conditions as $condition) {
                $select->where(
                    $linkageTableName . '.' . $condition['field'] . ' ' . $condition['condition'] . ' (?)',
                    $condition['value']
                );
            }
            $this->addFilterToMap($columnFilter, $columnFilter . '_table.' . $fieldName);
        }

        return $this;
    }

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Retrieve storefront value
     *
     * @param array $objects
     * @param bool $returnValue
     * @return array|string|null
     */
    protected function getStorefrontValue($objects, $returnValue)
    {
        $storefrontValue = null;
        $minStoreIdStorefrontValue = null;
        $minStoreIdAvailable = null;
        foreach ($objects as $object) {
            if ($object[StoreValueInterface::STORE_ID] == $this->storeId) {
                $storefrontValue = $returnValue ? $object[StoreValueInterface::VALUE] : $object;
            }
            if (null === $minStoreIdAvailable) {
                $minStoreIdAvailable = $object[StoreValueInterface::STORE_ID];
            }
            if ($minStoreIdAvailable >= $object[StoreValueInterface::STORE_ID]
                && !empty($object[StoreValueInterface::VALUE])
            ) {
                $minStoreIdAvailable = $object[StoreValueInterface::STORE_ID];
                $minStoreIdStorefrontValue = $returnValue ? $object[StoreValueInterface::VALUE] : $object;
            }
        }
        $storefrontValue = empty($storefrontValue)
            ? $minStoreIdStorefrontValue
            : $storefrontValue;

        return $storefrontValue;
    }
}
