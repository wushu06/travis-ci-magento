<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Elementary\EmployeesManager\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

class Bookmark extends \Magento\Ui\Component\Bookmark
{
    /**
     * @var \Elementary\EmployeesManager\Model\CustomerEmployee
     */
    protected $customeremployee;

    /**
     * [__construct description]
     * @param  ContextInterface                $context            [description]
     * @param  \Elementary\EmployeesManager\Model\CustomerEmployee $customeremployee         [description]
     * @param  BookmarkRepositoryInterface     $bookmarkRepository [description]
     * @param  BookmarkManagementInterface     $bookmarkManagement [description]
     * @param  {Array}  array                  $components         [description]
     * @param  {Array}  array                  $data               [description]
     */
    public function __construct(
        ContextInterface $context,
        \Elementary\EmployeesManager\Model\CustomerEmployee $customeremployee,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $bookmarkRepository, $bookmarkManagement, $components, $data);
        $this->customeremployee = $customeremployee;
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        $namespace = $this->getContext()->getRequestParam('namespace', $this->getContext()->getNamespace());
        $config = [];
        if (!empty($namespace)) {
            $storeId = $this->getContext()->getRequestParam('store');

            if (empty($storeId)) {
                $storeId = $this->getContext()->getFilterParam('store_id');
            }

            $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
            /** @var \Magento\Ui\Api\Data\BookmarkInterface $bookmark */
            foreach ($bookmarks->getItems() as $bookmark) {
                if ($bookmark->isCurrent()) {
                    $config['activeIndex'] = $bookmark->getIdentifier();
                }

                $config = array_merge_recursive($config, $bookmark->getConfig());

                if (!empty($storeId)) {
                    $config['current']['filters']['applied']['store_id'] = $storeId;
                }
            }
        }

        $this->setData('config', array_replace_recursive($config, $this->getConfiguration($this)));

        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}