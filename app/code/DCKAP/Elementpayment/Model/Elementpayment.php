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

namespace Dckap\Elementpayment\Model;

/**
 * Pay In Store payment method model
 */
class Elementpayment extends \Magento\Payment\Model\Method\AbstractMethod
{

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'elementpayment';

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;
}
