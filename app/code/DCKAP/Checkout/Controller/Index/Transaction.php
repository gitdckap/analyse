<?php

namespace Dckap\Checkout\Controller\Index;

class Transaction extends \Magento\Framework\App\Action\Action
{

    protected $customerSession;
    protected $resultPageFactory;
    protected $_checkoutSession;
    protected $paymentConfig;
    protected $encryptor;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Checkout\Model\SessionFactory $_checkoutSession,
        \Dckap\Checkout\Gateway\PaymentConfig $paymentConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->_checkoutSession = $_checkoutSession;
        $this->paymentConfig = $paymentConfig;
        $this->encryptor = $encryptor;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $customer = $this->customerSession->getCustomer();
        $checkoutSession = $this->_checkoutSession->create();
        $checkoutReviewData = $checkoutSession->getCheckoutData();
        $amount = $checkoutReviewData['order_total'];
        $authorizedAmount = (float)$params['AmountApproved'];
        if ($authorizedAmount < $amount) {
            $merchantName = $this->paymentConfig->getMerchantName();
            $merchantSiteId = $this->encryptor->decrypt($this->paymentConfig->getMerchantSiteId());
            $merchantKey = $this->encryptor->decrypt($this->paymentConfig->getMerchantKey());
            $token = $params['Token'];
            $curl = curl_init();
            curl_setopt_array($curl, [
            CURLOPT_URL => "https://ps1.merchantware.net/Merchantware/ws/RetailTransaction/v46/Credit.asmx",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "<?xml version=\"1.0\" encoding=\"utf-8\"?>
                                    <soap12:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:soap12=\"http://www.w3.org/2003/05/soap-envelope\">
                                        <soap12:Body>
                                            <Void xmlns=\"http://schemas.merchantwarehouse.com/merchantware/v46/\">
                                                <Credentials>
                                                    <MerchantName>".$merchantName."</MerchantName>
                                                    <MerchantSiteId>".$merchantSiteId."</MerchantSiteId>
                                                    <MerchantKey>".$merchantKey."</MerchantKey>
                                                </Credentials>
                                                <Request>
                                                    <Token>".$token."</Token>
                                                    <RegisterNumber>123</RegisterNumber>
                                                    <CardAcceptorTerminalId>32</CardAcceptorTerminalId>
                                                </Request>
                                            </Void>
                                        </soap12:Body>
                                    </soap12:Envelope>",
            CURLOPT_HTTPHEADER => [
                "cache-control: no-cache",
                "content-type: text/xml"
            ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                $voiderror  = "cURL Error #:" . $err;
            } else {
                $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
                $xml = simplexml_load_string($clean_xml);
                $voidResult = (array)$xml->Body->VoidResponse->VoidResult;
                    
                if ($voidResult['ApprovalStatus'] = 'APPROVED') {
                } else {
                }
            }
        }

        // ends void transaction if amount not matched only for checkout page
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
