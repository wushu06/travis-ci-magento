<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Controller\Adminhtml\Rma;

use Magento\Backend\App\Action\Context;
use Aheadworks\Rma\Model\ResourceModel\Request\CollectionFactory;
use Aheadworks\Rma\Model\ResourceModel\Request\Collection;
use Magento\Ui\Component\MassAction\Filter;
use Aheadworks\Rma\Api\RequestManagementInterface;
use Magento\Backend\App\Action;

/**
 * Class MassChangeStatus
 *
 * @package Aheadworks\Rma\Controller\Adminhtml\Rma
 */
class MassChangeStatus extends Action
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'Aheadworks_Rma::manage_rma';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var RequestManagementInterface
     */
    private $requestManagement;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param RequestManagementInterface $requestManagement
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        RequestManagementInterface $requestManagement
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->requestManagement = $requestManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $this->changeStatus($collection);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/index');
        return $resultRedirect;
    }

    /**
     * Change status
     *
     * @param Collection $collection
     * @return void
     */
    private function changeStatus($collection)
    {
        $status = (int)$this->getRequest()->getParam('status');
        $count = 0;
        foreach ($collection->getItems() as $item) {
            if ($this->requestManagement->changeStatus($item->getId(), $status, true)) {
                $count++;
            }
        }
        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated', $count));
    }
}
