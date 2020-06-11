<?php

namespace Elementary\Banner\Controller\Adminhtml\Banner;

use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Magento\Backend\Model\View\Result\Page;
use Magento\Theme\Block\Html\Title;

/**
 * New Action Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class NewAction extends AbstractAction
{
    /**
     * New Action
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu(self::ACL_RESOURCE);
        $resultPage->addBreadcrumb(__('Banners'), __('Banners'));
        $resultPage->addBreadcrumb(__('Banners'), __('Banners'));
        $resultPage->addBreadcrumb(__('Add Banner'), __('Add Banner'));

        /** @var Title $pageTitle */
        $pageTitle = $resultPage->getLayout()->getBlock('page.title');
        $pageTitle->setPageTitle(__('Add Banner'));

        return $resultPage;
    }
}
