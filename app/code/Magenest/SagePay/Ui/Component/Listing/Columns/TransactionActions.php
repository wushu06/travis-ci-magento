<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class TransactionActions extends Column
{
    const GRID_URL_PATH_RESTORE = 'sagepay/transaction/restore';
    const GRID_URL_PATH_VOID = 'sagepay/transaction/void';
    const GRID_URL_PATH_DELETE = 'sagepay/transaction/delete';
    const GRID_URL_PATH_VIEW_ORDER = 'sales/order/view';

    protected $urlBuilder;
    protected $editUrl;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['id'])) {
                    if (!isset($item['order_id'])) {
                        $item[$name]['restore'] = [
                            'href' => $this->urlBuilder->getUrl(self::GRID_URL_PATH_RESTORE, ['id' => $item['id']]),
                            'label' => __('Restore order'),
                            'confirm' => [
                                'title' => __('Restore this order'),
                                'message' => __('Are you sure you wan\'t to restore this order from transaction "${ $.$data.transaction_id }"?')
                            ]
                        ];
                    }else{
                        $item[$name]['vieworder'] = [
                            'href' => $this->urlBuilder->getUrl(self::GRID_URL_PATH_VIEW_ORDER, ['order_id' => $item['order_id']]),
                            'target' => '_black',
                            'label' => __('View order'),
                        ];
                    }

//                    $item[$name]['void'] = [
//                        'href' => $this->urlBuilder->getUrl(self::GRID_URL_PATH_VOID, ['id' => $item['id']]),
//                        'label' => __('Void transaction'),
//                        'confirm' => [
//                            'title' => __('Void transaction'),
//                            'message' => __('Are you sure you wan\'t to void this transaction "${ $.$data.transaction_id }"?')
//                        ]
//                    ];
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(self::GRID_URL_PATH_DELETE, ['id' => $item['id']]),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete transaction'),
                            'message' => __('Are you sure you wan\'t to delete this transaction "${ $.$data.transaction_id }"?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
