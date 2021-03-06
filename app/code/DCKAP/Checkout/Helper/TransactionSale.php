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

namespace DCKAP\Checkout\Helper;

use Cayan\Payment\Gateway\Config\Credit\Config as CayanConfig;
use Cayan\Payment\Helper\Data as CayanHelper;
use Cayan\Payment\Model\Api\Credit\Api as CreditApi;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

/**
 * Sale Credit Transaction Gateway Client
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class TransactionSale
{
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $helper;
    /**
     * @var \Cayan\Payment\Model\Api\Credit\Api
     */
    private $creditApi;
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $config;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    protected $cayanGeneral;

    public function __construct(
        CayanHelper $data,
        CreditApi $creditApi,
        CayanConfig $config,
        LoggerInterface $logger,
        \Cayan\Payment\Gateway\Config\General $cayanGeneral
    ) {
        $this->helper = $data;
        $this->creditApi = $creditApi;
        $this->config = $config;
        $this->logger = $logger;
        $this->cayanGeneral = $cayanGeneral;
    }

    public function placeRequest($data)
    {

        $isVault = array_key_exists('PaymentData', $data) && $data['PaymentData']['Source'] === 'Vault';
        $MerchantName = $this->cayanGeneral->getMerchantName();
        $MerchantSiteId = $this->cayanGeneral->getMerchantSiteId();
        $MerchantKey = $this->cayanGeneral->getMerchantKey();
        $credentials = [
            "MerchantName" => $MerchantName,
            "MerchantSiteId" => $MerchantSiteId,
            "MerchantKey" => $MerchantKey
        ];
        $data['Credentials'] = $credentials;
        if ($this->config->getPaymentAction() === CayanConfig::PAYMENT_ACTION_AUTHORIZE) {
            if (isset($data['PaymentData'])) {
                if (!$isVault) {
                    unset($data['options']);
                }
                $response = $this->creditApi->authorize($data);
            } else {
                if (!$isVault) {
                    unset($data['options']);
                }
                $response = $this->creditApi->capture($data);
            }
        } else {
            $response = $this->creditApi->sale($data);
        }
        if (!is_null($response)) {
            $response = json_decode(json_encode($response), true);
        }
        if ($this->config->debug()) {
            $this->logger->debug(__('Request made to Cayan Credit Sale API.'), ['request' => $data]);
            $this->logger->debug(__('Response received from Cayan Credit Sale API.'), ['response' => $response]);
        }

        return $response;
    }
}
