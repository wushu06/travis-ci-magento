<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Status;

use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\StatusInterfaceFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey;
use Aheadworks\Rma\Block\Adminhtml\Status\Edit\Preview as BlockPreview;
use Magento\Framework\View\LayoutFactory;
use Aheadworks\Rma\Model\Status\PostDataProcessor\PreviewEmail as PreviewEmailPostDataProcessor;
use Aheadworks\Rma\Model\StorefrontValueResolver;

/**
 * Class Preview
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Status
 */
class Preview extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::statuses';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var FormKey
     */
    private $formKey;

    /**
     * @var StatusInterfaceFactory
     */
    private $statusFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var PreviewEmailPostDataProcessor
     */
    private $previewEmailPostDataProcessor;

    /**
     * StorefrontValueResolver
     */
    private $storefrontValueResolver;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param FormKey $formKey
     * @param StatusInterfaceFactory $statusFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param PreviewEmailPostDataProcessor $previewEmailPostDataProcessor
     * @param StorefrontValueResolver $storefrontValueResolver
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        FormKey $formKey,
        StatusInterfaceFactory $statusFactory,
        DataObjectHelper $dataObjectHelper,
        PreviewEmailPostDataProcessor $previewEmailPostDataProcessor,
        StorefrontValueResolver $storefrontValueResolver
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKey = $formKey;
        $this->layoutFactory = $layoutFactory;
        $this->statusFactory = $statusFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->previewEmailPostDataProcessor = $previewEmailPostDataProcessor;
        $this->storefrontValueResolver = $storefrontValueResolver;
    }

    /**
     * Preview action
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $result = ['error' => true, 'message' => __('Invalid response data.')];

        $previewData = $this->getRequest()->getParam('request_data');
        if (!$this->isValidRequestData($previewData)) {
            return $resultJson->setData($result);
        }

        try {
            $previewData = $this->previewEmailPostDataProcessor->prepareEntityData($previewData);
            $storeId = $previewData['store_id'];
            /** @var BlockPreview $previewBlock */
            $previewBlock = $this->layoutFactory->create()->createBlock(BlockPreview::class);
            $previewHtml = $previewBlock
                ->setStoreId($storeId)
                ->setStatus($this->prepareStatus($previewData, $storeId))
                ->setToAdmin($previewData['to_admin'])
                ->toHtml();
            $result = ['error' => false, 'content' => $previewHtml];
        } catch (\Exception $e) {
            $result = ['error' => true, 'message' => __($e->getMessage())];
        }

        return $resultJson->setData($result);
    }

    /**
     * Is valid request data
     *
     * @param array $previewData
     * @return bool
     */
    private function isValidRequestData($previewData)
    {
        return $this->getRequest()->isAjax() && isset($previewData['form_key'])
            && $previewData['form_key'] == $this->formKey->getFormKey();
    }

    /**
     * Prepare status data
     *
     * @param array $previewData
     * @param int $storeId
     * @return StatusInterface
     */
    private function prepareStatus($previewData, $storeId)
    {
        $statusObject = $this->statusFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $statusObject,
            $previewData,
            StatusInterface::class
        );
        $statusObject
            ->setStorefrontAdminTemplate(
                $this->storefrontValueResolver->getStorefrontValueEmailTemplate(
                    $statusObject->getAdminTemplates(),
                    $storeId
                )
            )->setStorefrontCustomerTemplate(
                $this->storefrontValueResolver->getStorefrontValueEmailTemplate(
                    $statusObject->getCustomerTemplates(),
                    $storeId
                )
            )->setStorefrontThreadTemplate(
                $this->storefrontValueResolver->getStorefrontValue(
                    $statusObject->getThreadTemplates(),
                    $storeId
                )
            );

        return $statusObject;
    }
}
