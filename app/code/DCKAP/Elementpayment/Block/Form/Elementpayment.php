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

namespace Dckap\Elementpayment\Block\Form;

class Elementpayment extends \Magento\Payment\Block\Form
{
    /**
     * Purchase order template
     *
     * @var string
     */
    protected $_template = 'Dckap_Elementpayment::form/elementpayment.phtml';
}
