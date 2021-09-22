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

namespace Cayan\Payment\Gateway\Credit\Response;

use Cayan\Payment\Gateway\Helper\SubjectReader;
use Cayan\Payment\Gateway\Config\Credit\Config as CreditConfig;
use Magento\Payment\Gateway\Response\HandlerInterface;

/**
 * Payment Details Handler
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class PaymentDetailsHandler implements HandlerInterface
{
    /**
     * @var \Cayan\Payment\Gateway\Helper\SubjectReader
     */
    private $subjectReader;
    /**
     * @var CreditConfig
     */
    private $creditConfig;

    /**
     * @param SubjectReader $subjectReader
     * @param CreditConfig $creditConfig
     */
    public function __construct(SubjectReader $subjectReader, CreditConfig $creditConfig)
    {
        $this->subjectReader = $subjectReader;
        $this->creditConfig = $creditConfig;
    }

    /**
     * Handle the response
     *
     * @param array $handlingSubject
     * @param array $response
     * @throws \Exception
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDataObject = $this->subjectReader->readPayment($handlingSubject);
        $payment = $paymentDataObject->getPayment();

        $payment->setCcTransId($response['AuthorizationCode']);
        $payment->setLastTransId($response['AuthorizationCode']);
        $payment->setAdditionalInformation('token', $response['Token']);
        $payment->setAdditionalInformation('cc_type', $response['CardType']);
        $payment->setAdditionalInformation('cc_card_number', $response['CardNumber']);

        if ($this->creditConfig->getPaymentAction() == CreditConfig::PAYMENT_ACTION_AUTHORIZE) {
            /** @var \Magento\Sales\Model\Order\Payment $orderPayment */
            $payment = $paymentDataObject->getPayment();
            $payment->setTransactionId($response['AuthorizationCode']);
            $payment->setIsTransactionClosed(0);
        }
    }
}
