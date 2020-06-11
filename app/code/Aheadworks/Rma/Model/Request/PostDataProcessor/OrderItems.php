<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PostDataProcessor;

/**
 * Class OrderItems
 *
 * @package Aheadworks\Rma\Model\Request\PostDataProcessor
 */
class OrderItems implements ProcessorInterface
{
    /**
     * @var CustomField
     */
    private $customFieldProcessor;

    /**
     * @param CustomField $customFieldProcessor
     */
    public function __construct(
        CustomField $customFieldProcessor
    ) {
        $this->customFieldProcessor = $customFieldProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if (isset($data['order_items'])) {
            $data['order_items'] = $this->preparedRequestItems($data['order_items']);
        } else {
            $data['order_items'] = [];
        }

        return $data;
    }

    /**
     * Prepared request items
     *
     * @param array $rawItems
     * @return array
     */
    private function preparedRequestItems($rawItems)
    {
        $preparedItems = [];
        foreach ($rawItems as $rawItem) {
            $preparedItem = $this->customFieldProcessor->process($rawItem);
            if (isset($preparedItem['qty']) || isset($preparedItem['custom_fields'])) {
                $preparedItems[] = $preparedItem;
            }
        }

        return $preparedItems;
    }
}
