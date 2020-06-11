<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block;

use Aheadworks\Rma\Model\Config;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class FooterLink
 *
 * @package Aheadworks\Rma\Block
 */
class FooterLink extends Current
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param DefaultPathInterface $defaultPath
     * @param CustomerSession $customerSession
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        CustomerSession $customerSession,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        $this->customerSession = $customerSession;
        $data = $this->addLink($data);
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Add link
     *
     * @param array $data
     * @return array
     */
    private function addLink($data)
    {
        if (!isset($data['label'])) {
            $data['label'] = __('Create New Return');
        }
        if (!isset($data['path'])) {
            if ($this->customerSession->isLoggedIn()) {
                $data['path'] = 'aw_rma/customer/index';
            } else {
                $data['path'] = $this->config->isAllowGuestsCreateRequest()
                    ? 'aw_rma/guest/index'
                    : 'customer/account/login';
            }
        }

        return $data;
    }
}
