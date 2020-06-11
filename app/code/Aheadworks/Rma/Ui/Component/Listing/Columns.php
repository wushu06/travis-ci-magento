<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Listing;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Model\CustomField\Renderer\Backend\Grid\Mapper;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Ui\Component\Listing\Columns as UiColumns;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Class Columns
 *
 * @package Aheadworks\Rma\Ui\Component\Listing
 */
class Columns extends UiColumns
{
    /**
     * @var UiComponentFactory
     */
    private $componentFactory;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $componentFactory
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Mapper $mapper
     * @param RequestInterface $request
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $componentFactory,
        CustomFieldRepositoryInterface $customFieldRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Mapper $mapper,
        RequestInterface $request,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->componentFactory = $componentFactory;
        $this->customFieldRepository = $customFieldRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->mapper = $mapper;
        $this->request = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare()
    {
        $customFields = $this->getCustomFields();
        foreach ($customFields as $customField) {
            $config = $this->mapper->map($customField);
            $this->createComponent(
                $this->getCustomFieldName($customField->getId()),
                'column',
                $config
            );
        }
        parent::prepare();

        $config = $this->getData('config');
        foreach ($config['fieldSwitcher'] as $rule) {
            if ($this->request->getParam('page') == $rule['page']) {
                foreach ($this->getChildComponents() as &$component) {
                    if (in_array($component->getName(), $rule['action']['columns'])) {
                        $componentConfig = $component->getData('config');
                        $componentConfig[$rule['action']['name']] = $rule['action']['value'];
                        $component->setData('config', $componentConfig);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['custom_fields']) || !is_array($item['custom_fields'])) {
                continue;
            }
            foreach ($item['custom_fields'] as $customField) {
                $item[$this->getCustomFieldName($customField['field_id'])] = $customField['value'];
            }
        }

        return $dataSource;
    }

    /**
     * Retrieve custom field name
     *
     * @param int $fieldId
     * @return string
     */
    private function getCustomFieldName($fieldId)
    {
        return 'custom_field_' . $fieldId;
    }

    /**
     * Retrieve custom fields
     *
     * @return CustomFieldInterface[]
     */
    private function getCustomFields()
    {
        $this->searchCriteriaBuilder->addFilter(CustomFieldInterface::REFERS, Refers::REQUEST);

        return $this->customFieldRepository->getList($this->searchCriteriaBuilder->create())->getItems();
    }

    /**
     * Create component
     *
     * @param string $columnName
     * @param string $type
     * @param array $config
     * @return $this
     */
    private function createComponent($columnName, $type, $config)
    {
        $component = $this->componentFactory->create(
            $columnName,
            $type,
            ['context' => $this->getContext()]
        );
        $component->setData('config', $config);
        $component->prepare();
        $this->addComponent($columnName, $component);

        return $this;
    }
}
