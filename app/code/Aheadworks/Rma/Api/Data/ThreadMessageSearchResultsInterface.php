<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for thread message search results
 * @api
 */
interface ThreadMessageSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get thread message list
     *
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageInterface[]
     */
    public function getItems();

    /**
     * Set thread message list
     *
     * @param \Aheadworks\Rma\Api\Data\ThreadMessageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
