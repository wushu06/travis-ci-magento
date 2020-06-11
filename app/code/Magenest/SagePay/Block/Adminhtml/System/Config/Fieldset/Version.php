<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 18/01/2019
 * Time: 9:41
 */

namespace Magenest\SagePay\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Backend\Block\Template;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Module\Dir\Reader as DirReader;

class Version extends Template implements RendererInterface
{
    protected $dirReader;
    protected $directory_list;

    public function __construct(
        DirReader $dirReader,
        Template\Context $context,
        DirectoryList $directory_list,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->directory_list = $directory_list;
        $this->dirReader = $dirReader;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = '';
        if ($element->getData('group')['id'] == 'version') {
            $html = $this->toHtml();
        }
        return $html;
    }

    public function getVersion()
    {
        $installVersion = "unidentified";
        $composer = $this->getComposerInformation("Magenest_SagePay");

        if ($composer) {
            $installVersion = $composer['version'];
        }

        return $installVersion;
    }

    public function getComposerInformation($moduleName)
    {
        $dir = $this->dirReader->getModuleDir("", $moduleName);

        if (file_exists($dir.'/composer.json')) {
            return json_decode(file_get_contents($dir.'/composer.json'), true);
        }

        return false;
    }

    public function getTemplate()
    {
        return 'Magenest_SagePay::system/config/fieldset/version.phtml';
    }

    public function getDownloadDebugUrl()
    {
        return $this->getUrl('sagepay/config/downloadDebug', ['version'=>$this->getVersion()]);
    }

    public function getDebugFilePath() {
        try {
            return $this->directory_list->getPath("var") . "/log/sagepay/debug.log";
        } catch (FileSystemException $e) {
        }
    }
}
