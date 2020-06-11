<?php

namespace Elementary\Banner\Block\Adminhtml\Banner;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;

/**
 * Banner Edit
 *
 * @package   Elementary\Banner
 * @author    Michael Cole <mike@elementarydigital.co.uk>
 * @copyright Elementary Digital - 2018
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_registry = null;

    /**
     * Edit constructor
     *
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context  $context,
        Registry $registry,
        array    $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_objectId = 'banner_id';
        $this->_blockGroup = 'Elementary_Banner';
        $this->_controller = 'adminhtml_banner';

        parent::_construct();

        if ($this->_isAllowedAction('Elementary_Banner::banner')) {
            $this->buttonList->update('save', 'label', __('Save Banner'));
            $this->buttonList->add('saveandcontinue', [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ], -100);
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Elementary_Banner::banner')) {
            $this->buttonList->update('delete', 'label', __('Delete Banner'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId Admin Resource
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Save and Continue Url
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('banner/*/save', [
            '_current'   => true,
            'back'       => 'edit',
            'active_tab' => '{{tab_id}}'
        ]);
    }
}