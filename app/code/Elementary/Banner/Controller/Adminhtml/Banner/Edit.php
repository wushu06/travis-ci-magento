<?php

namespace Elementary\Banner\Controller\Adminhtml\Banner;

use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Elementary\Banner\Model\Banner;
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
        $bannerId = (int) $this->getRequest()->getParam('banner_id', null);
        if (!$bannerId) {
            $this->messageManager->addError(__('This banner does not exist.'));
            $redirect = $this->resultRedirectFactory->create();

            return $redirect->setPath('*/*/');
        }

        /** @var Banner $bannerModel */
        $bannerModel = $this->_bannerFactory->create();

        /** @var Banner $banner */
        $banner = $bannerModel->load($bannerId);
        $bannerData = new DataObject();
        $bannerData->setData(Banner::STATUS, 1);
        if ($banner->getId()) {
            $bannerData->addData($banner->getData());
        }
        $formData = $this->_getSession()->getFormData(true);
        if ($formData) {
            $bannerData->setData($formData);
        }

        $this->_registry->register('banner', $bannerData);

        /** @var Page $resultPage */
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu(self::ACL_RESOURCE);
        $resultPage->addBreadcrumb(__('Banners'), __('Banners'));
        $resultPage->addBreadcrumb(__('Banners'), __('Banners'));
        $resultPage->addBreadcrumb(__('Edit Banner'), __('Edit Banner'));

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
        /** @var Banner $banner */
        $banner = $this->_registry->registry('banner');

        return __('Edit "%1"', $banner->getIdentifier());
    }
}
