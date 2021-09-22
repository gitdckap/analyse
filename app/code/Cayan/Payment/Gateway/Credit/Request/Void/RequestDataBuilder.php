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

namespace Cayan\Payment\Gateway\Credit\Request\Void;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Cayan\Payment\Gateway\Helper\SubjectReader;

/**
 * Credentials Data Builder
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class RequestDataBuilder implements BuilderInterface
{
    const CODE = 'Request';

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
        $order = $paymentDataObject->getPayment()->getOrder();
        $paymentToken = $paymentDataObject->getPayment()->getAdditionalInformation('token');
        $data = [
            self::CODE => [
                'Token' => (string)$paymentToken,
                'MerchantTransactionId' => (string)$order->getData('quote_id')
            ]
        ];

        return $data;
    }
}
