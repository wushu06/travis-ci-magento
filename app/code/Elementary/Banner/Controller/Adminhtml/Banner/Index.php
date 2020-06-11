<?php

namespace Elementary\Banner\Controller\Adminhtml\Banner;

use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Magento\Backend\Model\View\Result\Page;

/**
 * Index Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Index extends AbstractAction
{
    /**
     * Index Action
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

        return $resultPage;
    }
}
