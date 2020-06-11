<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CannedResponse;

use Aheadworks\Rma\Api\Data\CannedResponseInterface;

/**
 * Class PostDataProcessor
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\CannedResponse
 */
class PostDataProcessor
{
    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data)
    {
        $data = $this->prepareResponseValues($data);

        return $data;
    }

    /**
     * Prepare response values
     *
     * @param array $data
     * @return array
     */
    private function prepareResponseValues($data)
    {
        $responseValues = isset($data[CannedResponseInterface::STORE_RESPONSE_VALUES])
            ? $data[CannedResponseInterface::STORE_RESPONSE_VALUES]
            : [];
        foreach ($responseValues as $key => $responseValue) {
            if (isset($responseValue['delete']) && $responseValue['delete']) {
                unset($data[CannedResponseInterface::STORE_RESPONSE_VALUES][$key]);
            }
        }

        return $data;
    }
}
