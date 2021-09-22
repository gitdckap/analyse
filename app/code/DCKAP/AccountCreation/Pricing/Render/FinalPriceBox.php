<?php

namespace Dckap\AccountCreation\Pricing\Render;

use Magento\Catalog\Pricing\Price;
use Magento\Framework\Pricing\Render\PriceBox as BasePriceBox;
use Magento\Msrp\Pricing\Price\MsrpPrice;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Pricing\Price\MinimalPriceCalculatorInterface;

class FinalPriceBox extends \Magento\Catalog\Pricing\Render\FinalPriceBox
{
    protected $httpContext;
    protected $scopeConfig;

    public function __construct(
        Context $context,
        SaleableInterface $saleableItem,
        PriceInterface $price,
        RendererPool $rendererPool,
        array $data = [],
        SalableResolverInterface $salableResolver = null,
        MinimalPriceCalculatorInterface $minimalPriceCalculator = null,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver,
            $minimalPriceCalculator
        );
        $this->httpContext = $httpContext;
        $this->scopeConfig = $scopeConfig;
    }

    protected function wrapResult($html)
    {
        
        $result = '<div class="price-box ' . $this->getData('css_classes') . '" ' .
            'data-role="priceBox" ' .
            'data-product-id="' . $this->getSaleableItem()->getId() . '" ' .
            'data-price-box="product-id-' . $this->getSaleableItem()->getId() . '"' .
            '>' . $html . '</div>';

        $value =  $this->scopeConfig->getValue(
            'dckapextension/BitExpert_ForceCustomerLogin/price_display',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($value) {
            return $result;
        } else {
            $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
            if (!$isLoggedIn) {
                return '';
            }
        }
        return $result;
    }
}
