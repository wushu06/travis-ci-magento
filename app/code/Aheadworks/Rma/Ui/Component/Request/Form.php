<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Request;

use Magento\Ui\Component\Form as MagentoForm;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Aheadworks\Rma\Ui\Component\Request\Form\ButtonProvider;

/**
 * Class Form
 *
 * @package Aheadworks\Rma\Ui\Component\Request
 */
class Form extends MagentoForm
{
    /**
     * @var ButtonProvider
     */
    private $buttonProvider;

    /**
     * @param ContextInterface $context
     * @param FilterBuilder $filterBuilder
     * @param ButtonProvider $buttonProvider
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        FilterBuilder $filterBuilder,
        ButtonProvider $buttonProvider,
        array $components = [],
        array $data = []
    ) {
        parent::__construct(
            $context,
            $filterBuilder,
            $components,
            $data
        );
        $this->buttonProvider = $buttonProvider;
    }

    /**
     * Prepare component configuration
     *
     * @return void
     * @throws \Exception
     */
    public function prepare()
    {
        $buttons = $this->getData('buttons');
        $additionalButtons = $this->buttonProvider->getAdditionalButtons();
        foreach ($additionalButtons as $additionalButton) {
            $buttons = array_merge($buttons, $additionalButton);
        }

        $this->setData('buttons', $buttons);
        parent::prepare();
    }
}
