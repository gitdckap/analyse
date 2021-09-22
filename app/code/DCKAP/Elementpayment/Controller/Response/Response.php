<?php
/**
 * @author DCKAP Team
 * @copyright Copyright (c) 2017 DCKAP (https://www.dckap.com)
 * @package Dckap_Elementpayment
 */

/**
 * Copyright Â© 2017 DCKAP. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Dckap\Elementpayment\Controller\Response;

use Magento\Framework\Controller\ResultFactory;

class Response extends \Magento\Framework\App\Action\Action
{
    protected $registry;

    public function __construct(\Magento\Framework\App\Action\Context $context)
    {
        parent::__construct($context);
    }

    
    public function execute()
    {
        $response = $this->getRequest()->getParams();
             
        if ($response['HostedPaymentStatus'] == 'Complete') {
            if ($response['ExpressResponseCode'] == 0) {
                $paymentId = $response['PaymentAccountID'];
                $reports = '</script>';
                $reports .= '
				<script type="text/javascript"> window.parent.document.getElementById("element_transaction_id").value = "' . $paymentId . '";
				window.parent.jQuery("#placeorder").css("style","display:block");
                    window.parent.jQuery("#placeorder").trigger( "click" );
				</script>';
//                $resultJson = $this->resultFactory->create();
//                $resultJson->setData($reports);
//                return $resultJson;
                $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
                $response->setContents($reports);
                return $response;
            }
        } elseif ($response['HostedPaymentStatus'] == 'Cancelled') {

            $reports = '</script>
				<script type="text/javascript"> window.parent.location.reload();
				</script>';

//            $resultJson = $this->resultFactory->create();
//            $resultJson->setData($reports);
//            return $resultJson;
            $response = $this->resultFactory->create(ResultFactory::TYPE_RAW);
            $response->setContents($reports);
            return $response;

        }
    }
}
