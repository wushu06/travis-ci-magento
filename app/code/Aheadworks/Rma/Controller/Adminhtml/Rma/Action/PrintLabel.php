<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma\Action;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Pdf as PrintLabelPdf;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Aheadworks\Rma\Model\Url\ParamEncryptor;
use Aheadworks\Rma\Api\Data\RequestInterface;

/**
 * Class PrintLabel
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma\Action
 */
class PrintLabel extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var PrintLabelPdf
     */
    private $printLabelPdf;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ParamEncryptor
     */
    private $encryptor;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param PrintLabelPdf $printLabelPdf
     * @param FileFactory $fileFactory
     * @param ParamEncryptor $encryptor
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        PrintLabelPdf $printLabelPdf,
        FileFactory $fileFactory,
        ParamEncryptor $encryptor
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
        $this->printLabelPdf = $printLabelPdf;
        $this->fileFactory = $fileFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $rmaRequest = $this->getRmaRequest();
            $this->fileFactory->create(
                'RMA ' . $rmaRequest->getIncrementId() . '.pdf',
                $this->printLabelPdf->getPdf($rmaRequest),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Retrieve RMA request
     *
     * @return RequestInterface
     * @throws NotFoundException
     */
    private function getRmaRequest()
    {
        try {
            $id = $this->encryptor->decrypt('id', $this->getRequest()->getParam('hash'));
            $requestEntity = $this->requestRepository->getByExternalLink($id);
        } catch (NoSuchEntityException $e) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $requestEntity;
    }
}
