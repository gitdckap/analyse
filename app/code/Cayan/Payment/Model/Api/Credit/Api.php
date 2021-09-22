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

namespace Cayan\Payment\Model\Api\Credit;

use Cayan\Payment\Model\Api\AbstractApi;

/**
 * Cayan Credit Card API Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class Api extends AbstractApi
{
    /**
     * Request a payment authorization from Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/credit#authorize
     * @param array $params
     * @return \stdClass|null
     */
    public function authorize($params)
    {
        try {
            $apiUrl = $this->getApiUrl();
            $request = $this->buildRequest($apiUrl);
            $response = $request->Authorize($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while authorizing the requested credit card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'AuthorizeResult') ? $response->AuthorizeResult : null;
    }

    /**
     * Perform sale transaction in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/credit#sale
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
                __('An error occurred while performing a sale transaction against the requested credit card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'SaleResult') ? $response->SaleResult : null;
    }

    /**
     * Perform capture transaction in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/credit#capture
     * @param array $params
     * @return \stdClass|null
     */
    public function capture($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->Capture($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while performing a capture transaction against the requested credit card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'CaptureResult') ? $response->CaptureResult : null;
    }

    /**
     * Perform refund transaction in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/credit#refund
     * @param array $params
     * @return \stdClass|null
     */
    public function refund($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->Refund($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while performing a refund transaction against the requested credit card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'RefundResult') ? $response->RefundResult : null;
    }

    /**
     * Perform void transaction in Cayan
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/credit#void
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
                __('An error occurred while performing a void transaction against the requested credit card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'VoidResult') ? $response->VoidResult : null;
    }

    /**
     * Store card in Cayan Vault
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/credit#boardcard
     * @param array $params
     * @return \stdClass|null
     */
    public function boardCard($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->BoardCard($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while boarding the requested credit card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'BoardCardResult') ? $response->BoardCardResult : null;
    }

    /**
     * Remove stored card from Cayan Vault
     *
     * @see https://cayan.com/developers/merchantware/merchantware-4-5/credit#unboardcard
     * @param array $params
     * @return null
     */
    public function unboardCard($params)
    {
        try {
            $request = $this->buildRequest($this->getApiUrl());
            $response = $request->UnboardCard($params);
        } catch (\Exception $e) {
            $this->_logger->error(
                __('An error occurred while unboarding the requested credit card: "%1"', $e->getMessage()),
                ['parameters' => $params]
            );

            return null;
        }

        return property_exists($response, 'UnboardCardResult') ? $response->UnboardCardResult : null;
    }

    /**
     * Retrieve the Cayan API URL
     *
     * @return string
     */
    private function getApiUrl()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CC_CONFIG_PATH . 'api_url');
    }
}
