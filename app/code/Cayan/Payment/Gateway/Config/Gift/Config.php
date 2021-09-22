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

namespace Cayan\Payment\Gateway\Config\Gift;

use Cayan\Payment\Gateway\Config\AbstractConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Gift Card Gateway Configuration
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class Config extends AbstractConfig
{
    const DEFAULT_PATH_PATTERN = 'cayancard/%s/%s';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = 'default_values',
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Check if gift card is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return $this->getFlag('active', $storeId);
    }

    /**
     * Retrieve the payment method title
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return $this->getValue('title', $storeId);
    }

    /**
     * Retrieve the API URL
     *
     * @param int|null $storeId
     * @return string
     */
    public function getApiUrl($storeId = null)
    {
        return $this->getValue('api_url', $storeId);
    }

    /**
     * Retrieve the maximum gift card code length
     *
     * @param int|null $storeId
     * @return int
     */
    public function getMaxLength($storeId = null)
    {
        return $this->getValue('max_length', $storeId);
    }

    /**
     * Check if partial authorization is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isPartialAuthorizationEnabled($storeId = null)
    {
        return false;
    }

    /**
     * Retrieve gift card cache lifetime
     *
     * @param int|null $storeId
     * @return int
     */
    public function getCacheLifetime($storeId = null)
    {
        return $this->getValue('cache_lifetime', $storeId);
    }

    /**
     * Check if PIN field is enabled on gift card form
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isPinEnabled($storeId = null)
    {
        return $this->getFlag('enable_pin', $storeId);
    }
}
