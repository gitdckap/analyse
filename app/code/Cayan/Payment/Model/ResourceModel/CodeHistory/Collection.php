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

namespace Cayan\Payment\Model\ResourceModel\CodeHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Gift Card Code History Collection
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Cayan\Payment\Model\CodeHistory',
            'Cayan\Payment\Model\ResourceModel\CodeHistory'
        );
    }
}
