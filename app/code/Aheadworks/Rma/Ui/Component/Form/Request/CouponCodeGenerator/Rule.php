<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request\CouponCodeGenerator;

use Magento\Ui\Component\Form\Field;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Aheadworks\Rma\Model\ThirdPartyModule\Manager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Rule
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request\CouponCodeGenerator
 */
class Rule extends Field
{
    /**
     * @var Manager
     */
    private $thirdPartyModuleManager;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ObjectManagerInterface $objectManager
     * @param Manager $thirdPartyModuleManager
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ObjectManagerInterface $objectManager,
        Manager $thirdPartyModuleManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->thirdPartyModuleManager = $thirdPartyModuleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        parent::prepare();
        if ($this->thirdPartyModuleManager->isCCGModuleEnabled()) {
            $config = $this->getData('config');
            /** @var \Aheadworks\Rma\Model\ThirdPartyModule\Aheadworks\CouponCodeGenerator\RuleList $ruleList */
            $ruleList = $this->objectManager->get(
                \Aheadworks\Rma\Model\ThirdPartyModule\Aheadworks\CouponCodeGenerator\RuleList::class
            );
            $requestId = $this->context->getRequestParam('id');
            $config['options'] = $ruleList->getRuleOptionArrayForRequest($requestId);
            $this->setData('config', $config);
        }
    }
}
