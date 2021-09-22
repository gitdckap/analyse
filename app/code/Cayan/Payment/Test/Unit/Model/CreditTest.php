<?php

/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Test\Unit\Model;

use PHPUnit\Framework\TestCase as TestCase;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class CreditTest extends TestCase
{
    /**
     * @var \Cayan\Payment\Model\Api\Credit\Api
     */
    protected $creditHelper;

    /**
     * @var \SoapClient
     */
    protected $soapClient;

    /**
     * CreditTest setup.
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->creditHelper = $objectManager->getObject('Cayan\Payment\Model\Api\Credit\Api');
        $wsdl = "https://ps1.merchantware.net/Merchantware/ws/RetailTransaction/v45/Credit.asmx?WSDL";
        $options = array(
            'uri' => 'http://www.w3.org/2003/05/soap-envelope',
            'location' => $wsdl,
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
        );
        $this->soapClient = new \SoapClient($wsdl, $options);
    }

    /**
     * Test Authorize Payment.
     */
    public function testAuthorize()
    {
        $data = null;
        try {
            $params = array(
                "Credentials" => array(
                    "MerchantName" => "Test Cayan Magento 2",
                    "MerchantSiteId" => "WKXP8VWN",
                    "MerchantKey" => "XSWW3-2TXCJ-50NV0-R3VTI-BUMMN"
                ),
                "PaymentData" => array(
                    "Source" => "Keyed",
                    "CardNumber" => "4012000033330026",
                    "ExpirationDate" => "1218",
                    "CardHolder" => "John Doe",
                    "AvsStreetAddress" => "1 Federal Street",
                    "AvsZipCode" => "02110",
                    "CardVerificationValue" => "123"
                ),
                "Request" => array(
                    "Amount" => "1.05",
                    "InvoiceNumber" => "1556",
                    "RegisterNumber" => "35",
                    "MerchantTransactionId" => "167901",
                )
            );
            $data = $this->soapClient->Authorize($params);
        } catch (\Exception $ex) {
            $this->creditHelper->logMessage("CreditTest error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
     * Test Api Sale
     */
    public function testSale()
    {
        $data = null;
        try {
            $params = array(
                "Credentials" => array(
                    "MerchantName" => "Test Cayan Magento 2",
                    "MerchantSiteId" => "WKXP8VWN",
                    "MerchantKey" => "XSWW3-2TXCJ-50NV0-R3VTI-BUMMN"
                ),
                "PaymentData" => array(
                    "Source" => "Keyed",
                    "CardNumber" => "4012000033330026",
                    "ExpirationDate" => "1218",
                    "CardHolder" => "John Doe",
                    "AvsStreetAddress" => "1 Federal Street",
                    "AvsZipCode" => "02110",
                    "CardVerificationValue" => "123"
                ),
                "Request" => array(
                    "Amount" => "1.05",
                    "CashBackAmount" => "0.00",
                    "SurchargeAmount" => "0.00",
                    "TaxAmount" => "0.00",
                    "InvoiceNumber" => "1556",
                    "PurchaseOrderNumber" => "1556",
                    "CustomerCode" => "20",
                    "RegisterNumber" => "35",
                    "MerchantTransactionId" => "166901",
                    "CardAcceptorTerminalId" => "3",
                    "EnablePartialAuthorization" => "False",
                    "ForceDuplicate" => "False"
                )
            );
            $data = $this->soapClient->Sale($params);
        } catch (\Exception $ex) {
            $this->creditHelper->logMessage("CreditTest error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
     * Test capture api method.
     */
    public function testCapture()
    {
        $data = null;
        try {
            $params = array(
                "Credentials" => array(
                    "MerchantName" => "Test Cayan Magento 2",
                    "MerchantSiteId" => "WKXP8VWN",
                    "MerchantKey" => "XSWW3-2TXCJ-50NV0-R3VTI-BUMMN"
                ),
                "Request" => array(
                    "Token" => "608939",
                    "Amount" => "2.99",
                    "InvoiceNumber" => "1556",
                    "RegisterNumber" => "35",
                    "MerchantTransactionId" => "167902",
                    "CardAcceptorTerminalId" => "3"
                )
            );
            $data = $this->soapClient->Capture($params);
        } catch (\Exception $ex) {
            $this->creditHelper->logMessage("CreditTest error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
     * Test refund transaction.
     */
    public function testRefund()
    {
        $data = null;
        try {
            $params = array(
                "Credentials" => array(
                    "MerchantName" => "Test Cayan Magento 2",
                    "MerchantSiteId" => "WKXP8VWN",
                    "MerchantKey" => "XSWW3-2TXCJ-50NV0-R3VTI-BUMMN"
                ),
                "PaymentData" => array(
                    "Source" => "Keyed",
                    "CardNumber" => "4012000033330026",
                    "ExpirationDate" => "1218",
                    "CardHolder" => "John Doe"
                ),
                "Request" => array(
                    "Amount" => "4.01",
                    "InvoiceNumber" => "1701",
                    "RegisterNumber" => "35",
                    "MerchantTransactionId" => "165901",
                    "CardAcceptorTerminalId" => "3"
                )
            );
            $data = $this->soapClient->Refund($params);
        } catch (\Exception $ex) {
            $this->creditHelper->logMessage("CreditTest error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
     * Test void a transaction.
     */
    public function testVoid()
    {
        $data = null;
        try {
            $params = array(
                "Credentials" => array(
                    "MerchantName" => "Test Cayan Magento 2",
                    "MerchantSiteId" => "WKXP8VWN",
                    "MerchantKey" => "XSWW3-2TXCJ-50NV0-R3VTI-BUMMN"
                ),
                "PaymentData" => array(
                    "Source" => "Keyed",
                    "CardNumber" => "4012000033330026",
                    "ExpirationDate" => "1218",
                    "CardHolder" => "John Doe"
                ),
                "Request" => array(
                    "Token" => "608973",
                    "RegisterNumber" => "35",
                    "MerchantTransactionId" => "167901",
                    "CardAcceptorTerminalId" => "3"
                )
            );
            $data = $this->soapClient->Void($params);
        } catch (\Exception $ex) {
            $this->creditHelper->logMessage("CreditTest error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }
}
