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

use Cayan\Payment\Gateway\Config\General as CayanGatewayConfig;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Credentials Data Builder
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class CredentialsDataBuilder implements BuilderInterface
{
    const CODE = 'Credentials';

    /**
     * @var \Cayan\Payment\Gateway\Config\General
     */
    private $config;

    /**
     * @param \Cayan\Payment\Gateway\Config\General $config
     */
    public function __construct(CayanGatewayConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Construct the request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        return [
            self::CODE => [
                'MerchantName' => $this->config->getMerchantName(),
                'MerchantSiteId' => $this->config->getMerchantSiteId(),
                'MerchantKey' => $this->config->getMerchantKey()
            ]
        ];
    }
}
