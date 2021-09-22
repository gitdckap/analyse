<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Cloras\Base\Plugin;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;

class ProductPlugin
{
    private $logger;

    private $customerSession;

    private $registry;

    private $customerModel;

    private $dir;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        Session $customerSession,
        \Cloras\Base\Helper\Data $helper,
        Http $request,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Magento\Catalog\Model\Product $productRepository,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $groupedProduct
    ) {
        $this->logger            = $logger;
        $this->helper            = $helper;
        $this->request           = $request;
        $this->registry          = $registry;
        $this->customerModel     = $customerModel;
        $this->customerSession   = $customerSession;
        $this->dir               = $dir;
        $this->productRepository = $productRepository;
        $this->priceCurrency = $priceCurrency;
        $this->groupedProduct = $groupedProduct;
    }

    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
        
        $items = [];
    
        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct) {
            $this->skipProducts($subject, $currentProduct, $result);
        }//end if

        $excludedPages = [
            'catalog_category_view',
            'catalogsearch_result_index',
            'cms_index_index',
        ];
        if (!in_array($this->request->getFullActionName(), $excludedPages) && $this->customerSession->isLoggedIn()) {
            /*list($status, $p21CustomerId, $integrationData, $filterBy) = $this->helper->isServiceEnabled(
                'dynamic_pricing'
            );*/
            
            list($status, $customerData, $integrationData, $filterBy, $isLoggedIn) = $this->helper->isServiceEnabled(
                \Cloras\Base\Helper\Data::SERVICE_TYPE
            );
            $this->logger->info('product plugin started', (array)$status);
            if ($status) {
                $itemId = $subject->getSku();
                if ($filterBy != 'sku') {
                    if (is_object($subject->getCustomAttribute($filterBy))) {
                        $itemId = $subject->getCustomAttribute($filterBy)->getValue();
                    }
                }
                $this->logger->info('product item id: ', (array)$itemId);
                $qty = 1;
                $includedPages = ['quoteproducts_index_index'];
                if (in_array($this->request->getFullActionName(), $includedPages)) {
                    $quote_items = $this->customerSession->getData('quote_items');

                    if (is_array($quote_items)) {
                        if (array_key_exists($itemId, $quote_items)) {
                            $qty = $quote_items[$itemId];
                        }
                    }
                }

                $sessionPrices = [];
                if (!empty($this->customerSession->getClorasCustomPrice())) {
                    //$sessionPrices = $this->customerSession->getClorasCustomPrice();
                }
                
                $result = $this->getPriceInfo(
                    $itemId,
                    $sessionPrices,
                    $qty,
                    $subject,
                    $filterBy,
                    $customerData,
                    $integrationData,
                    $isLoggedIn
                );
            }//end if
        }//end if
    
        return $result;
    }

    /*
    * skip the cross sell, related, upsell products
    */
    private function skipProducts($subject, $currentProduct, $result)
    {

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
    }

    private function getProductIdsByType($subject)
    {
        
        $productIds = [];

        if ($subject->getTypeId() == 'simple') {
            $productIds[] = $subject->getId();
        } elseif ($subject->getTypeId() == 'grouped') {
            $childProductIds = $this->groupedProduct->getChildrenIds($subject->getId());

            
            if (!empty($childProductIds)) {
                $filterKey = key($childProductIds);
                if (!empty($filterKey)) {
                    $productIds = $childProductIds[$filterKey];
                }
            }
        }//end if

        return $productIds;
    }

    private function getPriceInfo(
        $itemId,
        $sessionPrices,
        $qty,
        $subject,
        $filterBy,
        $customerData,
        $integrationData,
        $isLoggedIn
    ) {

        if (!array_key_exists($itemId, $sessionPrices) || $qty > 1) {
            $uom          = '';
            $inv_mast_uid = '';

            $productIds = $this->getProductIdsByType($subject);

            $items = $this->helper->getProductItems(
                $productIds,
                $sessionPrices,
                $qty,
                $filterBy
            );


            $this->logger->info('product items: ', (array)$items);

            if (!empty($items)) {
                $price = $this->helper->fetchDynamicPrice(
                    $customerData,
                    $integrationData,
                    $items,
                    $itemId,
                    $isLoggedIn,
                    $qty
                );
                $this->logger->info('Plugin ERP Price: ', (array)$price);

                if (!empty($price)) {
                    if (array_key_exists($itemId, $price) && array_key_exists('price', $price[$itemId])) {
                        if (round($price[$itemId]['price']) != 0) {
                            return number_format($price[$itemId]['price'], 4, ".", "");
                        }
                    }
                }
            }
        } else {
            if (isset($sessionPrices) && array_key_exists($itemId, $sessionPrices)) {
                if (array_key_exists('price', $sessionPrices[$itemId])) {
                    if ($sessionPrices[$itemId]['price'] > 0) {
                          return number_format($sessionPrices[$itemId]['price'], 4, ".", "");
                    }
                }
            }
        }//end if
    }
}//end class
