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

namespace Cayan\Payment\Gateway\Credit\Request\Refund;

use Cayan\Payment\Gateway\Helper\SubjectReader;
use Cayan\Payment\Model\Helper\Discount as CayanHelper;
use Magento\Payment\Gateway\Request\BuilderInterface;

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
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $helper;

    /**
     * @param \Cayan\Payment\Gateway\Helper\SubjectReader $subjectReader
     * @param \Cayan\Payment\Model\Helper\Discount $cayanHelper
     */
    public function __construct(SubjectReader $subjectReader, CayanHelper $cayanHelper)
    {
        $this->subjectReader = $subjectReader;
        $this->helper = $cayanHelper;
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
        $paymentToken = $paymentDataObject->getPayment()->getAdditionalInformation('token');
        $data = [
            self::CODE => [
                'Source' => 'PreviousTransaction',
                'Token' => $paymentToken
            ]
        ];

        return $data;
    }
}
