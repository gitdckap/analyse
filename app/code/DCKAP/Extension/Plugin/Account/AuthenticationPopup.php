<?php

namespace DCKAP\Extension\Plugin\Account;

use DCKAP\Extension\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AuthenticationPopup
{
    protected $extensionHelper;
    protected $scopeConfig;

    public function __construct(Data $extensionHelper, ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        $this->extensionHelper = $extensionHelper;
    }

    public function afterGetConfig(\Magento\Customer\Block\Account\AuthenticationPopup $authenticationPopup, $result)
    {
        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            ScopeInterface::SCOPE_WEBSITE
        );
        
            $result['is_b2c'] = $this->extensionHelper->checkIsB2c();
       
        return $result;
    }
}
