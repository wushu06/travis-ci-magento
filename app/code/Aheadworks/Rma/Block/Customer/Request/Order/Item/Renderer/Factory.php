<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Sales\Model\Order\Item;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Factory
 *
 * @package Aheadworks\Rma\Block\Customer\Request\Order\Item\Renderer
 */
class Factory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomFieldInterface[]
     */
    private $itemCustomField;

    /**
     * @var array
     */
    private $itemRendererMap = [
        'bundle' => Bundle::class,
        'configurable' => Configurable::class,
        'default' => DefaultRenderer::class
    ];

    /**
     * @var array
     */
    private $itemRendererTemplates = [
        'default_' . EditAt::NEW_REQUEST_PAGE =>
            'Aheadworks_Rma::customer/request/newrequest/step/createrequest/items/renderer/default.phtml',
        'bundle_' . EditAt::NEW_REQUEST_PAGE =>
            'Aheadworks_Rma::customer/request/newrequest/step/createrequest/items/renderer/bundle.phtml',
        'configurable_' . EditAt::NEW_REQUEST_PAGE =>
            'Aheadworks_Rma::customer/request/newrequest/step/createrequest/items/renderer/configurable.phtml',
        'default_select_order' =>
            'Aheadworks_Rma::customer/request/newrequest/step/selectorder/items/renderer/default.phtml',
        'bundle_select_order' =>
            'Aheadworks_Rma::customer/request/newrequest/step/selectorder/items/renderer/bundle.phtml',
        'configurable_select_order' =>
            'Aheadworks_Rma::customer/request/newrequest/step/selectorder/items/renderer/configurable.phtml'
    ];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomFieldRepositoryInterface $customFieldRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customFieldRepository = $customFieldRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * Return newly created instance of a order item
     *
     * @param Item $orderItem
     * @param int $requestStatus
     * @param bool $renderDefault
     * @return BlockInterface
     */
    public function create($orderItem, $requestStatus, $renderDefault = false)
    {
        $type = $renderDefault ? 'default' : $orderItem->getProductType();
        $instance = $this->objectManager->create(
            $this->getItemRenderer($type),
            [
                'data' => [
                    'template' => $this->getItemRendererTemplate($type, $requestStatus),
                    'item' => $orderItem,
                    'request_status' => $requestStatus,
                    'custom_fields' => $this->getItemCustomFields($requestStatus)
                ]
            ]
        );

        return $instance;
    }

    /**
     * Retrieve item renderer
     *
     * @param int $type
     * @return string
     */
    private function getItemRenderer($type)
    {
        return isset($this->itemRendererMap[$type])
            ? $this->itemRendererMap[$type]
            : $this->itemRendererMap['default'];
    }

    /**
     * Retrieve item renderer template
     *
     * @param string $type
     * @param int $requestStatus
     * @return mixed
     */
    private function getItemRendererTemplate($type, $requestStatus)
    {
        $type = $type . '_' . $requestStatus;
        return isset($this->itemRendererTemplates[$type])
            ? $this->itemRendererTemplates[$type]
            : $this->itemRendererTemplates['default_' . $requestStatus];
    }

    /**
     * Retrieve item custom fields
     *
     * @param int $requestStatus
     * @return CustomFieldInterface[]
     * @throws LocalizedException
     */
    public function getItemCustomFields($requestStatus)
    {
        if (null === $this->itemCustomField) {
            $this->searchCriteriaBuilder
                ->addFilter(CustomFieldInterface::REFERS, Refers::ITEM)
                ->addFilter('editable_or_visible_for_status', $requestStatus)
                ->addFilter(CustomFieldInterface::OPTIONS, 'enabled')
                ->addFilter(CustomFieldInterface::IS_ACTIVE, Enabledisable::ENABLE_VALUE)
                ->addFilter(CustomFieldInterface::WEBSITE_IDS, $this->storeManager->getWebsite()->getId());

            $this->itemCustomField = $this->customFieldRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();
        }

        return $this->itemCustomField;
    }
}
