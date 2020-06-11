<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CustomField;

/**
 * Class PostDataProcessor
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\CustomField
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
        if (empty($data['editable_admin_for_status_ids'])) {
            $data['editable_admin_for_status_ids'] = [];
        }
        if (empty($data['visible_for_status_ids'])) {
            $data['visible_for_status_ids'] = [];
        }
        if (empty($data['editable_for_status_ids'])) {
            $data['editable_for_status_ids'] = [];
        }
        $data = $this->prepareOptions($data);

        return $data;
    }

    /**
     * Prepare options
     *
     * @param array $data
     * @return array
     */
    private function prepareOptions($data)
    {
        $options = isset($data['options']) ? $data['options'] : [];
        foreach ($options as $key => $option) {
            if (isset($option['delete']) && $option['delete']) {
                unset($data['options'][$key]);
            }
            if (isset($option['store_labels']) && !empty($option['store_labels'])) {
                $data['options'][$key]['store_labels'] = $this->prepareOptionLabels($option['store_labels']);
            }
        }

        return $data;
    }

    /**
     * Prepare option labels
     *
     * @param array $storeLabels
     * @return array
     */
    private function prepareOptionLabels($storeLabels)
    {
        foreach ($storeLabels as $storeId => &$storeLabel) {
            $storeLabel['store_id'] = $storeId;
        }

        return $storeLabels;
    }
}
