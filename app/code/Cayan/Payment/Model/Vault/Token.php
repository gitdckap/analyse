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

namespace Cayan\Payment\Model\Vault;

use Cayan\Payment\Gateway\Config\Credit\Config as CreditCardConfig;
use Cayan\Payment\Gateway\Credit\Request\CredentialsDataBuilder;
use Cayan\Payment\Model\Api\Credit\Api as CreditCardApi;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenRepository;
use Psr\Log\LoggerInterface;

/**
 * Vault Token Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 * @author Joseph Leedy
 */
class Token
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Vault\Model\PaymentTokenRepository
     */
    private $paymentTokenRepository;
    /**
     * @var \Cayan\Payment\Model\Api\Credit\Api
     */
    private $creditCardApi;
    /**
     * @var \Cayan\Payment\Gateway\Credit\Request\CredentialsDataBuilder
     */
    private $credentialsDataBuilder;
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $creditCardConfig;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Vault\Model\PaymentTokenRepository $paymentTokenRepository
     * @param \Cayan\Payment\Model\Api\Credit\Api $creditCardApi
     * @param \Cayan\Payment\Gateway\Credit\Request\CredentialsDataBuilder $credentialsDataBuilder
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $creditCardConfig
     */
    public function __construct(
        LoggerInterface $logger,
        PaymentTokenRepository $paymentTokenRepository,
        CreditCardApi $creditCardApi,
        CredentialsDataBuilder $credentialsDataBuilder,
        CreditCardConfig $creditCardConfig
    ) {

        $this->logger = $logger;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->creditCardApi = $creditCardApi;
        $this->credentialsDataBuilder = $credentialsDataBuilder;
        $this->creditCardConfig = $creditCardConfig;
    }

    /**
     * Remove the payment token from the Cayan Vault
     *
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $paymentToken
     * @return bool
     */
    public function remove(PaymentTokenInterface $paymentToken)
    {
        /** @var \Magento\Vault\Api\Data\PaymentTokenInterface $tokenModel */
        $tokenModel = $this->paymentTokenRepository->getById($paymentToken->getEntityId());

        if (empty($tokenModel->getPublicHash())) {
            return false;
        }

        $request = array_merge(
            $this->credentialsDataBuilder->build([]),
            [
                'Request' => [
                    'VaultToken' => $paymentToken->getGatewayToken()
                ]
            ]
        );
        $response = $this->creditCardApi->unboardCard($request);

        if ($this->creditCardConfig->debug()) {
            $this->logger->debug(__('Request sent to Cayan UnboardCard API.'), ['request' => $request]);
            $this->logger->debug(__('Response received from Cayan UnboardCard API.'), ['response' => $response]);
        }

        return !is_null($response) && $response->ErrorCode === '';
    }
}
