<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export\DataCollector\Processor;

use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderInterface;
use Aheadworks\Rma\Model\Request\Export\DataCollector\RowHeaderMapper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class Request
 *
 * @package Aheadworks\Rma\Model\Request\Export\DataCollector\Processor
 */
class Request extends AbstractProcessor
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @param RowHeaderMapper $headerMapper
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     */
    public function __construct(
        RowHeaderMapper $headerMapper,
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver
    ) {
        $this->localeDate = $localeDate;
        $this->locale = $localeResolver->getLocale();
        parent::__construct($headerMapper);
    }

    /**
     * @inheritdoc
     */
    public function prepareRowData($request, $requestItem, $resultRow)
    {
        $resultRow[$this->rowHeaderMapper->getHeaderPosition(RowHeaderInterface::REQUEST_ID)]
            = $request->getIncrementId();

        $convertedDate = $this->localeDate->date(
            new \DateTime($request->getCreatedAt(), new \DateTimeZone('UTC')),
            $this->locale,
            true
        );

        $resultRow[$this->rowHeaderMapper->getHeaderPosition(RowHeaderInterface::CREATED_AT)]
            = $convertedDate->format('M j, Y h:i:s');

        return $resultRow;
    }

    /**
     * @inheritdoc
     */
    public function prepareRowHeaders()
    {
        return [
            RowHeaderInterface::REQUEST_ID,
            RowHeaderInterface::CREATED_AT,
        ];
    }
}
