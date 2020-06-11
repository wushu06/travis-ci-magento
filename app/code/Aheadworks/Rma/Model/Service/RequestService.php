<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Service;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\RequestManagementInterface;
use Aheadworks\Rma\Api\RequestRepositoryInterface;
use Aheadworks\Rma\Api\ThreadMessageManagementInterface;
use Aheadworks\Rma\Model\Request\Modifier;
use Aheadworks\Rma\Model\Request\Notifier as RequestNotifier;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;
use Aheadworks\Rma\Model\Url;

/**
 * Class RequestService
 *
 * @package Aheadworks\Rma\Model\Service
 */
class RequestService implements RequestManagementInterface
{
    /**
     * @var RequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var Modifier
     */
    private $modifier;

    /**
     * @var ThreadMessageManagementInterface
     */
    private $threadMessageManagement;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var RequestNotifier
     */
    private $requestNotifier;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var array
     */
    private $statusChangedCache = [];

    /**
     * @param RequestRepositoryInterface $requestRepository
     * @param Modifier $modifier
     * @param ThreadMessageManagementInterface $threadMessageManagement
     * @param ResourceConnection $resourceConnection
     * @param RequestNotifier $requestNotifier
     * @param Url $url
     */
    public function __construct(
        RequestRepositoryInterface $requestRepository,
        Modifier $modifier,
        ThreadMessageManagementInterface $threadMessageManagement,
        ResourceConnection $resourceConnection,
        RequestNotifier $requestNotifier,
        Url $url
    ) {
        $this->requestRepository = $requestRepository;
        $this->modifier = $modifier;
        $this->threadMessageManagement = $threadMessageManagement;
        $this->resourceConnection = $resourceConnection;
        $this->requestNotifier = $requestNotifier;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function createRequest(RequestInterface $request, $causedByAdmin, $storeId = null)
    {
        $request = $this->performSaveRequest(
            $this->modifier->modifyRequestBeforeCreate($request, $causedByAdmin, $storeId),
            $causedByAdmin,
            $storeId,
            true
        );
        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function updateRequest(RequestInterface $request, $causedByAdmin, $storeId = null)
    {
        $statusChanged = $this->isStatusChanged($request);
        $request = $this->performSaveRequest(
            $this->modifier->modifyRequestBeforeUpdate($request, $causedByAdmin),
            $causedByAdmin,
            $storeId,
            $statusChanged
        );
        return $request;
    }

    /**
     * {@inheritdoc}
     */
    public function changeStatus($requestId, $status, $causedByAdmin, $storeId = null)
    {
        $request = $this->requestRepository->get($requestId);
        $request->setStatusId($status);

        if (!$this->isStatusChanged($request)) {
            return false;
        }

        try {
            $this->updateRequest($request, $causedByAdmin, $storeId);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrintLabelUrl($requestId, $storeId = null)
    {
        $request = $this->requestRepository->get($requestId);
        $params = ['id' => $request->getExternalLink()];
        if (!empty($storeId)) {
            $params['store_id'] = $storeId;
        }

        return $this->url->getEncryptUrl('aw_rma/request/printLabel', $params);
    }

    /**
     * @inheritdoc
     */
    public function getPrintLabelUrlForAdmin($requestId)
    {
        $request = $this->requestRepository->get($requestId);
        $params = ['id' => $request->getExternalLink()];

        return $this->url->getEncryptUrl('aw_rma_admin/rma_action/printLabel', $params);
    }

    /**
     * Check if status changed after update
     *
     * @param RequestInterface $newRequest
     * @return bool
     */
    private function isStatusChanged($newRequest)
    {
        $requestId = $newRequest->getId();
        if (!isset($this->statusChangedCache[$requestId])) {
            $oldRequest = $this->requestRepository->get($requestId, true);

            $this->statusChangedCache[$requestId] = !empty($newRequest->getStatusId())
                && $newRequest->getStatusId() != $oldRequest->getStatusId();
        }
        return $this->statusChangedCache[$requestId];
    }

    /**
     * Perform save request
     *
     * @param RequestInterface $request
     * @param bool $causedByAdmin
     * @param int|null $storeId
     * @param bool $statusChanged
     * @return RequestInterface
     * @throws LocalizedException
     */
    private function performSaveRequest($request, $causedByAdmin, $storeId = null, $statusChanged = false)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $request = $this->requestRepository->save($request);

            if ($statusChanged) {
                $this->requestNotifier->notifyAboutStatusChangeOnThread($request, $storeId);
                $this->requestNotifier->notifyAboutStatusChangeOnEmail($request, $causedByAdmin, $storeId);
            }
            if ($request->getThreadMessage()) {
                $notify = !$statusChanged;
                $this->addThreadMessage($request, $causedByAdmin, $notify);
            }

            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw new LocalizedException(__('Could not save request. %1', $e->getMessage()), $e);
        }

        return $request;
    }

    /**
     * Add thread message
     *
     * @param RequestInterface $request
     * @param bool $causedByAdmin
     * @param bool $notify
     * @return $this
     */
    private function addThreadMessage($request, $causedByAdmin, $notify)
    {
        $ownerId = !empty($request->getCustomerId()) ? $request->getCustomerId() : 0;
        $request->getThreadMessage()
            ->setRequestId($request->getId())
            ->setOwnerId($ownerId)
            ->setOwnerType($request->getLastReplyBy())
            ->setIsAuto(false)
            ->setIsInternal($causedByAdmin && $request->getThreadMessage()->isInternal());
        $this->threadMessageManagement->addThreadMessage($request->getThreadMessage(), $notify);

        return $this;
    }
}
