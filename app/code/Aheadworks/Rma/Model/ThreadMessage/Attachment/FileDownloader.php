<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\ThreadMessage\Attachment;

use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class FileDownloader
 *
 * @package Aheadworks\Rma\Model\ThreadMessage\Attachment
 */
class FileDownloader
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param ResponseInterface $response
     * @param Filesystem $filesystem
     */
    public function __construct(
        ResponseInterface $response,
        Filesystem $filesystem
    ) {
        $this->response = $response;
        $this->filesystem = $filesystem;
    }

    /**
     * Download file
     *
     * @param ThreadMessageAttachmentInterface $attachment
     * @return ResponseInterface
     * @throws LocalizedException
     */
    public function download($attachment)
    {
        $file = $this->getFilePath($attachment->getFileName());
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if (!$dir->isFile($file)) {
            throw new LocalizedException(__('File not found.'));
        }
        $contentLength = $dir->stat($file)['size'];

        $this->response->setHttpResponseCode(200)
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', 'application/octet-stream', true)
            ->setHeader('Content-Length', $contentLength, true)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $attachment->getName() . '"', true)
            ->setHeader('Last-Modified', date('r'), true)
            ->sendHeaders();

        $stream = $dir->openFile($file, 'r');
        $content = '';
        while (!$stream->eof()) {
            $content .= $stream->read(1024);
        }
        $stream->close();

        return $this->response->setBody($content);
    }

    /**
     * Retrieve full file path
     *
     * @param string $fileName
     * @return string
     */
    private function getFilePath($fileName)
    {
        $DS = DIRECTORY_SEPARATOR;

        return FileUploader::FILE_DIR . $DS . $fileName;
    }
}
