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

namespace Cayan\Payment\Gateway\Credit\Http\Client;

use Cayan\Payment\Gateway\Config\Credit\Config as CayanConfig;
use Cayan\Payment\Model\Api\Credit\Api as CreditCardApi;
use Cayan\Payment\Model\Helper\Discount as DiscountHelper;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

/**
 * Transaction Void HTTP Client
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class TransactionVoid implements ClientInterface
{
    /**
     * @var \Cayan\Payment\Model\Api\Credit\Api
     */
    private $creditCardApi;
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $config;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $discountHelper;

    /**
     * @param \Cayan\Payment\Model\Api\Credit\Api $creditCardApi
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $config
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     */
    public function __construct(
        CreditCardApi $creditCardApi,
        LoggerInterface $logger,
        CayanConfig $config,
        DiscountHelper $discountHelper
    ) {
        $this->creditCardApi = $creditCardApi;
        $this->config = $config;
        $this->logger = $logger;
        $this->discountHelper = $discountHelper;
    }

    /**
     * Place the Void Transaction request
     *
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return mixed
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        $response = $this->creditCardApi->void($request);

        if ($this->config->debug()) {
            $this->logger->debug(__('Request sent to Cayan Void API.'), ['request' => $request]);
            $this->logger->debug(__('Response received from Cayan Void API.'), ['response' => $response]);
        }

        if (!is_null($response)) {
            $response = json_decode(json_encode($response), true);
        }

        return $response;
    }
}
