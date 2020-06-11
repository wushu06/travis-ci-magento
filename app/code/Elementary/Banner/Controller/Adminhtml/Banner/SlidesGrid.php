<?php

namespace Elementary\Banner\Controller\Adminhtml\Banner;

use Magento\Framework\View\Result\Layout;
use Elementary\Banner\Controller\Adminhtml\AbstractAction;

/**
 * Slides Grid Controller
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class SlidesGrid extends AbstractAction
{
    /**
     * Render Banner Slides Grid
     *
     * @return Layout
     */
    public function execute()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getLayout()
            ->getBlock('banner.edit.tab.slides')
            ->setSlides($this->getRequest()->getParam('slides', null));

        return $layout;
    }
}
