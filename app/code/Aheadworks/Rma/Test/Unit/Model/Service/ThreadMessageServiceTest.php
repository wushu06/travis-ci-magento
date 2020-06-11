<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Test\Unit\Model\Service;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageSearchResultsInterface;
use Aheadworks\Rma\Model\Service\ThreadMessageService;
use Magento\Framework\Api\SearchCriteria;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Aheadworks\Rma\Api\Data\ThreadMessageAttachmentInterface;
use Aheadworks\Rma\Api\Data\ThreadMessageInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\ThreadMessageRepositoryInterface;
use Aheadworks\Rma\Model\Source\ThreadMessage\Owner;
use Aheadworks\Rma\Model\ThreadMessage\Notifier as ThreadMessageNotifier;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class ThreadMessageServiceTest
 * Test for \Aheadworks\Rma\Model\Service\ThreadMessageService
 *
 * @package Aheadworks\Rma\Test\Unit\Model
 */
class ThreadMessageServiceTest extends TestCase
{
    /**
     * @var ThreadMessageService
     */
    private $model;

    /**
     * @var ThreadMessageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageRepositoryMock;

    /**
     * @var RequestRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $requestRepositoryMock;

    /**
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $searchCriteriaBuilderMock;

    /**
     * @var ThreadMessageNotifier|\PHPUnit_Framework_MockObject_MockObject
     */
    private $threadMessageNotifierMock;

    /**
     * Init mocks for tests
     *
     * @return void
     */
    public function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->threadMessageRepositoryMock = $this->getMockForAbstractClass(ThreadMessageRepositoryInterface::class);
        $this->requestRepositoryMock = $this->getMockForAbstractClass(RequestRepositoryInterface::class);
        $this->searchCriteriaBuilderMock = $this->getMockBuilder(SearchCriteriaBuilder::class)
            ->setMethods(['addFilter', 'create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->threadMessageNotifierMock = $this->getMockBuilder(ThreadMessageNotifier::class)
            ->setMethods(['notifyAboutNewMessage'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = $objectManager->getObject(
            ThreadMessageService::class,
            [
                'threadMessageRepository' => $this->threadMessageRepositoryMock,
                'requestRepository' => $this->requestRepositoryMock,
                'searchCriteriaBuilder' => $this->searchCriteriaBuilderMock,
                'threadMessageNotifier' => $this->threadMessageNotifierMock
            ]
        );
    }

    /**
     * Test addThreadMessage method
     *
     * @param bool $isNotify
     * @dataProvider addThreadMessageDataProvider
     */
    public function testAddThreadMessage($isNotify)
    {
        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $this->threadMessageRepositoryMock->expects($this->once())
            ->method('save')
            ->willReturn($threadMessageMock);

        if ($isNotify) {
            $requestMock = $this->getMockForAbstractClass(RequestInterface::class);
            $this->requestRepositoryMock->expects($this->once())
                ->method('get')
                ->willReturn($requestMock);
            $requestMock->expects($this->once())
                ->method('setThreadMessage')
                ->with($threadMessageMock);

            $threadMessageMock
                ->method('getOwnerType')
                ->willReturn(Owner::ADMIN);

            $this->threadMessageNotifierMock->expects($this->once())
                ->method('notifyAboutNewMessage')
                ->with($requestMock, true);
        }

        $this->assertSame($threadMessageMock, $this->model->addThreadMessage($threadMessageMock, $isNotify));
    }

    /**
     * Test getAttachment method
     */
    public function testGetAttachment()
    {
        $fileName = 'fileName';
        $messageId = 1;
        $requestId = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->exactly(3))
            ->method('addFilter')
            ->withConsecutive(
                [ThreadMessageInterface::ID, $messageId],
                [ThreadMessageInterface::REQUEST_ID, $requestId],
                [ThreadMessageAttachmentInterface::FILE_NAME, $fileName]
            )->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $threadMessageMock = $this->getMockForAbstractClass(ThreadMessageInterface::class);
        $searchResultsMock = $this->getMockForAbstractClass(ThreadMessageSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$threadMessageMock]);
        $this->threadMessageRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);

        $attachmentMock = $this->getMockForAbstractClass(ThreadMessageAttachmentInterface::class);
        $attachmentMock->expects($this->once())
            ->method('getFileName')
            ->willReturn($fileName);
        $threadMessageMock->expects($this->once())
            ->method('getAttachments')
            ->willReturn([$attachmentMock]);

        $this->assertSame($attachmentMock, $this->model->getAttachment($fileName, $messageId, $requestId));
    }

    /**
     * Test getAttachment method on exception
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage File not found.
     */
    public function testGetAttachmentOnException()
    {
        $fileName = 'fileName';
        $messageId = 1;
        $requestId = 1;

        $searchCriteriaMock = $this->getMockBuilder(SearchCriteria::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->searchCriteriaBuilderMock->expects($this->exactly(3))
            ->method('addFilter')
            ->withConsecutive(
                [ThreadMessageInterface::ID, $messageId],
                [ThreadMessageInterface::REQUEST_ID, $requestId],
                [ThreadMessageAttachmentInterface::FILE_NAME, $fileName]
            )->willReturnSelf();
        $this->searchCriteriaBuilderMock->expects($this->once())
            ->method('create')
            ->willReturn($searchCriteriaMock);

        $searchResultsMock = $this->getMockForAbstractClass(ThreadMessageSearchResultsInterface::class);
        $searchResultsMock->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->threadMessageRepositoryMock->expects($this->once())
            ->method('getList')
            ->with($searchCriteriaMock)
            ->willReturn($searchResultsMock);

        $this->model->getAttachment($fileName, $messageId, $requestId);
    }

    /**
     * Data provider for addThreadMessage method test
     *
     * @return array
     */
    public function addThreadMessageDataProvider()
    {
        return [[true], [false]];
    }
}
