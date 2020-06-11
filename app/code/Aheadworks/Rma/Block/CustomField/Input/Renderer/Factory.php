<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\CustomField\Input\Renderer;

use Aheadworks\Rma\Api\Data\CustomFieldInterface;
use Aheadworks\Rma\Block\CustomField\Label;
use Aheadworks\Rma\Block\CustomField\Wrapper;
use Aheadworks\Rma\Model\CustomField\Renderer\Frontend\Mapper;
use Aheadworks\Rma\Model\Source\CustomField\Type;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\LayoutFactory;

/**
 * Class Factory
 *
 * @package Aheadworks\Rma\Block\CustomField\Input\Renderer
 */
class Factory
{
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var Mapper
     */
    private $mapper;

    /**
     * @var array
     */
    private $customFieldRendererMap = [
        Type::TEXT => Text::class,
        Type::TEXT_AREA => TextArea::class,
        Type::MULTI_SELECT => MultiSelect::class,
        Type::SELECT => Select::class
    ];

    /**
     * @param LayoutFactory $layoutFactory
     * @param Mapper $mapper
     */
    public function __construct(
        LayoutFactory $layoutFactory,
        Mapper $mapper
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->mapper = $mapper;
    }

    /**
     * Return newly created instance of a order item
     *
     * @param CustomFieldInterface $customField
     * @param int $requestStatus
     * @param string $name
     * @param string|array|null $value
     * @return BlockInterface
     */
    public function create($customField, $requestStatus, $name, $value = null)
    {
        $data = $this->mapper->map($customField, $requestStatus, $name);
        $data['value'] = $value;
        $wrapperInstance = $this->layoutFactory->create()->createBlock(
            Wrapper::class,
            '',
            ['data' => $data]
        );
        $wrapperInstance->addChild(
            $data['uid'],
            $this->getCustomFieldRenderer($data['is_editable'], $customField->getType()),
            $data
        );

        return $wrapperInstance;
    }

    /**
     * Retrieve custom field renderer
     *
     * @param bool $isEditable
     * @param int $type
     * @return string
     */
    private function getCustomFieldRenderer($isEditable, $type)
    {
        if ($isEditable) {
            $renderer = $this->customFieldRendererMap[$type];
        } else {
            $renderer = Label::class;
        }

        return $renderer;
    }
}
