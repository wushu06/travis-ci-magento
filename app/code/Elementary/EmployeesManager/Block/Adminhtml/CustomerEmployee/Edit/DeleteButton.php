<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Block\Adminhtml\CustomerEmployee\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton implements ButtonProviderInterface {
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * [__construct description]
     * @param Context  $context  [description]
     * @param Registry $registry [description]
     */
    public function __construct(
        Context $context,
        Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * Delete records confirm popup
     *
     * @return array
     */
    public function getButtonData() {
        if ($this->registry->registry('entity_id') > 0) {
            $data = [
                'label' => __('Delete'),
                'class' => 'delete',
                'id' => 'customeremployee-edit-delete-button',
                'data_attribute' => [
                    'url' => $this->getDeleteUrl(),
                ],
                'on_click' =>
                    'deleteConfirm(\'' . __("Are you sure you want to do this?") . '\', \'' . $this->getDeleteUrl() . '\')',
                'sort_order' => 20,
            ];
            return $data;
        }
    }

    /**
     * Delete records controller URL
     *
     * @return string
     */
    public function getDeleteUrl() {
        return $this->urlBuilder->getUrl('*/*/delete', ['entity_id' => $this->registry->registry('entity_id')]);
    }
}