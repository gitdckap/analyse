<?php

namespace DCKAP\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class Quoteafter implements ObserverInterface
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
        $this->logger->info('sales quote item add after observer working fine');
        $allitems = $observer->getEvent()->getData('items');
        foreach ($allitems as $quoteitem) {
            $custPrice = 0.0;
            $uom = 'EA';
            $sku = $quoteitem->getSku();
            $cartQty = $quoteitem->getQty();
            $protype = $quoteitem->getOptionByCode('product_type');
            if ($protype && $protype->getValue() == 'grouped') {
                $this->logger->info($protype->getValue());
                $this->logger->info('inside only grouped product condition');
                
                if ($additionalOptions = $quoteitem->getOptionByCode('additional_options')) {
                    $additionalOption = (array) $this->serializer->unserialize($additionalOptions->getValue());
                    if (isset($additionalOption['custom_uom'])) {
                        $uom = $additionalOption['custom_uom']['value'];
                    }
                }

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

                $this->logger->info('custPrice - '.$custPrice);
                if (round($custPrice) != 0 || $custPrice > 0) {
                    $quoteitem->setCustomPrice($custPrice);
                    $quoteitem->setOriginalCustomPrice($custPrice);
                    $this->logger->info('pricesetup success - '.$custPrice);
                    $quoteitem->getProduct()->setIsSuperMode(true);
                }
            }
        }
    }
}
