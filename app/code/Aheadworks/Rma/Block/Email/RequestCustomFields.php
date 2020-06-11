<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Email;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Model\CustomField\Resolver\CustomField as CustomFieldResolver;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class RequestCustomFields
 *
 * @method DataObject getRmaRequest()
 * @method int getStoreId()
 * @package Aheadworks\Rma\Block\Email
 */
class RequestCustomFields extends Template
{
    /**
     * @var CustomFieldResolver
     */
    private $customFieldResolver;

    /**
     * @param Context $context
     * @param CustomFieldResolver $customFieldResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomFieldResolver $customFieldResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customFieldResolver = $customFieldResolver;
    }

    /**
     * Retrieve request custom fields
     *
     * @return array
     */
    public function getCustomFields()
    {
        $resolvedCustomFields = [];
        $request = $this->getRmaRequest();
        if (!$request) {
            return $resolvedCustomFields;
        }
        if (empty($request->getData('custom_fields')) || !is_array($request->getData('custom_fields'))) {
            return $resolvedCustomFields;
        }

        $customFields = $request->getData('custom_fields');
        /** @var RequestCustomFieldValueInterface $customField */
        foreach ($customFields as $customField) {
            $value = $this->customFieldResolver
                ->getValue($customField->getFieldId(), $customField->getValue(), $this->getStoreId());
            $label = $this->customFieldResolver->getLabel($customField->getFieldId(), $this->getStoreId());
            $resolvedCustomFields[$label] = $value;
        }

        return $resolvedCustomFields;
    }
}
