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

namespace Cayan\Payment\Gateway\Credit\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;

/**
 * Transfer Factory
 *
 * @package Cayan\Payment\Gateway
 * @author Igor Miura
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilder
     */
    private $transferBuilder;

    /**
     * @param \Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder
     */
    public function __construct(
        TransferBuilder $transferBuilder
    ) {
        $this->transferBuilder = $transferBuilder;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return \Magento\Payment\Gateway\Http\TransferInterface
     */
    public function create(array $request)
    {
        return $this->transferBuilder
            ->setBody($request)
            ->build();
    }
}
