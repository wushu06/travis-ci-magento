<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for status search results
 * @api
 */
interface StatusSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get status list
     *
     * @return \Aheadworks\Rma\Api\Data\StatusInterface[]
     */
    public function getItems();

    /**
     * Set status list
     *
     * @param \Aheadworks\Rma\Api\Data\StatusInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
