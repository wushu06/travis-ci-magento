<?php

namespace Elementary\Banner\Controller\Adminhtml\Slide;

use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Elementary\Banner\Model\Slide;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\DataObject;
use Magento\Theme\Block\Html\Title;

/**
 * Edit Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Edit extends AbstractAction
{
    /**
     * Edit Action
     *
     * @return Page|Redirect
     */
    public function execute()
    {
        $slideId = (int) $this->getRequest()->getParam('slide_id', null);
        if (!$slideId) {
            $this->messageManager->addError(__('This slide does not exist.'));
            $redirect = $this->resultRedirectFactory->create();

            return $redirect->setPath('*/*/');
        }

        /** @var Slide $slideModel */
        $slideModel = $this->_slideFactory->create();
        /** @var Slide $slide */
        $slide = $slideModel->load($slideId);
        $slideData = new DataObject();
        $slideData->setData(Slide::STATUS, 1);
        if ($slide->getId()) {
            $slideData->addData($slide->getData());
        }
        $formData = $this->_getSession()->getFormData(true);
        if ($formData) {
            $slideData->setData($formData);
        }

        $this->_registry->register('slide', $slideData);

        /** @var Page $resultPage */
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu(self::ACL_RESOURCE);
        $resultPage->addBreadcrumb(__('Slide'), __('Slide'));
        $resultPage->addBreadcrumb(__('Slide'), __('Slide'));
        $resultPage->addBreadcrumb(__('Edit Slide'), __('Edit Slide'));

        /** @var Title $pageTitle */
        $pageTitle = $resultPage->getLayout()->getBlock('page.title');
        $pageTitle->setPageTitle($this->getPageTitle());

        return $resultPage;
    }

    /**
     * Get Page Title
     *
     * @return string
     */
    protected function getPageTitle()
    {
        /** @var Slide $slide */
        $slide = $this->_registry->registry('slide');

        return __('Edit "%1"', $slide->getTitle());
    }
}
