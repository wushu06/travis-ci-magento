<?php

namespace Elementary\Banner\Controller\Adminhtml\Slide;

use Elementary\Banner\Controller\Adminhtml\AbstractAction;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\DataObject;
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
        $resultPage->addBreadcrumb(__('Slides'), __('Slides'));
        $resultPage->addBreadcrumb(__('Slides'), __('Slides'));
        $resultPage->addBreadcrumb(__('Add Slide'), __('Add Slide'));

        /** @var Title $pageTitle */
        $pageTitle = $resultPage->getLayout()->getBlock('page.title');
        $pageTitle->setPageTitle(__('Add Slide'));

        return $resultPage;
    }
}
