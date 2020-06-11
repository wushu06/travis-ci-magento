<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\MassAction\Request;

use Aheadworks\Rma\Api\StatusRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Statuses
 *
 * @package Aheadworks\Rma\Ui\Component\MassAction\Request
 */
class Statuses implements JsonSerializable
{
    /**
     * @var array
     */
    private $options;

    /**
     * Additional options params
     *
     * @var array
     */
    private $data;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     */
    private $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     */
    private $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     */
    private $additionalData = [];

    /**
     * @var StatusRepositoryInterface
     */
    private $statusRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param StatusRepositoryInterface $statusRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StatusRepositoryInterface $statusRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
        $this->statusRepository = $statusRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            $statuses = $this->statusRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $this->prepareData();
            foreach ($statuses as $status) {
                $this->options[$status->getId()] = [
                    'type' => 'status' . $status->getId(),
                    'label' => $status->getStorefrontLabel(),
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$status->getId()]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $status->getId()]
                    );
                }

                $this->options[$status->getId()] = array_merge_recursive(
                    $this->options[$status->getId()],
                    $this->additionalData
                );
            }

            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     */
    private function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
