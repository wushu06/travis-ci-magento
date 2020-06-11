<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Api\Data;

/**
 * Thread message attachment interface
 * @api
 */
interface ThreadMessageAttachmentInterface
{
    /**#@+
     * Constants defined for keys of the data array.
     * Identical to the name of the getter in snake case
     */
    const MESSAGE_ID = 'message_id';
    const NAME = 'name';
    const FILE_NAME = 'file_name';
    /**#@-*/

    /**
     * Get message id
     *
     * @return int
     */
    public function getMessageId();

    /**
     * Set message id
     *
     * @param int $messageId
     * @return $this
     */
    public function setMessageId($messageId);

    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get file name
     *
     * @return string
     */
    public function getFileName();

    /**
     * Set file name
     *
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName);
}
