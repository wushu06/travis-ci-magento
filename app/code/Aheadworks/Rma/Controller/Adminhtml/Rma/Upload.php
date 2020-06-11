<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Model\ThreadMessage\Attachment\FileUploader;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Aheadworks\Rma\Controller\Customer\Upload as CustomerUpload;
use Aheadworks\Rma\Model\Config;

/**
 * Class Upload
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class Upload extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

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
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $result = $this->fileUploader
                ->setAllowedExtensions($this->config->getAllowFileExtensions())
                ->saveToTmpFolder(CustomerUpload::FILE_ID);
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }
        return $resultJson->setData($result);
    }
}
