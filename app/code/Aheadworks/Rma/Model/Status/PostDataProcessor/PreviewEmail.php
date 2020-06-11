<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Status\PostDataProcessor;

use Magento\Framework\Stdlib\BooleanUtils;

/**
 * Class PreviewEmail
 *
 * @package Aheadworks\Rma\Model\Status\PostDataProcessor\Status
 */
class PreviewEmail
{
    /**
     * @var BooleanUtils
     */
    private $booleanUtils;

    /**
     * @param BooleanUtils $booleanUtils
     */
    public function __construct(
        BooleanUtils $booleanUtils
    ) {
        $this->booleanUtils = $booleanUtils;
    }

    /**
     * Prepare entity data for save
     *
     * @param array $data
     * @return array
     */
    public function prepareEntityData($data)
    {
        $data['to_admin'] = $this->booleanUtils->toBoolean($data['to_admin']);

        return $data;
    }
}
