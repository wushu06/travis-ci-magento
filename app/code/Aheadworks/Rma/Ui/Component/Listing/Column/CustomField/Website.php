<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Listing\Column\CustomField;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Website
 *
 * @package Aheadworks\Rma\Ui\Component\Listing\Column\CustomField
 */
class Website extends Column
{
    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param WebsiteRepositoryInterface $websiteRepository
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        WebsiteRepositoryInterface $websiteRepository,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['website_ids'])) {
                continue;
            }
            $websiteNames = [];
            foreach ($item['website_ids'] as $websiteId) {
                try {
                    $websiteNames[] = $this->websiteRepository->getById($websiteId)->getName();
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
            $item[$fieldName] = implode(', ', $websiteNames);
        }
        return $dataSource;
    }
}
