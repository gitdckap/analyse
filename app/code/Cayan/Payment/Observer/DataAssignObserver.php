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

namespace Cayan\Payment\Observer;

use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Data Assign Observer
 *
 * @package Cayan\Payment\Observer
 * @author Igor Miura
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const PAYMENT_METHOD_NONCE = 'payment_method_nonce';
    const PAYMENT_CC_NUMBER = 'cc_number';
    const PAYMENT_CC_CVV = 'cc_cvv';
    const PAYMENT_CC_EXP_YEAR = 'cc_exp_year';
    const PAYMENT_CC_EXP_MONTH = 'cc_exp_month';
    const PAYMENT_CC_HOLDER_NAME = 'cc_holder_name';
    const PAYMENT_VAULT_PUBLIC_HASH = 'public_hash';
    const PAYMENT_VAULT_HOLDER_NAME = 'holder_name';

    /**
     * @var array
     */
    private $additionalInformationList = [
        self::PAYMENT_METHOD_NONCE,
        self::PAYMENT_CC_NUMBER,
        self::PAYMENT_CC_CVV,
        self::PAYMENT_CC_EXP_YEAR,
        self::PAYMENT_CC_EXP_MONTH,
        self::PAYMENT_CC_HOLDER_NAME,
        self::PAYMENT_VAULT_PUBLIC_HASH,
        self::PAYMENT_VAULT_HOLDER_NAME
    ];

    /**
     * Add credit card info to additional data field of payment
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        if (!is_array($additionalData)) {
            return;
        }

        $paymentInfo = $this->readPaymentModelArgument($observer);

        foreach ($this->additionalInformationList as $additionalInformationKey) {
            if (array_key_exists($additionalInformationKey, $additionalData)) {
                $paymentInfo->setAdditionalInformation(
                    $additionalInformationKey,
                    $additionalData[$additionalInformationKey]
                );
            }
        }
    }
}
