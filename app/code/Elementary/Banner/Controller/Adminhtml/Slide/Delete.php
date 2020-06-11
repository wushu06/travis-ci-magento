<?php

namespace Elementary\Banner\Controller\Adminhtml\Slide;

use Elementary\Banner\Model\Slide;
use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;

/**
 * Delete Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Delete extends AbstractAction
{
    /**
     * Banner Delete Action
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        $slideId = (int) $this->getRequest()->getParam('slide_id', null);
        $redirect = $this->resultRedirectFactory->create();

        if (!$slideId) {
            $this->messageManager->addError(__('This slide does not exist.'));

            return $redirect->setPath('*/*/index');
        }

        /** @var Slide $slideModel */
        $slideModel = $this->_slideFactory->create();
        $slide = $slideModel->load($slideId);

        if (!$slide->getId()) {
            $this->messageManager->addError(__('This slide does not exist.'));

            return $redirect->setPath('*/*/index');
        }

        try {
            $title = $slide->getTitle();
            $slide->delete();

            $this->messageManager->addSuccess(__('"%1" has been successfully deleted.',
                $title
            ));

        } catch (\Exception $e) {
            $this->messageManager->addError(__('This slide could not be deleted (%1).',
                $e->getMessage()
            ));
        }

        return $redirect->setPath('*/*/index');
    }
}
