<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Guest\Request\View;

use Aheadworks\Rma\Block\Customer\Request\View\Actions as CustomerRequestActions;

/**
 * Class Actions
 *
 * @package Aheadworks\Rma\Block\Guest\Request\View
 */
class Actions extends CustomerRequestActions
{
    /**
     * {@inheritdoc}
     */
    public function getRmaRequest()
    {
        $requestId = $this->getRequest()->getParam('id');
        return $this->requestRepository->getByExternalLink($requestId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestIdentityValue()
    {
        return $this->getRmaRequest()->getExternalLink();
    }
}
