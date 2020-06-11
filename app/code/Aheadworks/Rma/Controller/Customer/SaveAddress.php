<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Customer;

use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Controller\CustomerAction;
use Aheadworks\Rma\Model\Request\PostDataProcessor\Composite as RequestPostDataProcessor;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Aheadworks\Rma\Api\Data\RequestInterfaceFactory as RmaRequestInterfaceFactory;
use Aheadworks\Rma\Api\Data\RequestInterface as RmaRequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class SaveAddress
 *
 * @package Aheadworks\Rma\Controller\Customer
 */
class SaveAddress extends CustomerAction
{
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
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param CustomerSession $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param DataObjectHelper $dataObjectHelper
     * @param RequestManagementInterface $requestManagement
     * @param RequestPostDataProcessor $requestPostDataProcessor
     * @param RmaRequestInterfaceFactory $requestFactory
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        CustomerSession $customerSession,
        FormKeyValidator $formKeyValidator,
        DataObjectHelper $dataObjectHelper,
        RequestManagementInterface $requestManagement,
        RequestPostDataProcessor $requestPostDataProcessor,
        RmaRequestInterfaceFactory $requestFactory
    ) {
        parent::__construct($context, $requestRepository, $customerSession);
        $this->formKeyValidator = $formKeyValidator;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->requestManagement = $requestManagement;
        $this->requestPostDataProcessor = $requestPostDataProcessor;
        $this->requestFactory = $requestFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $result = ['error' => true, 'message' => __('Invalid response data.')];
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $this->validate();
                $data = $this->requestPostDataProcessor->prepareEntityData($data);
                $this->performSave($data);
                $result = ['error' => false, 'message' => __('Address has been successfully updated.')];
            } catch (\Exception $e) {
                $result = ['error' => true, 'message' => __($e->getMessage())];
            }
        }
        return $resultJson->setData($result);
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
     * Perform update
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

        return $this->requestManagement->updateRequest($requestObject, false);
    }
}
