<?php

namespace Dckap\Theme\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RemoveBlock implements ObserverInterface
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_request = $request;
    }

    public function execute(Observer $observer)
    {
        $layout = $observer->getLayout();
        $remove = true;
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $website_mode = $this->_scopeConfig->getValue('themeconfig/mode_config/website_mode', $storeScope);
        if ($website_mode != 'b2c') {
            $remove = false;
        }
        if ($remove) {
            $layout->unsetElement('request_for_quote_button');
        }

       /* if ($this->_request->getFullActionName() == 'catalog_product_view') {
            $layout->unsetElement('breadcrumbs');
        }*/
    }
}
