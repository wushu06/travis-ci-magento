<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Guest\Request;

use Aheadworks\Rma\Model\Renderer\CmsBlock;
use Aheadworks\Rma\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Url as CustomerUrl;

/**
 * Class NewRequest
 *
 * @package Aheadworks\Rma\Block\Guest\Request
 */
class NewRequest extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::guest/request/newrequest.phtml';

    /**
     * @var CmsBlock
     */
    private $cmsBlockRenderer;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param CmsBlock $cmsBlockRenderer
     * @param Config $config
     * @param CustomerUrl $customerUrl
     * @param array $data
     */
    public function __construct(
        Context $context,
        CmsBlock $cmsBlockRenderer,
        Config $config,
        CustomerUrl $customerUrl,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cmsBlockRenderer = $cmsBlockRenderer;
        $this->config = $config;
        $this->customerUrl = $customerUrl;
    }

    /**
     * Retrieve guest RMA page block
     *
     * @return string
     */
    public function getGuestPageBlock()
    {
        return $this->cmsBlockRenderer->render($this->config->getGuestPageBlock());
    }

    /**
     * Retrieve login post url
     *
     * @return string
     */
    public function getLoginPostUrl()
    {
        return $this->customerUrl->getLoginPostUrl();
    }

    /**
     * Retrieve forgot password url
     *
     * @return string
     */
    public function getForgotPasswordUrl()
    {
        return $this->customerUrl->getForgotPasswordUrl();
    }

    /**
     * Retrieve next post url
     *
     * @return string
     */
    public function getNextPostUrl()
    {
        return $this->getUrl('*/*/createRequest');
    }
}
