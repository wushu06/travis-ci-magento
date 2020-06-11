<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\CustomField;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Loader
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\CustomField
 */
class Loader
{
    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomFieldInterface[]|null
     */
    private $customFields = null;

    /**
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        CustomFieldRepositoryInterface $customFieldRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customFieldRepository = $customFieldRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get row fields for header row
     */
    public function getFieldNamesForExport()
    {
        $rowFields = [];
        $customFields = $this->getCustomFields();
        foreach ($customFields as $customField) {
            $rowFields[] = $customField->getName();
        }

        return $rowFields;
    }

    /**
     * Get custom fields used for reports
     *
     * @return CustomFieldInterface[]
     * @throws LocalizedException
     */
    private function getCustomFields()
    {
        if ($this->customFields === null) {
            $this->searchCriteriaBuilder
                ->addFilter(CustomFieldInterface::IS_INCLUDED_IN_REPORT, Enabledisable::ENABLE_VALUE)
                ->addFilter(CustomFieldInterface::OPTIONS, 'enabled');
            $this->customFields = $this->customFieldRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();
        }

        return $this->customFields;
    }
}
