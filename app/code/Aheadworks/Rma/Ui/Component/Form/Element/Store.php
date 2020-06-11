<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Form\Element;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

/**
 * Class Store
 * @package Aheadworks\Rma\Ui\Component\Form\Element
 */
class Store extends Select
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ContextInterface $context
     * @param StoreManagerInterface $storeManager
     * @param array|null $options
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        $options = null,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $options, $components, $data);
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        $config = $this->getData('config');
        if ($this->storeManager->hasSingleStore()) {
            $config['visible'] = false;
            $this->setData('config', $config);
        }
        parent::prepare();
    }
}
