<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\ThreadMessage\Attachment;

use Magento\Framework\UrlInterface;
use Aheadworks\Rma\Model\ThreadMessage\Attachment\FileUploader;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaStorage\Model\File\Uploader;
use PHPUnit\Framework\TestCase;

/**
 * Class FileUploaderTest
 * Test for \Aheadworks\Rma\Model\ThreadMessage\Attachment\FileUploader
 *
 * @package Aheadworks\Rma\Test\Unit\Model\ThreadMessage\Attachment
 */
class FileUploaderTest extends TestCase
{
    /**
     * @var FileUploader
     */
    private $model;

    /**
     * @var UploaderFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $uploaderFactoryMock;

    /**
     * @var Filesystem|\PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystemMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $this->uploaderFactoryMock = $this->getMockBuilder(UploaderFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->filesystemMock = $this->getMockBuilder(Filesystem::class)
            ->setMethods(['getDirectoryRead'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            FileUploader::class,
            [
                'uploaderFactory' => $this->uploaderFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'filesystem' => $this->filesystemMock
            ]
        );
    }

    /**
     * Testing of saveToTmpFolder method
     */
    public function testSaveToTmpFolder()
    {
        $baseMediaUrl = 'https://ecommerce.aheadworks.com/pub/media/';
        $tmpMediaPath = '/tmp/media';
        $fileName = 'file.jpg';
        $fileId = base64_encode($fileName);
        $fileSize = '123';
        $fileCode = 'img';
        $filePath = '/var/www/mysite/pub/media/aw_rma/media';
        $allowedExtensions = ['jpg', 'pdf', 'png'];

        $directoryReadMock = $this->getMockForAbstractClass(ReadInterface::class);
        $directoryReadMock->expects($this->once())
            ->method('getAbsolutePath')
            ->with(FileUploader::FILE_DIR)
            ->willReturn($tmpMediaPath);
        $this->filesystemMock->expects($this->once())
            ->method('getDirectoryRead')
            ->with(DirectoryList::MEDIA)
            ->willReturn($directoryReadMock);

        $uploaderMock = $this->getMockBuilder(Uploader::class)
            ->setMethods(['setAllowRenameFiles', 'setFilesDispersion', 'setAllowedExtensions', 'save'])
            ->disableOriginalConstructor()
            ->getMock();
        $uploaderMock->expects($this->once())
            ->method('setAllowRenameFiles')
            ->with(true)
            ->willReturnSelf();
        $uploaderMock->expects($this->once())
            ->method('setAllowedExtensions')
            ->with($allowedExtensions)
            ->willReturnSelf();
        $uploaderMock->expects($this->any())
            ->method('save')
            ->with($tmpMediaPath)
            ->willReturn([
                'file' => $fileName,
                'size' => $fileSize,
                'name' => $fileName,
                'path' => $filePath
            ]);

        $this->uploaderFactoryMock->expects($this->once())
            ->method('create')
            ->with(['fileId' => $fileCode])
            ->willReturn($uploaderMock);

        $storeMock = $this->getMockBuilder(Store::class)
            ->setMethods(['getBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseMediaUrl);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $this->assertEquals(
            [
                'file' => $fileName,
                'size' => $fileSize,
                'name' => $fileName,
                'url' => $baseMediaUrl . FileUploader::FILE_DIR . '/' . $fileName,
                'path' => $filePath,
                'full_path' => $filePath . '/' . $fileName,
                'file_name' => $fileName,
                'id' => $fileId
            ],
            $this->model->setAllowedExtensions($allowedExtensions)->saveToTmpFolder($fileCode)
        );
    }

    /**
     * Testing of getMediaUrl method
     */
    public function testGetMediaUrl()
    {
        $baseMediaUrl = 'https://ecommerce.aheadworks.com/pub/media/';
        $fileName = 'file.jpg';

        $storeMock = $this->getMockBuilder(Store::class)
            ->setMethods(['getBaseUrl'])
            ->disableOriginalConstructor()
            ->getMock();
        $storeMock->expects($this->once())
            ->method('getBaseUrl')
            ->with(UrlInterface::URL_TYPE_MEDIA)
            ->willReturn($baseMediaUrl);
        $this->storeManagerMock->expects($this->once())
            ->method('getStore')
            ->willReturn($storeMock);

        $expectedPath = $baseMediaUrl . FileUploader::FILE_DIR . '/' . $fileName;
        $this->assertEquals($expectedPath, $this->model->getMediaUrl($fileName));
    }

    /**
     * Testing of getAllowedExtensions method
     */
    public function testGetAllowedExtensions()
    {
        $allowedExtensions = ['jpg', 'pdf', 'png'];
        $this->assertTrue(is_array($this->model->setAllowedExtensions($allowedExtensions)->getAllowedExtensions()));
    }

    /**
     * Testing of setAllowedExtensions method
     */
    public function testSetAllowedExtensions()
    {
        $allowedExtensions = ['jpg', 'pdf', 'png'];
        $this->assertTrue(is_array($this->model->setAllowedExtensions($allowedExtensions)->getAllowedExtensions()));
    }
}
