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

namespace Cayan\Payment\Block;

use Cayan\Payment\Gateway\Config\Credit\Config as GatewayConfig;
use Cayan\Payment\Model\Adminhtml\Source\CcType;
use Cayan\Payment\Model\Ui\CreditCardConfigProvider;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Config;

/**
 * Credit Card Form Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class Form extends Cc
{
    /**
     * @var string
     */
    protected $_template = 'Cayan_Payment::form/cc.phtml';
    /**
     * @var \Magento\Payment\Helper\Data
     */
    private $paymentDataHelper;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $sessionQuote;
    /**
     * @var \Magento\Payment\Model\Config
     */
    private $gatewayConfig;
    /**
     * @var \Cayan\Payment\Model\Adminhtml\Source\CcType
     */
    private $ccType;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param \Magento\Payment\Helper\Data $paymentDataHelper
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $gatewayConfig
     * @param \Cayan\Payment\Model\Adminhtml\Source\CcType $ccType
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        Data $paymentDataHelper,
        Quote $sessionQuote,
        GatewayConfig $gatewayConfig,
        CcType $ccType,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);

        $this->paymentDataHelper = $paymentDataHelper;
        $this->sessionQuote = $sessionQuote;
        $this->gatewayConfig = $gatewayConfig;
        $this->ccType = $ccType;

        $this->gatewayConfig->setMethodCode(CreditCardConfigProvider::METHOD_CODE);
    }

    /**
     * Retrieve list of available credit card types for order billing address country
     *
     * @return array
     */
    public function getCcAvailableTypes()
    {
        return $this->ccType->toArrayForm();
    }

    /**
     * Check if CVV validation is enabled
     *
     * @return boolean
     */
    public function useCvv()
    {
        return true;
    }

    /**
     * Check if vault is enabled
     *
     * @return bool
     */
    public function isVaultEnabled()
    {
        $vaultPayment = $this->getVaultPayment();

        return $vaultPayment->isActive($this->sessionQuote->getStoreId());
    }

    /**
     * Retrieve configured vault payment for Cayan
     *
     * @return \Magento\Payment\Model\MethodInterface|\Magento\Vault\Model\VaultPaymentInterface
     */
    private function getVaultPayment()
    {
        return $this->paymentDataHelper->getMethodInstance(CreditCardConfigProvider::METHOD_VAULT_CODE);
    }
}
