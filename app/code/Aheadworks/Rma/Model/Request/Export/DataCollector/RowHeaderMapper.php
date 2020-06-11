<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector;

/**
 * Class RowHeaderMapper
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector
 */
class RowHeaderMapper
{
    /**
     * @var array
     */
    private $headers = [];

    /**
     * Get header position
     *
     * @param string $header
     * @return int|bool
     */
    public function getHeaderPosition($header)
    {
        return array_search($header, $this->headers);
    }

    /**
     * Get list of headers
     *
     * @return array
     */
    public function getHeaders()
    {
        $translatedHeaders = [];
        foreach ($this->headers as $header) {
            $translatedHeaders[] = __($header);
        }
        return $translatedHeaders;
    }

    /**
     * Add headers

     * @param array $headers
     */
    public function addRowHeaders($headers)
    {
        $this->headers = array_merge($this->headers, $headers);
    }
}
