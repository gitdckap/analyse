<?php

namespace Dckap\Checkout\Controller\Payinvoice;

class Authorize extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $tansactionSaleHelper;
    protected $dckapCheckoutHelper;
    protected $clorasHelper;
    protected $clorasDDIHelper;
    protected $extensionHelper;
    protected $paymentConfig;
    protected $anetConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Dckap\Checkout\Helper\TransactionSale $tansactionSaleHelper,
        \Dckap\Checkout\Helper\Data $dckapCheckoutHelper,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper,
        \Magento\AuthorizenetAcceptjs\Gateway\Config $paymentConfig,
        \AuthorizeNet\Core\Gateway\Config\Config $anetConfig
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->tansactionSaleHelper = $tansactionSaleHelper;
        $this->dckapCheckoutHelper = $dckapCheckoutHelper;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->extensionHelper = $extensionHelper;
        $this->paymentConfig = $paymentConfig;
        $this->anetConfig = $anetConfig;

        parent::__construct($context);
    }

    public function execute()
    {
        $responseData = $data = [];
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $params = $this->getRequest()->getParams();
            /*
            $config = $this->paymentConfig;
            $loginId = $config->getLoginId();
            $transactionKey = $config->getTransactionKey();
            $environment = $config->getEnvironment();
            $paymentAction = $config->getPaymentAction();
            */
            
            $anetConfig=$this->anetConfig;
            $loginId=$anetConfig->getLoginId();
            $transactionKey = $anetConfig->getTransKey();
            $environment= ($anetConfig->isTestMode()==1)?'sandbox':'production';
            $paymentAction=$anetConfig->getConfigValue($field='payment_action', $code='anet_creditcard', null);

            $transactionType = 'authOnlyTransaction';
     
            $cardNumber = $params['payment']['cc_number'];
            $expDate = $params['payment']['cc_exp_month'] . $params['payment']['cc_exp_year'];
            $cvv = $params['payment']['cc_cid'];
            $invoiceNumber = $params['invoice'];

            if ($environment == 'sandbox') {
                $url = "https://apitest.authorize.net/xml/v1/request.api";
            } else {
                $url = "https://api.authorize.net/xml/v1/request.api";
            }

            if ($paymentAction == 'authorize_capture') {
                $transactionType = 'authCaptureTransaction';
            }

            if (isset($params['invoice']) && $params['invoice'] != '') {
                $customerId = $this->customerSession->getCustomerId();
                $customerName = $this->customerSession->getCustomer()->getName();
                $customerEmail = $this->customerSession->getCustomer()->getEmail();
                $billingAddress = $this->customerSession->getCustomer()->getDefaultBillingAddress();
                $shipingAddress = $this->customerSession->getCustomer()->getDefaultShippingAddress();

                $billTo = "<billTo><firstName>" . $billingAddress->getFirstname() . "</firstName><lastName>" . $billingAddress->getLastname() . "</lastName><address>" . $billingAddress->getStreetFull() . "</address><city>" . $billingAddress->getCity() . "</city><state>" . $billingAddress->getRegionCode() . "</state><zip>" . $billingAddress->getPostcode() . "</zip><country>" . $billingAddress->getCountryId() . "</country></billTo>";

                $shipTo = "<shipTo><firstName>" . $shipingAddress->getFirstname() . "</firstName><lastName>" . $shipingAddress->getLastname() . "</lastName><address>" . $shipingAddress->getStreetFull() . "</address><city>" . $shipingAddress->getCity() . "</city><state>" . $shipingAddress->getRegionCode() . "</state><zip>" . $shipingAddress->getPostcode() . "</zip><country>" . $shipingAddress->getCountryId() . "</country></shipTo>";

                $data['invoice'] = $this->dckapCheckoutHelper->getInvoices(['data' => $params['invoice']]);

                $amount = $data['invoice']['total'];

                $curl = curl_init();
                curl_setopt_array($curl, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => "<createTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">
  <merchantAuthentication>
	<name>" . $loginId . "</name>
	<transactionKey>" . $transactionKey . "</transactionKey>
  </merchantAuthentication>
  <transactionRequest>
	<transactionType>" . $transactionType . "</transactionType>
	<amount>" . $amount . "</amount>
	<payment>
	  <creditCard>
		<cardNumber>" . $cardNumber . "</cardNumber>
		<expirationDate>" . $expDate . "</expirationDate>
		<cardCode>" . $cvv . "</cardCode>
	  </creditCard>
	</payment>
	<order>
	  <invoiceNumber>" . $invoiceNumber . "</invoiceNumber>
	  <description>Invoice Payment from ECOMM PRO</description>
	</order>
	<lineItems>
	  <lineItem>
		<itemId>1</itemId>
		<name>Invoice Payment</name>
		<description>Invoice Payment from ECOMM PRO</description>
		<quantity>1</quantity>
		<unitPrice>" . $amount . "</unitPrice>
	  </lineItem>
	</lineItems>
	<customer>
	  <id>" . $customerId . "</id>
	  <email>" . $customerEmail . "</email>
	</customer>" . $billTo . $shipTo . "</transactionRequest>
</createTransactionRequest>",
                    CURLOPT_HTTPHEADER => [
                        "cache-control: no-cache",
                        "content-type: text/xml"
                    ],
                ]);
                $response = curl_exec($curl);
                $err = curl_error($curl);
                curl_close($curl);
                if ($err) {
                    $this->messageManager->addErrorMessage($err);
                    $redirectUrl = $this->_url->getUrl('dckapcheckout/index/index');
                    $redirectUrl .= '?data='.$invoiceNumber;
                    return $resultRedirect->setPath($redirectUrl);
                } else {
                    $clean_xml = str_ireplace(['<?xml version="1.0" encoding="utf-8"?>', ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd"'], '', $response);
                    $xml = simplexml_load_string($clean_xml, "SimpleXMLElement", LIBXML_NOCDATA);
                    $json = json_encode($xml);
                    $paymentData = json_decode($json, true);
                    if (isset($paymentData['messages']['resultCode']) && $paymentData['messages']['resultCode'] == 'Error') {
                        $this->messageManager->addErrorMessage(str_replace('AnetApi/xml/v1/schema/AnetApiSchema.xsd:', '', $paymentData['messages']['message']['text']));
                        $redirectUrl = $this->_url->getUrl('dckapcheckout/index/index');
                        $redirectUrl .= '?data='.$invoiceNumber;
                        return $resultRedirect->setPath($redirectUrl);
                    } else {
                        $data['cc_amount_approved'] = (string)$amount;
                        $ccType = '';
                        if ($paymentData['transactionResponse']['accountType'] == 'Visa') {
                            $ccType = '4';
                        } elseif ($paymentData['transactionResponse']['accountType'] == 'Mastercard') {
                            $ccType = '3';
                        } elseif ($paymentData['transactionResponse']['accountType'] == 'AmericanExpress') {
                            $ccType = '1';
                        } elseif ($paymentData['transactionResponse']['accountType'] == 'Discover') {
                            $ccType = '3';
                        }
                        $data['cc_type'] = $ccType;
                        $data['cc_number'] = $paymentData['transactionResponse']['accountNumber'];
                        $data['cc_holder'] = $customerName;
                        $data['cc_token'] = $paymentData['transactionResponse']['transId'];

                        $erpResponseData = $this->submitPayInvoice($data);
                        if (isset($erpResponseData['data']['isValid']) && $erpResponseData['data']['isValid'] == 'yes') {
                            $responseData['status'] = 'success';
                            $responseData['msg'] = 'Payment submitted successfully';
                        } else {
                            $responseData['status'] = 'failure';
                            $errorMsg = is_string($erpResponseData)?$erpResponseData:'Something went wrong ';
                            $responseData['msg'] = isset($erpResponseData['data']['errorMessage'])?$erpResponseData['data']['errorMessage']:$errorMsg;
                        }
                    }
                }
            } else {
                $responseData['status'] = 'failure';
                $responseData['msg'] = 'Invalid invoice details';
            }
        } catch (\Exception $e) {
            $responseData['status'] = 'failure';
            $responseData['msg'] = 'Invalid invoice details';
        }

        if ($responseData['status'] == 'success') {
            $this->messageManager->addSuccessMessage($responseData['msg']);
        } elseif ($responseData['status'] == 'failure') {
            $this->messageManager->addErrorMessage($responseData['msg']);
        } else {
            $this->messageManager->addErrorMessage(__("Something went wrong. Try again later"));
        }
        $redirectUrl = $this->_url->getUrl('quickrfq/invoice/summary');
        return $resultRedirect->setPath($redirectUrl);
    }

    protected function submitPayInvoice($data)
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('pay_invoice');
        if ($status) {
            $responseData = $this->clorasDDIHelper->submitPayment($integrationData, $data);
            if ($responseData && !empty($responseData)) {
                return $responseData;
            }
        }
        return false;
    }
}
