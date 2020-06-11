<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Renderer;

use Aheadworks\Rma\Model\Source\Config\Cms\Block as BlockConfig;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class CmsBlock
 *
 * @package Aheadworks\Rma\Model\Renderer
 */
class CmsBlock
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BlockRepositoryInterface
     */
    private $cmsBlockRepository;

    /**
     * @var FilterProvider
     */
    private $cmsFilterProvider;

    /**
     * @var array
     */
    private $cmsBlockHtml = [];

    /**
     * @param StoreManagerInterface $storeManager
     * @param BlockRepositoryInterface $cmsBlockRepository
     * @param FilterProvider $cmsFilterProvider
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        BlockRepositoryInterface $cmsBlockRepository,
        FilterProvider $cmsFilterProvider
    ) {
        $this->storeManager = $storeManager;
        $this->cmsBlockRepository = $cmsBlockRepository;
        $this->cmsFilterProvider = $cmsFilterProvider;
    }

    /**
     * Retrieve html code by id
     *
     * @param int $bockId
     * @param int|null $storeId
     * @return string
     */
    public function render($bockId, $storeId = null)
    {
        if (!$storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $cacheKey = $bockId . '-' . $storeId;
        if (!isset($this->cmsBlockHtml[$cacheKey])) {
            $cmsBlockHtml = '';
            if ($bockId && $bockId != BlockConfig::DONT_DISPLAY) {
                $cmsBlock = $this->cmsBlockRepository->getById($bockId);
                if ($cmsBlock->isActive()) {
                    $cmsBlockHtml = $this->cmsFilterProvider
                        ->getBlockFilter()
                        ->setStoreId($storeId)
                        ->filter($cmsBlock->getContent());
                }
            }
            $this->cmsBlockHtml[$cacheKey] = $cmsBlockHtml;
        }
        return $this->cmsBlockHtml[$cacheKey];
    }
}
