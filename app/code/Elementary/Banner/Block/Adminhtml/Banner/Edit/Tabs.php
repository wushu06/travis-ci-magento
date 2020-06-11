<?php

namespace Elementary\Banner\Block\Adminhtml\Banner\Edit;

use Magento\Backend\Block\Widget;

/**
 * Banner Tabs
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

        $this->setId('banner_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Banner Information'));
    }

    /**
     * Prepare Tabs
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab('main_section', [
            'label'   => __('Banner Information'),
            'content' => $this->getLayout()->createBlock(Tabs\Main::class)->toHtml()
        ]);

        $this->addTab('slides', [
            'label' => __('Banner Slides'),
            'url'   => $this->getUrl('*/*/slides', [
                '_current' => true
            ]),
            'class' => 'ajax'
        ]);

        parent::_prepareLayout();

        return $this;
    }
}
