<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Setup\Updater\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Model\Status\ConfigDefault as StatusConfigDefault;
use Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault as ActionConfigDefault;
use Aheadworks\Rma\Api\CustomFieldOptionActionRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterfaceFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Aheadworks\Rma\Api\Data\OrderInterface;
use Aheadworks\Rma\Api\Data\CartInterface;
use Aheadworks\Rma\Api\Data\CreditmemoInterface;
use Aheadworks\Rma\Api\Data\CustomFieldOptionActionInterface as ActionInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Updater
 *
 * @package Aheadworks\Rma\Setup\Updater\Data
 */
class Updater
{
    /**
     * @var StatusConfigDefault
     */
    private $statusConfigDefault;

    /**
     * @var SalesSetupFactory
     */
    private $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var ActionConfigDefault
     */
    private $actionConfigDefault;

    /**
     * @var CustomFieldOptionActionRepositoryInterface
     */
    private $actionRepository;

    /**
     * @var CustomFieldOptionActionInterfaceFactory
     */
    private $actionFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @param StatusConfigDefault $statusConfigDefault
     * @param ActionConfigDefault $actionConfigDefault
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param CustomFieldOptionActionRepositoryInterface $actionRepository
     * @param CustomFieldOptionActionInterfaceFactory $actionFactory
     * @param DataObjectHelper $dataObjectHelper
     */
    public function __construct(
        StatusConfigDefault $statusConfigDefault,
        ActionConfigDefault $actionConfigDefault,
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        CustomFieldOptionActionRepositoryInterface $actionRepository,
        CustomFieldOptionActionInterfaceFactory $actionFactory,
        DataObjectHelper $dataObjectHelper
    ) {
        $this->statusConfigDefault = $statusConfigDefault;
        $this->actionConfigDefault = $actionConfigDefault;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->actionRepository = $actionRepository;
        $this->actionFactory = $actionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
    }

    /**
     * Update to 1.4.0 version
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     * @throws LocalizedException
     */
    public function update140(ModuleDataSetupInterface $setup)
    {
        $this->updateSortOrderForRequestDefaultStatuses($setup);
        $this->addAwRmaRequestIdAttribute($setup);
        $this->installActions();
        return $this;
    }

    /**
     * Update sort order for request default statuses
     *
     * @param ModuleDataSetupInterface $setup
     */
    private function updateSortOrderForRequestDefaultStatuses(ModuleDataSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        foreach ($this->statusConfigDefault->get() as $statusData) {
            $connection->update(
                $setup->getTable('aw_rma_request_status'),
                [
                    StatusInterface::SORT_ORDER => $statusData[StatusInterface::SORT_ORDER]
                ],
                [
                    $connection->quoteInto(StatusInterface::ID . ' = ?', $statusData[StatusInterface::ID])
                ]
            );
        }
    }

    /**
     * Install sales attribute
     *
     * @param ModuleDataSetupInterface $setup
     * @return $this
     */
    private function addAwRmaRequestIdAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var SalesSetup $salesSetup */
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);
        $salesSetup->addAttribute(
            'order',
            OrderInterface::AW_RMA_REQUEST_ID,
            ['type' => Table::TYPE_INTEGER]
        );
        $salesSetup->addAttribute(
            'creditmemo',
            CreditmemoInterface::AW_RMA_REQUEST_ID,
            ['type' => Table::TYPE_INTEGER]
        );

        /** @var QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $quoteSetup->addAttribute(
            'quote',
            CartInterface::AW_RMA_REQUEST_ID,
            ['type' => Table::TYPE_INTEGER]
        );

        return $this;
    }

    /**
     * Add actions data to table with actions
     *
     * @throws LocalizedException
     */
    private function installActions()
    {
        $actionsData = $this->actionConfigDefault->get();
        foreach ($actionsData as $actionData) {
            $action = $this->actionFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $action,
                $actionData,
                ActionInterface::class
            );
            $this->actionRepository->save($action);
        }
    }
}
