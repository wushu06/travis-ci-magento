<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Model\ThreadMessage\Attachment\FileDownloader;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;

/**
 * Class Download
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class Download extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

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
     * @param ThreadMessageManagementInterface $threadMessageManagement
     * @param FileDownloader $fileDownloader
     */
    public function __construct(
        Context $context,
        ThreadMessageManagementInterface $threadMessageManagement,
        FileDownloader $fileDownloader
    ) {
        parent::__construct($context);
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
                $this->getRequest()->getParam('id')
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
