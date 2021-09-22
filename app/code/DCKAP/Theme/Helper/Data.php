<?php

namespace Dckap\Theme\Helper;

use Magento\Framework\App\Helper\Context;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product\Option;

class Data extends AbstractHelper
{

    protected $option;

    protected $customerSession;

    public function __construct(
        Context $context,
        Option $option,
        \Magento\Customer\Model\SessionFactory $customerSession
    ) {
        $this->option = $option;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getOrderPadView()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            if ($customerSession->getCustomData() && !empty($customerSession->getCustomData())) {
                $customData = $customerSession->getCustomData();
                if ($customData['viewOrderPad'] && $customData['viewOrderPad'] == 'yes') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function getQuoteOptionView()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            if ($customerSession->getCustomData() && !empty($customerSession->getCustomData())) {
                $customData = $customerSession->getCustomData();
                if ($customData['allowQuote'] && $customData['allowQuote'] == 'yes') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function getSalesOrderView()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            if ($customerSession->getCustomData() && !empty($customerSession->getCustomData())) {
                $customData = $customerSession->getCustomData();
                if ($customData['viewSalesOrder'] && $customData['viewSalesOrder'] == 'yes') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function getViewInvoice()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            if ($customerSession->getCustomData() && !empty($customerSession->getCustomData())) {
                $customData = $customerSession->getCustomData();
                if ($customData['viewInvoice'] && $customData['viewInvoice'] == 'yes') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function getPayOnline()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            if ($customerSession->getCustomData() && !empty($customerSession->getCustomData())) {
                $customData = $customerSession->getCustomData();
                if (isset($customData['payOnline']) && $customData['payOnline'] == 'yes') {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
        return false;
    }

    public function getFooterBlockId()
    {
        return  $this->scopeConfig->getValue(
            'themeconfig/footer_config/footer_block',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function isVisible()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $website_mode = $this->scopeConfig->getValue('themeconfig/mode_config/website_mode', $storeScope);
        if ($website_mode != 'b2c') {
            return true;
        }
        return false;
    }

    public function getWebsiteMode()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        return $this->scopeConfig->getValue('themeconfig/mode_config/website_mode', $storeScope);
    }

     /**
      * Get footer useful links
      *
      * @return mixed
      */
    public function getUseFulLinksData()
    {
        return  $this->scopeConfig->getValue(
            'footer_links/useful_links/links_list',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get footer bottom links
     *
     * @return mixed
     */
    public function getFooterBottomLinksData()
    {
        return  $this->scopeConfig->getValue(
            'footer_links/useful_links/footer_bottom_links_list',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get footer social links
     * @return mixed
     */
    public function getFollowUsLinks()
    {
        return  $this->scopeConfig->getValue(
            'footer_links/useful_links/follow_us_links_list',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $value
     * @return array|mixed
     */
    public function unserialize($value)
    {
        $data = [];
        if (!$value) {
            return $data;
        }
        try {
            $data = unserialize($value);
        } catch (\Exception $exception) {
            $data = [];
        }
        if (empty($data) && json_decode($value)) {
            $data = json_decode($value, true);
        }
        return $data;
    }

    public function getPDPPrintOption()
    {
        return  $this->scopeConfig->getValue(
            'themeconfig/pdp_config/print_option',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfig($config = false)
    {
        if ($config) {
            return $this->scopeConfig->getValue($config, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        }
        return false;
    }
}
