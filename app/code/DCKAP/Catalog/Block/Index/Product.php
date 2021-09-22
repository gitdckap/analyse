<?php

namespace DCKAP\Catalog\Block\Index;

class Product extends \Magento\Framework\View\Element\Template
{
    protected $registry;
    protected $productFactory;
    protected $clorasHelper;
    protected $clorasDDIHelper;
    protected $itemData = [];
    private $customerSession;
    protected $priceHelper;
    protected $extensionHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper
    ) {
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->customerSession = $customerSession;
        $this->priceHelper = $priceHelper;
        $this->extensionHelper = $extensionHelper;
        parent::__construct($context);
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    protected function getItemData()
    {
        $product = $this->registry->registry('current_product');
        $sku = $product->getData('sku');
        if ($this->customerSession->isLoggedIn()) {
            $itemData = $this->customerSession->getProductData();
//        var_dump($itemData);
//        var_dump($sku);
            if ($itemData && !empty($itemData) && isset($itemData[$sku])) {
                return $itemData[$sku];
            } else {
                list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('price_stock');
                if ($status) {
                    $responseData = $this->clorasDDIHelper->getPriceStock($integrationData, $sku);
                    if ($responseData && !empty($responseData)) {
                        $itemData = $this->customerSession->getProductData();
                        $itemData[$sku] = $responseData[0];
                        $this->customerSession->setProductData($itemData);
                        $itemData = $this->customerSession->getProductData();
                        return $itemData[$sku];
                    }
                }
            }
        } else {
            $itemData = $this->customerSession->getGuestProductData();
            if ($itemData && !empty($itemData) && isset($itemData[$sku])) {
                return $itemData[$sku];
            } else {
                list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('guest_price_stock');
                if ($status) {
                    $responseData = $this->clorasDDIHelper->getGuestPriceStock($integrationData, $sku);
                    if ($responseData && !empty($responseData)) {
                        $itemData[$sku] = $responseData[0];
                        return $itemData[$sku];
                    }
                }
            }
        }
        return false;
    }

    public function getTierPriceData()
    {
        $itemData = $this->getItemData();
        if ($itemData && !empty($itemData)) {
            return $itemData['prices'];
        }
        return false;
    }

    public function getWarehouseData()
    {
        $itemData = $this->getItemData();
        if ($itemData && !empty($itemData)) {
            return $itemData['lineItem']['locations'];
        }
        return false;
    }

    public function getUOMData()
    {
        $itemData = $this->getItemData();
        if ($itemData && !empty($itemData)) {
            return $itemData['lineItem']['uom'];
        }
        return false;
    }

    public function getPriceWithCurrency($price = false)
    {
        if ($price) {
            return $this->priceHelper->currency((float)$price, true, false);
        }
        return false;
    }

    public function getERPPrice()
    {
        $itemData = $this->getItemData();
        if ($itemData && !empty($itemData)) {
            return $this->getPriceWithCurrency($itemData['prices']['netPrice']);
        }
        return false;
    }

    public function getCurrentProduct()
    {
        return $this->registry->registry('current_product');
    }

    public function getErpProductData($sku)
    {
        if ($this->customerSession->isLoggedIn()) {
            $itemData = $this->customerSession->getProductData();
            if ($itemData && !empty($itemData) && isset($itemData[$sku])) {
                return $itemData[$sku];
            } else {
                list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('price_stock');
                if ($status) {
                    $responseData = $this->clorasDDIHelper->getPriceStock($integrationData, $sku);
                    if ($responseData && !empty($responseData)) {
                        $itemData = $this->customerSession->getProductData();
                        $itemData[$sku] = $responseData[0];
                        $this->customerSession->setProductData($itemData);
                        $itemData = $this->customerSession->getProductData();
                        return $itemData[$sku];
                    }
                }
            }
        } else {
            $itemData = $this->customerSession->getGuestProductData();
            if ($itemData && !empty($itemData) && isset($itemData[$sku])) {
                return $itemData[$sku];
            } else {
                list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('guest_price_stock');
                if ($status) {
                    $responseData = $this->clorasDDIHelper->getGuestPriceStock($integrationData, $sku);
                    if ($responseData && !empty($responseData)) {
                        $itemData[$sku] = $responseData[0];
                        return $itemData[$sku];
                    }
                }
            }
        }
    }

    public function getGuestPriceDisplay()
    {
        if (!($this->customerSession->isLoggedIn())) {
            return $this->extensionHelper->getGuestPriceDisplay();
        }
        return true;
    }
}
