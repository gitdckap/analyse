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

namespace Cayan\Payment\Model\Api\Credit\Vault;

use Cayan\Payment\Model\Api\Credit\Api as CreditApi;

/**
 * Vault Credit API Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class Api extends CreditApi
{
    /**
     * Authorize payment using vault
     *
     * @param array $params
     * @return \stdClass|null
     */
    public function authorize($params)
    {
        return parent::authorize($params);
    }

    /**
     * Authorize & Capture payment using vault
     *
     * @param array $params
     * @return \stdClass|null
     */
    public function sale($params)
    {
        return parent::sale($params);
    }
}
