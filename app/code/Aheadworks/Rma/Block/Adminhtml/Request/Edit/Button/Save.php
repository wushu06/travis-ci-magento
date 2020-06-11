<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Magento\Config\Model\Config\Source\Enabledisable;

/**
 * Class Save
 *
 * @package Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button
 */
class Save extends ButtonAbstract implements ButtonProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        // @todo refactoring after resolve task M2RMA-68
        $buttons = [];
        $actions = $this->getActionsConfig();
        foreach ($actions as $action) {
            $button = $this->getButton($action);
            if (empty($button)) {
                continue;
            }
            $buttons[] = $button;
        }

        $primaryButton = array_shift($buttons);
        $buttonConfig = [
            'class_name' => Container::SPLIT_BUTTON,
            'options'    => $buttons
        ];

        return array_merge($primaryButton, $buttonConfig);
    }

    /**
     * Retrieve button config
     *
     * @param array $action
     * @return array
     */
    private function getButton($action)
    {
        return [
            'label'          => __($action['label']),
            'class'          => 'save primary',
            'data_attribute' => $action['data_attribute'],
            'sort_order'     => $action['sort_order']
        ];
    }

    /**
     * Retrieve actions config
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getActionsConfig()
    {
        $this->statusList
            ->getSearchCriteriaBuilder()
            ->addFilter(StatusInterface::IS_ACTIVE, Enabledisable::ENABLE_VALUE);
        $statusList = $this->statusList->retrieve();
        $currentStatus = $this->resolveCurrentRequestStatus($statusList);
        $statusConfigsBeforeCurrentStatus = [];
        $statusConfigsAfterCurrentStatus = [];

        $config = [];
        if ($currentStatus) {
            foreach ($statusList as $status) {
                if ($status->getId() == $currentStatus->getId()) {
                    continue;
                }
                $status->getSortOrder() > $currentStatus->getSortOrder()
                    ? $statusConfigsBeforeCurrentStatus[] = $this->prepareActionConfig($status)
                    : $statusConfigsAfterCurrentStatus[] = $this->prepareActionConfig($status);
            }
            $config = array_merge($statusConfigsBeforeCurrentStatus, $statusConfigsAfterCurrentStatus);
        } else {
            foreach ($statusList as $status) {
                $config[] = $this->prepareActionConfig($status);
            }
        }

        return $config;
    }

    /**
     * Prepare data attribute
     *
     * @param array $params
     * @return array
     */
    private function prepareDataAttribute($params)
    {
        $dataAttribute = [
            'mage-init'  => [
                'buttonAdapter' => [
                    'actions' => [
                        [
                            'targetName' => 'aw_rma_request_form.aw_rma_request_form',
                            'actionName' => 'save',
                            'params' => [
                                true,
                                array_merge($params, ['back' => 'edit'])
                            ]
                        ]
                    ]
                ]
            ],
        ];

        return $dataAttribute;
    }

    /**
     * Resolve current request status
     *
     * @param StatusInterface[] $statusList
     * @return StatusInterface|false
     */
    private function resolveCurrentRequestStatus($statusList)
    {
        $request = $this->getRmaRequest();
        if (!$request) {
            return false;
        }
        $status = array_filter(
            $statusList,
            function (StatusInterface $status) use ($request) {
                return $status->getId() == $request->getStatusId();
            }
        );

        return reset($status);
    }

    /**
     * Prepare action config
     *
     * @param StatusInterface $status
     * @return array
     */
    private function prepareActionConfig($status)
    {
        return [
            'action' => strtolower(str_replace(' ', '_', $status->getName())),
            'label' => __('Set to %1', $status->getName()),
            'data_attribute' => $this->prepareDataAttribute(['status_id' => $status->getId()]),
            'sort_order' => $status->getSortOrder()
        ];
    }
}
