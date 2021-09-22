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

namespace Cayan\Payment\Gateway\Credit\Validator;

use Cayan\Payment\Gateway\Config\Credit\Config;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Response Validator
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class ResponseValidator extends AbstractValidator
{
    const APPROVED_STATUS = 'APPROVED';

    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    protected $config;

    /**
     * @param \Magento\Payment\Gateway\Validator\ResultInterfaceFactory $resultFactory
     * @param \Cayan\Payment\Gateway\Config\Credit\Config $config
     */
    public function __construct(ResultInterfaceFactory $resultFactory, Config $config)
    {
        parent::__construct($resultFactory);

        $this->config = $config;
    }

    /**
     * Validate the response
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $response = $validationSubject['response'];
        $isOk = true;
        $errors = [];

        if (empty($response) || !isset($response['ErrorMessage']) || $response['ErrorMessage'] !== ''
            || $response['ApprovalStatus'] !== self::APPROVED_STATUS) {
            $isOk = false;
            $errors[] = __('Payment declined, please check your credit card and try again.');
        }

        return $this->createResult($isOk, $errors);
    }
}
