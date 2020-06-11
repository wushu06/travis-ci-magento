<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Aheadworks\Rma\Api\CannedResponseRepositoryInterface;
use Aheadworks\Rma\Model\CannedResponse\StoreValueResolver;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CannedResponse
 *
 * @package Aheadworks\Rma\Model\Source
 */
class CannedResponse implements OptionSourceInterface
{
    /**
     * @var CannedResponseRepositoryInterface
     */
    private $cannedResponseRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StoreValueResolver
     */
    private $storeValueResolver;

    /**
     * @param CannedResponseRepositoryInterface $cannedResponseRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param StoreValueResolver $storeValueResolver
     */
    public function __construct(
        CannedResponseRepositoryInterface $cannedResponseRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        StoreValueResolver $storeValueResolver
    ) {
        $this->cannedResponseRepository = $cannedResponseRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->storeValueResolver = $storeValueResolver;
    }

    /**
     * Retrieve canned responses
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => '',
                'label' =>__('Please select canned response...')
            ]
        ];
        $cannedResponses = $this->getCannedResponses();
        $stores = $this->storeManager->getStores();
        foreach ($cannedResponses as $cannedResponse) {
            foreach ($stores as $store) {
                $storeId = $store->getId();
                $responseText = $this->storeValueResolver->getValueByStoreId(
                    $cannedResponse->getStoreResponseValues(),
                    $storeId
                );
                if ($responseText) {
                    $options[] = [
                        'store_id' => $storeId,
                        'value' => $cannedResponse->getId(),
                        'label' => $cannedResponse->getTitle(),
                        'content' => $responseText
                    ];
                }
            }
        }

        return $options;
    }

    /**
     * Retrieve enabled canned responses
     *
     * @return CannedResponseInterface[]|array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getCannedResponses()
    {
        $this->searchCriteriaBuilder
            ->addFilter(CannedResponseInterface::IS_ACTIVE, true, 'eq');
        $cannedResponses = $this->cannedResponseRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();

        return $cannedResponses;
    }
}
