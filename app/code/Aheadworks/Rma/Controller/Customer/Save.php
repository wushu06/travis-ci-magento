<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Customer;

use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Model\Request\PostDataProcessor\Composite as RequestPostDataProcessor;
use Aheadworks\Rma\Model\Request\Resolver\Customer\Session as CustomerSessionResolver;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory as RmaRequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestInterface as RmaRequestInterface;

/**
 * Class CreateRequestStep
 *
 * @package Aheadworks\Rma\Controller\Customer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var RequestManagementInterface
     */
    private $requestManagement;

    /**
     * @var RequestPostDataProcessor
     */
    private $requestPostDataProcessor;

    /**
     * @var RmaRequestInterfaceFactory
     */
    private $requestFactory;

    /**
     * @var CustomerSessionResolver
     */
    private $customerSessionResolver;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param RequestManagementInterface $requestManagement
     * @param RequestPostDataProcessor $requestPostDataProcessor
     * @param RmaRequestInterfaceFactory $requestFactory
     * @param CustomerSessionResolver $customerSessionResolver
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        FormKeyValidator $formKeyValidator,
        DataObjectHelper $dataObjectHelper,
        RequestManagementInterface $requestManagement,
        RequestPostDataProcessor $requestPostDataProcessor,
        RmaRequestInterfaceFactory $requestFactory,
        CustomerSessionResolver $customerSessionResolver
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestManagement = $requestManagement;
        $this->requestPostDataProcessor = $requestPostDataProcessor;
        $this->requestFactory = $requestFactory;
        $this->customerSessionResolver = $customerSessionResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->customerSession->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                $this->validate();
                $data = $this->requestPostDataProcessor->prepareEntityData($data);
                $requestEntity = $this->performSave($data);
                $this->messageManager->addSuccessMessage(
                    __(
                        'Return has been successfully created.'
                        . ' You will receive an email with the request details and instructions shortly.'
                    )
                );
                $this->customerSession->unsAwRmaRequestOrderId();
                return $resultRedirect->setPath('*/*/view', ['id' => $requestEntity->getId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while creating the return.'));
            }
            return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate form
     *
     * @throws LocalizedException
     */
    private function validate()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
        }
    }

    /**
     * Perform save
     *
     * @param array $data
     * @return RmaRequestInterface
     */
    private function performSave($data)
    {
        $requestObject = $this->requestFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestObject,
            $data,
            RmaRequestInterface::class
        );
        $requestObject
            ->setCustomerId($this->customerSessionResolver->getCustomerId($requestObject))
            ->setCustomerEmail($this->customerSessionResolver->getCustomerEmail($requestObject))
            ->setCustomerName($this->customerSessionResolver->getCustomerFullName($requestObject));

        return $this->requestManagement->createRequest($requestObject, false);
    }
}
