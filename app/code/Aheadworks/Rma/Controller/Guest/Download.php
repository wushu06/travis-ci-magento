<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Guest;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Controller\GuestAction;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\ThreadMessage\Attachment\FileDownloader;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Download
 *
 * @package Aheadworks\Rma\Controller\Guest
 */
class Download extends GuestAction
{
    /**
     * @var ThreadMessageManagementInterface
     */
    private $threadMessageManagement;

    /**
     * @var FileDownloader
     */
    private $fileDownloader;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param Config $config
     * @param ThreadMessageManagementInterface $threadMessageManagement
     * @param FileDownloader $fileDownloader
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        Config $config,
        ThreadMessageManagementInterface $threadMessageManagement,
        FileDownloader $fileDownloader
    ) {
        parent::__construct($context, $requestRepository, $config);
        $this->threadMessageManagement = $threadMessageManagement;
        $this->fileDownloader = $fileDownloader;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            // @todo refactoring use ParamEncryptor class
            $attachment = $this->threadMessageManagement->getAttachment(
                $this->getRequest()->getParam('file'),
                $this->getRequest()->getParam('message'),
                $this->getRmaRequest()->getId()
            );
            return $this->fileDownloader->download($attachment);
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
