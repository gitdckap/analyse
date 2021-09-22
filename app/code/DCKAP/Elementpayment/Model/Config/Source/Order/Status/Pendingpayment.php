<?php
/**
 * @author DCKAP Team
 * @copyright Copyright (c) 2017 DCKAP (https://www.dckap.com)
 * @package Dckap_Elementpayment
 */

/**
 * Copyright © 2017 DCKAP. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dckap\Elementpayment\Model\Config\Source\Order\Status;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Config\Source\Order\Status;

/**
 * Order Status source model
 */
class Pendingpayment extends Status
{
    /**
     * @var string[]
     */
    protected $_stateStatuses = [Order::STATE_PENDING_PAYMENT];
}
