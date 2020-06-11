<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\CannedResponse;

use Aheadworks\Rma\Model\ResourceModel\AbstractCollection;
use Aheadworks\Rma\Model\CannedResponse;
use Aheadworks\Rma\Model\ResourceModel\CannedResponse as ResourceCannedResponse;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;
use Aheadworks\Rma\Model\CannedResponse\StoreValueResolver;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Collection
 *
 * @package Aheadworks\Rma\Model\ResourceModel\CustomField
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var StoreValueResolver
     */
    private $storeValueResolver;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(CannedResponse::class, ResourceCannedResponse::class);
    }

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param StoreValueResolver $storeValueResolver
     * @param null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreValueResolver $storeValueResolver,
        $connection = null,
        AbstractDb $resource = null
    ) {
        $this->storeValueResolver = $storeValueResolver;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            $this->getTable('aw_rma_canned_response_text'),
            'id',
            'response_id',
            ['store_id', 'value'],
            'store_response_values'
        );

        /** @var \Magento\Framework\DataObject $item */
        foreach ($this as $item) {
            $item->setData(
                CannedResponseInterface::RESPONSE_TEXT,
                $this->storeValueResolver->getValueByStoreId(
                    $item->getStoreResponseValues(),
                    $this->storeId
                )
            );
        }

        return parent::_afterLoad();
    }
}
