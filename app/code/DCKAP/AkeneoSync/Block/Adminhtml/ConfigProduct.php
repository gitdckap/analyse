<?php

namespace DCKAP\AkeneoSync\Block\Adminhtml;

use \Magento\Backend\Block\Template;
use \Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class ConfigProduct extends Template
{
    protected $scopeConfig;

    private $_eavConfig;
    protected $_encryptor;
    protected $date;

    const XML_PATH_AKENEO_CONFIG = 'dckap_akeneosync/general/enabled';
    const XML_PATH_AKENEO_PAGINATION = 'dckap_akeneosync/general/pagination';

    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CollectionFactory $collection,
        ProductAction $action,
        StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $data = []
    )
    {
        parent::__construct($context, $data);
        $this->productCollection = $collection;
        $this->productAction = $action;
        $this->storeManager = $storeManager;
        $this->_eavConfig = $eavConfig;
        $this->_encryptor = $encryptor;
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
    }

    public function getAkeneoConfig()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_AKENEO_CONFIG, $storeScope);
    }

    public function getPaginationValue()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::XML_PATH_AKENEO_PAGINATION, $storeScope);
    }


    public function getItemCount()
    {
        $token = $this->getToken();

        if ($token) {
            $ContentType = 'application/json';
            $authValue = 'Bearer ' . $token;

            $headers = [
                'Content-Type' => $ContentType,
                'Authorization' => $authValue,
            ];
            $pageNo = 1;
            $limit = 2;

            $mode = $this->getConfigValue('akeneo_connector/products_filters/mode');
            $search_query = "";
            if ($mode == "standard") {
                $search_filter1 = $this->addStandardFilter();
                if ($search_filter1) {
                    $search_query = 'search=' .urlencode('{') . $search_filter1. urlencode('}');
                }

            } elseif ($mode == "advanced") {
                $msg = "Please choose filter mode value as standard";
                return $msg;
            }

            $url = $this->getConfigValue('akeneo_connector/akeneo_api/base_url');
            $lastChar = substr($url, -1);
            $slash = ($lastChar == "/") ? "" : "/";
            $serviceUrl = $url . $slash . "api/rest/v1/product-models?" . $search_query . "&page=" . $pageNo . "&with_count=true&pagination_type=page&limit=" . $limit;
            $requestData = [];

            $response = $this->clientCurl($method = 'GET', $serviceUrl, $headers, $requestData);

            if ($response->getStatus() == 200) {
                $responseBody = $response->getBody();
                $results = json_decode($response->getBody(), true);
                $items = $results['_embedded']['items'];
                $total_count = $results["items_count"];
                if ($total_count) {
                    return $total_count;
                }
                else{
                    $msg = "No Data found for the selected Date";
                    return $msg;
                }
            } else {
                return "Import Fail";
            }

        }

    }


    public function getToken()
    {

        $clientId = $this->getConfigValue('akeneo_connector/akeneo_api/client_id');
        $secret = $this->getConfigValue('akeneo_connector/akeneo_api/client_secret');
        $username = $this->getConfigValue('akeneo_connector/akeneo_api/username');
        $encrypt_password = $this->getConfigValue('akeneo_connector/akeneo_api/password');
        $password = $this->_encryptor->decrypt($encrypt_password);
        $url = $this->getConfigValue('akeneo_connector/akeneo_api/base_url');
        $lastChar = substr($url, -1);
        $slash = ($lastChar == "/") ? "" : "/";
        $authServiceUrl = $url . $slash . "api/oauth/v1/token";
        $authToken = "";

        if ($clientId && $secret && $username && $password && $authServiceUrl) {
            $encode_value = base64_encode($clientId . ":" . $secret);
            $authReqArray = array("grant_type" => "password", "username" => $username, "password" => $password);
            $authReq = json_encode($authReqArray);

            $authHeaders = [
                'Content-Type' => 'application/json',
                'Authorization' => "Basic " . $encode_value,
            ];

            $tokenResponse = $this->clientCurl($method = 'POST', $authServiceUrl, $authHeaders, $authReq);
            if ($tokenResponse->getStatus() == 200) {
                $tokenResult = json_decode($tokenResponse->getBody(), true);
                $authToken = isset($tokenResult['access_token']) ? $tokenResult['access_token'] : "";
            }
        }
        return $authToken;
    }

    public function clientCurl($method, $serviceUrl, $headers, $requestData = '')
    {
        $response = '';
        try {
            $client = new \Zend_Http_Client();
            $client->setUri($serviceUrl);
            $client->setConfig(array('timeout' => 3000));

            if ($requestData) {
                $client->setRawData($requestData);
            }

            $client->setHeaders($headers);
            $response = $client->request($method);

            return $response;
        } catch (\Zend\Http\Client\Exception $e) {
            return $e->getMessage();
        }

        return $response;
    }

    public function getConfigValue($path)
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $storeScope);
    }

    public function addStandardFilter()
    {
        $query = "";
        $updated_mode = $this->getConfigValue('akeneo_connector/products_filters/updated_mode');
        switch ($updated_mode) {
            case ">":
                $value = $this->getConfigValue("akeneo_connector/products_filters/updated_greater") . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('">"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('"') . $value . urlencode('"}]');
                break;
            case "<":
                $value = $this->getConfigValue("akeneo_connector/products_filters/updated_lower") . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('"<"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('"') . $value . urlencode('"}]');
                break;
            case "SINCE LAST N DAYS":
                $value = $this->getConfigValue("akeneo_connector/products_filters/updated");
                $dateVal = date("Y-m-d", strtotime("-" . $value . " day")) . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('">"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('"') . $dateVal . urlencode('"}]');
                break;
            case "BETWEEN":
                $value1 = $this->getConfigValue("akeneo_connector/products_filters/updated_between_before") . "%2000:00:00";
                $value2 = $this->getConfigValue("akeneo_connector/products_filters/updated_between_after") . "%2000:00:00";
                $query = urlencode('"') . 'updated' . urlencode('"') . ':' . urlencode('[{"') . 'operator' . urlencode('"') . ':' . urlencode('"BETWEEN"') . ',' . urlencode('"') . 'value' . urlencode('"') . ':' . urlencode('["') . $value2 . urlencode('"') . ',' . urlencode('"') . $value1 . urlencode('"]}]');
                break;
            default :
                $query = "";
        }

        return $query;
    }


}
