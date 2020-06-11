<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault\Reader;

use Magento\Framework\Config\Reader\Filesystem;
use Magento\Framework\Config\Dom;
use Magento\Framework\Config\FileResolverInterface;
use Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault\Converter\Xml as ConverterXml;
use Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault\SchemaLocator;
use Magento\Framework\Config\ValidationStateInterface;

/**
 * Class Xml
 *
 * @package Aheadworks\Rma\Model\CustomField\Option\Action\ConfigDefault\Reader
 */
class Xml extends Filesystem
{
    /**
     * @param FileResolverInterface $fileResolver
     * @param ConverterXml $converter
     * @param SchemaLocator $schemaLocator
     * @param ValidationStateInterface $validationState
     * @param string $fileName
     * @param array $idAttributes
     * @param string $domDocumentClass
     * @param string $defaultScope
     */
    public function __construct(
        FileResolverInterface $fileResolver,
        ConverterXml $converter,
        SchemaLocator $schemaLocator,
        ValidationStateInterface $validationState,
        $fileName = 'rma_actions.xml',
        $idAttributes = [],
        $domDocumentClass = Dom::class,
        $defaultScope = 'global'
    ) {
        parent::__construct(
            $fileResolver,
            $converter,
            $schemaLocator,
            $validationState,
            $fileName,
            $idAttributes,
            $domDocumentClass,
            $defaultScope
        );
    }
}
