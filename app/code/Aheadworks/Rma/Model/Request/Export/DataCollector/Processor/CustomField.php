<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector\Processor;

use Aheadworks\Rma\Model\CustomField\Resolver\CustomField as CustomFieldResolver;
use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderMapper;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Model\Request\Export\DataCollector\Processor\CustomField\Loader as CustomFieldLoader;
use Magento\Store\Model\Store;

/**
 * Class CustomField
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector\Processor
 */
class CustomField extends AbstractProcessor
{
    /**
     * @var CustomFieldResolver
     */
    private $customFieldResolver;

    /**
     * @var CustomFieldLoader
     */
    private $customFieldLoader;

    /**
     * @param RowHeaderMapper $headerMapper
     * @param CustomFieldResolver $customFieldResolver
     * @param CustomFieldLoader $customFieldLoader
     */
    public function __construct(
        RowHeaderMapper $headerMapper,
        CustomFieldResolver $customFieldResolver,
        CustomFieldLoader $customFieldLoader
    ) {
        $this->customFieldResolver = $customFieldResolver;
        $this->customFieldLoader = $customFieldLoader;
        parent::__construct($headerMapper);
    }

    /**
     * @inheritdoc
     */
    public function prepareRowData($request, $requestItem, $resultRow)
    {
        $resultRow = $this->processCustomFields($request->getCustomFields(), $resultRow);
        $resultRow = $this->processCustomFields($requestItem[RequestItemInterface::CUSTOM_FIELDS], $resultRow);

        return $resultRow;
    }

    /**
     * @inheritdoc
     */
    public function prepareRowHeaders()
    {
        return $this->customFieldLoader->getFieldNamesForExport();
    }

    /**
     * Process custom fields
     *
     * @param array $customFields
     * @param array $resultRow
     * @return array
     */
    private function processCustomFields($customFields, $resultRow)
    {
        if (!$customFields) {
            return $resultRow;
        }

        foreach ($customFields as $customField) {
            $customFieldName = $this->customFieldResolver->getName(
                $customField[RequestCustomFieldValueInterface::FIELD_ID],
                Store::DEFAULT_STORE_ID
            );
            $selectedOptionName = $this->customFieldResolver->getValue(
                $customField[RequestCustomFieldValueInterface::FIELD_ID],
                $customField[RequestCustomFieldValueInterface::VALUE],
                Store::DEFAULT_STORE_ID
            );

            $rowFieldPosition = $this->rowHeaderMapper->getHeaderPosition($customFieldName);
            if ($rowFieldPosition) {
                $resultRow[$rowFieldPosition] = $selectedOptionName;
            }
        }

        return $resultRow;
    }
}
