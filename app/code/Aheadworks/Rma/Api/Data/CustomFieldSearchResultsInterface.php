<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for custom field search results
 * @api
 */
interface CustomFieldSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get custom field list
     *
     * @return \Aheadworks\Rma\Api\Data\CustomFieldInterface[]
     */
    public function getItems();

    /**
     * Set custom field list
     *
     * @param \Aheadworks\Rma\Api\Data\CustomFieldInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
