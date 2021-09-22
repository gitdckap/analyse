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

namespace Cayan\Payment\Gateway\Config\Vault;

use Cayan\Payment\Gateway\Config\AbstractConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Vault Configuration
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 * @author Joseph Leedy
 */
class Config extends AbstractConfig
{
    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = 'cayancc_vault',
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
    }

    /**
     * Check if the Cayan Vault is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->getFlag('active');
    }

    /**
     * Retrieve vault payment title
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return $this->getValue('title', $storeId);
    }
}
