<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Request;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Ui\Component\Form\Field;

/**
 * Class Date
 *
 * @package Aheadworks\Rma\Ui\Component\Form\Request
 */
class Date extends Field
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param TimezoneInterface $localeDate
     * @param UiComponentInterface[] $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        TimezoneInterface $localeDate,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localeDate = $localeDate;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        parent::prepareDataSource($dataSource);
        $config = $this->getData('config');
        $dataScope = $config['dataScope'];
        if (isset($dataSource['data']['id'])
            && isset($dataSource['data'][$dataScope]) && $dataSource['data'][$dataScope]
        ) {
            try {
                $date = $this->localeDate->date($dataSource['data'][$dataScope], null, true)->format('F d, Y H:i');
            } catch (\Exception $e) {
                $date = null;
            }
            $dataSource['data'][$dataScope] = $date;
        }
        return $dataSource;
    }
}
