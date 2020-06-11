<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PostDataProcessor;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class ThreadMessage
 *
 * @package Aheadworks\Rma\Model\Request\PostDataProcessor
 */
class ThreadMessage implements ProcessorInterface
{

    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(
        BooleanUtils $booleanUtils
    ) {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * {@inheritdoc}
     */
    public function process($data)
    {
        if ($this->isThreadMessageEmpty($data) && $this->isThreadMessageAttachmentEmpty($data)) {
            $data[RequestInterface::THREAD_MESSAGE] = null;
        }
        if (isset($data[RequestInterface::THREAD_MESSAGE][ThreadMessageInterface::IS_INTERNAL])) {
            $data[RequestInterface::THREAD_MESSAGE][ThreadMessageInterface::IS_INTERNAL] =
                $this->booleanUtils->toBoolean(
                    $data[RequestInterface::THREAD_MESSAGE][ThreadMessageInterface::IS_INTERNAL]
                );
        }

        return $data;
    }

    /**
     * Check if thread message empty
     *
     * @param array $data
     * @return bool
     */
    private function isThreadMessageEmpty($data)
    {
        return isset($data[RequestInterface::THREAD_MESSAGE][ThreadMessageInterface::TEXT])
            && empty($data[RequestInterface::THREAD_MESSAGE][ThreadMessageInterface::TEXT]);
    }

    /**
     * Check if thread message attachment empty
     *
     * @param array $data
     * @return bool
     */
    private function isThreadMessageAttachmentEmpty($data)
    {
        return !isset($data[RequestInterface::THREAD_MESSAGE][ThreadMessageInterface::ATTACHMENTS])
            || empty($data[RequestInterface::THREAD_MESSAGE][ThreadMessageInterface::ATTACHMENTS]);
    }
}
