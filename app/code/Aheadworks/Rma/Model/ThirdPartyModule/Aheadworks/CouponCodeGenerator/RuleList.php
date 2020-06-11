<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ThirdPartyModule\Aheadworks\CouponCodeGenerator;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

/**
 * Class RuleList
 *
 * @package Aheadworks\Rma\Model\ThirdPartyModule\Aheadworks\CouponCodeGenerator
 */
class RuleList
{
    /**
     * @var \Aheadworks\Coupongenerator\Model\ResourceModel\Salesrule\Collection
     */
    private $collection;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param RequestRepositoryInterface $requestRepository
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepositoryInterface $customerRepository
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        RequestRepositoryInterface $requestRepository,
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository,
        MetadataPool $metadataPool
    ) {
        $this->objectManager = $objectManager;
        $this->collection = $this->objectManager->create(
            \Aheadworks\Coupongenerator\Model\ResourceModel\Salesrule\Collection::class
        );
        $this->requestRepository = $requestRepository;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Get rule options allowed for rma request
     *
     * @param int $requestId
     * @return array
     * @throws LocalizedException
     */
    public function getRuleOptionArrayForRequest($requestId)
    {
        $optionArray = [];
        try {
            $request = $this->requestRepository->get($requestId);
            $this->collection
                ->setActiveRules()
                ->addWebsiteFilter($this->getWebsiteIdForStore($request->getStoreId()));
            $customerGroupId = GroupInterface::NOT_LOGGED_IN_ID;
            if ($request->getCustomerId()) {
                $customer = $this->customerRepository->getById($request->getCustomerId());
                $customerGroupId = $customer->getGroupId();
            }
            $this->addCustomerGroupsFilter($customerGroupId);
            $optionArray = $this->collection->toOptionArray();
        } catch (NoSuchEntityException $exception) {
        }

        return $optionArray;
    }

    /**
     * Get website ID by store ID
     *
     * @param int $storeId
     * @return int
     * @throws NoSuchEntityException
     */
    private function getWebsiteIdForStore($storeId)
    {
        return $this->storeManager->getStore($storeId)->getWebsiteId();
    }

    /**
     * Extend rule collection to include filter by customer groups
     *
     * @param int $customerGroupId
     * @throws \Exception
     */
    private function addCustomerGroupsFilter($customerGroupId)
    {
        $linkField = $this->metadataPool->getMetadata(RuleInterface::class)->getLinkField();
        $condition = [
            'main_table.rule_id = msrcg.' . $linkField,
            'msrcg.customer_group_id =' . $customerGroupId
        ];

        $this->collection
            ->getSelect()
            ->joinRight(
                ['msrcg' => $this->collection->getResource()->getTable('salesrule_customer_group')],
                implode(' AND ', $condition),
                []
            )
            ->group('main_table.rule_id');
    }
}
