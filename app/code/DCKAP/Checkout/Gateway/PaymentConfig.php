<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Dckap\Checkout\Gateway;

use Magento\Framework\App\Config\ScopeConfigInterface;

//use \Cayan\Payment\Gateway\Config\AbstractConfig;

/**
 * General Gateway Configuration
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 * @author Joseph Leedy
 */
class PaymentConfig extends AbstractConfig
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = 'elementpayment',
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Retrieve the merchant name
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantName($storeId = null)
    {
        return $this->getValue('merchant_name', $storeId);
    }

    /**
     * Retrieve the merchant site ID
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantSiteId($storeId = null)
    {
        return $this->getValue('merchant_site_id', $storeId);
    }

    /**
     * Retrieve the merchant key
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantKey($storeId = null)
    {
        return $this->getValue('merchant_key', $storeId);
    }

    /**
     * Retrieve the Web API key used for checkout
     *
     * @param int|null $storeId
     * @return string
     */
    public function getWebApiKey($storeId = null)
    {
        return $this->getValue('api_key', $storeId);
    }
}
