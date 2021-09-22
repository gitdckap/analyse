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

namespace Cayan\Payment\Model\Api\Card;

use Cayan\Payment\Gateway\Config\Gift\Config as GiftConfig;
use Cayan\Payment\Helper\Data;
use Cayan\Payment\Model\Api\AbstractApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Cayan Gift Card API Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class Api extends AbstractApi
{
    /**
     * @var \Cayan\Payment\Gateway\Config\Gift\Config
     */
    private $giftConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Cayan\Payment\Helper\Data $helper
     * @param \Cayan\Payment\Gateway\Config\Gift\Config $giftConfig
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        GiftConfig $giftConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $scopeConfig, $helper, $resource, $resourceCollection, $data);

        $this->giftConfig = $giftConfig;
    }

    /**
     * Activate gift card in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/gift-card#ActivateCard
     * @param array $params
     * @return \stdClass|null
     */
    public function activateCard($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->ActivateCard($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while activating the requested gift card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'ActivateCardResult') ? $response->ActivateCardResult : null;
    }

    /**
     * Retrieve gift card balance from Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/gift-card#BalanceInquiry
     * @param array $params
     * @return \stdClass|null
     */
    public function balanceInquiry($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->BalanceInquiry($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while retrieving a balance for the requested gift card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'BalanceInquiryResult') ? $response->BalanceInquiryResult : null;
    }

    /**
     * Perform gift card sale transaction in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/gift-card#Sale
     * @param array $params
     * @return \stdClass|null
     */
    public function sale($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->Sale($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while performing a sale transaction for the requested gift card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'SaleResult') ? $response->SaleResult : null;
    }

    /**
     * Void gift card transaction in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/gift-card#Void
     * @param array $params
     * @return \stdClass|null
     */
    public function void($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->Void($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while performing a void transaction for the requested gift card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'VoidResult') ? $response->VoidResult : null;
    }

    /**
     * Refund gift card transaction in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/gift-card#Refund
     * @param $params
     * @return \stdClass|null
     */
    public function refund($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->Refund($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while performing a refund transaction for the requested gift card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'RefundResult') ? $response->RefundResult : null;
    }

    /**
     * Retrieve the Cayan API URL
     *
     * @return string
     */
    private function getApiUrl()
    {
        return $this->giftConfig->getApiUrl();
    }
}
