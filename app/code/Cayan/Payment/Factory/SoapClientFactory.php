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

namespace Cayan\Payment\Factory;

/**
 * SoapClient Factory
 *
 * @package Cayan\Payment\Factory
 * @author Joseph Leedy
 */
class SoapClientFactory
{
    /**
     * Configures and initializes an instance of the SoapClient object
     *
     * @see http://php.net/soapclient
     * @param string|null $wsdl
     * @param array $options
     * @return \SoapClient
     */
    public function create($wsdl = null, array $options = [])
    {
        return new \SoapClient($wsdl, $options);
    }
}
