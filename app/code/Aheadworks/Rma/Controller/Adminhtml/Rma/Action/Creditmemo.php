<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma\Action;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Aheadworks\Rma\Model\Request\Order\Creditmemo as RequestCreditMemo;

/**
 * Class Creditmemo
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma\Action
 */
class Creditmemo extends Action
{
    /**
     * @inheritdoc
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var ForwardFactory
     */
    private $resultForwardFactory;

    /**
     * @var RequestCreditMemo
     */
    private $creditMemo;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param ForwardFactory $resultForwardFactory
     * @param RequestCreditMemo $creditMemo
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        ForwardFactory $resultForwardFactory,
        RequestCreditMemo $creditMemo
    ) {
        parent::__construct($context);
        $this->requestRepository = $requestRepository;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->creditMemo = $creditMemo;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $requestId = $this->getRequest()->getParam('request_id');
        $request = $this->requestRepository->get($requestId);

        $requestParams = $this->getRequest()->getParams();
        $requestParams['creditmemo'] = [
            'items' => $this->creditMemo->prepareItems($requestId)
        ];
        $requestParams['order_id'] = $request->getOrderId();
        $requestParams['request_id'] = $request->getId();
        $this->getRequest()->setParams($requestParams);

        /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        $resultForward
            ->setModule('sales')
            ->setController('order_creditmemo');
        return $resultForward->forward('new');
    }
}
