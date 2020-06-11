<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Model\CustomField\Renderer\Backend\Mapper;
use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Container;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\ActionDelete;
use Magento\Config\Model\Config\Source\Enabledisable;
use Aheadworks\Rma\Model\CustomField\Resolver\Request as CustomFieldRequestResolver;

/**
 * Class CustomFields
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request
 */
class CustomFields extends Container
{
    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var CustomFieldRequestResolver
     */
    private $requestResolver;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param RequestRepositoryInterface $requestRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param CustomFieldRequestResolver $requestResolver
     * @param Mapper $mapper
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CustomFieldRepositoryInterface $customFieldRepository,
        RequestRepositoryInterface $requestRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        CustomFieldRequestResolver $requestResolver,
        Mapper $mapper,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->uiComponentFactory = $uiComponentFactory;
        $this->customFieldRepository = $customFieldRepository;
        $this->requestRepository = $requestRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->mapper = $mapper;
        $this->requestResolver = $requestResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $status = $this->getRequestStatus();
        $refersTo = $this->getData('config/refersTo') ? : Refers::REQUEST;
        $addActionDelete = $this->getData('config/addActionDelete');
        foreach ($this->getCustomFields($refersTo, $status) as $customField) {
            $config = $this->mapper->map($customField, $status);
            $this->createComponent(
                $this->getCustomFieldName($customField),
                Field::NAME,
                $config
            );
        }
        if ($addActionDelete) {
            $this->createComponent(
                'action_delete',
                ActionDelete::NAME,
                $this->getActionDeleteConfig()
            );
        }

        parent::prepare();
    }

    /**
     * Retrieve custom field name
     *
     * @param CustomFieldInterface $customField
     * @return string
     */
    private function getCustomFieldName($customField)
    {
        return 'custom_fields' . '.' . $customField->getId();
    }

    /**
     * Retrieve custom fields
     *
     * @param string $refersTo
     * @param int $status
     * @return CustomFieldInterface[]
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    private function getCustomFields($refersTo, $status)
    {
        $this->searchCriteriaBuilder
            ->addFilter(CustomFieldInterface::REFERS, $refersTo)
            ->addFilter(CustomFieldInterface::OPTIONS, 'enabled');
        if ($status == EditAt::NEW_REQUEST_PAGE) {
            $this->searchCriteriaBuilder->addFilter(CustomFieldInterface::IS_ACTIVE, Enabledisable::ENABLE_VALUE);
        } else {
            $requestStoreId = $this->getRequest()->getStoreId();
            $websiteId = $this->storeManager->getStore($requestStoreId)->getWebsiteId();
            $this->searchCriteriaBuilder->addFilter(CustomFieldInterface::WEBSITE_IDS, $websiteId);
            $this->searchCriteriaBuilder->addFilter(
                'main_table.id',
                $this->requestResolver->getCustomFieldIdsByRequest($this->getRequest(), $refersTo),
                'in'
            );
        }

        return $this->customFieldRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }

    /**
     * Create component
     *
     * @param string $fieldName
     * @param string $type
     * @param array $config
     * @return $this
     * @throws LocalizedException
     */
    private function createComponent($fieldName, $type, $config)
    {
        $component = $this->uiComponentFactory->create(
            $fieldName,
            $type,
            ['context' => $this->getContext()]
        );
        $component->setData('config', $config);
        $component->prepare();
        $this->addComponent($fieldName, $component);

        return $this;
    }

    /**
     * Retrieve request status
     *
     * @return int
     * @throws NoSuchEntityException
     */
    private function getRequestStatus()
    {
        $request = $this->getRequest();

        return !empty($request) ? $request->getStatusId() : EditAt::NEW_REQUEST_PAGE;
    }

    /**
     * Retrieve request
     *
     * @return RequestInterface|null
     * @throws NoSuchEntityException
     */
    private function getRequest()
    {
        $id = $this->getContext()->getRequestParam(
            $this->getContext()->getDataProvider()->getRequestFieldName()
        );
        return !empty($id) ? $this->requestRepository->get($id) : null;
    }

    /**
     * Retrieve action delete config
     *
     * @return array
     */
    private function getActionDeleteConfig()
    {
        return [
            'componentType' => 'actionDelete',
            'component' => 'Aheadworks_Rma/js/ui/dynamic-rows/action-delete',
            'dataType' => 'text',
            'label' => __('Actions'),
            'template' => 'Magento_Backend/dynamic-rows/cells/action-delete',
            'imports' => [
                'visible' => '${ $.provider }:data.newRequest'
            ],
            'additionalClasses' => [
                'control-table-options-cell' => true
            ],
            'columnsHeaderClasses' => [
                'control-table-options-th' => true
            ]
        ];
    }
}
