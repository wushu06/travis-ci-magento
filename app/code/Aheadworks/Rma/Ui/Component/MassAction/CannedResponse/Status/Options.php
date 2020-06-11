<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\MassAction\CannedResponse\Status;

use Aheadworks\Rma\Model\Source\CannedResponse\Status as StatusSource;
use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;

/**
 * Class Options
 *
 * @package Aheadworks\Rma\Ui\Component\MassAction\Request
 */
class Options implements JsonSerializable
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
     * @var StatusSource
     */
    private $statusSource;

    /**
     * @param UrlInterface $urlBuilder
     * @param StatusSource $statusSource
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        StatusSource $statusSource,
        array $data = []
    ) {
        $this->data = $data;
        $this->statusSource = $statusSource;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            $statusArray = $this->statusSource->toOptionArrayForMassStatus();
            $this->prepareData();
            foreach ($statusArray as $status) {
                $this->options[$status['value']] = [
                    'type' => 'status' . $status['value'],
                    'label' => $status['label'],
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$status['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $status['value']]
                    );
                }

                $this->options[$status['value']] = array_merge_recursive(
                    $this->options[$status['value']],
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
