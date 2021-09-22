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

namespace Cayan\Payment\Gateway\Config\Credit;

use Cayan\Payment\Gateway\Config\AbstractConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Credit Card Gateway Configuration
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class Config extends AbstractConfig
{
    const PAYMENT_ACTION_AUTHORIZE = 'authorize';
    const PAYMENT_ACTION_AUTHORIZE_CAPTURE = 'authorize_capture';
    const KEY_COUNTRY_CREDIT_CARD = 'country_creditcard';

    /**
     * Check if CVV is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCvvEnabled($storeId = null)
    {
        return $this->getFlag('useccv', $storeId);
    }

    /**
     * Check if enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return $this->getFlag('active', $storeId);
    }

    /**
     * Check whether all or only specific countries are allowed
     *
     * @param int|null $storeId
     * @return bool
     */
    public function getAllowSpecific($storeId = null)
    {
        return $this->getFlag('allowspecific', $storeId);
    }

    /**
     * Retrieve the allowed countries
     *
     * @param int|null $storeId
     * @return array
     */
    public function getSpecificCountries($storeId = null)
    {
        return $this->getValue('specificcountry', $storeId);
    }

    /**
     * Retrieve the payment action
     *
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentAction($storeId = null)
    {
        return $this->getValue('payment_action', $storeId);
    }

    /**
     * Retrieve the payment method title
     *
     * @param int|null $storeId
     * @return string
     */
    public function getTitle($storeId = null)
    {
        return $this->getValue('title', $storeId);
    }

    /**
     * Retrieve the API URL
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getApiUrl($storeId = null)
    {
        return $this->getValue('api_url', $storeId);
    }

    /**
     * Check if debug mode is enabled
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function debug($storeId = null)
    {
        return $this->getFlag('debug', $storeId);
    }

    /**
     * Retrieve the country specific card types
     *
     * @param int|null $storeId
     * @return array
     * @todo Find a more secure way to do instead of using unserialize().
     */
    public function getCountrySpecificCardTypeConfig($storeId = null)
    {
        $countriesCardTypes = unserialize($this->getValue(self::KEY_COUNTRY_CREDIT_CARD, $storeId));

        return is_array($countriesCardTypes) ? $countriesCardTypes : [];
    }

    /**
     * Retrieve a list of card types available for the specified country
     *
     * @param string $country
     * @param int|null $storeId
     * @return array
     */
    public function getCountryAvailableCardTypes($country, $storeId = null)
    {
        $types = $this->getCountrySpecificCardTypeConfig($storeId);

        return !empty($types[$country]) ? $types[$country] : [];
    }
}
