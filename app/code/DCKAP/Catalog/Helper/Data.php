<?php

namespace DCKAP\Catalog\Helper;

use Magento\Catalog\Model\Product\Option;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    protected $option;
    protected $scopeConfig;
    protected $storeManager;
    protected $customerSession;
    protected $clorasHelper;
    protected $clorasDDIHelper;
    protected $_collection;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Option $option,
        \Magento\Customer\Model\Session $customerSession,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        $this->option = $option;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->_collection = $collection;
        parent::__construct($context);
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue($config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getMediaUrl()
    {
        $media_dir = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return $media_dir;
    }

    public function getConfigUrl($product_id)
    {
        $StrUrl= false;
        if (false == empty($product_id) && false == is_null($product_id)) {
            $collection = $this->_collection->addAttributeToSelect('*')->addFieldToFilter('entity_id', $product_id)
                ->load();
            foreach ($collection as $product) {
                $StrUrl = $product->getProductUrl();
            }
        }
        return $StrUrl;
    }

    /**
     * @param bool $sku
     * @return mixed
     *
     * Used in cart observer, PDP
     */
    public function getSessionProductsData($sku = false)
    {
        if ($sku) {
            if ($this->customerSession->isLoggedIn()) {
                $sessionProductData = $this->customerSession->getProductData();
                if (isset($sessionProductData[$sku])) {
                    return $sessionProductData;
                } else {
                    list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('price_stock');
                    if ($status) {
                        $responseData = $this->clorasDDIHelper->getPriceStock($integrationData, $sku);
                        if ($responseData && !empty($responseData)) {
                            $itemData = $this->customerSession->getProductData();
                            $itemData[$sku] = $responseData[0];
                            $this->customerSession->setProductData($itemData);
                            $itemData = $this->customerSession->getProductData();

                            return $itemData;
                        }
                    }
                }
            } else {
                $sessionProductData = $this->customerSession->getGuestProductData();
                if (isset($sessionProductData[$sku])) {
                    return $sessionProductData;
                } else {
                    list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('guest_price_stock');
                    if ($status) {
                        $responseData = $this->clorasDDIHelper->getGuestPriceStock($integrationData, $sku);
                        if ($responseData && !empty($responseData)) {
                            $itemData[$sku] = $responseData[0];

                            return $itemData;
                        }
                    }
                }
            }
        }
        $sessionProductData = $this->customerSession->getProductData();
        return $sessionProductData;
    }

    /**
     * @param bool $sku
     * @return mixed
     *
     * Used in PLP
     */
    public function getSessionProductData($sku = false)
    {
        if ($sku) {
            if ($this->customerSession->isLoggedIn()) {
                $sessionProductData = $this->customerSession->getProductData();
                if (isset($sessionProductData[$sku])) {
                    return $sessionProductData[$sku];
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
                $sessionProductData = $this->customerSession->getGuestProductData();
                if (isset($sessionProductData[$sku])) {
                    return $sessionProductData[$sku];
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
        return false;
    }

    public function getSessionQuoteProductData($sku = false)
    {
        if ($sku) {
            $sessionProductData = $this->customerSession->getQuoteProductData();
            if (isset($sessionProductData[$sku])) {
                return $sessionProductData[$sku];
            }
        }
        return false;
    }

    /**
     * Used on convert quote into order
     */
    public function checkloggedin()
    {
        if ($this->customerSession->isLoggedIn()) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Check whether site belongs to b2b or b2c
     */
    public function isB2c()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $website_mode = $this->scopeConfig->getValue('themeconfig/mode_config/website_mode', $storeScope);
        if ($website_mode != 'b2c') {
            return true;
        }
        return false;
    }

    /**
     * Used in everywhere to display stock status / availability / Inventory based on location
     *
     * It returns,
     * 0 - nothing will be displayed
     * 1 - stock status only be displayed
     * 2 - stock status and availability also displayed
     * 3 - stock status, availability and inventory based location also displayed
     */
    public function getStockDisplay()
    {
        if ($this->checkloggedin()) {
            $generalStockDisplayConfig = $this->scopeConfig->getValue(
                'dckapextension/dckap_inventory/enable_inventory_location',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            return $generalStockDisplayConfig;
        } else {
            $guestStockDisplayConfig = $this->scopeConfig->getValue(
                'dckapextension/BitExpert_ForceCustomerLogin/stock_display',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if ($guestStockDisplayConfig == 1) {
                $generalStockDisplayConfig = $this->scopeConfig->getValue(
                    'dckapextension/dckap_inventory/enable_inventory_location',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
                return $generalStockDisplayConfig;
            }
        }
        return 0;
    }
}
