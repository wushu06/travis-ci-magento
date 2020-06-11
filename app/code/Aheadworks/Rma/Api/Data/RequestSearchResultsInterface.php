<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for request search results
 * @api
 */
interface RequestSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get request list
     *
     * @return \Aheadworks\Rma\Api\Data\RequestInterface[]
     */
    public function getItems();

    /**
     * Set request list
     *
     * @param \Aheadworks\Rma\Api\Data\RequestInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
