<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma\Export;

use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Model\Request\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Csv
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma\Export
 */
class Csv extends AbstractExport
{
    /**
     * @param Context $context
     * @param ConvertToCsv $converter
     * @param FileFactory $fileFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ConvertToCsv $converter,
        FileFactory $fileFactory,
        Filter $filter,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $converter,
            $fileFactory,
            $filter,
            $logger
        );
    }

    /**
     * Export data provider to CSV
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        return $this->fileFactory->create('export.csv', $this->converter->getFile(), 'var');
    }
}
