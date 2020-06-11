<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request;

use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Block\Customer\Request\View\Items;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;
use Aheadworks\Rma\Model\Source\Request\Status;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Block\CustomField\Input\Renderer\Factory as CustomFieldRendererFactory;
use Aheadworks\Rma\Model\Request\Resolver\Order as OrderResolver;

/**
 * Class View
 *
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class View extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/view.phtml';

    /**
     * @var RequestRepositoryInterface
     */
    protected $requestRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var OrderResolver
     */
    private $orderResolver;

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var StatusResolver
     */
    private $statusResolver;

    /**
     * @var CustomFieldRendererFactory
     */
    private $customFieldRendererFactory;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param OrderResolver $orderResolver
     * @param StatusRepositoryInterface $statusRepository
     * @param StatusResolver $statusResolver
     * @param CustomFieldRendererFactory $customFieldRendererFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomFieldRepositoryInterface $customFieldRepository,
        OrderResolver $orderResolver,
        StatusRepositoryInterface $statusRepository,
        StatusResolver $statusResolver,
        CustomFieldRendererFactory $customFieldRendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->requestRepository = $requestRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customFieldRepository = $customFieldRepository;
        $this->orderResolver = $orderResolver;
        $this->statusRepository = $statusRepository;
        $this->statusResolver = $statusResolver;
        $this->customFieldRendererFactory = $customFieldRendererFactory;
    }

    /**
     * Retrieve RMA request
     *
     * @return \Aheadworks\Rma\Api\Data\RequestInterface
     */
    public function getRmaRequest()
    {
        $requestId = $this->getRequest()->getParam('id');
        return $this->requestRepository->get($requestId);
    }

    /**
     * Retrieve request identity value
     *
     * @return int|string
     */
    public function getRequestIdentityValue()
    {
        return $this->getRmaRequest()->getId();
    }

    /**
     * Retrieve update request url
     *
     * @return string
     */
    public function getUpdateRequestUrl()
    {
        return $this->getUrl('*/*/updateRequest');
    }

    /**
     * Retrieve storefront status label by request
     *
     * @param RequestInterface $rmaRequest
     * @return string
     */
    public function getStorefrontStatusLabel($rmaRequest)
    {
        return $this->statusRepository->get($rmaRequest->getStatusId())->getStorefrontLabel();
    }

    /**
     * Retrieve request custom fields input html
     *
     * @param RequestCustomFieldValueInterface $requestCustomField
     * @return string
     */
    public function getRequestCustomFieldsInputHtml($requestCustomField)
    {
        $customField = $this->customFieldRepository->get($requestCustomField->getFieldId());
        $fieldName = 'custom_fields.' . $customField->getId();
        $value = $requestCustomField->getValue();
        $renderer = $this->customFieldRendererFactory
            ->create($customField, $this->getRmaRequest()->getStatusId(), $fieldName, $value);

        return $renderer->toHtml();
    }

    /**
     * Retrieve order increment id by request
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->orderResolver->getIncrementId($this->getRmaRequest()->getOrderId());
    }

    /**
     * Retrieve order date by request
     *
     * @return string
     */
    public function getOrderCreatedAt()
    {
        return $this->orderResolver->getCreatedAt($this->getRmaRequest()->getOrderId());
    }

    /**
     * Retrieve order view url
     *
     * @param int $orderId
     * @return string
     */
    public function getOrderViewUrl($orderId)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $orderId]);
    }

    /**
     * Check if can reply
     *
     * @return bool
     */
    public function canReply()
    {
        return $this->statusResolver->isAvailableActionForStatus('update', $this->getRmaRequest(), false);
    }

    /**
     * Retrieve items html
     *
     * @return string
     */
    public function getItemsHtml()
    {
        /** @var Items $block */
        $block = $this->getLayout()->createBlock(Items::class);
        if (!$block) {
            return '';
        }
        $block
            ->setOrderItems($this->getRmaRequest()->getOrderItems())
            ->setStatusId($this->getRmaRequest()->getStatusId());

        return $block->toHtml();
    }

    /**
     * Retrieve thread message input html
     *
     * @return string
     */
    public function getThreadMessageHtml()
    {
        $block = $this->getLayout()->getBlock('aw_rma.thread.message');

        return $block->toHtml();
    }

    /**
     * Retrieve thread list html
     *
     * @return string
     */
    public function getThreadListHtml()
    {
        /** @var ThreadList $block */
        $block = $this->getLayout()->getBlock('aw_rma.thread.list');
        $block
            ->setRequestId($this->getRmaRequest()->getId())
            ->setRequestIdentityValue($this->getRequestIdentityValue());

        return $block->toHtml();
    }

    /**
     * Check if status approved
     *
     * @return bool
     */
    public function isStatusApproved()
    {
        return $this->getRmaRequest()->getStatusId() == Status::APPROVED;
    }
}
