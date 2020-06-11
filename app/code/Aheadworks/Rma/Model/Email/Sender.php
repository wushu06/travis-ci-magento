<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Email;

use Magento\Framework\Mail\Template\TransportBuilder;

/**
 * Class Sender
 *
 * @package Aheadworks\Rma\Model\Email
 */
class Sender
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        TransportBuilder $transportBuilder
    ) {
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Send email message
     *
     * @param EmailMetadataInterface $emailMetadata
     * @return void
     */
    public function send($emailMetadata)
    {
        $this->transportBuilder
            ->setTemplateIdentifier($emailMetadata->getTemplateId())
            ->setTemplateOptions($emailMetadata->getTemplateOptions())
            ->setTemplateVars($emailMetadata->getTemplateVariables())
            ->setFrom(['name' => $emailMetadata->getSenderName(), 'email' => $emailMetadata->getSenderEmail()])
            ->addTo($emailMetadata->getRecipientEmail(), $emailMetadata->getRecipientName())
            ->getTransport()
            ->sendMessage();
    }
}
