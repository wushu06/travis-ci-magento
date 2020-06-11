<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\NewRequest\Step;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Model\Config;
use Aheadworks\Rma\Model\Source\CustomField\EditAt;
use Aheadworks\Rma\Model\Source\CustomField\Refers;
use Aheadworks\Rma\Model\Renderer\CmsBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Aheadworks\Rma\Block\CustomField\Input\Renderer\Factory as CustomFieldRendererFactory;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CreateRequest
 *
 * @package Aheadworks\Rma\Block\Customer\Request\NewRequest\Step
 */
class CreateRequest extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/newrequest/step/createrequest.phtml';

    /**
     * @var CmsBlock
     */
    private $cmsBlockRenderer;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var CustomFieldRendererFactory
     */
    private $customFieldRendererFactory;

    /**
     * @param Context $context
     * @param CmsBlock $cmsBlockRenderer
     * @param Config $config
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param CustomFieldRendererFactory $customFieldRendererFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CmsBlock $cmsBlockRenderer,
        Config $config,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomFieldRepositoryInterface $customFieldRepository,
        CustomFieldRendererFactory $customFieldRendererFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->cmsBlockRenderer = $cmsBlockRenderer;
        $this->config = $config;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldRendererFactory = $customFieldRendererFactory;
    }

    /**
     * Retrieve reasons and details block html
     *
     * @return string
     */
    public function getReasonsAndDetailsBlockHtml()
    {
        return $this->cmsBlockRenderer->render($this->config->getReasonsAndDetailsBlock());
    }

    /**
     * Retrieve policy block html
     *
     * @return string
     */
    public function getPolicyBlockHtml()
    {
        return $this->cmsBlockRenderer->render($this->config->getPolicyBlock());
    }

    /**
     * Retrieve request custom fields
     *
     * @return CustomFieldInterface[]
     * @throws LocalizedException
     */
    public function getRequestCustomFields()
    {
        $this->searchCriteriaBuilder
            ->addFilter(CustomFieldInterface::REFERS, Refers::REQUEST)
            ->addFilter('editable_or_visible_for_status', EditAt::NEW_REQUEST_PAGE)
            ->addFilter(CustomFieldInterface::OPTIONS, 'enabled')
            ->addFilter(CustomFieldInterface::IS_ACTIVE, Enabledisable::ENABLE_VALUE)
            ->addFilter(CustomFieldInterface::WEBSITE_IDS, $this->_storeManager->getWebsite()->getId());

        return $this->customFieldRepository
            ->getList($this->searchCriteriaBuilder->create())
            ->getItems();
    }

    /**
     * Retrieve request custom fields input html
     *
     * @param CustomFieldInterface $customField
     * @return string
     */
    public function getRequestCustomFieldHtml(CustomFieldInterface $customField)
    {
        $fieldName = 'custom_fields.' . $customField->getId();
        $renderer = $this->customFieldRendererFactory->create($customField, EditAt::NEW_REQUEST_PAGE, $fieldName);

        return $renderer->toHtml();
    }

    /**
     * Retrieve thread message input html
     *
     * @return string
     */
    public function getThreadMessageHtml()
    {
        $block = $this->getLayout()->getBlock('aw_rma.thread.message');

        return $block->toHtml();
    }

    /**
     * Retrieve submit url
     *
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/save');
    }

    /**
     * Retrieve order id
     *
     * @return int
     */
    public function getOrderId()
    {
        return $this->getRequest()->getParam('order_id');
    }
}
