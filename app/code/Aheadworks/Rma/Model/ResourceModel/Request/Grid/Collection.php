<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Request\Grid;

use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Aheadworks\Rma\Model\ResourceModel\Request\Collection as RequestCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Aheadworks\Rma\Model\CustomField\Processor\ReadHandler as CustomFieldReadHandlerProcessor;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Mapper as PrintLabelMapper;

/**
 * Class Collection
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Request\Grid
 */
class Collection extends RequestCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor
     * @param PrintLabelMapper $printLabelMapper
     * @param mixed|null $mainTable
     * @param AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param string $model
     * @param AdapterInterface|null $connection
     * @param AbstractDb $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        CustomFieldReadHandlerProcessor $customFieldReadHandlerProcessor,
        PrintLabelMapper $printLabelMapper,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = Document::class,
        $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $customFieldReadHandlerProcessor,
            $printLabelMapper,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'order_increment_id') {
            $this->addFilter($field, $condition, 'public');
            return $this;
        }
        if ($field == 'customer') {
            return $this->addFilterByCustomer($condition['customer']);
        }
        if ($field == 'customer_info') {
            return $this->addFilterByCustomerInfo($condition);
        }
        if ($field == 'products') {
            return $this->addFilterByProducts($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == 'order_increment_id') {
            $this->joinLinkageTable(
                'sales_order',
                'order_id',
                'entity_id',
                'order_increment_id',
                'increment_id',
                [],
                false,
                true
            );
        }
        return parent::setOrder($field, $direction);
    }

    /**
     * {@inheritdoc}
     */
    protected function _renderFiltersBefore()
    {
        $this->joinLinkageTable(
            'sales_order',
            'order_id',
            'entity_id',
            'order_increment_id',
            'increment_id',
            [],
            false
        );
        $this->joinOrderedItems();
        parent::_renderFiltersBefore();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            'sales_order',
            'order_id',
            'entity_id',
            'increment_id',
            'order_increment_id'
        );
        $this->attachRelationTable(
            'customer_entity',
            'customer_id',
            'entity_id',
            ['firstname', 'lastname', 'email'],
            'customer'
        );
        parent::_afterLoad();
    }

    /**
     * @inheritdoc
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $countSelect->columns($this->getProductsColumn());
        $countSelect->group('main_table.id');

        $resultSelect = $this->getConnection()->select()->from($countSelect, 'COUNT(*)');

        return $resultSelect;
    }

    /**
     * Add ordered items to collection
     *
     * @return $this
     */
    private function joinOrderedItems()
    {
        $this->getSelect()
            ->join(
                ['request_item_table' => $this->getTable('aw_rma_request_item')],
                'main_table.id = request_item_table.request_id',
                []
            )->join(
                ['sales_order_item_table' => $this->getTable('sales_order_item')],
                'request_item_table.item_id = sales_order_item_table.item_id',
                $this->getProductsColumn()
            )->group('main_table.id');

        return $this;
    }

    /**
     * Return products column
     *
     * @return array
     */
    private function getProductsColumn()
    {
        return [
            'products' => new \Zend_Db_Expr("GROUP_CONCAT(sales_order_item_table.name SEPARATOR ', ')")
        ];
    }

    /**
     * Add filter by customer
     *
     * It filters by Customer ID and Email
     *
     * @param array $customer
     * @return $this
     */
    private function addFilterByCustomer($customer)
    {
        $this->getSelect()
            ->where('main_table.customer_id IS NULL AND main_table.customer_email = ?', $customer['customer_email'])
            ->orWhere('main_table.customer_id = ?', $customer['customer_id']);

        return $this;
    }

    /**
     * Add filter by customer info
     *
     * It filters by Customer Name and Email
     *
     * @param string $condition
     * @return $this
     */
    private function addFilterByCustomerInfo($condition)
    {
        $whereCondition = [
            $this->_translateCondition('main_table.customer_name', $condition),
            $this->_translateCondition('main_table.customer_email', $condition)
        ];
        $this->getSelect()->where(new \Zend_Db_Expr(implode(' OR ', $whereCondition)));

        return $this;
    }

    /**
     * Add filter by products
     *
     * It filters by ordered product names
     *
     * @param string $condition
     * @return $this
     */
    private function addFilterByProducts($condition)
    {
        $this->getSelect()->having($this->_translateCondition('products', $condition));

        return $this;
    }
}
