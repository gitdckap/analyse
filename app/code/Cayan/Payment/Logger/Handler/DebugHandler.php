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

namespace Cayan\Payment\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Debug Log Handler
 *
 * @package Cayan\Payment\Logger
 * @author Joseph Leedy
 */
class DebugHandler extends Base
{
    protected $fileName = '/var/log/cayan_payment_debug.log';
    protected $loggerType = Logger::DEBUG;

    /**
     * {@inheritdoc}
     * @param bool $bubble
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
        $bubble = false
    ) {
        parent::__construct($filesystem, $filePath);

        $this->setBubble($bubble);
    }
}
