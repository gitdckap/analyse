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

namespace Cayan\Payment\Block;

use Magento\Payment\Block\ConfigurableInfo;

/**
 * Info Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class Info extends ConfigurableInfo
{
    /**
     * Retrieve label
     *
     * @param string $field
     * @return \Magento\Framework\Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }
}
