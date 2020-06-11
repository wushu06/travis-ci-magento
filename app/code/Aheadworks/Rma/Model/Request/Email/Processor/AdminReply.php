<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email\Processor;

/**
 * Class AdminReply
 *
 * @package Aheadworks\Rma\Model\Request\Email\Processor
 */
class AdminReply extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function prepareRequestTemplateVariables()
    {
        $requestVariables = [
            'url' => $this->getCustomerRmaUrl()
        ];

        return $requestVariables;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplateId()
    {
        return $this->config->getEmailTemplateReplyByAdmin($this->getStoreId());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientName()
    {
        return $this->getRequest()->getCustomerName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientEmail()
    {
        return $this->getRequest()->getCustomerEmail();
    }
}
