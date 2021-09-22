<?php
namespace Dckap\Theme\Block;

class Template extends \Magento\Framework\View\Element\Template
{
    public $_coreRegistry;
    protected $_storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }
    
    public function getConfig($config_path, $storeCode = null)
    {
        return $this->_scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
    public function isHomePage()
    {
        $currentUrl = $this->getUrl('', ['_current' => true]);
        $urlRewrite = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        return $currentUrl == $urlRewrite;
    }
    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }
}
