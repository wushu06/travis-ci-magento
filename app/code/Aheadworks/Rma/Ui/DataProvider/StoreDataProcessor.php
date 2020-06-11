<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\DataProvider;

use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StoreDataProcessor
 *
 * @package Aheadworks\Rma\Ui\DataProvider
 */
class StoreDataProcessor
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Prepare form data
     *
     * @param array $data
     * @param array $expandableFields
     * @return array
     */
    public function prepareFormData($data, $expandableFields = [])
    {
        $storesData = $this->getStoresData();
        foreach ($expandableFields as $expandableField) {
            $fieldData = [];
            if (isset($data[$expandableField])) {
                $fieldData = $data[$expandableField];
            }
            $newFieldData = [];
            foreach ($storesData as $store) {
                $newRecord = $store;
                foreach ($fieldData as $record) {
                    if (isset($record['store_id']) && $record['store_id'] == $store['store_id']) {
                        $newRecord = array_merge($record, $store);
                    }
                }
                $newFieldData[] = $newRecord;
            }
            $data[$expandableField] = $newFieldData;
        }
        return $data;
    }

    /**
     * Retrieve stores data
     *
     * @return array
     */
    private function getStoresData()
    {
        $options = [];
        /** @var Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            $options[] = [
                'store_id' => $store->getId(),
                'store_view_name' => $store->getDataUsingMethod('name')
            ];
        }
        return $options;
    }
}
