<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Request\Update\Validator as UpdateValidator;
use Aheadworks\Rma\Model\Request\Update\ValidatorFactory as UpdateValidatorFactory;
use Aheadworks\Rma\Model\Request\Update\Merger;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\Rma\Model\Source\Request\Status as RequestStatus;
use Aheadworks\Rma\Model\Request\PrintLabel\Resolver as PrintLabelResolver;
use Magento\Framework\Math\Random;

/**
 * Class Modifier
 *
 * @package Aheadworks\Rma\Model\Request\Update
 */
class Modifier
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PrintLabelResolver
     */
    private $printLabelResolver;

    /**
     * @var UpdateValidatorFactory
     */
    private $updateValidatorFactory;

    /**
     * @var Merger
     */
    private $merger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param PrintLabelResolver $printLabelResolver
     * @param UpdateValidatorFactory $updateValidatorFactory
     * @param Merger $merger
     * @param Config $config
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        OrderRepositoryInterface $orderRepository,
        PrintLabelResolver $printLabelResolver,
        UpdateValidatorFactory $updateValidatorFactory,
        Merger $merger,
        Config $config
    ) {
        $this->requestRepository = $requestRepository;
        $this->orderRepository = $orderRepository;
        $this->printLabelResolver = $printLabelResolver;
        $this->updateValidatorFactory = $updateValidatorFactory;
        $this->merger = $merger;
        $this->config = $config;
    }

    /**
     * Update request data
     *
     * @param RequestInterface $newRequest
     * @param bool $causedByAdmin
     * @return RequestInterface
     * @throws LocalizedException
     */
    public function modifyRequestBeforeUpdate($newRequest, $causedByAdmin)
    {
        $request = $this->requestRepository->get($newRequest->getId());
        /** @var UpdateValidator $validator */
        $validator = $this->updateValidatorFactory->create();
        $validator
            ->setIsCausedByAdmin($causedByAdmin)
            ->setRequest($request);
        if ($validator->isValid($newRequest)) {
            $this->merger->mergeRequest($request, $newRequest);
            if ($request->getThreadMessage()) {
                $request->setLastReplyBy($this->getLastReplyOwnerType($causedByAdmin));
            }
        } else {
            $messages = $validator->getMessages();
            throw new LocalizedException(
                __('RMA request cannot be changed. %1', array_shift($messages))
            );
        }

        return $request;
    }

    /**
     * Prepare request before create
     *
     * @param RequestInterface $request
     * @param bool $causedByAdmin
     * @param int $storeId
     * @return RequestInterface
     * @throws LocalizedException
     */
    public function modifyRequestBeforeCreate($request, $causedByAdmin, $storeId)
    {
        $status = $this->config->isAllowAutoApprove($storeId)
            ? RequestStatus::APPROVED
            : RequestStatus::PENDING_APPROVAL;
        try {
            $order = $this->orderRepository->get($request->getOrderId());
        } catch (\Exception $e) {
            throw new LocalizedException(__('Incorrect order id.'));
        }
        $request
            ->setStoreId($order->getStoreId())
            ->setPaymentMethod($order->getPayment()->getMethod())
            ->setStatusId($status)
            ->setExternalLink($this->generateExternalLink())
            ->setLastReplyBy($request->getThreadMessage() ? $this->getLastReplyOwnerType($causedByAdmin) : 0)
            ->setPrintLabel($this->printLabelResolver->resolve($request));

        return $request;
    }

    /**
     * Retrieve last reply owner type
     *
     * @param bool $causedByAdmin
     * @return int
     */
    private function getLastReplyOwnerType($causedByAdmin)
    {
        return $causedByAdmin ? Owner::ADMIN : Owner::CUSTOMER;
    }

    /**
     * Generate external link for request
     *
     * @return string
     */
    private function generateExternalLink()
    {
        return strtoupper(uniqid(dechex(Random::getRandomNumber())));
    }
}
