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

namespace Cayan\Payment\Gateway\Vault\Http\Client;

use Cayan\Payment\Gateway\Config\Credit\Config as CayanConfig;
use Cayan\Payment\Helper\Data as CayanHelper;
use Cayan\Payment\Model\Api\Credit\Vault\Api as VaultApi;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

/**
 * Vault Sale Transaction
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class TransactionSale implements ClientInterface
{
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $helper;
    /**
     * @var \Cayan\Payment\Model\Api\Credit\Vault\Api
     */
    private $vaultApi;
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $config;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Cayan\Payment\Helper\Data $data
     * @param \Cayan\Payment\Model\Api\Credit\Vault\Api $vaultApi
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CayanHelper $data,
        VaultApi $vaultApi,
        CayanConfig $config,
        LoggerInterface $logger
    ) {
        $this->helper = $data;
        $this->vaultApi = $vaultApi;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Place sale transaction request
     *
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return \stdClass|null
     * @throws \Magento\Payment\Gateway\Http\ClientException
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();

        if ($this->config->getPaymentAction() == CayanConfig::PAYMENT_ACTION_AUTHORIZE) {
            $response = $this->vaultApi->authorize($data);
        } else {
            $response = $this->vaultApi->sale($data);
        }

        $response = json_decode(json_encode($response), true);

        if ($this->config->debug()) {
            $this->logger->debug(__('Request made to Cayan Vault Sale API.'), ['request' => $data]);
            $this->logger->debug(__('Response received from Cayan Vault Sale API.'), ['response' => $response]);
        }

        return $response;
    }
}
