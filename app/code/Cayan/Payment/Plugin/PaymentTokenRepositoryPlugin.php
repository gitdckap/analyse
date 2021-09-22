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

namespace Cayan\Payment\Plugin;

use Cayan\Payment\Model\Vault\Token as CayanVaultToken;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\PaymentTokenRepositoryInterface;

/**
 * Payment Token Repository Plug-in
 *
 * @package Cayan\Payment\Plugin
 * @author Joseph Leedy
 */
class PaymentTokenRepositoryPlugin
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Cayan\Payment\Model\Vault\Token
     */
    private $cayanVaultToken;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Cayan\Payment\Model\Vault\Token $cayanVaultToken
     */
    public function __construct(ScopeConfigInterface $scopeConfig, CayanVaultToken $cayanVaultToken)
    {
        $this->scopeConfig = $scopeConfig;
        $this->cayanVaultToken = $cayanVaultToken;
    }

    /**
     * @param \Magento\Vault\Api\PaymentTokenRepositoryInterface $paymentTokenRepository
     * @param callable $proceed
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken
     * @return bool
     */
    public function aroundDelete(
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        callable $proceed,
        PaymentTokenInterface $paymentToken
    ) {
        $result = $proceed($paymentToken);

        if (!$result || $paymentToken->getPaymentMethodCode() !== 'cayancc'
            || !$this->scopeConfig->isSetFlag('payment/cayancc/active')) {
            return $result;
        }

        return $this->cayanVaultToken->remove($paymentToken);
    }
}
