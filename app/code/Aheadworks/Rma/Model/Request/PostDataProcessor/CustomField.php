<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PostDataProcessor;

/**
 * Class CustomField
 *
 * @package Aheadworks\Rma\Model\Request\PostDataProcessor
 */
class CustomField implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        $data = $this->prepareCustomFields($data);

        return $data;
    }

    /**
     * Prepare custom fields for item
     *
     * @param array $data
     * @return array
     */
    private function prepareCustomFields($data)
    {
        if (!isset($data['custom_fields'])) {
            return $data;
        }

        $preparedCustomFields = [];
        foreach ($data['custom_fields'] as $customFieldId => $customFieldValue) {
            if (empty($customFieldValue)) {
                continue;
            }

            $preparedCustomFields[] = [
                'field_id' => $customFieldId,
                'value' => $customFieldValue,
            ];
        }
        $data['custom_fields'] = $preparedCustomFields;

        return $data;
    }
}
