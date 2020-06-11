<?php

namespace Elementary\Banner\Block\Adminhtml\Slide\Edit;

use Magento\Backend\Block\Widget;
use Elementary\Banner\Block\Adminhtml\Slide\Edit\Tabs\Main;

/**
 * Slide Tabs
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Tabs extends Widget\Tabs
{
    /**
     * Tabs Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('slide_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Slide Information'));
    }

    /**
     * Prepare Tabs
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab('main_section', [
            'label'   => __('Slide Information'),
            'content' => $this->getLayout()->createBlock(Main::class)->toHtml()
        ]);

        parent::_prepareLayout();

        return $this;
    }
}
