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

namespace Dckap\Elementpayment\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Quote\Api\Data\PaymentInterface;

/**
 * Observer class for Assigning the Data
 */
class DataAssignObserver extends AbstractDataAssignObserver
{
    const PAYMENT_ACCOUNT_ID = '';
    /**
     * @var array
     */
    protected $additionalInformationList = [
        self::PAYMENT_ACCOUNT_ID

    ];

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        $paymentInfo = $this->readPaymentModelArgument($observer);

        $paymentInfo->setAdditionalInformation($additionalData);
    }
}
