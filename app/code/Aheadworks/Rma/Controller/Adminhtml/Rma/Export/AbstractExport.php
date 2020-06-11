<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Model\Request\Export\ConverterInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractExport
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma\Export
 */
abstract class AbstractExport extends Action
{
    /**
     * @var ConverterInterface
     */
    protected $converter;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param ConverterInterface $converter
     * @param FileFactory $fileFactory
     * @param Filter $filter
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        ConverterInterface $converter,
        FileFactory $fileFactory,
        Filter $filter,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
        $this->filter = $filter ;
        $this->logger = $logger;
    }

    /**
     * Checking if the user has access to requested component.
     */
    protected function _isAllowed()
    {
        if ($this->_request->getParam('namespace')) {
            try {
                $component = $this->filter->getComponent();
                $dataProviderConfig = $component->getContext()
                    ->getDataProvider()
                    ->getConfigData();
                if (isset($dataProviderConfig['aclResource'])) {
                    return $this->_authorization->isAllowed(
                        $dataProviderConfig['aclResource']
                    );
                }
            } catch (\Throwable $exception) {
                $this->logger->critical($exception);

                return false;
            }
        }

        return true;
    }
}
