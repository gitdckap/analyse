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

namespace Dckap\Elementpayment\Block;
class Elementpayment extends \Magento\Framework\View\Element\Template
{
    protected $scopeConfig;

    /**
     * Constructor
     *
     * @param FilterInterface[] $filterList
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {

        $this->scopeConfig = $scopeConfig;


    }

    public function getElementPayment($payment_account_id)
    {
        $AcceptorID = $this->scopeConfig->getValue('payment/elementpayment/acceptor_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_token = $this->scopeConfig->getValue('payment/elementpayment/account_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_id = $this->scopeConfig->getValue('payment/elementpayment/account_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $input_xml = $input_xml = "<PaymentAccountQuery xmlns='https://services.elementexpress.com'>
<Credentials>
<AccountID>$account_id</AccountID>
<AccountToken>$account_token</AccountToken>
<AcceptorID>$AcceptorID</AcceptorID>
</Credentials>
<Application>
<ApplicationID>7681</ApplicationID>
<ApplicationName>Express.PHP</ApplicationName>
<ApplicationVersion>1.0.0</ApplicationVersion>
</Application>

<PaymentAccountParameters>
        <PaymentAccountID>$payment_account_id</PaymentAccountID>
      </PaymentAccountParameters>
</PaymentAccountQuery>";

        $url = "https://certservices.elementexpress.com";

        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml', 'Expect: '));
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);

// Following line is compulsary to add as it is:
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // echo curl_error($ch);
        curl_close($ch);
        $xml = simplexml_load_string($output);

//echo $xml->ExpressResponseCode;
        if ($xml->Response->ExpressResponseCode == 0) {
            return $xml;
        }

        return '';
    }


    public function getTransactionDetails($payment_account_id, $grand_total, $order_id)
    {

        $AcceptorID = $this->scopeConfig->getValue('payment/elementpayment/acceptor_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_token = $this->scopeConfig->getValue('payment/elementpayment/account_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $account_id = $this->scopeConfig->getValue('payment/elementpayment/account_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $order_id = $order_id[0];

        $ticket_number = rand(0, 6);
        $grand_total = number_format($grand_total, 2, '.', '');
        $input_xml = $input_xml = "<CreditCardAuthorization xmlns='https://transaction.elementexpress.com'>  
  <Credentials>  
  <AccountID>$account_id</AccountID>  
 <AccountToken>$account_token</AccountToken>  
  <AcceptorID>$AcceptorID</AcceptorID>  
  </Credentials>  
  <Application>  
  <ApplicationID>7681</ApplicationID>  
  <ApplicationName>Express.PHP</ApplicationName>  
  <ApplicationVersion>1.0.0</ApplicationVersion>  
  </Application>  
  <Terminal>  
  <TerminalID>01</TerminalID>  
  <CardholderPresentCode>4</CardholderPresentCode>  
  <CardInputCode>4</CardInputCode>  
  <TerminalCapabilityCode>5</TerminalCapabilityCode> 
  <TerminalType>3</TerminalType>
  <TerminalEnvironmentCode>2</TerminalEnvironmentCode>  
  <CardPresentCode>3</CardPresentCode>  
  <MotoECICode>2</MotoECICode>  
  <CVVPresenceCode>1</CVVPresenceCode>  
  </Terminal>  
 <PaymentAccount>
          <PaymentAccountID>$payment_account_id</PaymentAccountID>
          <PaymentAccountType>0</PaymentAccountType>
          <PaymentAccountReferenceNumber>$order_id</PaymentAccountReferenceNumber>
          <PASSUpdaterBatchStatus>0</PASSUpdaterBatchStatus>
        <PASSUpdaterOption>0</PASSUpdaterOption>
        </PaymentAccount> 
  <Transaction>  
  <TransactionAmount>$grand_total</TransactionAmount>  
  <MarketCode>2</MarketCode>  
  <ReferenceNumber>$order_id</ReferenceNumber>
  <TicketNumber>$ticket_number</TicketNumber>
  </Transaction>  
  </CreditCardAuthorization>";

        $url = "https://certtransaction.elementexpress.com/";

        //setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0)');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: text/xml', 'Expect: '));
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);

// Following line is compulsary to add as it is:
        curl_setopt($ch, CURLOPT_POSTFIELDS, $input_xml);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
        $output = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // echo curl_error($ch);
        curl_close($ch);
        $xml = simplexml_load_string($output);  

        if ($xml->Response->ExpressResponseCode == 0) {
            return $xml;
        }

        return '';
    }

}
