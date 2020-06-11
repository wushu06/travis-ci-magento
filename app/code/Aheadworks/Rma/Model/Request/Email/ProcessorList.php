<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Email;

use Aheadworks\Rma\Model\Request\Email\Processor\AbstractProcessor;
use Aheadworks\Rma\Model\Request\Email\Processor\AdminChangedStatus;
use Aheadworks\Rma\Model\Request\Email\Processor\CustomerChangedStatus;
use Aheadworks\Rma\Model\Request\Email\Processor\AdminReply;
use Aheadworks\Rma\Model\Request\Email\Processor\CustomerReply;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class ProcessorList
 *
 * @package Aheadworks\Rma\Model\Request\Email
 */
class ProcessorList
{
    /**#@+
     * Constants defined for keys of the data array
     */
    const ADMIN_CHANGED_STATUS_PROCESSOR = 'admin_changed_status_processor';
    const CUSTOMER_CHANGED_STATUS_PROCESSOR = 'customer_changed_status_processor';
    const ADMIN_REPLY_PROCESSOR = 'admin_reply_processor';
    const CUSTOMER_REPLY_PROCESSOR = 'customer_reply_processor';
    /**#@-*/

    /**
     * @var string[]
     */
    private $processorTypes = [
        self::ADMIN_CHANGED_STATUS_PROCESSOR => AdminChangedStatus::class,
        self::CUSTOMER_CHANGED_STATUS_PROCESSOR => CustomerChangedStatus::class,
        self::ADMIN_REPLY_PROCESSOR => AdminReply::class,
        self::CUSTOMER_REPLY_PROCESSOR => CustomerReply::class,
    ];

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var AbstractProcessor[]
     */
    private $processorList = [];

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * Retrieve processor by type
     *
     * @param string $type
     * @return AbstractProcessor
     */
    public function getProcessor($type)
    {
        if (!isset($this->processorList[$type])) {
            $this->processorList[$type] = $this->objectManager->create($this->processorTypes[$type]);
        }

        return $this->processorList[$type];
    }
}
