<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cloras\DDI\Plugin;

class CategoryPlugin
{
    protected $logger;
    protected $helper;
    protected $session;
    
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\SessionFactory $session,
        \Cloras\DDI\Helper\Data $helper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem
    ) {
        $this->logger = $logger;
        $this->session = $session;
        $this->helper = $helper;
        $this->stockItem = $stockItem;
    }
    public function afterGetLoadedProductCollection(\Magento\Catalog\Block\Product\ListProduct $subject, $result)
    {
		$this->logger->info('get product collection on category plugin started');

        $customerSession = $this->session->create();
        
        if ($customerSession->isLoggedIn()) {
            list($status, $p21CustomerId, $integrationData, $filterBy) = $this->helper->isServiceEnabled(
                'dynamic_pricing_and_inventory'
            );
            $productIds = [];
            if ($status) {
                foreach ($result as $product) {
                    $productIds[] = $product->getId();
                }
				$sessionPrices = [];
				if (!empty($customerSession->getClorasCustomPrice())) {
					$sessionPrices = $customerSession->getClorasCustomPrice();
				}
				//$this->logger->info($sessionPrices);
                $items = $this->helper->getProductItems(
                    $productIds,
                    $sessionPrices,
                    $qty = 1,
                    $filterBy
                );

                
                if (!empty($items)) {
                    $price = $this->helper->fetchDynamicPrice(
                        $p21CustomerId,
                        $integrationData,
                        $items,
                        $itemId = 0,
                        $qty = 1
                    );
                }
            }
        }
        $this->logger->info('get product collection on category plugin end');
		return $result;
        
    }
}
