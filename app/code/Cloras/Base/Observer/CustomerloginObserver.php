<?php

namespace Cloras\Base\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class CustomerloginObserver implements ObserverInterface
{
    private $customerSession;
    
    private $checkoutSession;

    private $helper;

    private $productloader;

    private $quoteRepository;

    public function __construct(
        \Magento\Customer\Model\SessionFactory $customerSession,
        Session $checkoutSession,
        \Cloras\Base\Helper\Data $helper,
        \Magento\Catalog\Model\ProductFactory $productloader,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->helper          = $helper;
        $this->productloader   = $productloader;
        $this->quoteRepository = $quoteRepository;
    }//end __construct()

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customerSession = $this->customerSession->create();
        list($status, $customerData, $integrationData, $filterBy) = $this->helper->isServiceEnabled('dynamic_pricing');
        if ($status) {
            $itemId         = '';
            $qty            = 0;
            $quoteId        = 0;
            $sessionDetails = [];
            $sessionItems   = $this->checkoutSession->loadCustomerQuote()->getQuote()->getAllItems();
            $sessionItemIds = [];
            $items          = [];
           
            list($sessionDetails, $sessionItemIds) = $this->getProductSessionDetails(
                $sessionItems,
                $sessionItemIds,
                $sessionDetails,
                $filterBy
            );

            if (!empty($sessionDetails)) {
                $productIds = array_keys($sessionDetails);

                $sessionPrices = [];
                if (!empty($customerSession->getClorasCustomPrice())) {
                    $sessionPrices = $customerSession->getClorasCustomPrice();
                }

                $items = $this->helper->getProductItems(
                    $productIds,
                    $sessionPrices,
                    $qty,
                    $filterBy
                );

                if (!empty($sessionItemIds) && !empty($items)) {
                    $quoteItemIds = array_keys($sessionItemIds);

                    $quoteId = $this->getQuoteId($quoteItemIds, $customerData, $integrationData, $items);

                    if ($quoteId) {
                        $quote = $this->quoteRepository->get($quoteId);
                        $this->quoteRepository->save($quote->collectTotals());
                    }
                }//end if
            }//end if
        }//end if
    }//end execute()

    private function getProductSessionDetails($sessionItems, $sessionItemIds, $sessionDetails, $filterBy)
    {
        foreach ($sessionItems as $session_item) {
            $qty       = (int) $session_item['qty'];
            $productId = $session_item['product_id'];
            $sku       = $session_item['sku'];
            if ($productId) {
                $product = $this->getProduct($productId);
                $sku     = $product->getSku();
                if ($filterBy != 'sku') {
                    if (is_object($product->getCustomAttribute($filterBy))) {
                           $sku = $product->getCustomAttribute($filterBy)->getValue();
                    }
                }

                $quote_item_id = $session_item['item_id'];

                $sessionDetails[$productId] = [$sku => $qty];

                $sessionItemIds[$session_item['item_id']] = [
                'sku'      => $sku,
                'qty'      => $qty,
                'quote_id' => $session_item['quote_id'],
                ];
            }
        }//end foreach

        return [
            $sessionDetails,
            $sessionItemIds
        ];
    }

    private function getQuoteId($quoteItemIds, $customerData, $integrationData, $items)
    {
        $quoteId = "";
        foreach ($quoteItemIds as $quoteItemId => $quoteItem) {
            $itemId = $quoteItem['sku'];
            $qty    = $quoteItem['qty'];
            $price = $this->helper->fetchDynamicPrice(
                $customerData,
                $integrationData,
                $items,
                $itemId,
                $qty
            );
            if (!empty($price)) {
                if (array_key_exists($itemId, $price)) {
                    $this->saveCheckoutSession($quoteItemId, $price, $itemId);
                    $quoteId = $quoteItem['quote_id'];
                }
            }
        }
        return $quoteId;
    }

    private function getProduct($productId)
    {
        $product = $this->productloader->create()->load($productId);
        return $product;
    }

    private function saveCheckoutSession($quoteItemId, $price, $itemId)
    {
        $customprice = $this->checkoutSession->getQuote()->getItemById($quoteItemId);
        $customprice->setCustomPrice($price[$itemId]['price']);
        $customprice->setOriginalCustomPrice($price[$itemId]['price']);
        $customprice->save();
    }
}//end class
