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

namespace Cayan\Payment\Gateway\Credit\Request\Capture;

use Cayan\Payment\Gateway\Helper\SubjectReader;
use Cayan\Payment\Helper\Data as DataHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Request Data Builder
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
     * @var \Cayan\Payment\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Cayan\Payment\Gateway\Helper\SubjectReader $subjectReader
     * @param \Cayan\Payment\Helper\Data $dataHelper
     */
    public function __construct(
        SubjectReader $subjectReader,
        DataHelper $dataHelper
    ) {
        $this->subjectReader = $subjectReader;
        $this->dataHelper = $dataHelper;
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
        /** @var \Magento\Sales\Model\Order $order */
        $order = $paymentDataObject->getPayment()->getOrder();
        $finalAmount = (float)$order->getBaseGrandTotal();

        if (!$order->getId()) {
            $data = [
                self::CODE => [
                    'Amount' => (string)$finalAmount,
                    'TaxAmount' => (string)$order->getTaxAmount(),
                    'InvoiceNumber' => $this->dataHelper->truncateOrderNumber((string)$order->getIncrementId()),
                    'CustomerCode' => $order->getCustomerIsGuest() ? '0' : (string)$order->getCustomerId(),
                    'MerchantTransactionId' => (string)$order->getQuoteId()
                ]
            ];
        } else {
            $data = [
                self::CODE => [
                    'Token' => $paymentDataObject->getPayment()->getAdditionalInformation('token'),
                    'Amount' => (string)$finalAmount,
                    'InvoiceNumber' => $this->dataHelper->truncateOrderNumber((string)$order->getIncrementId()),
                    'RegisterNumber' => (string)$order->getQuoteId(),
                    'MerchantTransactionId' => (string)$order->getQuoteId()
                ]
            ];
        }
        return $data;
    }
}
