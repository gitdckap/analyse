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

namespace Cayan\Payment\Gateway\Validator;

use Cayan\Payment\Gateway\Config\Credit\Config;
use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterfaceFactory;

/**
 * Country Validator
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class CountryValidator extends AbstractValidator
{
    /**
     * @var \Cayan\Payment\Gateway\Config\Credit\Config
     */
    private $config;

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
     * Validate country
     *
     * @param array $validationSubject
     * @return \Magento\Payment\Gateway\Validator\ResultInterface
     */
    public function validate(array $validationSubject)
    {
        $isValid = true;
        $storeId = $validationSubject['storeId'];

        if ((int)$this->config->getValue('allowspecific', $storeId) === 1) {
            $availableCountries = explode(',', $this->config->getValue('specificcountry', $storeId));

            if (!in_array($validationSubject['country'], $availableCountries)) {
                $isValid = false;
            }
        }

        return $this->createResult($isValid);
    }
}
