<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Aheadworks\Rma\Block\Html\Pager;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Model\Request\Resolver\Order as OrderResolver;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;

/**
 * Class ListRequest
 *
 * @package Aheadworks\Rma\Block\Customer\Request
 */
class ListRequest extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/list.phtml';

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var OrderResolver
     */
    private $orderResolver;

    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @var \Aheadworks\Rma\Api\Data\RequestSearchResultsInterface[]|null
     */
    private $customerRequests;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param StatusRepositoryInterface $statusRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param CustomerSession $customerSession
     * @param OrderResolver $orderResolver
     * @param OrderItemResolver $orderItemResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        StatusRepositoryInterface $statusRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        CustomerSession $customerSession,
        OrderResolver $orderResolver,
        OrderItemResolver $orderItemResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->requestRepository = $requestRepository;
        $this->statusRepository = $statusRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->customerSession = $customerSession;
        $this->orderResolver = $orderResolver;
        $this->orderItemResolver = $orderItemResolver;
    }

    /**
     * Retrieve customer requests
     *
     * @return \Aheadworks\Rma\Api\Data\RequestSearchResultsInterface|bool
     */
    public function getCustomerRequests()
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!$customerId) {
            return false;
        }

        if (null === $this->customerRequests) {
            $sortOrder = $this->sortOrderBuilder
                ->setField(RequestInterface::CREATED_AT)
                ->setDirection(SortOrder::SORT_DESC)
                ->create();
            $this->searchCriteriaBuilder
                ->addFilter(RequestInterface::CUSTOMER_ID, $customerId)
                ->addSortOrder($sortOrder);

            $this->customerRequests = $this->requestRepository
                ->getList($this->searchCriteriaBuilder->create());
        }

        return $this->customerRequests;
    }

    /**
     * Retrieve order increment id by request
     *
     * @param RequestInterface $rmaRequest
     * @return string
     */
    public function getOrderIncrementId($rmaRequest)
    {
        return $this->orderResolver->getIncrementId($rmaRequest->getOrderId());
    }

    /**
     * Retrieve order item name by id
     *
     * @param int $itemId
     * @return string
     */
    public function getOrderItemName($itemId)
    {
        return $this->orderItemResolver->getName($itemId);
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
     * Retrieve pager
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getCustomerRequests() ? $this->getChildHtml('pager') : '';
    }

    /**
     * Retrieve request view url
     *
     * @param int $requestId
     * @return string
     */
    public function getRequestViewUrl($requestId)
    {
        return $this->getUrl('*/*/view', ['id' => $requestId]);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        /** @var Pager $pager */
        $pager = $this->getLayout()->createBlock(
            Pager::class,
            'aw_rma_customer_requests.pager'
        );

        $this->searchCriteriaBuilder->setCurrentPage($pager->getCurrentPage());
        $this->searchCriteriaBuilder->setPageSize($pager->getLimit());

        if ($this->getCustomerRequests()) {
            $pager->setSearchResults($this->getCustomerRequests());
            $this->setChild('pager', $pager);
        }

        return $this;
    }
}
