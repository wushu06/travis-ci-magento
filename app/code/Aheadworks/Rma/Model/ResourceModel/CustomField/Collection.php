<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CustomField;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionInterface;
use Aheadworks\Rma\Model\Source\CustomField\StatusType;
use Aheadworks\Rma\Model\Source\CustomField\Type;
use Aheadworks\Rma\Model\ResourceModel\AbstractCollection;
use Aheadworks\Rma\Model\CustomField;
use Aheadworks\Rma\Model\ResourceModel\CustomField as ResourceCustomField;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\DataObject;

/**
 * Class Collection
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(CustomField::class, ResourceCustomField::class);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $addPublicFilter = [
            CustomFieldInterface::WEBSITE_IDS,
            CustomFieldInterface::EDITABLE_FOR_STATUS_IDS
        ];
        if (in_array($field, $addPublicFilter)) {
            $this->addFilter($field, $condition, 'public');
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add enabled options filter to collection
     *
     * @return $this
     */
    public function addEnabledOptionsFilter()
    {
        $connection = $this->getConnection();
        $select = $this->getConnection()->select()
            ->from(
                ['cf' => $this->getTable('aw_rma_custom_field')],
                ['cf_id' => 'cf.id', 'enabled_count' => new \Zend_Db_Expr('COUNT(cf.id)')]
            )->joinLeft(
                ['cfo' => $this->getTable('aw_rma_custom_field_option')],
                'cf.id = cfo.field_id'
            )->where('cf.type IN (?)', [Type::SELECT, Type::MULTI_SELECT])
            ->where('cfo.enabled = ?', 1)
            ->group('cf.id');

        $whereCondition = [
            $connection->quoteInto(
                '(main_table.type IN (?) AND IFNULL(cfeo.enabled_count, 0) > 0)',
                [Type::SELECT, Type::MULTI_SELECT]
            ),
            $connection->quoteInto(
                'main_table.type IN (?)',
                [Type::TEXT, Type::TEXT_AREA]
            ),
        ];
        $whereCondition = implode(' OR ', $whereCondition);

        $this->getSelect()
            ->joinLeft(
                ['cfeo' => $select],
                'main_table.id = cfeo.cf_id',
                []
            )->where('(' . $whereCondition . ')');

        return $this;
    }

    /**
     * Add editable or visible for status filter to collection
     *
     * @return $this
     */
    public function addEditableOrVisibleForStatusFilter($status)
    {
        $this->getSelect()
            ->joinLeft(
                ['cfs' => $this->getTable('aw_rma_custom_field_status')],
                'main_table.id = cfs.field_id',
                []
            )->where('cfs.status_type in (?)', [StatusType::CUSTOMER_VISIBLE, StatusType::CUSTOMER_EDITABLE])
            ->where('cfs.status = ?', $status);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            $this->getTable('aw_rma_custom_field_website'),
            'id',
            'field_id',
            'website_id',
            'website_ids'
        );
        $this->attachRelationTable(
            $this->getTable('aw_rma_custom_field_status'),
            'id',
            'field_id',
            'status',
            'visible_for_status_ids',
            [['field' => 'status_type', 'condition' => '=', 'value' => StatusType::CUSTOMER_VISIBLE]]
        );
        $this->attachRelationTable(
            $this->getTable('aw_rma_custom_field_status'),
            'id',
            'field_id',
            'status',
            'editable_admin_for_status_ids',
            [['field' => 'status_type', 'condition' => '=', 'value' => StatusType::ADMIN_EDITABLE]]
        );
        $this->attachRelationTable(
            $this->getTable('aw_rma_custom_field_status'),
            'id',
            'field_id',
            'status',
            'editable_for_status_ids',
            [['field' => 'status_type', 'condition' => '=', 'value' => StatusType::CUSTOMER_EDITABLE]]
        );
        $this->attachRelationTable(
            $this->getTable('aw_rma_custom_field_frontend_label'),
            'id',
            'field_id',
            ['store_id', 'value'],
            'frontend_labels'
        );

        /** @var \Magento\Framework\DataObject $item */
        foreach ($this as $item) {
            $item->setData(
                CustomFieldInterface::STOREFRONT_LABEL,
                $this->getStorefrontValue($item->getData(CustomFieldInterface::FRONTEND_LABELS), true)
            );
            $this->attachOptions($item);
        }

        return parent::_afterLoad();
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable(
            $this->getTable('aw_rma_custom_field_website'),
            'id',
            'field_id',
            'website_ids',
            'website_id'
        );
        $this->joinLinkageTable(
            $this->getTable('aw_rma_custom_field_status'),
            'id',
            'field_id',
            'visible_for_status_ids',
            'status',
            [['field' => 'status_type', 'condition' => '=', 'value' => StatusType::CUSTOMER_VISIBLE]]
        );
        $this->joinLinkageTable(
            $this->getTable('aw_rma_custom_field_status'),
            'id',
            'field_id',
            'editable_admin_for_status_ids',
            'status',
            [['field' => 'status_type', 'condition' => '=', 'value' => StatusType::ADMIN_EDITABLE]]
        );
        $this->joinLinkageTable(
            $this->getTable('aw_rma_custom_field_status'),
            'id',
            'field_id',
            'editable_for_status_ids',
            'status',
            [['field' => 'status_type', 'condition' => '=', 'value' => StatusType::CUSTOMER_EDITABLE]]
        );
        parent::_renderFiltersBefore();
    }

    /**
     * Attach options
     *
     * @param DataObject $item
     * @return void
     */
    private function attachOptions($item)
    {
        $options = [];
        if (in_array($item->getData(CustomFieldInterface::TYPE), [Type::MULTI_SELECT, Type::SELECT])) {
            $connection = $this->getConnection();
            $itemId = (int)$item->getData(CustomFieldInterface::ID);
            $select = $connection->select()
                ->from($this->getTable('aw_rma_custom_field_option'))
                ->where('field_id = :id')
                ->order('sort_order ' . SortOrder::SORT_ASC);
            $optionsData = $connection->fetchAll($select, ['id' => $itemId]);
            foreach ($optionsData as $optionData) {
                $optionData = $this->attachOptionValues($optionData);
                $optionData = $this->attachOptionActionStatuses($optionData);
                $options[] = $optionData;
            }
        }
        $item->setData(CustomFieldInterface::OPTIONS, $options);
    }

    /**
     * Attach values to option
     *
     * @param array $optionData
     * @return array
     */
    private function attachOptionValues($optionData)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('aw_rma_custom_field_option_value'), ['store_id', 'value'])
            ->where('option_id = :id');
        $optionValuesData = $connection->fetchAll($select, ['id' => $optionData[CustomFieldOptionInterface::ID]]);

        $optionData[CustomFieldOptionInterface::STORE_LABELS] = $optionValuesData;
        $optionData[CustomFieldOptionInterface::STOREFRONT_LABEL] = $this->getStorefrontValue($optionValuesData, true);

        return $optionData;
    }

    /**
     * Attach action statuses to option
     *
     * @param array $optionData
     * @return array
     */
    private function attachOptionActionStatuses($optionData)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('aw_rma_custom_field_option_action_status'))
            ->where('option_id = :id');
        $actionStatusesData = $connection->fetchAll($select, ['id' => $optionData[CustomFieldOptionInterface::ID]]);

        $actionStatuses = [];
        foreach ($actionStatusesData as $actionStatusesRow) {
            $actionStatuses[] = $actionStatusesRow['status_id'];
        }

        $optionData[CustomFieldOptionInterface::ACTION_STATUSES] = $actionStatuses;
        return $optionData;
    }
}
