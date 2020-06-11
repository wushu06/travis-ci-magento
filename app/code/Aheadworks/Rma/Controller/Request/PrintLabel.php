<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Request;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Pdf as PrintLabelPdf;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Aheadworks\Rma\Model\Url\ParamEncryptor;

/**
 * Class PrintLabel
 *
 * @package Aheadworks\Rma\Controller\Request
 */
class PrintLabel extends Action
{
    /**
     * @var StatusResolver
     */
    private $statusResolver;

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
     * @param StatusResolver $statusResolver
     * @param RequestRepositoryInterface $requestRepository
     * @param PrintLabelPdf $printLabelPdf
     * @param FileFactory $fileFactory
     * @param ParamEncryptor $encryptor
     */
    public function __construct(
        Context $context,
        StatusResolver $statusResolver,
        RequestRepositoryInterface $requestRepository,
        PrintLabelPdf $printLabelPdf,
        FileFactory $fileFactory,
        ParamEncryptor $encryptor
    ) {
        parent::__construct($context);
        $this->statusResolver = $statusResolver;
        $this->requestRepository = $requestRepository;
        $this->printLabelPdf = $printLabelPdf;
        $this->fileFactory = $fileFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->statusResolver->isAvailableActionForStatus('print_label', $this->getRmaRequest(), false)) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }

    /**
     * Retrieve RMA request
     *
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
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

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $storeId = $this->encryptor->decrypt('store_id', $this->getRequest()->getParam('hash'));
            $rmaRequest = $this->getRmaRequest();
            $this->fileFactory->create(
                'RMA ' . $rmaRequest->getIncrementId() . '.pdf',
                $this->printLabelPdf->getPdf($rmaRequest, $storeId),
                DirectoryList::VAR_DIR,
                'application/pdf'
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('*/*/');
    }
}
