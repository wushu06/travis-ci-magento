<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Request;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Model\ResourceModel\AbstractCollection;
use Aheadworks\Rma\Model\Request;
use Aheadworks\Rma\Model\ResourceModel\Request as ResourceRequest;
use Magento\Framework\DataObject;
use Aheadworks\Rma\Model\CustomField\Processor\ReadHandler as CustomFieldReadHandlerProcessor;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Aheadworks\Rma\Model\Request\PrintLabel\Mapper as PrintLabelMapper;

/**
 * Class Collection
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Request
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected $_idFieldName = 'id';

    /**
     * @var CustomFieldReadHandlerProcessor
     */
    private $customFieldReadHandlerProcessor;

    /**
     * @var PrintLabelMapper
     */
    private $printLabelMapper;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface $connection
     * @param AbstractDb $resource
     * @param CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor
     * @param PrintLabelMapper $printLabelMapper
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor,
        PrintLabelMapper $printLabelMapper,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->customFieldReadHandlerProcessor = $customFieldReadHandlerProcessor;
        $this->printLabelMapper = $printLabelMapper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Request::class, ResourceRequest::class);
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == RequestItemInterface::ITEM_ID) {
            $this->addFilter($field, $condition, 'public');
            return $this;
        }
        if (strpos($field, 'custom_field_') !== false) {
            $this->addFilter($field, $condition, 'public');
            return $this;
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFilterToMap('store_id', 'main_table.store_id');
        $this->addFilterToMap('increment_id', 'main_table.increment_id');
        $this->addFilterToMap('created_at', 'main_table.created_at');
        $this->addFilterToMap('updated_at', 'main_table.updated_at');
        $this->addFilterToMap('order_id', 'main_table.order_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable(
            $this->getTable('aw_rma_request_item'),
            'id',
            'request_id',
            'item_id',
            'item_id'
        );

        foreach ($this->_filters as $filter) {
            if (strpos($filter['field'], 'custom_field_') !== false) {
                $fieldId = (int)str_replace('custom_field_', '', $filter['field']);
                $this->joinLinkageTable(
                    $this->getTable('aw_rma_request_custom_field_value'),
                    'id',
                    'entity_id',
                    $filter['field'],
                    'value',
                    [['field' => 'field_id', 'condition' => '=', 'value' => $fieldId]]
                );
            }
        }

        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            $this->getTable('aw_rma_request_custom_field_value'),
            'id',
            'entity_id',
            ['field_id', 'value'],
            'custom_fields'
        );
        /** @var \Magento\Framework\DataObject $item */
        foreach ($this as $item) {
            $item->setData(
                RequestInterface::CUSTOM_FIELDS,
                $this->customFieldReadHandlerProcessor->preparedCustomFieldsData(
                    $item->getData(RequestInterface::CUSTOM_FIELDS)
                )
            );
            $this->attachOrderItems($item);
            $this->convertPrintLabel($item);
        }
    }

    /**
     * Attach order items
     *
     * @param DataObject $item
     * @return $this
     */
    private function attachOrderItems($item)
    {
        $connection = $this->getConnection();
        $requestId = (int)$item->getData(RequestInterface::ID);
        $select = $connection->select()
            ->from($this->getTable('aw_rma_request_item'))
            ->where('request_id = :id');
        $itemsData = $connection->fetchAll($select, ['id' => $requestId]);

        $items = [];
        foreach ($itemsData as $itemData) {
            $itemData = $this->attachOrderItemCustomFields($itemData);
            $items[] = $itemData;
        }
        $item->setData(RequestInterface::ORDER_ITEMS, $items);

        return $this;
    }

    /**
     * Attach order item custom fields
     *
     * @param array $itemData
     * @return array
     */
    private function attachOrderItemCustomFields($itemData)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                $this->getTable('aw_rma_request_item_custom_field_value'),
                [RequestCustomFieldValueInterface::FIELD_ID, RequestCustomFieldValueInterface::VALUE]
            )->where('entity_id = :id');
        $itemCustomFieldsData = $connection->fetchAll($select, ['id' => $itemData[RequestItemInterface::ID]]);
        $itemData[RequestItemInterface::CUSTOM_FIELDS] = $this->customFieldReadHandlerProcessor
            ->preparedCustomFieldsData($itemCustomFieldsData);

        return $itemData;
    }

    /**
     * Attach order items
     *
     * @param DataObject $item
     * @return $this
     */
    private function convertPrintLabel($item)
    {
        $itemData = $this->printLabelMapper->databaseToEntity(
            RequestInterface::class,
            $item->getData()
        );
        $item->setData(RequestInterface::PRINT_LABEL, $itemData[RequestInterface::PRINT_LABEL]);

        return $this;
    }
}
