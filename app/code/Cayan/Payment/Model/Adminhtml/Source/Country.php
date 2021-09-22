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

namespace Cayan\Payment\Model\Adminhtml\Source;

use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;
use Magento\Framework\Option\ArrayInterface;

/**
 * Country Source Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class Country implements ArrayInterface
{
    /**
     * @var array
     */
    private $options;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    private $countryCollection;
    /**
     * Countries not supported by Cayan
     *
     * @var array
     */
    private $excludedCountries = [
        'MM',
        'IR',
        'SD',
        'BY',
        'CI',
        'CD',
        'CG',
        'IQ',
        'LR',
        'LB',
        'KP',
        'SL',
        'SY',
        'ZW',
        'AL',
        'BA',
        'MK',
        'ME',
        'RS'
    ];

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection
     */
    public function __construct(CountryCollection $countryCollection)
    {
        $this->countryCollection = $countryCollection;
    }

    /**
     * Convert the countries to an array of options
     *
     * @param bool $isMultiSelect
     * @return array
     */
    public function toOptionArray($isMultiSelect = false)
    {
        if (!$this->options) {
            $this->options = $this->countryCollection
                ->addFieldToFilter('country_id', ['nin' => $this->getExcludedCountries()])
                ->loadData()
                ->toOptionArray(false);
        }

        $options = $this->options;

        if (!$isMultiSelect) {
            array_unshift($options, ['value' => '', 'label' => __('--Please Select--')]);
        }

        return $options;
    }

    /**
     * Check if country is restricted (not supported by Cayan)
     *
     * @param string $countryId
     * @return bool
     */
    public function isCountryRestricted($countryId)
    {
        return in_array($countryId, $this->getExcludedCountries());
    }

    /**
     * Retrieve list of excluded countries
     *
     * @return array
     */
    public function getExcludedCountries()
    {
        return $this->excludedCountries;
    }
}
