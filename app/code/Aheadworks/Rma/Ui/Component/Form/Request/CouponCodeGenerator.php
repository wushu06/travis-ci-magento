<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request;

use Magento\Ui\Component\Container;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Rma\Model\ThirdPartyModule\Manager;

/**
 * Class CouponCodeGenerator
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request
 */
class CouponCodeGenerator extends Container
{
    /**
     * @var Manager
     */
    private $thirdPartyModuleManager;

    /**
     * @param ContextInterface $context
     * @param Manager $thirdPartyModuleManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        Manager $thirdPartyModuleManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getData('config');
        if (!$this->thirdPartyModuleManager->isCCGModuleEnabled()) {
            $config['componentDisabled'] = true;
        }
        $this->setData('config', $config);
    }
}
