<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Processor;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Model\Source\CustomField\Type;

/**
 * Class ReadHandler
 *
 * @package Aheadworks\Rma\Model\CustomField\Processor
 */
class ReadHandler
{
    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @param CustomFieldRepositoryInterface $customFieldRepository
     */
    public function __construct(
        CustomFieldRepositoryInterface $customFieldRepository
    ) {
        $this->customFieldRepository = $customFieldRepository;
    }

    /**
     * Prepared custom fields data
     *
     * @param array $customFieldsData
     * @return array
     */
    public function preparedCustomFieldsData($customFieldsData)
    {
        $preparedCustomFields = [];
        foreach ($customFieldsData as $customField) {
            if (isset($preparedCustomFields[$customField['field_id']])) {
                $preparedCustomFields[$customField['field_id']]['value'][] = $customField['value'];
            } else {
                $customFieldEntity = $this->customFieldRepository->get($customField['field_id']);
                if ($customFieldEntity->getType() == Type::MULTI_SELECT && !is_array($customField['value'])) {
                    $customField['value'] = [$customField['value']];
                }
                $preparedCustomFields[$customField['field_id']] = $customField;
            }
        }

        return $preparedCustomFields;
    }
}
