<?php
/**
 * @author DCKAP Team
 * @copyright Copyright (c) 2017 DCKAP (https://www.dckap.com)
 * @package DCKAP_Elementpayment
 */

/**
 * Copyright © 2017 DCKAP. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DCKAP\Elementpayment\Block\Info;

class Elementpayment extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'DCKAP_Elementpayment::form/elementpayment.phtml';
    /**
     * @var string
     */
    protected $_infoBlockType = 'DCKAP\Elementpayment\Block\Info\Elementpayment';

    /**
     * @return string
     */
    public function toPdf()
    {
        $this->setTemplate('DCKAP_Elementpayment::info/pdf/elementpayment.phtml');
        return $this->toHtml();
    }
}
