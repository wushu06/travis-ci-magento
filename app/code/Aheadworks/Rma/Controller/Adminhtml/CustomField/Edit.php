<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CustomField;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;

/**
 * Class Edit
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\CustomField
 */
class Edit extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::custom_fields';

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Context $context
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        CustomFieldRepositoryInterface $customFieldRepository,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->customFieldRepository = $customFieldRepository;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Edit action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $customFieldId = (int)$this->getRequest()->getParam('id');
        if ($customFieldId) {
            try {
                $customField = $this->customFieldRepository->get($customFieldId);
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('This custom field no longer exists.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage
            ->setActiveMenu('Aheadworks_Rma::custom_fields')
            ->getConfig()->getTitle()->prepend(
                $customFieldId ? __('Edit "%1" custom field', $customField->getName()) : __('New custom field')
            );
        return $resultPage;
    }
}
