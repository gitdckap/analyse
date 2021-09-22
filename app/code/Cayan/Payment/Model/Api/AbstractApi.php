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

namespace Cayan\Payment\Model\Api;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Cayan\Payment\Helper\Data;

/**
 * Base API Model
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
abstract class AbstractApi extends AbstractModel
{
    const XML_PATH_GENERAL_CONFIG_PATH = 'payment/cayangeneral/';
    const XML_PATH_CC_CONFIG_PATH = 'payment/cayancc/';
    const XML_PATH_GIFT_CONFIG_PATH = 'payment/giftcard/';
    const XML_PATH_GENERAL_PAYMENT_TYPE = 1;
    const XML_PATH_CC_PAYMENT_TYPE = 2;
    const XML_PATH_GIFT_PAYMENT_TYPE = 3;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Registry $scopeConfig
     * @param \Cayan\Payment\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $scopeConfig,
        Data $helper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
    }

    /**
     * Create the soap request to be used when calling the API
     *
     * @param string $url
     * @return \SoapClient
     */
    public function buildRequest($url)
    {
        $options = array(
            'uri' => 'http://www.w3.org/2003/05/soap-envelope',
            'location' => $url,
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
            'soap_version' => SOAP_1_1,
            'cache_wsdl' => WSDL_CACHE_NONE,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
        );

        return new \SoapClient($url, $options);
    }
}
