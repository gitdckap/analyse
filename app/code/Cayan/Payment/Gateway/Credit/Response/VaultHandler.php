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

namespace Cayan\Payment\Gateway\Credit\Response;

use Cayan\Payment\Gateway\Config\Credit\Config as CreditConfig;
use Cayan\Payment\Gateway\Config\Vault\Config as VaultConfig;
use Cayan\Payment\Gateway\Config\General as GeneralConfig;
use Cayan\Payment\Gateway\Helper\SubjectReader;
use Cayan\Payment\Model\Api\Credit\Api as ApiModel;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Vault\Model\CreditCardTokenFactory;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

/**
 * Vault Handler
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class VaultHandler implements HandlerInterface
{
    const TOKEN_ENABLED_KEY = 'is_active_payment_token_enabler';

    /**
     * @var \Cayan\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;
    /**
     * @var \Magento\Vault\Model\CreditCardTokenFactory
     */
    private $paymentInterface;
    /**
     * @var \Cayan\Payment\Model\Api\Credit\Api
     */
    private $apiModel;
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $creditConfig;
    /**
     * @var \Cayan\Payment\Gateway\Config\Vault\Config
     */
    private $vaultConfig;
    /**
     * @var \Cayan\Payment\Gateway\Config\General
     */
    private $generalConfig;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $session;

    /**
     * @param \Cayan\Payment\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Vault\Model\CreditCardTokenFactory $paymentToken
     * @param \Cayan\Payment\Model\Api\Credit\Api $apiModel
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $creditConfig
     * @param \Cayan\Payment\Gateway\Config\Vault\Config $vaultConfig
     * @param \Cayan\Payment\Gateway\Config\General $generalConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        SubjectReader $subjectReader,
        CreditCardTokenFactory $paymentToken,
        ApiModel $apiModel,
        CreditConfig $creditConfig,
        VaultConfig $vaultConfig,
        GeneralConfig $generalConfig,
        LoggerInterface $logger,
        Session $session
    ) {
        $this->subjectReader = $subjectReader;
        $this->paymentInterface = $paymentToken;
        $this->apiModel = $apiModel;
        $this->creditConfig = $creditConfig;
        $this->vaultConfig = $vaultConfig;
        $this->generalConfig = $generalConfig;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * Handle the response from Cayan Vault
     *
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDataObject->getPayment();
        $paymentAdditionalInformation = $payment->getAdditionalInformation();

        if ($this->vaultConfig->isEnabled() && $this->isCustomer()
            && array_key_exists(self::TOKEN_ENABLED_KEY, $paymentAdditionalInformation)
            && (bool)$paymentAdditionalInformation[self::TOKEN_ENABLED_KEY] === true) {
            // add vault payment token entity to extension attributes
            $paymentToken = $this->getVaultPaymentToken($payment, $response);

            if (!is_null($paymentToken)) {
                $extensionAttributes = $this->getExtensionAttributes($payment);

                $extensionAttributes->setVaultPaymentToken($paymentToken);
            }
        }
    }

    /**
     * Get payment extension attributes
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \Magento\Sales\Api\Data\OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();

        if (is_null($extensionAttributes)) {
            $extensionAttributes = $this->paymentInterface->create();

            $payment->setExtensionAttributes($extensionAttributes);
        }

        return $extensionAttributes;
    }

    /**
     * Get vault payment token entity
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param array $response
     * @return \Magento\Vault\Api\Data\PaymentTokenInterface|null
     */
    private function getVaultPaymentToken(InfoInterface $payment, array $response)
    {
        // Create vault token with card info.
        $billingAddress = $payment->getOrder()->getBillingAddress();
        $originalStreet = $billingAddress->getStreet();
        $street = '';

        if (is_array($originalStreet)) {
            foreach ($originalStreet as $streetItem) {
                $street = $street.$streetItem . ' ';
            }
        } else {
            $street = $originalStreet;
        }

        $data = [
            'Credentials' => [
                'MerchantName' => $this->generalConfig->getMerchantName(),
                'MerchantSiteId' => $this->generalConfig->getMerchantSiteId(),
                'MerchantKey' => $this->generalConfig->getMerchantKey()
            ],
            'PaymentData' => [
                'Source' => 'Keyed',
                'CardNumber' => $payment->getAdditionalInformation('cc_number'),
                'ExpirationDate' => $payment->getAdditionalInformation('cc_exp_month') .
                    $payment->getAdditionalInformation('cc_exp_year'),
                'CardHolder' => $payment->getAdditionalInformation('cc_holder_name'),
                'AvsStreetAddress' => $street,
                'AvsZipCode' => $billingAddress->getPostcode()
            ]
        ];

        $tokenResponse = $this->apiModel->boardCard($data);

        if ($this->creditConfig->debug()) {
            if ((strlen($data['PaymentData']['CardNumber']) - 4) > 0) {
                $data['PaymentData']['CardNumber'] = str_repeat('*', strlen($data['PaymentData']['CardNumber']) - 4)
                    . substr($data['PaymentData']['CardNumber'], -4);
            }

            $this->logger->debug(__('Request made to Cayan Vault BoardCard API.'), ['request' => $data]);
            $this->logger->debug(
                __('Response received from Cayan Vault BoardCard API.'),
                ['response' => $tokenResponse]
            );
        }

        if (is_null($tokenResponse) || (property_exists($tokenResponse, 'ErrorMessage')
                && $tokenResponse->ErrorMessage !== '')) {
            return null;
        }

        $token = $tokenResponse->VaultToken;
        /** @var \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentInterface->create();

        $paymentToken->setGatewayToken($token);

        $creditCardType = $response['CardType'];
        $maskedCc = $response['CardNumber'];
        $cardHolder = $payment->getAdditionalInformation('cc_holder_name');

        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $creditCardType,
            'maskedCC' => $maskedCc,
            'CardHolder' => $cardHolder
        ]));

        // Set token as valid for five years
        $futureDate = date('Y-m-d', strtotime('+5 year'));

        $paymentToken->setData('expires_at', $futureDate);

        return $paymentToken;
    }

    /**
     * Convert payment token details to JSON
     *
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON(array $details)
    {
        $json = json_encode($details);

        return $json ?: '{}';
    }

    /**
     * Check if the customer is logged in
     *
     * @return bool
     */
    private function isCustomer()
    {
        return $this->session->isLoggedIn();
    }
}
