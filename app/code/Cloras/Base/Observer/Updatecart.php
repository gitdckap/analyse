<?php

namespace Cloras\Base\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class Updatecart implements ObserverInterface
{
    private $registry = null;

    private $logger;

    private $helper;

    private $customerFactory;

    private $customerResourceFactory;

    private $customer;

    private $customerData;

    private $session;

    private $checkoutSession;

    private $productRepository;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger,
        \Cloras\Base\Helper\Data $helper,
        \Magento\Customer\Model\SessionFactory $session,
        Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->registry                = $registry;
        $this->logger                  = $logger;
        $this->helper                  = $helper;
        $this->session                 = $session;
        $this->checkoutSession         = $checkoutSession;
        $this->productRepository       = $productRepository;
    }//end __construct()

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getEvent()->getData('info');

        $cart = $observer->getEvent()->getData('cart');

        $price = [];

        $cusprice = 0;
        $sessionPrices = [];
        list($status, $customerData, $integrationData, $filterBy) = $this->helper->isServiceEnabled(
            'dynamic_pricing'
        );
        $convert_data = (array) $data;
       
        if ($status) {
            $convert_data = (array) $data;
           
                $cartKeyData = array_keys($convert_data);
        
                $this->setItemCustomPrice(
                    $convert_data,
                    $cartKeyData,
                    $sessionPrices,
                    $filterBy,
                    $customerData,
                    $integrationData
                );
        }//end if
    }//end execute()

    private function setItemCustomPrice(
        $convert_data,
        $cartKeyData,
        $sessionPrices,
        $filterBy,
        $customerData,
        $integrationData
    ) {
        foreach ($convert_data[$cartKeyData[0]] as $itemId => $itemInfo) {
            $item = $this->checkoutSession->getQuote()->getItemById($itemId);
            if (!$item) {
                continue;
            }

            if (!empty($itemInfo['remove']) || isset($itemInfo['qty']) && $itemInfo['qty'] == '0') {
                $this->removeItem($itemId);
                continue;
            }

            $items        = [];
            $proceed      = false;
            $productIds[] = $item->getProductId();
            $qty          = $itemInfo['qty'];
            $cusprice = 0;

            $items = $this->helper->getProductItems(
                $productIds,
                $sessionPrices,
                $qty,
                $filterBy
            );
        
            if (!empty($items)) {
                $proceed = true;
            }

            if ($proceed) {
                $itemId = $this->getItemId($item, $filterBy);
                // check session is active or not.
                $price = $this->helper->fetchDynamicPrice(
                    $customerData,
                    $integrationData,
                    $items,
                    $itemId,
                    $qty
                );

                if (!empty($price)) {
                    if (array_key_exists($itemId, $price)) {
                        if (array_key_exists('price', $price[$itemId])) {
                                $cusprice = $price[$itemId]['price'];
                        }
                    }
                }

                if (round($cusprice) != 0) {
                    $item->setOriginalCustomPrice($cusprice);
                    $item->setCustomPrice($cusprice);
                }
            }//end if
        }//end foreach
    }

    private function getItemId($item, $filterBy)
    {
        $product_obj = $this->productRepository->get($item->getSku());
        $itemId  = $product_obj->getSku();
        if ($filterBy != 'sku') {
            if (is_object($product_obj->getCustomAttribute($filterBy))) {
                $itemId = $product_obj->getCustomAttribute($filterBy)->getValue();
            }
        }

        return $itemId;
    }
}//end class
