<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\Export;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class ConvertToCsv
 *
 * @package Aheadworks\Rma\Model\Request\Export
 */
class ConvertToCsv implements ConverterInterface
{
    /**
     * @var DirectoryList
     */
    private $directory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var DataCollector
     */
    private $dataCollector;

    /**
     * @var int|null
     */
    private $pageSize = null;

    /**
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param DataCollector $dataCollector
     * @param int $pageSize
     * @throws FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        Filter $filter,
        DataCollector $dataCollector,
        $pageSize = 200
    ) {
        $this->filter = $filter;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->dataCollector = $dataCollector;
        $this->pageSize = $pageSize;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws LocalizedException
     */
    public function getFile()
    {
        $component = $this->filter->getComponent();

        $name = sha1(microtime());
        $file = 'export/'. $component->getName() . $name . '.csv';

        $this->filter->prepareComponent($component);
        $this->filter->applySelectionOnTargetProvider();
        $dataProvider = $component->getContext()->getDataProvider();

        $this->directory->create('export');
        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();
        $headers = $this->dataCollector->getHeaders();
        $stream->writeCsv($headers);

        $i = 1;
        $searchCriteria = $dataProvider->getSearchCriteria()
            ->setCurrentPage($i)
            ->setPageSize($this->pageSize);
        $totalCount = (int) $dataProvider->getSearchResult()->getTotalCount();
        while ($totalCount > 0) {
            $items = $dataProvider->getSearchResult()->getItems();
            /** @var  $item */
            foreach ($items as $item) {
                $rows = $this->dataCollector->getRowsData($item, $headers);
                foreach ($rows as $row) {
                    $stream->writeCsv($row);
                }
            }
            $searchCriteria->setCurrentPage(++$i);
            $totalCount = $totalCount - $this->pageSize;
        }
        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }
}
