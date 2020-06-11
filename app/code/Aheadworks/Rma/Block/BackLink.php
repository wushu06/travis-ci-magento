<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class BackLink
 *
 * @method BackLink setRefererUrl(string $refererUrl)
 * @method string getRefererUrl()
 * @package Aheadworks\Rma\Block
 */
class BackLink extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::back_link.phtml';

    /**
     * Get back Url
     *
     * @return string
     */
    public function getBackUrl()
    {
        // The RefererUrl must be set in appropriate controller
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }

        return $this->getUrl();
    }
}
