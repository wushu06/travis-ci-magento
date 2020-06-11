<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Guest;

use Magento\Framework\Exception\NotFoundException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\App\Action\Action;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Request\Order as RequestOrder;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Class CreateRequest
 *
 * @package Aheadworks\Rma\Controller\Guest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CreateRequest extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RequestOrder
     */
    private $requestOrder;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var OrderInterface
     */
    private $order;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Config $config
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestOrder $requestOrder
     * @param FormKeyValidator $formKeyValidator
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestOrder $requestOrder,
        FormKeyValidator $formKeyValidator
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->requestOrder = $requestOrder;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->config->isAllowGuestsCreateRequest()) {
            throw new NotFoundException(__('Page not found.'));
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
                $this->validate($data);
                $order = $this->getOrder($data);
                $this->getRequest()
                    ->setParams(['order_id' => $order->getEntityId()]);

                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->resultPageFactory->create();
                $resultPage
                    ->getConfig()
                    ->getTitle()
                    ->set(__('New Return for Order #%1', $order->getIncrementId()));
                return $resultPage;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while loading the page.'));
            }
        }

        return $resultRedirect->setPath('aw_rma/guest');
    }

    /**
     * Validate
     *
     * @param array $data
     * @throws LocalizedException
     */
    private function validate($data)
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            throw new LocalizedException(__('Invalid Form Key. Please refresh the page.'));
        }
        if (!isset($data['order_increment_id'])) {
            throw new LocalizedException(__('Order Number isn\'t specified.'));
        }
        if (!isset($data['email'])) {
            throw new LocalizedException(__('Email isn\'t specified.'));
        }
        $order = $this->getOrder($data);
        if (empty($order) || !$order->getEntityId()) {
            throw new LocalizedException(__('Couldn\'t load order by given Order Number.'));
        }
        if (strcasecmp($order->getCustomerEmail(), $data['email'])) {
            throw new LocalizedException(__('Order Number and Email didn\'t match each other.'));
        }
        if ($order->getCustomerId()) {
            throw new LocalizedException(
                __('This order has been placed by registered customer. '
                    . 'Please, authorize and request RMA via customer account.')
            );
        }
        if (!$this->requestOrder->isAllowedForOrder($order)) {
            throw new LocalizedException(
                __(
                    'Specified order has been created more than %1 days ago or has not been completed.',
                    $this->config->getReturnPeriod($order->getStoreId())
                )
            );
        }
    }

    /**
     * Retrieve order
     *
     * @param array $data
     * @return OrderInterface
     */
    private function getOrder($data)
    {
        if (null === $this->order) {
            $orderIncrementId = trim($data['order_increment_id']);
            $orderIncrementId = preg_replace('/^#/', '', $orderIncrementId);
            $this->searchCriteriaBuilder->addFilter(OrderInterface::INCREMENT_ID, $orderIncrementId);

            $orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $this->order = array_shift($orders);
        }

        return $this->order;
    }
}
