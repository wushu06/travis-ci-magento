<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Customer;

use Magento\Framework\DB\Select;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OrderTotals
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Customer
 */
class OrderTotals extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('sales_order', 'entity_id');
    }

    /**
     * Retrieve total purchased amount by email
     *
     * @param string $customerEmail
     * @param int $storeId
     * @return float
     */
    public function getTotalPurchasedAmountByEmail($customerEmail, $storeId)
    {
        $connection = $this->getConnection();
        $select = $this
            ->getTotalPurchasedAmount($storeId)
            ->where('customer_email = ?', $customerEmail);

        return $connection->fetchOne($select);
    }

    /**
     * Retrieve total orders by email
     *
     * @param string $customerEmail
     * @param int $storeId
     * @return int
     */
    public function getTotalOrdersByEmail($customerEmail, $storeId)
    {
        $connection = $this->getConnection();
        $select = $this
            ->getTotalOrders($storeId)
            ->where('customer_email = ?', $customerEmail);

        return $connection->fetchOne($select);
    }

    /**
     * Retrieve total purchased amount by id
     *
     * @param int $customerId
     * @param int $storeId
     * @return float
     */
    public function getTotalPurchasedAmountById($customerId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $this
            ->getTotalPurchasedAmount($storeId)
            ->where('customer_id = ?', $customerId);

        return $connection->fetchOne($select);
    }

    /**
     * Retrieve total orders by email
     *
     * @param int $customerId
     * @param int $storeId
     * @return int
     */
    public function getTotalOrdersById($customerId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $this
            ->getTotalOrders($storeId)
            ->where('customer_id = ?', $customerId);

        return $connection->fetchOne($select);
    }

    /**
     * Retrieve total purchased amount
     *
     * @param int $storeId
     * @return Select
     */
    private function getTotalPurchasedAmount($storeId)
    {
        $select = $this
            ->getQueryForOrder($storeId)
            ->columns(['total' => '(SUM(base_grand_total) - IFNULL(SUM(base_total_refunded), 0))']);

        return $select;
    }

    /**
     * Retrieve total orders
     *
     * @param int $storeId
     * @return Select
     */
    private function getTotalOrders($storeId)
    {
        $select = $this
            ->getQueryForOrder($storeId)
            ->columns(['total' => 'COUNT(*)']);

        return $select;
    }

    /**
     * Retrieve query for order
     *
     * @param int $storeId
     * @return Select
     */
    private function getQueryForOrder($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['sales_order_table' => $this->getTable('sales_order')], [])
            ->where('sales_order_table.store_id IN (?)', $this->getStoreIds($storeId))
            ->where('sales_order_table.state IN (?)', [SalesOrder::STATE_COMPLETE]);

        return $select;
    }

    /**
     * Retrieve stores ids on one website
     *
     * @param int $storeId
     * @return array
     */
    private function getStoreIds($storeId)
    {
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        return $this->storeManager->getWebsite($websiteId)->getStoreIds();
    }
}
