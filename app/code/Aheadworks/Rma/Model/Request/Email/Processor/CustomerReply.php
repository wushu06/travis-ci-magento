<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email\Processor;

use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Aheadworks\Rma\Model\Email\EmailMetadataInterfaceFactory;
use Magento\Backend\Model\Url as BackendUrl;

/**
 * Class CustomerReply
 *
 * @package Aheadworks\Rma\Model\Request\Email\Processor
 */
class CustomerReply extends AbstractProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function prepareRequestTemplateVariables()
    {
        $requestVariables = [
            'admin_url' => $this->getAdminRmaUrl()
        ];

        return $requestVariables;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTemplateId()
    {
        return $this->config->getEmailTemplateReplyByCustomer($this->getStoreId());
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientName()
    {
        return $this->getSenderName();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRecipientEmail()
    {
        return $this->getSenderEmail();
    }
}
