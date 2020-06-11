<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\View;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;
use Aheadworks\Rma\Model\Source\Request\Status;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Actions
 *
 * @method bool|null getOnlyShowUpdateActions()
 * @package Aheadworks\Rma\Block\Customer\Request\View
 */
class Actions extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/view/actions.phtml';

    /**
     * @var RequestRepositoryInterface
     */
    protected $requestRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StatusResolver
     */
    private $statusResolver;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param Config $config
     * @param StatusResolver $statusResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        Config $config,
        StatusResolver $statusResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->requestRepository = $requestRepository;
        $this->config = $config;
        $this->statusResolver = $statusResolver;
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
     * Check if can canceled
     *
     * @return bool
     */
    public function canCancel()
    {
        return $this->statusResolver->isAvailableActionForStatus('cancel', $this->getRmaRequest(), false)
            && !$this->getOnlyShowUpdateActions();
    }

    /**
     * Check if can update request
     *
     * @return bool
     */
    public function canUpdateRequest()
    {
        return $this->statusResolver->isAvailableActionForStatus('update', $this->getRmaRequest(), false);
    }

    /**
     * Check if can print label
     *
     * @return bool
     */
    public function canPrintLabel()
    {
        return $this->statusResolver->isAvailableActionForStatus('print_label', $this->getRmaRequest(), false)
            && !$this->getOnlyShowUpdateActions();
    }

    /**
     * Check if can confirm shipping
     *
     * @return bool
     */
    public function canConfirmShipping()
    {
        return $this->statusResolver->isAvailableActionForStatus('confirm_shipping', $this->getRmaRequest(), false);
    }

    /**
     * Retrieve "Confirm Shipping" alert text
     *
     * @return string
     */
    public function getConfirmShippingPopupText()
    {
        return $this->config->getConfirmShippingPopupText();
    }

    /**
     * Retrieve print label url
     *
     * @return string
     */
    public function getPrintLabelUrl()
    {
        return $this->getUrl('*/*/printLabel', ['id' => $this->getRequestIdentityValue()]);
    }

    /**
     * Retrieve confirm shipping status value
     *
     * @return string
     */
    public function getConfirmShippingStatusValue()
    {
        return Status::PACKAGE_SENT;
    }

    /**
     * Retrieve cancel status value
     *
     * @return string
     */
    public function getCancelStatusValue()
    {
        return Status::CANCELED;
    }

    /**
     * Retrieve update request form selector
     *
     * @return string
     */
    public function getUpdateRequestFormSelector()
    {
        return '[data-role=aw-rma-update-request-form]';
    }

    /**
     * Retrieve update request action selector
     *
     * @return string
     */
    public function getUpdateRequestActionSelector()
    {
        return '[data-role=aw-rma-update-request-status]';
    }
}
