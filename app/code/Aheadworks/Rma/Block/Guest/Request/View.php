<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Guest\Request;

use Aheadworks\Rma\Block\Customer\Request\View as CustomerRequestView;

/**
 * Class View
 *
 * @package Aheadworks\Rma\Block\Guest\Request
 */
class View extends CustomerRequestView
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
