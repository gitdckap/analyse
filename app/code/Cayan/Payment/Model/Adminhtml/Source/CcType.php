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

use Magento\Payment\Model\Source\Cctype as PaymentCcType;

/**
 * Credit Card Type Source Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class CcType extends PaymentCcType
{
    /**
     * List of specific credit card types
     *
     * @var array
     */
    private $specificCardTypesList = [
    ];

    /**
     * Allowed credit card types
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'MI', 'DN'];
    }

    /**
     * Returns credit cards types
     *
     * @return array
     */
    public function getCcTypeLabelMap()
    {
        return array_merge($this->specificCardTypesList, $this->_paymentConfig->getCcTypes());
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->getCcTypeLabelMap() as $code => $name) {
            if (in_array($code, $allowed)) {
                $options[] = ['value' => $name, 'label' => $name];
            }
        }

        // Add Cayan additional flags
        $options[] = ['value' => 'Wex', 'label' => 'Wex'];
        $options[] = ['value' => 'Voyager', 'label' => 'Voyager'];
        $options[] = ['value' => 'ChinaUnionPay', 'label' => 'China Union Pay'];
        $options[] = ['value' => 'Debit', 'label' => 'Debit'];

        return $options;
    }

    /**
     * Return credit card types to be used on admin checkout
     *
     * @return array
     */
    public function toArrayForm()
    {
        $finalValues = [];

        foreach ($this->toOptionArray() as $option) {
            $finalValues[$option['value']] = $option['label'];
        }

        return $finalValues;
    }
}
