<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Ui\Component\Listing;

use Aheadworks\Rma\Model\Source\Request\Status;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Component\Bookmark as UiBookmark;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class Bookmark
 *
 * @package Aheadworks\Rma\Ui\Component\Listing
 */
class Bookmark extends UiBookmark
{
    /**
     * @var string
     */
    const RMA_LISTING_NAMESPACE = 'aw_rma_request_listing';

    /**
     * @var BookmarkInterfaceFactory
     */
    private $bookmarkFactory;

    /**
     * @var UserContextInterface
     */
    private $userContext;

    /**
     * @param BookmarkInterfaceFactory $bookmarkFactory
     * @param UserContextInterface $userContext
     * @param ContextInterface $context
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param array $components
     * @param array $data
     */
    public function __construct(
        BookmarkInterfaceFactory $bookmarkFactory,
        UserContextInterface $userContext,
        ContextInterface $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
        parent::__construct($context, $bookmarkRepository, $bookmarkManagement, $components, $data);
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        parent::prepare();
        $config = $this->getConfiguration();
        if (!isset($config['views'])) {
            $this->addView('default', __('Default View'), ['payment_method']);
            $this->addView(
                'pending_approval',
                __('Pending Approval'),
                ['payment_method', 'status_id', 'updated_at'],
                ['status_id' => [(string)Status::PENDING_APPROVAL]]
            );
            $this->addView(
                'package_sent',
                __('Package Sent'),
                ['payment_method', 'status_id', 'created_at'],
                ['status_id' => [(string)Status::PACKAGE_SENT]]
            );
            $this->addView(
                'package_received',
                __('Package Received'),
                ['payment_method', 'created_at'],
                ['status_id' => [(string)Status::PACKAGE_RECEIVED]]
            );
            $this->addView(
                'issue_refund',
                __('Issue Refund'),
                ['created_at'],
                ['status_id' => [(string)Status::ISSUE_REFUND]]
            );
        }
    }

    /**
     * Add view to the current config and save the bookmark to db
     *
     * @param string $index
     * @param string $label
     * @param array $hideColumns columns to hide comparing to default view config
     * @param array $filters applied filters as $filterName => $filterValue array
     * @return $this
     */
    private function addView($index, $label, $hideColumns = [], $filters = [])
    {
        $config = $this->getConfiguration();

        $viewConf = $this->getDefaultViewConfig();
        $viewConf = array_merge($viewConf, [
            'index'     => $index,
            'label'     => $label,
            'value'     => $label,
            'editable'  => false
        ]);
        foreach ($hideColumns as $hideColumn) {
            $viewConf['data']['columns'][$hideColumn]['visible'] = false;
        }
        foreach ($filters as $filterName => $filterValue) {
            $viewConf['data']['filters']['applied'][$filterName] = $filterValue;
        }
        $viewConf['data']['displayMode'] = 'grid';

        $this->saveBookmark($index, $label, $viewConf);

        $config['views'][$index] = $viewConf;
        $this->setData('config', array_replace_recursive($config, $this->getConfiguration()));
        return $this;
    }

    /**
     * Save bookmark to db
     *
     * @param string $index
     * @param string $label
     * @param array $viewConf
     */
    private function saveBookmark($index, $label, $viewConf)
    {
        $bookmark = $this->bookmarkFactory->create();
        $config = ['views' => [$index => $viewConf]];
        $bookmark->setUserId($this->userContext->getUserId())
            ->setNamespace(self::RMA_LISTING_NAMESPACE)
            ->setIdentifier($index)
            ->setTitle($label)
            ->setConfig(json_encode($config));
        $this->bookmarkRepository->save($bookmark);
    }

    /**
     * Retrieve default view config
     *
     * @return mixed
     */
    private function getDefaultViewConfig()
    {
        $config['editable']  = false;
        $config['data']['filters']['applied']['placeholder'] = true;
        $config['data']['columns'] = [
            'ids'                => ['sorting' => false, 'visible' => true],
            'increment_id'       => ['sorting' => 'desc', 'visible' => true],
            'order_increment_id' => ['sorting' => false, 'visible' => true],
            'payment_method'=> ['sorting' => false, 'visible' => true],
            'customer'      => ['sorting' => false, 'visible' => true],
            'products'      => ['sorting' => false, 'visible' => true],
            'last_reply_by' => ['sorting' => false, 'visible' => true],
            'status_id'     => ['sorting' => false, 'visible' => true],
            'store_id'      => ['sorting' => false, 'visible' => true],
            'updated_at'    => ['sorting' => false, 'visible' => true],
            'created_at'    => ['sorting' => false, 'visible' => true]
        ];

        $position = 0;
        foreach (array_keys($config['data']['columns']) as $colName) {
            $config['data']['positions'][$colName] = $position;
            $position++;
        }

        $config['data']['paging'] = [
            'options' => [
                20 => ['value' => 20, 'label' => 20],
                30 => ['value' => 30, 'label' => 30],
                50 => ['value' => 50, 'label' => 50],
                100 => ['value' => 30, 'label' => 30],
                200 => ['value' => 30, 'label' => 30]
            ],
            'value' => 20
        ];

        return $config;
    }
}
