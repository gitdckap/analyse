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

namespace Cayan\Payment\Gateway\Credit\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Cayan\Payment\Gateway\Helper\SubjectReader;

/**
 * Payment Data Builder
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class PaymentDataBuilder implements BuilderInterface
{
    const CODE = 'PaymentData';

    /**
     * @var \Cayan\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;

    /**
     * @param \Cayan\Payment\Gateway\Helper\SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * Construct the request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $vaultToken = $payment->getAdditionalInformation('payment_method_nonce');

        if (!is_null($vaultToken)) {
            $data = [
                self::CODE => [
                    'Source' => 'Vault',
                    'VaultToken' => $vaultToken
                ]
            ];
        } else {
            //Else is when is used the admin payment.
            /** @var \Magento\Customer\Model\Address $billingAddress */
            $billingAddress = $paymentDataObject->getPayment()->getOrder()->getBillingAddress();
            $originalStreet = $billingAddress->getStreet();
            $street = is_array($originalStreet) ? implode(' ', $originalStreet) : $originalStreet;
            $cardHolder = $payment->getAdditionalInformation('cc_holder_name');
            $month = (int)$payment->getAdditionalInformation('cc_exp_month');
            if ($month < 10) {
                $month = '0' . $month;
            } else {
                $month = (string)$month;
            }
            $expiration = $month . substr($payment->getAdditionalInformation('cc_exp_year'), -2);

            $data = [
                self::CODE => [
                    'Source' => 'Keyed',
                    'CardNumber' => $payment->getAdditionalInformation('cc_number'),
                    'ExpirationDate' => $expiration,
                    'CardHolder' => $cardHolder,
                    'AvsStreetAddress' => $street,
                    'AvsZipCode' => $billingAddress->getPostcode(),
                    'CardVerificationValue' => $payment->getAdditionalInformation('cc_cvv')
                ]
            ];
        }

        return $data;
    }
}
