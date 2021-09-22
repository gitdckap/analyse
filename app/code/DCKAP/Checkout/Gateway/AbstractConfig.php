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

use Magento\Payment\Gateway\Config\Config as GatewayConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Abstract Gateway Configuration
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
abstract class AbstractConfig extends GatewayConfig
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var string
     */
    protected $methodCode;
    /**
     * @var string
     */
    protected $pathPattern;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string|null $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);

        $this->scopeConfig = $scopeConfig;

        $this->setMethodCode($methodCode);
        $this->setPathPattern($pathPattern);
    }

    /**
     * Set method code
     *
     * @param string $methodCode
     */
    public function setMethodCode($methodCode)
    {
        parent::setMethodCode($methodCode);

        $this->methodCode = $methodCode;
    }

    /**
     * Set path pattern
     *
     * @param string $pathPattern
     */
    public function setPathPattern($pathPattern)
    {
        parent::setPathPattern($pathPattern);

        $this->pathPattern = $pathPattern;
    }

    /**
     * Retrieve flag from payment configuration
     *
     * @param string $field
     * @param int|null $storeId
     * @return bool
     */
    public function getFlag($field, $storeId = null)
    {
        if (is_null($this->methodCode) || is_null($this->pathPattern)) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            sprintf($this->pathPattern, $this->methodCode, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
