<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel;

use Aheadworks\Rma\Api\Data\RequestInterface;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Api\Data\RequestPrintLabelInterface;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver\FullName as FullNameAddressResolver;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver\Region as RegionAddressResolver;
use Aheadworks\Rma\Model\Request\PrintLabel\Address\Resolver\Country as CountryAddressResolver;
use Aheadworks\Rma\Model\Request\PrintLabel\Pdf\Document as PdfDocument;
use Aheadworks\Rma\Model\Request\PrintLabel\Pdf\DocumentFactory as PdfDocumentFactory;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;
use Aheadworks\Rma\Model\Request\Resolver\Order as OrderResolver;
use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Aheadworks\Rma\Model\CustomField\Resolver\CustomField as CustomFieldResolver;

/**
 * Class Pdf
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel
 */
class Pdf
{
    /**
     * @var PdfDocument
     */
    private $pdfDocument;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var FullNameAddressResolver
     */
    private $fullNameAddressResolver;

    /**
     * @var RegionAddressResolver
     */
    private $regionAddressResolver;

    /**
     * @var CountryAddressResolver
     */
    private $countryAddressResolver;

    /**
     * @var OrderResolver
     */
    private $orderResolver;

    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @var CustomFieldResolver
     */
    private $customFieldResolver;

    /**
     * @param PdfDocumentFactory $pdfDocumentFactory
     * @param TimezoneInterface $localeDate
     * @param FullNameAddressResolver $fullNameAddressResolver
     * @param RegionAddressResolver $regionAddressResolver
     * @param CountryAddressResolver $countryAddressResolver
     * @param OrderResolver $orderResolver
     * @param OrderItemResolver $orderItemResolver
     * @param CustomFieldResolver $customFieldResolver
     */
    public function __construct(
        PdfDocumentFactory $pdfDocumentFactory,
        TimezoneInterface $localeDate,
        FullNameAddressResolver $fullNameAddressResolver,
        RegionAddressResolver $regionAddressResolver,
        CountryAddressResolver $countryAddressResolver,
        OrderResolver $orderResolver,
        OrderItemResolver $orderItemResolver,
        CustomFieldResolver $customFieldResolver
    ) {
        $this->pdfDocument = $pdfDocumentFactory->create();
        $this->localeDate = $localeDate;
        $this->fullNameAddressResolver = $fullNameAddressResolver;
        $this->regionAddressResolver = $regionAddressResolver;
        $this->countryAddressResolver = $countryAddressResolver;
        $this->orderResolver = $orderResolver;
        $this->orderItemResolver = $orderItemResolver;
        $this->customFieldResolver = $customFieldResolver;
    }

    /**
     * Retrieve completed PDF to a string
     *
     * @param RequestInterface $request
     * @param int|null $storeId
     * @return string
     */
    public function getPdf($request, $storeId = null)
    {
        $this->pdfDocument->createNewPage();
        $this
            ->insertHeadline(__('RMA #%1', $request->getIncrementId()))
            ->insertDate($request->getCreatedAt(), $storeId);

        $yPosAddressStart = $this->pdfDocument->getY();
        $this->insertAddress($request->getPrintLabel());
        $yPosAddressEnd = $this->pdfDocument->getY();

        $this->pdfDocument->setY($yPosAddressStart);
        $this->insertDetails($request, $storeId);
        $yDetailsEnd = $this->pdfDocument->getY();

        $this->pdfDocument->setY(min($yPosAddressEnd, $yDetailsEnd));
        $this->insertItems($request, $storeId);

        return $this->pdfDocument->renderPdf();
    }

    /**
     * Insert headline
     *
     * @param $text
     * @return $this
     */
    private function insertHeadline($text)
    {
        $this->pdfDocument
            ->setFontRegular(18)
            ->drawText($text)
            ->deltaY(24);

        return $this;
    }

    /**
     * Insert date
     *
     * @param string $date
     * @param int|null $storeId
     * @return $this
     */
    private function insertDate($date, $storeId)
    {
        $dateText = __(
            'Date: %1',
            $this->localeDate->formatDate(
                $this->localeDate->scopeDate($storeId, $date, true),
                \IntlDateFormatter::SHORT,
                false
            )
        );

        $this->pdfDocument
            ->setFontRegular(10)
            ->drawText($dateText)
            ->deltaY(2);

        return $this;
    }

    /**
     * Insert address
     *
     * @param RequestPrintLabelInterface $address
     * @return $this
     */
    private function insertAddress($address)
    {
        $this->pdfDocument
            ->drawLine(550)
            ->deltaY(20)
            ->setFontRegular(18)
            ->drawBlock(__('Return address'))
            ->deltaY(20);

        $region = $this->regionAddressResolver
            ->getRegion($address->getRegionId(), $address->getRegion(), $address->getCountryId());
        $this->pdfDocument
            ->setFontRegular(12)
            ->drawBlock($this->fullNameAddressResolver->getFullName($address))
            ->deltaY(15)
            ->drawBlock(implode(' ', $address->getStreet()))
            ->deltaY(15)
            ->drawBlock(sprintf('%s, %s, %s', $address->getCity(), $region, $address->getPostcode()))
            ->deltaY(15)
            ->drawBlock($this->countryAddressResolver->getCountry($address->getCountryId()))
            ->deltaY(15)
            ->drawBlock($address->getTelephone());

        if (!empty($address->getCustomAttributes())) {
            $this->insertAddressCustomAttributes($address);
        }

        return $this;
    }

    /**
     * Insert address custom attributes
     *
     * @param RequestPrintLabelInterface $address
     * @return $this
     */
    private function insertAddressCustomAttributes($address)
    {
        $this->pdfDocument->setFontRegular(12);
        foreach ($address->getCustomAttributes() as $customAttribute) {
            if (empty($customAttribute->getValue())) {
                continue;
            }

            $this->pdfDocument
                ->deltaY(15)
                ->drawBlock($customAttribute->getValue());
        }

        return $this;
    }

    /**
     * Insert request details
     *
     * @param RequestInterface $request
     * @param int|null $storeId
     * @return $this
     */
    private function insertDetails($request, $storeId)
    {
        $blockLen = 48;
        $addToX = 280;
        $orderText = __('Order ID: #%1', $this->orderResolver->getIncrementId($request->getOrderId()));
        $this->pdfDocument
            ->deltaY(20)
            ->setFontRegular(18)
            ->drawBlock(__('Details'), $blockLen, $addToX)
            ->deltaY(20);

        $this->pdfDocument
            ->setFontRegular(12)
            ->drawBlock($orderText, $blockLen, $addToX)
            ->deltaY(12);

        foreach ($request->getCustomFields() as $customField) {
            if (!$this->customFieldResolver->isDisplayOnShippingLabel($customField->getFieldId(), $storeId)) {
                continue;
            }

            $value = $this->customFieldResolver
                ->getValue($customField->getFieldId(), $customField->getValue(), $storeId);
            $label = $this->customFieldResolver->getLabel($customField->getFieldId(), $storeId);

            $this->pdfDocument
                ->drawMultiLineText(sprintf('%s: %s', $label, $value), $blockLen, $addToX)
                ->deltaY(12);
        }

        return $this;
    }

    /**
     * Insert order item details
     *
     * @param RequestInterface $request
     * @param int|null $storeId
     * @return $this
     */
    private function insertItems($request, $storeId)
    {
        $this->insertOrderItemsHeader();
        $this->pdfDocument
            ->setFontRegular(12)
            ->deltaY(10)
            ->drawLine(550)
            ->deltaY(10);

        foreach ($request->getOrderItems() as $item) {
            $additionalDeltaY = $this->pdfDocument->getY();
            $this->pdfDocument->deltaY(5);
            $this->insertOrderItemRow($item, $storeId, $additionalDeltaY);
            $this->pdfDocument->setY($additionalDeltaY - 12);
            $this->pdfDocument
                ->drawLine(550)
                ->deltaY(12);
        }

        return $this;
    }

    /**
     * Insert order item header
     *
     * @return $this
     */
    private function insertOrderItemsHeader()
    {
        $this->pdfDocument
            ->deltaY(24)
            ->setFontRegular(18)
            ->drawText(__('Items RMA requested for'))
            ->deltaY(24)
            ->setFontBold(12);

        $offsetX = 10;
        $columns = $this->getOrderItemsColumns();
        foreach ($columns as $column) {
            $this->pdfDocument->drawBlock(__($column['caption']), $column['width'], $offsetX);
            $offsetX += $column['width'];
        }

        return $this;
    }

    /**
     * Insert order item row
     *
     * @param RequestItemInterface $item
     * @param int $storeId
     * @param int $additionalDeltaY
     * @return $this
     */
    private function insertOrderItemRow($item, $storeId, &$additionalDeltaY)
    {
        $offsetX = 10;
        $count = 0;
        $columns = $this->getOrderItemsColumns();
        $columnCount = count($columns);
        foreach ($columns as $fieldName => $column) {
            $yPos = $this->pdfDocument->getY();
            $this->pdfDocument->drawMultiLineText(
                $this->getOrderItemText($item, $fieldName),
                $column['text_width'],
                $offsetX
            );
            $offsetX += $column['width'];
            $count++;
            if ($additionalDeltaY > $this->pdfDocument->getY()) {
                $additionalDeltaY = $this->pdfDocument->getY();
            }
            if ($fieldName == 'name') {
                $this->pdfDocument->deltaY($additionalDeltaY - $this->pdfDocument->getY() + 12);
                $this->insertOrderItemCustomField($item, $storeId, $additionalDeltaY);
            }
            if ($count < $columnCount) {
                $this->pdfDocument->setY($yPos);
            }
        }

        return $this;
    }

    /**
     * Insert order item custom fields
     *
     * @param RequestItemInterface $item
     * @param int $storeId
     * @param int $additionalDeltaY
     * @return $this
     */
    private function insertOrderItemCustomField($item, $storeId, &$additionalDeltaY)
    {
        $customFieldValueBlockLen = 80;
        foreach ($item->getCustomFields() as $customField) {
            $offsetX = 20;
            if ($additionalDeltaY >= $this->pdfDocument->getY()) {
                $additionalDeltaY = $this->pdfDocument->getY() - 12;
            }
            $value = $this->customFieldResolver
                ->getValue($customField->getFieldId(), $customField->getValue(), $storeId);
            $label = $this->customFieldResolver->getLabel($customField->getFieldId(), $storeId);

            $this->pdfDocument
                ->setFontBold(12)
                ->drawBlock(sprintf('%s: ', $label), $customFieldValueBlockLen, $offsetX)
                ->deltaY(12)
                ->setFontRegular(12)
                ->drawMultiLineText($value, $customFieldValueBlockLen, $offsetX + 10)
                ->deltaY(12);
        }

        return $this;
    }

    /**
     * Retrieve order items columns
     *
     * @return array
     */
    private function getOrderItemsColumns()
    {
        return [
            'name' => ['caption' => 'Product Name', 'width' => 380, 'text_width' => 70],
            'sku' => ['caption' => 'SKU', 'width' => 80, 'text_width' => 10],
            'qty' => ['caption' => 'Qty', 'width' => 60, 'text_width' => 10],
        ];
    }

    /**
     * Retrieve text by field name from request item
     *
     * @param RequestItemInterface $item
     * @param string $fieldName
     * @return string
     */
    private function getOrderItemText($item, $fieldName)
    {
        if ($fieldName == 'qty') {
            $text = $item->getQty();
        } else {
            $fieldName = 'get' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($fieldName);
            $text = $this->orderItemResolver->{$fieldName}($item->getItemId());
        }

        return $text;
    }
}
