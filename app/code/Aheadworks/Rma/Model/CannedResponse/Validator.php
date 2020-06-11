<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CannedResponse;

use Magento\Framework\Validator\AbstractValidator;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;

/**
 * Class Validator
 * @package Aheadworks\Rma\Model\CannedResponse
 */
class Validator extends AbstractValidator
{
    /**
     * Returns true if canned response entity meets the validation requirements
     *
     * @param CannedResponseInterface $cannedResponse
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    public function isValid($cannedResponse)
    {
        $this->_clearMessages();
        if (!$this->isCannedResponseDataValid($cannedResponse)) {
            return false;
        }

        return true;
    }

    /**
     * Returns true if canned response data is correct
     *
     * @param CannedResponseInterface $cannedResponse
     * @return bool
     * @throws \Zend_Validate_Exception
     */
    private function isCannedResponseDataValid($cannedResponse)
    {
        $responseStoreIds = [];
        if ($cannedResponse->getStoreResponseValues() && (is_array($cannedResponse->getStoreResponseValues()))) {
            /** @var \Aheadworks\Rma\Api\Data\StoreValueInterface $storeResponseValue */
            foreach ($cannedResponse->getStoreResponseValues() as $storeResponseValue) {
                if (!in_array($storeResponseValue->getStoreId(), $responseStoreIds)) {
                    array_push($responseStoreIds, $storeResponseValue->getStoreId());
                } else {
                    $this->_addMessages(['Duplicated store view in canned response found.']);
                    return false;
                }
            }
        }
        return true;
    }
}
