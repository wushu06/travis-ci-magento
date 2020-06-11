<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ThreadMessage;

use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Magento\Framework\Api\AbstractSimpleObject;

/**
 * Class Attachment
 *
 * @package Aheadworks\Rma\Model\ThreadMessage
 */
class Attachment extends AbstractSimpleObject implements ThreadMessageAttachmentInterface
{
    /**
     * {@inheritdoc}
     */
    public function getMessageId()
    {
        return $this->_get(self::MESSAGE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageId($messageId)
    {
        return $this->setData(self::MESSAGE_ID, $messageId);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getFileName()
    {
        return $this->_get(self::FILE_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setFileName($fileName)
    {
        return $this->setData(self::FILE_NAME, $fileName);
    }
}
