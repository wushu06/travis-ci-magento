<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Backend\App\Action as BackendAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Class GenerateCoupon
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class GenerateCoupon extends BackendAction
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var \Aheadworks\Coupongenerator\Api\CouponManagerInterface
     */
    private $couponManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @param Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param JsonFactory $resultJsonFactory
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        CustomerRepositoryInterface $customerRepository,
        JsonFactory $resultJsonFactory,
        FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->customerRepository = $customerRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->formKeyValidator = $formKeyValidator;
        $objectManager = $context->getObjectManager();
        $this->couponManager = $objectManager->get(\Aheadworks\Coupongenerator\Api\CouponManagerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setRefererOrBaseUrl();
        }

        try {
            $data = $this->getRequest()->getPostValue();
            if ($this->isFormKeyValid() && $data) {
                $recipientEmail = isset($data['recipient_email']) ? $data['recipient_email'] : false;
                $ruleId = isset($data['rule_id']) ? $data['rule_id'] : false;
                $isSendEmail = isset($data['send_email_to_recipient']) ? (bool)$data['send_email_to_recipient'] : false;

                if (!$ruleId) {
                    throw new LocalizedException(__('Rule is not selected'));
                }
                if (!\Zend_Validate::is($recipientEmail, 'EmailAddress')) {
                    throw new LocalizedException(__('Email address is not correct: %1', $recipientEmail));
                }

                try {
                    $customer = $this->customerRepository->get($recipientEmail);
                    /** @var \Aheadworks\Coupongenerator\Api\Data\CouponGenerationResultInterface $result */
                    $result = $this->couponManager->generateForCustomer($ruleId, $customer->getId(), $isSendEmail);
                } catch (NoSuchEntityException $e) {
                    /** @var \Aheadworks\Coupongenerator\Api\Data\CouponGenerationResultInterface $result */
                    $result = $this->couponManager->generateForEmail($ruleId, $recipientEmail, $isSendEmail);
                }

                if ($result->getCoupon()) {
                    $result = [
                        'error' => false,
                        'couponCode' => $result->getCoupon()->getCode()
                    ];
                    return $this->sendResult($result);
                }
            }
        } catch (\Exception $e) {
            $result = [
                'error'     => true,
                'message'   => __($e->getMessage())
            ];
            return $this->sendResult($result);
        }

        $result = [
            'error'     => true,
            'message'   => __('Something went wrong while generating the coupon code')
        ];
        return $this->sendResult($result);
    }

    /**
     * Is valid request
     *
     * @return bool
     */
    private function isFormKeyValid()
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return false;
        }
        return true;
    }

    /**
     * Send result
     *
     * @param array $result
     * @return Json
     */
    private function sendResult($result)
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($result);
        return $resultJson;
    }
}
