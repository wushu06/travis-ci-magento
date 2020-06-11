<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Setup;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterfaceFactory;
use Aheadworks\Rma\Api\Data\CustomFieldOptionInterface;
use Aheadworks\Rma\Api\Data\StatusEmailTemplateInterface;
use Aheadworks\Rma\Api\Data\StatusInterface;
use Aheadworks\Rma\Api\Data\StatusInterfaceFactory;
use Aheadworks\Rma\Api\Data\StoreValueInterface;
use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Aheadworks\Rma\Model\Status\ConfigDefault as StatusConfigDefault;
use Aheadworks\Rma\Model\CustomField\ConfigDefault as CustomFieldConfigDefault;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Setup\Updater\Data\Updater as DataUpdater;

/**
 * Class InstallData
 *
 * @package Aheadworks\Rma\Setup
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var StatusConfigDefault
     */
    private $statusConfigDefault;

    /**
     * @var CustomFieldConfigDefault
     */
    private $customFieldConfigDefault;

    /**
     * @var StatusInterfaceFactory
     */
    private $statusFactory;

    /**
     * @var CustomFieldInterfaceFactory
     */
    private $customFieldFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataUpdater
     */
    private $updater;

    /**
     * @var array|null
     */
    private $statusIds;

    /**
     * @var array|null
     */
    private $websiteIds;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param StatusConfigDefault $statusConfigDefault
     * @param CustomFieldConfigDefault $customFieldConfigDefault
     * @param StatusInterfaceFactory $statusFactory
     * @param CustomFieldInterfaceFactory $customFieldFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StatusRepositoryInterface $statusRepository
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param StoreManagerInterface $storeManager
     * @param DataUpdater $updater
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        StatusConfigDefault $statusConfigDefault,
        CustomFieldConfigDefault $customFieldConfigDefault,
        StatusInterfaceFactory $statusFactory,
        CustomFieldInterfaceFactory $customFieldFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StatusRepositoryInterface $statusRepository,
        CustomFieldRepositoryInterface $customFieldRepository,
        StoreManagerInterface $storeManager,
        DataUpdater $updater
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->statusConfigDefault = $statusConfigDefault;
        $this->customFieldConfigDefault = $customFieldConfigDefault;
        $this->statusFactory = $statusFactory;
        $this->customFieldFactory = $customFieldFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->statusRepository = $statusRepository;
        $this->customFieldRepository = $customFieldRepository;
        $this->storeManager = $storeManager;
        $this->updater = $updater;
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->installStatuses();
        $this->installCustomFields();
        $this->updater->update140($setup);
    }

    /**
     * Install statuses
     *
     * @return $this
     * @throws LocalizedException
     */
    private function installStatuses()
    {
        foreach ($this->statusConfigDefault->get() as $statusData) {
            $statusData = $this->prepareStatusData($statusData);
            $statusObject = $this->statusFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $statusObject,
                $statusData,
                StatusInterface::class
            );

            $this->statusRepository->save($statusObject);
        }
        return $this;
    }

    /**
     * Prepare status data
     *
     * @param array $statusData
     * @return array
     */
    private function prepareStatusData($statusData)
    {
        $frontendLabels = [];
        $adminTemplates = [];
        $customerTemplates = [];
        $threadTemplates = [];
        foreach ($this->storeManager->getStores() as $store) {
            $frontendLabels[] = [
                StoreValueInterface::STORE_ID => $store->getId(),
                StoreValueInterface::VALUE => $statusData['frontend_label']['value']
            ];
            $threadTemplates[] = [
                StoreValueInterface::STORE_ID => $store->getId(),
                StoreValueInterface::VALUE => $statusData['thread_template']['value']
            ];
            foreach ($statusData['email_template'] as $emailTemplate) {
                if (empty($emailTemplate['value'])) {
                    continue;
                }
                $value = [
                    StatusEmailTemplateInterface::STORE_ID    => $store->getId(),
                    StatusEmailTemplateInterface::VALUE       => $emailTemplate['value'],
                    StatusEmailTemplateInterface::CUSTOM_TEXT => $emailTemplate['custom_text']
                ];
                if ($emailTemplate['template_type'] == 'customer') {
                    $customerTemplates[] = $value;
                } else {
                    $adminTemplates[] = $value;
                }
            }
        }
        $statusData[StatusInterface::NAME] = $statusData['frontend_label']['value'];
        $statusData[StatusInterface::FRONTEND_LABELS] = $frontendLabels;
        $statusData[StatusInterface::ADMIN_TEMPLATES] = $adminTemplates;
        $statusData[StatusInterface::CUSTOMER_TEMPLATES] = $customerTemplates;
        $statusData[StatusInterface::THREAD_TEMPLATES] = $threadTemplates;

        return $statusData;
    }

    /**
     * Install custom fields
     *
     * @return $this
     */
    private function installCustomFields()
    {
        foreach ($this->customFieldConfigDefault->get() as $customFieldData) {
            $customFieldData = $this->prepareCustomField($customFieldData);
            $customFieldObject = $this->customFieldFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $customFieldObject,
                $customFieldData,
                CustomFieldInterface::class
            );

            $this->customFieldRepository->save($customFieldObject);
        }
        return $this;
    }

    /**
     * Retrieve statuses ids
     *
     * @return array
     */
    private function getStatusIds()
    {
        if (null == $this->statusIds) {
            $statuses = $this->statusRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $this->statusIds = [];
            /** @var StatusInterface $status */
            foreach ($statuses as $status) {
                $this->statusIds[] = $status->getId();
            }
        }

        return $this->statusIds;
    }

    /**
     * Retrieve website ids
     *
     * @return array
     */
    private function getWebsiteIds()
    {
        if (null == $this->websiteIds) {
            $this->websiteIds = [];
            foreach ($this->storeManager->getWebsites() as $website) {
                $this->websiteIds[] = $website->getId();
            }
        }

        return $this->websiteIds;
    }

    /**
     * Prepare custom field
     *
     * @param array $customFieldData
     * @return array
     */
    private function prepareCustomField($customFieldData)
    {
        $frontendLabels = [];
        $customFieldOptions = [];
        foreach ($this->storeManager->getStores() as $store) {
            $frontendLabels[] = [
                StoreValueInterface::STORE_ID => $store->getId(),
                StoreValueInterface::VALUE => $customFieldData['frontend_label']['value']
            ];
        }
        foreach ($customFieldData['option'] as $option) {
            $storeLabels = [];
            foreach ($this->storeManager->getStores(true) as $store) {
                $storeLabels[] = [
                    StoreValueInterface::STORE_ID => $store->getId(),
                    StoreValueInterface::VALUE    => $option['value']
                ];
            }
            $customFieldOptions[] = [
                CustomFieldOptionInterface::ENABLED      => $option['enabled'],
                CustomFieldOptionInterface::SORT_ORDER   => $option['sort_order'],
                CustomFieldOptionInterface::IS_DEFAULT   => $option['default'],
                CustomFieldOptionInterface::STORE_LABELS => $storeLabels
            ];
        }
        $customFieldData = $this->prepareCustomFieldStatuses($customFieldData);
        $customFieldData[CustomFieldInterface::FRONTEND_LABELS] = $frontendLabels;
        $customFieldData[CustomFieldInterface::OPTIONS] = $customFieldOptions;
        $customFieldData[CustomFieldInterface::WEBSITE_IDS] = $this->getWebsiteIds();

        return $customFieldData;
    }

    /**
     * Prepare custom field statuses
     *
     * @param array $customFieldData
     * @return array
     */
    private function prepareCustomFieldStatuses($customFieldData)
    {
        $statusesRelatedFields = [
            CustomFieldInterface::VISIBLE_FOR_STATUS_IDS,
            CustomFieldInterface::EDITABLE_FOR_STATUS_IDS,
            CustomFieldInterface::EDITABLE_ADMIN_FOR_STATUS_IDS
        ];

        foreach ($statusesRelatedFields as $field) {
            $fieldValue = $customFieldData[$field];
            if (is_array($fieldValue)) {
                if (in_array('none', $fieldValue)) {
                    $customFieldData[$field] = [];
                } elseif (in_array('all', $fieldValue)) {
                    $customFieldData[$field] = $this->getStatusIds();
                }
            }
        }

        return $customFieldData;
    }
}
