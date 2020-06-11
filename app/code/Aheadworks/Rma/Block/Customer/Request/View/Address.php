<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\View;

use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Model\Request\PrintLabel\Layout\Processor\AddressAttributes;

/**
 * Class Address
 *
 * @package Aheadworks\Rma\Block\Customer\Request\View
 */
class Address extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::customer/request/view/address.phtml';

    /**
     * @var RequestRepositoryInterface
     */
    protected $requestRepository;

    /**
     * @var AddressAttributes
     */
    private $addressAttributes;

    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param AddressAttributes $addressAttributes
     * @param array $data
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        AddressAttributes $addressAttributes,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->requestRepository = $requestRepository;
        $this->addressAttributes = $addressAttributes;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout'])
            ? $data['jsLayout']
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        $this->jsLayout = $this->addressAttributes->process($this->jsLayout, $this->getRmaRequest());

        return \Zend_Json::encode($this->jsLayout);
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
}
