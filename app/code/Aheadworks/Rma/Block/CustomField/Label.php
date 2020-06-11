<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\CustomField;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Aheadworks\Rma\Model\CustomField\Resolver\CustomField as CustomFieldResolver;

/**
 * Class Wrapper
 *
 * @method array|string getValue()
 * @method int getCustomFieldId()
 * @package Aheadworks\Rma\Block\CustomField
 */
class Label extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Aheadworks_Rma::customfield/label.phtml';

    /**
     * @var CustomFieldResolver
     */
    private $customFieldResolver;

    /**
     * @param Context $context
     * @param CustomFieldResolver $customFieldResolver
     * @param array $data
     */
    public function __construct(
        Context $context,
        CustomFieldResolver $customFieldResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customFieldResolver = $customFieldResolver;
    }

    /**
     * Retrieve field label
     *
     * @return string
     */
    public function getFieldLabel()
    {
        return $this->customFieldResolver->getValue($this->getCustomFieldId(), $this->getValue());
    }
}
