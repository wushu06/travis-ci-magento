<?php

namespace Elementary\Banner\Controller\Adminhtml\Banner;

use Elementary\Banner\Model\Banner;
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
        $bannerId = (int) $this->getRequest()->getParam('banner_id', null);
        $redirect = $this->resultRedirectFactory->create();

        if (!$bannerId) {
            $this->messageManager->addError(__('This banner does not exist.'));

            return $redirect->setPath('*/*/index');
        }

        /** @var Banner $bannerModel */
        $bannerModel = $this->_bannerFactory->create();
        $banner = $bannerModel->load($bannerId);

        if (!$banner->getId()) {
            $this->messageManager->addError(__('This banner does not exist.'));

            return $redirect->setPath('*/*/index');
        }

        try {
            $title = $banner->getIdentifier();
            $banner->delete();

            $this->messageManager->addSuccess(__('"%1" has been successfully deleted.',
                $title
            ));

        } catch (\Exception $e) {
            $this->messageManager->addError(__('This banner could not be deleted (%1).',
                $e->getMessage()
            ));
        }

        return $redirect->setPath('*/*/index');
    }
}
