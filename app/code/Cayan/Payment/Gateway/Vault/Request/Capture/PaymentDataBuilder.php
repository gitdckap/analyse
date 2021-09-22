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

namespace Cayan\Payment\Gateway\Vault\Request\Capture;

use Cayan\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Vault\Model\PaymentTokenFactory;

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
     * @var PaymentTokenFactory
     */
    private $paymentTokenFactory;

    /**
     * @param \Cayan\Payment\Gateway\Helper\SubjectReader $subjectReader
     * @param \Magento\Vault\Model\PaymentTokenFactory $paymentTokenFactory
     */
    public function __construct(SubjectReader $subjectReader, PaymentTokenFactory $paymentTokenFactory)
    {
        $this->subjectReader = $subjectReader;
        $this->paymentTokenFactory = $paymentTokenFactory;
    }

    /**
     * Construct the request data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $cardHolder = $payment->getAdditionalInformation('holder_name');
        $publicHash = $payment->getAdditionalInformation('public_hash');
        $vaultToken = $this->paymentTokenFactory->create()->getCollection()
            ->addFieldToFilter('public_hash', $publicHash)
            ->setPageSize(1)
            ->getFirstItem()
            ->getGatewayToken();
        /** @var \Magento\Customer\Model\Address $billingAddress */
        $billingAddress = $paymentDataObject->getPayment()->getOrder()->getBillingAddress();
        $originalStreet = $billingAddress->getStreet();
        $street = is_array($originalStreet) ? implode(' ', $originalStreet) : $originalStreet;

        $data = [
            self::CODE => [
                'Source' => 'Vault',
                'VaultToken' => $vaultToken,
                'CardHolder' => $cardHolder,
                'AvsStreetAddress' => $street,
                'AvsZipCode' => $billingAddress->getPostcode()
            ]
        ];

        return $data;
    }
}
