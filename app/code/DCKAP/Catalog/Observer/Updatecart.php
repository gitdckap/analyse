<?php

namespace DCKAP\Catalog\Observer;

use Magento\Framework\Event\ObserverInterface;

class Updatecart implements ObserverInterface
{
    private $logger;
    private $customerSession;
    private $checkoutSession;
    private $productRepository;
    private $serializer;
    protected $dckapCatalogHelper;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \DCKAP\Catalog\Helper\Data $dckapCatalogHelper
    ) {
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->dckapCatalogHelper = $dckapCatalogHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->logger->info('update cart item after observer working fine');
        $price_quote = '';
        $data = $observer->getEvent()->getData('info');
        $convert_data = (array)$data;
        foreach ($convert_data as $itemsdata => $datainfo) {
            foreach ($datainfo as $itemId => $itemInfo) {
                $item = $this->checkoutSession->getQuote()->getItemById($itemId);
                if (!$item) {
                    continue;
                }

                $custPrice = 0.0;
                $sku = $item->getSku();
                $cartQty = $item->getQty();
//                $sessionProductData = $this->customerSession->create()->getProductData();
                $sessionProductData = $this->dckapCatalogHelper->getSessionProductsData($sku);
                $uom = 'EA';
                if ($additionalOptions = $item->getOptionByCode('additional_options')) {
                    $additionalOption = (array)$this->serializer->unserialize($additionalOptions->getValue());
                    if (isset($additionalOption['custom_uom'])) {
                        $uom = $additionalOption['custom_uom']['value'];
                    }
                    if (isset($additionalOption['quote'])) {
                        $price_quote = isset($additionalOption['quote']['value']) ? $additionalOption['quote']['value'] : '';
                    }
                }
                if ($sessionProductData && $sessionProductData[$sku]) {
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
                    $sessionQuoteProductData = $this->dckapCatalogHelper->getSessionQuoteProductData($sku . '_' . $uom);
                    if ($sessionQuoteProductData && isset($sessionQuoteProductData['netPrice'])) {
                        $custPrice = (float) str_replace('$', '', str_replace(',', '', $sessionQuoteProductData['netPrice']));
                    }
                }
                if (round($custPrice) != 0 || $custPrice > 0) {
                    $item->setOriginalCustomPrice($custPrice);
                    $item->setCustomPrice($custPrice);
                }
            }
        }
    }
}
