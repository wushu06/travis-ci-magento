<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CannedResponse;

use Aheadworks\Rma\Model\ResourceModel\CannedResponse\Collection;
use Magento\Framework\Controller\ResultFactory;
use Aheadworks\Rma\Api\Data\CannedResponseInterface;

/**
 * Class MassStatus
 * @package Aheadworks\Rma\Controller\Adminhtml\CannedResponse
 */
class MassStatus extends AbstractMassAction
{
    /**
     * @inheritdoc
     */
    protected function massAction(Collection $collection)
    {
        $status = (int) $this->getRequest()->getParam('status');
        $changedRecords = 0;

        foreach ($collection->getAllIds() as $cannedResponseId) {
            try {
                $cannedResponseModel = $this->cannedResponseRepository->get($cannedResponseId);
            } catch (\Exception $e) {
                $cannedResponseModel = null;
            }
            if ($cannedResponseModel) {
                $cannedResponseModel->setData(CannedResponseInterface::IS_ACTIVE, $status);
                $this->cannedResponseRepository->save($cannedResponseModel);
                $changedRecords++;
            }
        }

        if ($changedRecords) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been changed.', $changedRecords));
        } else {
            $this->messageManager->addSuccess(__('No records have been changed'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
