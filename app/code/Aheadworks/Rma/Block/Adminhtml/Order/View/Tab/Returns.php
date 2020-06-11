<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Adminhtml\Order\View\Tab;

use Magento\Framework\View\Element\Text\ListText;
use Magento\Backend\Block\Widget\Tab\TabInterface;

/**
 * Class Returns
 * @package Aheadworks\Rma\Block\Adminhtml\Order\View\Tab
 */
class Returns extends ListText implements TabInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Returns');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Returns');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
