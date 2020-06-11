<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Convert\Excel;
use Magento\Framework\Convert\ExcelFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\SearchResultIteratorFactory;
use Magento\Ui\Model\Export\SearchResultIterator;

/**
 * Class ConvertToXml
 *
 * @package Aheadworks\Rma\Model\Request\Export
 */
class ConvertToXml implements ConverterInterface
{
    /**
     * @var DirectoryList
     */
    protected $directory;

    /**
     * @var ExcelFactory
     */
    protected $excelFactory;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @var SearchResultIteratorFactory
     */
    protected $iteratorFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param ExcelFactory $excelFactory
     * @param DataCollector $dataCollector
     * @param SearchResultIteratorFactory $iteratorFactory
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        ExcelFactory $excelFactory,
        DataCollector $dataCollector,
        SearchResultIteratorFactory $iteratorFactory
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->dataCollector = $dataCollector;
        $this->excelFactory = $excelFactory;
        $this->iteratorFactory = $iteratorFactory;
    }

    /**
     * Returns row data
     *
     * @param array $row
     * @return array
     */
    public function getRowData($row)
    {
        return $row;
    }

    /**
     * Returns XML file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getFile()
    {
        $component = $this->filter->getComponent();

        $name = sha1(microtime());
        $file = 'export/'. $component->getName() . $name . '.xml';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();

        $component->getContext()->getDataProvider()->setLimit(0, 0);

        /** @var SearchResultInterface $searchResult */
        $searchResult = $component->getContext()->getDataProvider()->getSearchResult();

        /** @var DocumentInterface[] $searchResultItems */
        $searchResultItems = $searchResult->getItems();
        $headers = $this->dataCollector->getHeaders();

        $resultRows = [];
        /** @var  $item */
        foreach ($searchResultItems as $item) {
            $rows = $this->dataCollector->getRowsData($item, $headers);
            foreach ($rows as $row) {
                $resultRows[] = $row;
            }
        }

        /** @var SearchResultIterator $searchResultIterator */
        $searchResultIterator = $this->iteratorFactory->create(['items' => $resultRows]);

        /** @var Excel $excel */
        $excel = $this->excelFactory->create(
            [
                'iterator' => $searchResultIterator,
                'rowCallback'=> [$this, 'getRowData'],
            ]
        );

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $excel->setDataHeader($headers);
        $excel->write($stream, $component->getName() . '.xml');

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
