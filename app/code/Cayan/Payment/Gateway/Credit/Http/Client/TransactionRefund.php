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
use Cayan\Payment\Helper\Data as CayanHelper;
use Cayan\Payment\Model\Api\Credit\Api as CreditCardApi;
use Cayan\Payment\Model\Helper\Discount as DiscountHelper;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;

/**
 * Refund Credit Transaction Gateway Client
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class TransactionRefund implements ClientInterface
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
     * @var \Cayan\Payment\Helper\Data
     */
    private $helper;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $discountHelper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Cayan\Payment\Model\Api\Credit\Api $creditCardApi
     * @param \Cayan\Payment\Helper\Data $data
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $config
     * @param \Cayan\Payment\Model\Helper\Discount $discountHelper
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CreditCardApi $creditCardApi,
        CayanHelper $data,
        CayanConfig $config,
        DiscountHelper $discountHelper,
        LoggerInterface $logger
    ) {
        $this->creditCardApi = $creditCardApi;
        $this->config = $config;
        $this->helper = $data;
        $this->discountHelper = $discountHelper;
        $this->logger = $logger;
    }

    /**
     * Send the refund request to the Cayan API
     *
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return \stdClass|null
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $data = $transferObject->getBody();
        $response = $this->creditCardApi->refund($data);

        if ($this->config->debug()) {
            $this->logger->debug(__('Request made to Cayan Credit Refund API.'), ['request' => $data]);
            $this->logger->debug(__('Response received from Cayan Credit Refund API.'), ['response' => $response]);
        }

        if (!is_null($response)) {
            $response = json_decode(json_encode($response), true);
        }

        return $response;
    }
}
