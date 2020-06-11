<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api;

/**
 * Thread message management interface
 * @api
 */
interface ThreadMessageManagementInterface
{
    /**
     * Add new thread message
     *
     * @param \Aheadworks\Rma\Api\Data\ThreadMessageInterface $threadMessage
     * @param bool $notify
     * @param int|null $storeId
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addThreadMessage(
        \Aheadworks\Rma\Api\Data\ThreadMessageInterface $threadMessage,
        $notify = false,
        $storeId = null
    );

    /**
     * Retrieve attachment
     *
     * @param string $fileName
     * @param int $messageId
     * @param int $requestId
     * @return \Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAttachment($fileName, $messageId, $requestId);
}
