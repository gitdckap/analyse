<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutCustomField\Helper;

use Magento\Store\Model\StoreManagerInterface as StoreId;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_SECURE_IN_FRONTEND = 'web/secure/use_in_frontend';

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var string
     */
    protected $_configSectionId = 'custom_field';

    /**
     * @var StoreId
     */
    protected $storeId;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Serialize
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * Data constructor.
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param StoreId $storeId
     * @param \Magento\Framework\Serialize\Serializer\Serialize $serializer
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        StoreId $storeId,
        \Magento\Framework\Serialize\Serializer\Serialize $serializer,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->localeDate = $localeDate;
        $this->moduleManager = $moduleManager;
        $this->productMetadata = $productMetadata;
        $this->storeId = $storeId;
        $this->logger = $context->getLogger();
        $this->serializer = $serializer;
        $this->json = $json;
        parent::__construct($context);
    }

    /**
     * @return int
     */
    public function IntlDateFormatterShort()
    {
        return \IntlDateFormatter::SHORT;
    }

    /**
     * @param null|string $data
     * @return array|mixed
     */
    public function checkSerialize($data)
    {
        if ($data == null || $data == '') {
            return [];
        }
        $version = $this->productMetadata->getVersion();
        $checkVersion = version_compare($version, '2.2.0', '>=');

        if ($checkVersion) {
            $additionalData = $this->json->unserialize($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Unable to unserialize value.');
            }
        } else {
            $additionalData = $this->serializer->unserialize($data);
        }
        return $additionalData;
    }

    /**
     * @param string $path
     * @param null $store
     * @param null $scope
     * @return mixed
     */
    public function getConfig($path, $store = null, $scope = null)
    {
        if ($scope === null) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        }
        return $this->scopeConfig->getValue($path, $scope, $store);
    }

    /**
     * @param null $store
     * @param null $scope
     * @return bool
     */
    public function moduleEnabled($store = null, $scope = null)
    {
        return (bool)$this->getConfig($this->_configSectionId.'/general/enable', $store, $scope);
    }

    /**
     * @param null $store
     * @param null $scope
     * @return string
     */
    public function getTitle($store = null, $scope = null)
    {
        return (string)$this->getConfig($this->_configSectionId.'/general/title', $store, $scope);
    }

    /**
     * @param null $store
     * @param null $scope
     * @return bool
     */
    protected function hasPdfView($store = null, $scope = null)
    {
        return (bool)$this->getConfig($this->_configSectionId.'/general/pdf_view', $store, $scope);
    }

    /**
     * @return bool
     */
    protected function hasEmailView()
    {
        return (bool)$this->getConfig($this->_configSectionId.'/general/email_view');
    }

    /**
     * @param null $store
     * @param null $scope
     * @return bool
     */
    public function showInPdf($store = null, $scope = null)
    {
        return $this->moduleEnabled($store, $scope) && $this->hasPdfView($store, $scope);
    }

    /**
     * @return bool
     */
    public function showInEmail()
    {
        return $this->moduleEnabled() && $this->hasEmailView();
    }

    /**
     * @param string $json
     * @return string
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getVariableEmailHtml($json)
    {
        $html = '';
        if ($this->showInEmail() && $json && $this->hasDataCustomFieldEmail($json)) {
                $html = '<h3>' . $this->getTitle() . '</h3>';
                $bssCustomfield = $this->json->unserialize($json);
            foreach ($bssCustomfield as $key => $field) {
                if ($field['show_in_email'] == '1') {
                    $fieldText = $field['frontend_label'] . ': ';
                    if ((is_array($field['value']) && !empty($field['value'])) || $field['value'] != "") {
                        $html .= "<span>" . $this->setFieldTextHtml($field, $fieldText) . "</span><br/>";
                    }
                }
            }
        }
        return $html;
    }

    /**
     * @param array $field
     * @param string $fieldText
     * @return string
     */
    private function setFieldTextHtml($field, $fieldText)
    {
        if (is_array($field['value'])) {
            foreach ($field['value'] as $value) {
                $fieldText .= $value . ",";
            }
        } else {
            $fieldText .= $field['value'];
        }
        return $fieldText;
    }

    /**
     * @return int
     */
    public function getCurrentStoreId()
    {
        return $this->storeId->getStore()->getId();
    }

    /**
     * @param array $customField
     * @return bool
     */
    public function hasDataCustomFieldOrder($customField)
    {
        $customField = $this->json->unserialize($customField);
        foreach ($customField as $field) {
            if ($field['show_in_order'] == '1') {
                if (is_array($field['value']) && !empty($field['value'])) {
                    return true;
                } elseif ($field['value'] != "") {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $customField
     * @return bool
     */
    public function hasDataCustomFieldPdf($customField)
    {
        $customField = $this->json->unserialize($customField);
        foreach ($customField as $field) {
            if ($field['show_in_email'] == '1') {
                if (is_array($field['value']) && !empty($field['value'])) {
                    return true;
                } elseif ($field['value'] != "") {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $customField
     * @return bool
     */
    public function hasDataCustomFieldEmail($customField)
    {
        $customField = $this->json->unserialize($customField);
        foreach ($customField as $field) {
            if ($field['show_in_pdf'] == '1') {
                if (is_array($field['value']) && !empty($field['value'])) {
                    return true;
                } elseif ($field['value'] != "") {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $attributeCode
     * @param string  $customField
     * @return bool
     */
    public function isValueOrderByAttributeCode($attributeCode, $customField)
    {
        $customField = $this->json->unserialize($customField);
        if (isset($customField[$attributeCode])) {
            return $customField[$attributeCode]['val'];
        }
        return false;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getSaveCustomFieldtoQuote()
    {
        return $this->_getUrl(
            'customfield/express/saveCustomField',
            ['_secure' => true]
        );
    }

    /**
     * @return bool
     */
    public function isFormatDate()
    {
        return $this->getConfig($this->_configSectionId.'/general/date_format') == 1;
    }

    /**
     * @param string $moduleName
     * @return bool
     */
    public function isModuleInstall($moduleName)
    {
        if ($this->moduleManager->isEnabled($moduleName)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        $dateFormat = $this->scopeConfig->getValue(
            'orderdeliverydate/general/date_fields',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($dateFormat) {
            switch ($dateFormat) {
                case 1:
                    return 'mm/dd/yy';
                case 2:
                    return 'dd-mm-yy';
                case 3:
                    return 'yy-mm-dd';
                default:
                    return 'mm/dd/yy';
            }
        }
        return 'mm/dd/yy';
    }

    /**
     * @param null $date
     * @return string
     */
    public function formatDate($date = null)
    {
        $dateFormat = $this->getDateFormat();
        if ($dateFormat) {
            switch ($dateFormat) {
                case 'mm/dd/yy':
                    $dateFormat = 'm/d/Y';
                    break;
                case 'dd-mm-yy':
                    $dateFormat = 'd-m-Y';
                    break;
                case 'yy-mm-dd':
                    $dateFormat = 'Y-m-d';
                    break;
                default:
                    $dateFormat = 'm/d/y';
                    break;
            }
        }
        if ($date) {
            return $this->localeDate->scopeDate(null, $date, false)->format($dateFormat);
        } else {
            return $dateFormat;
        }
    }
}
