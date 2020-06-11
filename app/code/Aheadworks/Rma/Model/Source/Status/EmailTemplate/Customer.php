<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Source\Status\EmailTemplate;

use Magento\Framework\Option\ArrayInterface;
use Magento\Config\Model\Config\Source\Email\Template as EmailTemplate;

/**
 * Class Customer
 *
 * @package Aheadworks\Rma\Model\Source\Status\EmailTemplate
 */
class Customer implements ArrayInterface
{
    /**
     * @var EmailTemplate
     */
    private $emailTemplate;

    /**
     * @param EmailTemplate $emailTemplate
     */
    public function __construct(
        EmailTemplate $emailTemplate
    ) {
        $this->emailTemplate = $emailTemplate;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->emailTemplate
            ->setPath('aw_rma_email_template_to_customer_status_changed')
            ->toOptionArray();
    }
}
