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

class GiftTest extends TestCase
{

    /**
     * @var \Cayan\Payment\Model\Api\Card\Api
     */
    protected $cardHelper;

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
        $this->cardHelper = $objectManager->getObject('Cayan\Payment\Model\Api\Card\Api');
        $wsdl = "https://ps1.merchantware.net/Merchantware/ws/ExtensionServices/v45/Giftcard.asmx?WSDL";
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
     * Test Activate card.
     */
    public function testActivateCard()
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
                    "Source" => "KEYED",
                    "CardNumber" => "4012000033330026"
                ),
                "Request" => array(
                    "Amount" => "1000",
                    "InvoiceNumber" => "Transaction1000"
                )
            );
            $data = $this->soapClient->ActivateCard($params);
        } catch (\Exception $ex) {
            $this->cardHelper->logMessage("testActivateCard error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
    * Test Activate card.
    */
    public function testAddValue()
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
                    "Source" => "KEYED",
                    "CardNumber" => "0000987662954"
                ),
                "Request" => array(
                    "Amount" => "10.80",
                    "InvoiceNumber" => "Transaction1000"
                )
            );
            $data = $this->soapClient->AddValue($params);
        } catch (\Exception $ex) {
            $this->cardHelper->logMessage("testAddValue error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
    * Test balance inquiry.
    */
    public function testBalanceInquiry()
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
                    "Source" => "KEYED",
                    "CardNumber" => "4012000033330026"
                ),
                "Request" => array(
                    "InvoiceNumber" => "Transaction1000"
                )
            );
            $data = $this->soapClient->BalanceInquiry($params);
        } catch (\Exception $ex) {
            $this->cardHelper->logMessage("testBalanceInquiry error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
     * Test gift card sale.
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
                    "Source" => "KEYED",
                    "CardNumber" => "4012000033330026"
                ),
                "Request" => array(
                    "Amount" => "1.29",
                    "InvoiceNumber" => "Transaction1000",
                    "EnablePartialAuthorization" => "True"
                )
            );
            $data = $this->soapClient->Sale($params);
        } catch (\Exception $ex) {
            $this->cardHelper->logMessage("Sale error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }

    /**
     * Test void transaction.
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
                "Request" => array(
                    "Token" => "1234567890",
                    "InvoiceNumber" => "Transaction1000"
                )
            );
            $data = $this->soapClient->Void($params);
        } catch (\Exception $ex) {
            $this->cardHelper->logMessage("Void error: ".$ex->getMessage());
        }
        $this->assertNotNull($data);
    }
}
