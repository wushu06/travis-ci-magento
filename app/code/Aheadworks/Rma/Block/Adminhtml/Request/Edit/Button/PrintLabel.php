<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\Request\Resolver\Status as StatusResolver;
use Aheadworks\Rma\Model\Status\Request\StatusList;

/**
 * Class PrintLabel
 *
 * @package Aheadworks\Rma\Block\Adminhtml\Request\Edit\Button
 */
class PrintLabel extends ButtonAbstract implements ButtonProviderInterface
{
    /**
     * @param Context $context
     * @param RequestRepositoryInterface $requestRepository
     * @param StatusResolver $statusResolver
     * @param StatusList $statusList
     */
    public function __construct(
        Context $context,
        RequestRepositoryInterface $requestRepository,
        StatusResolver $statusResolver,
        StatusList $statusList
    ) {
        parent::__construct($context, $requestRepository, $statusResolver, $statusList);
    }

    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        $button = [];
        if ($this->isAvailableAction('print_label')) {
            $requestId = $this->getRmaRequest()->getId();
            $button = [
                'label' => __('Print Label'),
                'on_click' => sprintf(
                    "location.href = '%s';",
                    $this->getUrl('aw_rma_admin/rma/printLabel', ['id' => $requestId])
                ),
                'sort_order' => 25
            ];
        }

        return $button;
    }
}
