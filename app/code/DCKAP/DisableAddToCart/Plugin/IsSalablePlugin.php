<?php

namespace DCKAP\DisableAddToCart\Plugin;

use Magento\Framework\App\Http\Context;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class IsSalablePlugin
 *
 * @category Plugin
 * @package  Bodak\DisableAddToCart\Plugin
 */
class IsSalablePlugin
{
    /**
     * Scope config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * HTTP Context
     * Customer session is not initialized yet
     *
     * @var Context
     */
    protected $context;

//    const DISABLE_ADD_TO_CART = 'catalog/frontend/catalog_frontend_disable_add_to_cart_for_guest';
    const DISABLE_ADD_TO_CART = 'dckapextension/BitExpert_ForceCustomerLogin/price_display';

    /**
     * SalablePlugin constructor.
     *
     * @param ScopeConfigInterface $scopeConfig ScopeConfigInterface
     * @param Context              $context     Context
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Context $context
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->context = $context;
    }

    /**
     * Check if is disable add to cart and if customer is logged in
     *
     * @return bool
     */
    public function afterIsSalable()
    {
        $scope = ScopeInterface::SCOPE_STORE;
        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
//        if (!$this->scopeConfig->getValue(self::DISABLE_ADD_TO_CART, $scope) &&  $configValue=="b2b") {
        if (!$this->scopeConfig->getValue(self::DISABLE_ADD_TO_CART, $scope)) {
            if ($this->context->getValue(CustomerContext::CONTEXT_AUTH)) {
                return true;
            }
            return false;
        }
        return true;
    }
}
