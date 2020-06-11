<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ResourceModel\Status;

use Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Model\ResourceModel\AbstractCollection;
use Aheadworks\Rma\Model\Source\Status\TemplateType;
use Aheadworks\Rma\Model\Status;
use Aheadworks\Rma\Model\ResourceModel\Status as ResourceStatus;

/**
 * Class Collection
 *
 * @package Aheadworks\Rma\Model\ResourceModel\Status
 */
class Collection extends AbstractCollection
{
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(Status::class, ResourceStatus::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        $this->attachRelationTable(
            $this->getTable('aw_rma_request_status_frontend_label'),
            'id',
            'status_id',
            ['store_id', 'value'],
            'frontend_labels'
        );
        $this->attachRelationTable(
            $this->getTable('aw_rma_request_status_email_template'),
            'id',
            'status_id',
            ['store_id', 'value', 'custom_text'],
            'customer_templates',
            [['field' => 'template_type', 'condition' => '=', 'value' => TemplateType::CUSTOMER]]
        );
        $this->attachRelationTable(
            $this->getTable('aw_rma_request_status_email_template'),
            'id',
            'status_id',
            ['store_id', 'value', 'custom_text'],
            'admin_templates',
            [['field' => 'template_type', 'condition' => '=', 'value' => TemplateType::ADMIN]]
        );
        $this->attachRelationTable(
            $this->getTable('aw_rma_request_status_thread_template'),
            'id',
            'status_id',
            ['store_id', 'value'],
            'thread_templates'
        );

        /** @var \Magento\Framework\DataObject $item */
        foreach ($this as $item) {
            $item->setData(
                StatusInterface::STOREFRONT_LABEL,
                $this->getStorefrontValue($item->getData(StatusInterface::FRONTEND_LABELS), true)
            );
            $item->setData(
                StatusInterface::STOREFRONT_CUSTOMER_TEMPLATE,
                $this->getStorefrontValue($item->getData(StatusInterface::CUSTOMER_TEMPLATES), false)
            );
            $item->setData(
                StatusInterface::STOREFRONT_ADMIN_TEMPLATE,
                $this->getStorefrontValue($item->getData(StatusInterface::ADMIN_TEMPLATES), false)
            );
            $item->setData(
                StatusInterface::STOREFRONT_THREAD_TEMPLATE,
                $this->getStorefrontValue($item->getData(StatusInterface::THREAD_TEMPLATES), true)
            );
        }

        return parent::_afterLoad();
    }
}
