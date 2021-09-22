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

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Vault Data Builder
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class VaultDataBuilder implements BuilderInterface
{
    /**
     * Additional options in request to gateway
     */
    const OPTIONS = 'options';
    /**
     * The option that determines whether the payment method associated with
     * the successful transaction should be stored in the Vault.
     */
    const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

    /**
     * Construct the request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        $data = [
            self::OPTIONS => [
                self::STORE_IN_VAULT_ON_SUCCESS => true
            ]
        ];

        return $data;
    }
}
