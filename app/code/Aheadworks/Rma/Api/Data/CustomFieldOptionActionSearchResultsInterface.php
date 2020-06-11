<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for custom field option action search results
 * @api
 */
interface CustomFieldOptionActionSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get custom field option action list
     *
     * @return \Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface[]
     */
    public function getItems();

    /**
     * Set custom field option action list
     *
     * @param \Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
