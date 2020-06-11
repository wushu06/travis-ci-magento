<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveAndContinue
 *
 * @package Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button
 */
class SaveAndContinue extends ButtonAbstract implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $button = [];
        if ($this->isAvailableAction('update')) {
            $button = [
                'label' => __('Save and Continue Edit'),
                'class' => 'save-and-continue',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'aw_rma_request_form.aw_rma_request_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        false
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 40
            ];
        }

        return $button;
    }

    /**
     * Check is available action
     *
     * @param string $action
     * @return bool
     */
    protected function isAvailableAction($action)
    {
        if (null === $this->getRmaRequest()) {
            return $action == 'update';
        }

        return parent::isAvailableAction($action);
    }
}
