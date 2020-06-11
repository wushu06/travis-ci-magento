<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\ThreadMessage\Attachment;

use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Aheadworks\Rma\Model\ThreadMessage\Attachment\FileDownloader;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class FileDownloader
 * Test for \Aheadworks\Rma\Model\ThreadMessage\Attachment\FileDownloader
 *
 * @package Aheadworks\Rma\Test\Unit\Model\ThreadMessage\Attachment
 */
class FileDownloaderTest extends TestCase
{
    /**
     * @var FileDownloader
     */
    private $model;

    /**
     * @var ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $responseMock;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->responseMock = $this->getMockForAbstractClass(ResponseInterface::class);
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->setMethods(['getDirectoryWrite'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            FileDownloader::class,
            [
                'response' => $this->responseMock,
                'filesystem' => $this->filesystemMock
            ]
        );
    }

    /**
     * Testing of download method, on exception
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File not found.
     */
    public function testDownloadOnException()
    {
        $fileName = 'not file';

        $attachmentMock = $this->getMockForAbstractClass(ThreadMessageAttachmentInterface::class);
        $attachmentMock->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileName);

        $dirMock = $this->getMockForAbstractClass(Filesystem\Directory\WriteInterface::class);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryWrite')
            ->with(DirectoryList::MEDIA)
            ->willReturn($dirMock);

        $dirMock->expects($this->once())
            ->method('isFile')
            ->willReturn(false);

        $this->model->download($attachmentMock);
    }
}
