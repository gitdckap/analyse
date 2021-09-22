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

namespace Cayan\Payment\Gateway\Vault\Request;

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
     * @var \Magento\Vault\Model\PaymentTokenFactory
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
     * Construct request data
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $paymentDataObject = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDataObject->getPayment();
        $publicHash = $payment->getAdditionalInformation('public_hash');
        $vaultToken = $this->paymentTokenFactory->create()->getCollection()
            ->addFieldToFilter('public_hash', $publicHash)
            ->setPageSize(1)
            ->getFirstItem()
            ->getGatewayToken();
        $data = [
            self::CODE => [
                'Source' => 'Vault',
                'VaultToken' => $vaultToken
            ]
        ];

        return $data;
    }
}
