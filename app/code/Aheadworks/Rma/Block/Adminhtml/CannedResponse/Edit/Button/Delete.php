<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Adminhtml\CannedResponse\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;

/**
 * Class Delete
 * @package Aheadworks\Rma\Block\Adminhtml\CannedResponse\Edit\Button
 */
class Delete implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        $cannedResponseId = $this->context->getRequest()->getParam('id');
        if ($cannedResponseId) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl($cannedResponseId) . '\')',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    /**
     * Generate url by canned response ID
     *
     * @param $cannedResponseId
     * @return mixed
     */
    public function getDeleteUrl($cannedResponseId)
    {
        return $this->context->getUrlBuilder()->getUrl('*/*/delete', ['id' => $cannedResponseId]);
    }
}
