<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Name
 *
 * @package Aheadworks\Rma\Ui\Component\Listing\Column
 */
class Name extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
        $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'id';
        foreach ($dataSource['data']['items'] as &$item) {
            $item[$fieldName . '_label'] = $item[$fieldName];
            $item[$fieldName . '_url'] = $this->context->getUrl(
                $viewUrlPath,
                [$urlEntityParamName => $item[$urlEntityParamName]]
            );
        }

        return $dataSource;
    }
}
