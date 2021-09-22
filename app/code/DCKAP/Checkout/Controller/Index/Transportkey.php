<?php

namespace Dckap\Checkout\Controller\Index;

class Transportkey extends \Magento\Framework\App\Action\Action
{

    protected $customerSession;
    protected $resultJsonFactory;
    protected $paymentConfig;
    protected $_encryptor;
    protected $quote;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Dckap\Checkout\Gateway\PaymentConfig $paymentConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->paymentConfig = $paymentConfig;
        $this->_encryptor = $encryptor;
        $this->quote = $quote;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $responseData = $data = [];
        try {
            $params = $this->getRequest()->getParams();
            $quote = $this->quote->create()->load($params['quote_id']);
            $amount = $orderNumber = $customerCode = $redirectLocation = '';
            $orderNumber = (int)$quote->getId();
            if (isset($params['amount'])) {
                $amount = (float)$params['amount'];
            } else {
                $amount = (float)$quote->getGrandTotal();
            }
            $taxAmount = 0.00;
            if (isset($params['tax_amount'])) {
                $taxAmount = (float)$params['tax_amount'];
            }
            $customerCode = (int)$quote->getCustomerId();
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $redirectLocation = $baseUrl.'dckapcheckout/index/transaction';

            $merchantName = $this->paymentConfig->getMerchantName();
            $merchantSiteId = $this->_encryptor->decrypt($this->paymentConfig->getMerchantSiteId());
            $merchantKey = $this->_encryptor->decrypt($this->paymentConfig->getMerchantKey());

            $displayColors = '<DisplayColors><screenBackgroundColor>FFFFFF</screenBackgroundColor><containerBackgroundColor>FFFFFF</containerBackgroundColor></DisplayColors>';

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => "https://transport.merchantware.net/v4/transportService.asmx",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "<?xml version=\"1.0\" encoding=\"utf-8\"?><soap:Envelope xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\"><soap:Body><CreateTransaction xmlns=\"http://transport.merchantware.net/v4/\"><merchantName>".$merchantName."</merchantName><merchantSiteId>".$merchantSiteId."</merchantSiteId><merchantKey>".$merchantKey."</merchantKey><request><TransactionType>LEVEL2SALE</TransactionType><Amount>".$amount."</Amount><ClerkId>DDI</ClerkId><OrderNumber>".$orderNumber."</OrderNumber><Dba>DDI System - Development</Dba><SoftwareName>DDI nForm</SoftwareName><SoftwareVersion>21.0.21.7</SoftwareVersion><ForceDuplicate>false</ForceDuplicate><CustomerCode>".$customerCode."</CustomerCode><PoNumber>NA</PoNumber><TaxAmount>".$taxAmount."</TaxAmount><RedirectLocation>".$redirectLocation."</RedirectLocation>".$displayColors."<DisplayOptions><AlignLeft>false</AlignLeft><NoCardNumberMask>true</NoCardNumberMask><HideDetails>true</HideDetails><HideDowngradeMessage>true</HideDowngradeMessage><HideMessage>true</HideMessage><HideTooltips>true</HideTooltips><UseNativeButtons>true</UseNativeButtons></DisplayOptions><EntryMode>Keyed</EntryMode><TerminalId>02</TerminalId><EnablePartialAuthorization>true</EnablePartialAuthorization></request></CreateTransaction>\n</soap:Body></soap:Envelope>",
                CURLOPT_HTTPHEADER => [
                    "cache-control: no-cache",
                    "content-type: text/xml"
                ],
            ]);
//<RedirectLocation>http://127.0.0.1/ddisystem/dckapcheckout/index/dupccform</RedirectLocation>
            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $responseData['error']['msg']  = "cURL Error #:" . $err;
            } else {
                $clean_xml = str_ireplace(['SOAP-ENV:', 'SOAP:'], '', $response);
                $xml = simplexml_load_string($clean_xml);
                $createTransactionResult = (array)$xml->Body->CreateTransactionResponse->CreateTransactionResult;
                $responseData['success']['key'] = $createTransactionResult;
                /*$responseData['html'] = '<form id="Form1" method="post" action="https://transport.merchantware.net/v4/transportweb.aspx"><input type="text" name="transportKey" id="transportKey" value="'.$responseData['success']['key']['TransportKey'].'" /><input type="submit" value="Submit" /></form>';*/
            }
        } catch (\Exception $e) {
            $responseData['error']['msg'] = $e->getMessage();
        }
        return $resultJson->setData($responseData);
    }
}
