<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for canned response search results
 * @api
 */
interface CannedResponseSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get canned response list
     *
     * @return \Aheadworks\Rma\Api\Data\CannedResponseInterface[]
     */
    public function getItems();

    /**
     * Set canned response list
     *
     * @param \Aheadworks\Rma\Api\Data\CannedResponseInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
