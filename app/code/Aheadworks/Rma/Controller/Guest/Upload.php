<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Guest;

use Aheadworks\Rma\Model\Config;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\Json;
use Aheadworks\Rma\Model\ThreadMessage\Attachment\FileUploader;
use Magento\Framework\Exception\NotFoundException;
use Aheadworks\Rma\Controller\Customer\Upload as CustomerUpload;

/**
 * Class Upload
 *
 * @package Aheadworks\Rma\Controller\Guest
 */
class Upload extends Action
{
    /**
     * @var FileUploader
     */
    private $fileUploader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param FileUploader $fileUploader
     * @param Config $config
     */
    public function __construct(
        Context $context,
        FileUploader $fileUploader,
        Config $config
    ) {
        parent::__construct($context);
        $this->fileUploader = $fileUploader;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->config->isAllowGuestsCreateRequest() || !$this->config->isAllowCustomerAttachFiles()) {
            throw new NotFoundException(__('Page not found.'));
        }

        return parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $result = $this->fileUploader
                ->setAllowedExtensions($this->config->getAllowFileExtensions())
                ->saveToTmpFolder(CustomerUpload::FILE_ID);
        } catch (\Exception $exception) {
            $result = [
                'error' => $exception->getMessage(),
                'errorcode' => $exception->getCode()
            ];
        }
        return $resultJson->setData($result);
    }
}
