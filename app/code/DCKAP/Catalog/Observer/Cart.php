<?php

namespace DCKAP\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class Cart implements ObserverInterface
{
    private $logger;
    private $customerSession;
    private $productRepository;
    private $serializer;
    protected $dckapCatalogHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \DCKAP\Catalog\Helper\Data $dckapCatalogHelper
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->dckapCatalogHelper = $dckapCatalogHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('add to cart after observer working fine');
        $price_quote = '';
        $quoteitem = $observer->getEvent()->getData('quote_item');
        $product = $observer->getEvent()->getData('product');
        $sku = $product->getSku();
        $cartQty = $quoteitem->getQty();
        $custPrice = 0.0;
        $this->logger->info('sku - ' . $sku);
        $this->logger->info('qty - ' . $quoteitem->getQty());
//        $sessionProductData = $this->customerSession->create()->getProductData();

        $uom = 'EA';
        if ($additionalOptions = $quoteitem->getOptionByCode('additional_options')) {
            $additionalOption = (array) $this->serializer->unserialize($additionalOptions->getValue());
            if (isset($additionalOption['custom_uom'])) {
                $uom = $additionalOption['custom_uom']['value'];
            }
            if (isset($additionalOption['quote'])) {
                $price_quote = isset($additionalOption['quote']['value']) ? $additionalOption['quote']['value'] : '';
            }
        }
        $this->logger->info('uom - '.$uom);
        $sessionProductData = $this->dckapCatalogHelper->getSessionProductsData($sku);
        if ($sessionProductData && isset($sessionProductData[$sku])) {
            if (isset($sessionProductData[$sku]['prices']['netPrice'])) {
                $custPrice = (float)$sessionProductData[$sku]['prices']['netPrice'];
            }
            if ($cartQty > 1 && isset($sessionProductData[$sku]['lineItem']['uom']['uomCode']) && $uom == $sessionProductData[$sku]['lineItem']['uom']['uomCode']) {
                if (isset($sessionProductData[$sku]['prices']['priceBreak'])) {
                    $tierPrices = $sessionProductData[$sku]['prices']['priceBreak']['breakLines'];
                    if ($tierPrices && !empty($tierPrices)) {
                        foreach ($tierPrices as $tierPrice) {
                            if ($cartQty >= (int)$tierPrice['breakQty']) {
                                $custPrice = (float)$tierPrice['unitPrice'];
                            }
                        }
                    }
                }
            } else {
                if (isset($sessionProductData[$sku]['lineItem']['uom']['uomFactors'])) {
                    foreach ($sessionProductData[$sku]['lineItem']['uom']['uomFactors'] as $uomFactor) {
                        if ($uom == $uomFactor['altUomCode']) {
                            $custPrice = (float)$uomFactor['price'];
                        }
                    }
                }
            }
        }
        if ($price_quote) {
            $sessionQuoteProductData = $this->dckapCatalogHelper->getSessionQuoteProductData($sku.'_'.$uom);
            if ($sessionQuoteProductData && isset($sessionQuoteProductData['netPrice'])) {
                $custPrice = (float)str_replace('$', '', str_replace(',', '', $sessionQuoteProductData['netPrice']));
            }
        }

        $this->logger->info('custPrice - '.$custPrice);
        if (round($custPrice) != 0 || $custPrice > 0) {
            $quoteitem->setCustomPrice($custPrice);
            $quoteitem->setOriginalCustomPrice($custPrice);
            $this->logger->info('pricesetup success - '.$custPrice);
            $quoteitem->getProduct()->setIsSuperMode(true);
            return $this;
        }
    }
}
