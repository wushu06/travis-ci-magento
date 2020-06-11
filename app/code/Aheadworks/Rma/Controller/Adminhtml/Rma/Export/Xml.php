<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma\Export;

use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Model\Request\Export\ConvertToXml;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResponseInterface;

/**
 * Class Xml
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma\Export
 */
class Xml extends AbstractExport
{
    /**
     * @param Context $context
     * @param ConvertToXml $converter
     * @param FileFactory $fileFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ConvertToXml $converter,
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
     * Export data provider to XML
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        return $this->fileFactory->create('export.xml', $this->converter->getFile(), 'var');
    }
}
