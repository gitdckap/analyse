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

namespace Cayan\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Gift Card Code History Resource Model
 *
 * @package Cayan\Payment\Model
 */
class CodeHistory extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('cayan_codes_history', 'history_id');
    }
}
