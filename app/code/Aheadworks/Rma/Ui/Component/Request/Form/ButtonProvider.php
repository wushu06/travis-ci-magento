<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Request\Form;

use Aheadworks\Rma\Model\Source\CustomField\Option\Action as ActionSource;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface as ActionInterface;
use Magento\Framework\App\RequestInterface as HttpRequest;
use Aheadworks\Rma\Model\CustomField\Option\Action\AvailabilityChecker;

/**
 * Class ButtonProvider
 *
 * @package Aheadworks\Rma\Ui\Component\Request\Form
 */
class ButtonProvider
{
    /**
     * @var ActionSource
     */
    private $actionSource;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var AvailabilityChecker
     */
    private $availabilityChecker;

    /**
     * @param ActionSource $actionSource
     * @param HttpRequest $request
     * @param AvailabilityChecker $availabilityChecker
     */
    public function __construct(
        ActionSource $actionSource,
        HttpRequest $request,
        AvailabilityChecker $availabilityChecker
    ) {
        $this->actionSource = $actionSource;
        $this->request = $request;
        $this->availabilityChecker = $availabilityChecker;
    }

    /**
     * Get additional buttons for each action
     *
     * @return array
     * @throws \Exception
     */
    public function getAdditionalButtons()
    {
        $actions = $this->actionSource->getActions();
        $requestId = $this->request->getParam('id');
        $buttons = [];

        /** @var ActionInterface $action */
        foreach ($actions as $index => $action) {
            if (!$this->availabilityChecker->isAvailableAction($action->getOperation(), $requestId, true)) {
                continue;
            }
            $buttons[] = [
                'custom_button' . $action->getId() => [
                    'label' => __($action->getTitle()),
                    'sort_order' => 30 + $index,
                    'data_attribute' => $this->prepareDataAttribute($action->getOperation()),
                ]
            ];
        }

        return $buttons;
    }

    /**
     * Prepare data attribute
     *
     * @param string $urlPath
     * @return array
     */
    private function prepareDataAttribute($urlPath)
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
                                ['redirect_path' => $urlPath]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        return $dataAttribute;
    }
}
