<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cloras\DDI\Plugin;

class ProductPlugin
{
    private $logger;

    private $customerSession;

    private $registry;

    private $customerModel;

    private $dir;
    protected $scopeConfig;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Cloras\DDI\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Magento\Catalog\Model\Product $productRepository,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->logger            = $logger;
        $this->helper            = $helper;
        $this->request           = $request;
        $this->registry          = $registry;
        $this->customerModel     = $customerModel;
        $this->customerSession   = $customerSession;
        $this->dir               = $dir;
        $this->productRepository = $productRepository;
        $this->pricingHelper = $pricingHelper;
        $this->extensionHelper = $extensionHelper;
        $this->scopeConfig = $scopeConfig;
    }//end __construct()

    public function isB2B()
    {
        $configValue = $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        if($configValue=="b2b")
            return true;
        else
            return false;

    }
    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        if ($this->extensionHelper->getIsLogger() && $this->isB2B()) {
            $this->logger->info('product plugin started');
        }
        $items = [];

        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct) {
            $crossSellProducts = $currentProduct->getCrossSellProducts();
            $relatedProducts   = $currentProduct->getRelatedProducts();
            $upSellProducts    = $currentProduct->getUpSellProducts();

            if (!empty($crossSellProducts)) {
                foreach ($crossSellProducts as $crossSellProduct) {
                    if ($subject->getSku() == $crossSellProduct->getSku()) {
                        return $result;
                    }
                }
            }

            if (!empty($upSellProducts)) {
                foreach ($upSellProducts as $upSellProduct) {
                    if ($subject->getSku() == $upSellProduct->getSku()) {
                        return $result;
                    }
                }
            }

            if (!empty($relatedProducts)) {
                foreach ($relatedProducts as $relatedProduct) {
                    if ($subject->getSku() == $relatedProduct->getSku()) {
                        return $result;
                    }
                }
            }
        }//end if

        $excludedPages = [
            'catalog_category_view',
            'catalogsearch_result_index',
            'cms_index_index',
        ];

        if (!in_array($this->request->getFullActionName(), $excludedPages) && $this->customerSession->isLoggedIn()) {
            list($status, $p21CustomerId, $integrationData, $filterBy) = $this->helper->isServiceEnabled(
                'dynamic_pricing_and_inventory'
            );

            

            if ($status) {
                $itemId = $subject->getSku();
                if ($filterBy != 'sku') {
                    if (is_object($subject->getCustomAttribute($filterBy))) {
                        $itemId = $subject->getCustomAttribute($filterBy)->getValue();
                    }
                }

                $qty = 1;
                
                $sessionPrices = [];
                if (!empty($this->customerSession->getClorasCustomPrice())) {
                    $sessionPrices = $this->customerSession->getClorasCustomPrice();
                }
                /*print_r($sessionPrices);
                exit()*/;

                if (!array_key_exists($itemId, $sessionPrices) || $qty > 1) {
                    $uom          = '';
                    $inv_mast_uid = '';

                    if ($subject->getTypeId() == 'simple') {
                        $productIds[] = $subject->getId();
                        $items        = $this->helper->getProductItems(
                            $productIds,
                            $sessionPrices,
                            $qty,
                            $filterBy
                        );
                    }//end if

                
                    if (!empty($items)) {
                        $price = $this->helper->fetchDynamicPrice(
                            $p21CustomerId,
                            $integrationData,
                            $items,
                            $itemId,
                            $qty
                        );
                        // check the count and price exist in price
                        if (!empty($price)) {
                            if (!$this->registry->registry('prices_qty')) {
                                $this->registry->register('prices_qty', $price);
                            }
                                    
                                    
                            if (array_key_exists($itemId, $price)) {
                                if (round($price[$itemId]['price']) != 0) {
                                    return number_format($price[$itemId]['price'], 4, ".", "");
                                }
                            }
                        }
                    }
                } else {
                    /*print_r($sessionPrices);
                    exit();*/
                    if (isset($sessionPrices) && array_key_exists($itemId, $sessionPrices)) {
                        //set qty if session prices are available
                        if (!$this->registry->registry('prices_qty')) {
                            $this->registry->register('prices_qty', $sessionPrices);
                        }
                        
                        
                        if (array_key_exists('price', $sessionPrices[$itemId])) {
                            if ($sessionPrices[$itemId]['price'] > 0) {
                                return number_format($sessionPrices[$itemId]['price'], 4, ".", "");
                            }
                        }
                    }
                }//end if
            }//end if
        }//end if
        if ($this->extensionHelper->getIsLogger() && $this->isB2B()) {
            $this->logger->info('product plugin started');
        }
        return number_format($result, 4);
    }//end afterGetPrice()
}//end class
