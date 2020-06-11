<?php
/**
 * Copyright 2019 aheadWorks. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Model\Request\PrintLabel\Pdf;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\ReadInterface;

/**
 * Class Document
 *
 * @package Aheadworks\Rma\Model\Request\PrintLabel\Pdf
 */
class Document
{
    /**#@+
     * Constants defined for pdf document
     */
    const X_OFFSET = 25;
    const BOTTOM_OFFSET = 25;
    const CHARSET = 'UTF-8';
    /**#@-*/

    /**
     * @var int
     */
    private $y;

    /**
     * @var \Zend_Pdf
     */
    private $pdf;

    /**
     * @var \Zend_Pdf_Page
     */
    private $page;

    /**
     * @var WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var ReadInterface
     */
    private $rootDirectory;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->rootDirectory = $filesystem->getDirectoryRead(DirectoryList::ROOT);
        $this->pdf = new \Zend_Pdf();
    }

    /**
     * Retrieve completed PDF to a string
     *
     * @return string
     */
    public function renderPdf()
    {
        return $this->pdf->render();
    }

    /**
     * Create new page
     *
     * @return $this
     */
    public function createNewPage()
    {
        $oldPage = $this->page;
        $this->y = 800;
        $this->page = $this->pdf->newPage(\Zend_Pdf_Page::SIZE_A4);

        if (null !== $oldPage) {
            $this->page->setFont($oldPage->getFont(), $oldPage->getFontSize());
        }

        $this->addPageToPdf();

        return $this;
    }

    /**
     * Draw text
     *
     * @param string $text
     * @param float $addToX
     * @param string $charset
     * @return $this
     */
    public function drawText($text, $addToX = 0, $charset = self::CHARSET)
    {
        $x = self::X_OFFSET + $addToX;
        $this->page->drawText($text, $x, $this->y, $charset);

        return $this;
    }

    /**
     * Draw block
     *
     * @param string $text
     * @param float $blockLen
     * @param float $addToX
     * @param float $yStep
     * @param string $charset
     * @return $this
     */
    public function drawBlock($text, $blockLen = 50, $addToX = 0, $yStep = 12, $charset = self::CHARSET)
    {
        $count = 0;
        $x = self::X_OFFSET + $addToX;
        $text = wordwrap($text, $blockLen, '\n');
        $lines = explode('\n', $text);
        $fullCountOfLines = count($lines);
        foreach ($lines as $line) {
            if (strlen($line) > $blockLen) {
                $lineParts = str_split($line, $blockLen);
                $fullCountOfLines += count($lineParts);
                foreach ($lineParts as $linePart) {
                    $this->page->drawText($linePart, $x, $this->y, $charset);
                    if ($count++ < count($lineParts)) {
                        $this->deltaY($yStep);
                    }
                }
            } else {
                $this->page->drawText($line, $x, $this->y, $charset);
                if (++$count < $fullCountOfLines) {
                    $this->deltaY($yStep);
                }
            }
        }

        return $this;
    }

    /**
     * Draw multi line text
     *
     * @param string $text
     * @param float $blockLen
     * @param float $addToX
     * @param float $yStep
     * @return $this
     */
    public function drawMultiLineText($text, $blockLen = 50, $addToX = 0, $yStep = 12)
    {
        foreach (explode('\r\n', $text) as $str) {
            $this->drawBlock(strip_tags(ltrim($str)), $blockLen, $addToX, $yStep);
        }

        return $this;
    }

    /**
     * Draw line
     *
     * @param float $width
     * @return $this
     */
    public function drawLine($width)
    {
        $this->page->drawLine(self::X_OFFSET, $this->y, $width, $this->y);
        return $this;
    }

    /**
     * Set regular font
     *
     * @param  int $size
     * @return $this
     */
    public function setFontRegular($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Re-4.4.1.ttf')
        );
        $this->page->setFont($font, $size);

        return $this;
    }

    /**
     * Set bold font
     *
     * @param  int $size
     * @return $this
     */
    public function setFontBold($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf')
        );
        $this->page->setFont($font, $size);

        return $this;
    }

    /**
     * Set italic font
     *
     * @param  int $size
     * @return $this
     */
    public function setFontItalic($size = 7)
    {
        $font = \Zend_Pdf_Font::fontWithPath(
            $this->rootDirectory->getAbsolutePath('lib/internal/LinLibertineFont/LinLibertine_It-2.8.2.ttf')
        );
        $this->page->setFont($font, $size);

        return $this;
    }

    /**
     * Add delta value to y
     *
     * @param $value
     * @return $this
     */
    public function deltaY($value)
    {
        $this->y -= $value;
        if ($this->y < self::BOTTOM_OFFSET) {
            $this->createNewPage();
        }

        return $this;
    }

    /**
     * Retrieve y pos
     *
     * @return float
     */
    public function getY()
    {
        return $this->y;
    }

    /**
     * Set y pos
     *
     * @param float $y
     * @return $this
     */
    public function setY($y)
    {
        $this->y = $y;

        return $this;
    }

    /**
     * Add current page to pdf document
     *
     * @return $this
     */
    private function addPageToPdf()
    {
        $this->pdf->pages[] = $this->page;

        return $this;
    }
}
