<?php

namespace Dckap\Theme\Model\Cssconfig;

class Generator
{
    protected $_messageManager;
    protected $_cssconfigData;
    protected $_coreRegistry;
    protected $_storeManager;
    protected $_layoutManager;
    protected $scopeConfig;
    protected $themeProvider;

    public function __construct(
        \Dckap\Theme\Helper\Cssconfig $cssconfigData,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutInterface $layoutManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Design\Theme\ThemeProviderInterface $themeProvider
    ) {
        $this->_cssconfigData = $cssconfigData;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        $this->_layoutManager = $layoutManager;
        $this->_messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->themeProvider = $themeProvider;
    }
    
    public function generateCss($type, $websiteId, $storeId)
    {
        if (!$websiteId && !$storeId) {
            $websites = $this->_storeManager->getWebsites(false, false);
            foreach ($websites as $id => $value) {
                $this->generateWebsiteCss($type, $id);
            }
        } else {
            if ($storeId) {
                $this->generateStoreCss($type, $storeId);
            } else {
                $this->generateWebsiteCss($type, $websiteId);
            }
        }
    }
    
    protected function generateWebsiteCss($type, $websiteId)
    {
        $website = $this->_storeManager->getWebsite($websiteId);
        foreach ($website->getStoreIds() as $storeId) {
            $this->generateStoreCss($type, $storeId);
        }
    }
    protected function generateStoreCss($type, $storeId)
    {
        $store = $this->_storeManager->getStore($storeId);
        if (!$store->isActive())
            return;
        $storeCode = $store->getCode();
        $str1 = '_'.$storeCode;
        $str2 = $type.$str1.'.css';
        $str3 = $this->_cssconfigData->getCssConfigDir().$str2;
        $str4 = 'dckap/css/'.$type.'.phtml';
        /* Here need to check the site whether it use b2c or b2b theme */
        $themeId = $this->scopeConfig->getValue(
            \Magento\Framework\View\DesignInterface::XML_PATH_THEME_ID,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store->getId()
        );
        $theme = $this->themeProvider->getThemeById($themeId);
        if ($theme->getCode() == "DCKAP/DDItheme2") {
            $str4 = 'dckap/css/b2cdesign.phtml';
        }
        $this->_coreRegistry->register('cssgen_store', $storeCode);
        try {
            $block = $this->_layoutManager->createBlock('Dckap\Theme\Block\Template')->setData('area', 'frontend')->setTemplate($str4)->toHtml();
            if (!file_exists($this->_cssconfigData->getCssConfigDir())) {
                @mkdir($this->_cssconfigData->getCssConfigDir(), 0777);
            }
            $file = @fopen($str3, "w+");
            @flock($file, LOCK_EX);
            @fwrite($file, $block);
            @flock($file, LOCK_UN);
            @fclose($file);
            if (empty($block)) {
                throw new \Exception(__("Template file is empty or doesn't exist: ".$str4));
            }
        } catch (\Exception $e) {
            $exception_error = 'Failed generating CSS file: '.$str2.' in '.$this->_cssconfigData->getCssConfigDir();
            $this->_messageManager->addError(__($exception_error).'<br/>Message: '.$e->getMessage());
        }
        $this->_coreRegistry->unregister('cssgen_store');
    }
}
