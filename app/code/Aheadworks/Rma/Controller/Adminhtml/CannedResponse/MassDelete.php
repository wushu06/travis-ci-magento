<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\CannedResponse;

use Aheadworks\Rma\Model\ResourceModel\CannedResponse\Collection;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class MassDelete
 * @package Aheadworks\Rma\Controller\Adminhtml\CannedResponse
 */
class MassDelete extends AbstractMassAction
{
    /**
     * @inheritdoc
     */
    protected function massAction(Collection $collection)
    {
        $deletedRecords = 0;

        foreach ($collection->getAllIds() as $cannedResponseId) {
            $this->cannedResponseRepository->deleteById($cannedResponseId);
            $deletedRecords++;
        }

        if ($deletedRecords) {
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $deletedRecords));
        } else {
            $this->messageManager->addSuccess(__('No records have been deleted'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
