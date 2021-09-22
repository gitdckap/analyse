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

namespace Bss\CheckoutCustomField\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Bss\CheckoutCustomField\Api\Data\AttributeInterface;

/**
 * Class Attribute
 * @package Bss\CheckoutCustomField\Model
 * @SuppressWarnings(PHPMD)
 */
class Attribute extends \Magento\Eav\Model\Entity\Attribute\AbstractAttribute implements AttributeInterface
{
    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;

    /**
     * Attribute constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param JsonHelper $jsonHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        JsonHelper $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $eavConfig,
            $eavTypeFactory,
            $storeManager,
            $resourceHelper,
            $universalFactory,
            $optionDataFactory,
            $dataObjectProcessor,
            $dataObjectHelper
        );
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Bss\CheckoutCustomField\Model\ResourceModel\Attribute::class);
    }

    protected $_cacheTag = 'bss_checkout_attribute';
 
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'bss_checkout_attribute';

    /**
     * Get ATTRIBUTE ID.
     *
     * @return int
     */
    public function getAttributeId()
    {
        return $this->getData(self::BSS_ATTRIBUTE_ID);
    }

    /**
     * @param int $attributeId
     * @return Attribute|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function setAttributeId($attributeId)
    {
        return $this->setData(self::BSS_ATTRIBUTE_ID, $attributeId);
    }
 
    /**
     * Get ATTRIBUTE CODE.
     *
     * @return varchar
     */
    public function getAttributeCode()
    {
        return $this->getData(self::BSS_ATTRIBUTE_CODE);
    }

    /**
     * @param string $attributeCode
     * @return Attribute|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function setAttributeCode($attributeCode)
    {
        return $this->setData(self::BSS_ATTRIBUTE_CODE, $attributeCode);
    }
 
    /**
     * Get BACKEND LABEL.
     *
     * @return varchar
     */
    public function getBackendLabel()
    {
        return $this->getData(self::BSS_BACKEND_LABEL);
    }

    /**
     * @param string $backendLabel
     * @return Attribute
     */
    public function setBackendLabel($backendLabel)
    {
        return $this->setData(self::BSS_BACKEND_LABEL, $backendLabel);
    }

    /**
     * @param null $storeId
     * @return \Bss\CheckoutCustomField\Api\Data\varchar|void
     */
    public function getFrontendLabel($storeId = null)
    {
        if (!$this->getData(self::BSS_FRONTEND_LABEL)) {
            return;
        }
        $label = $this->jsonHelper->jsonDecode($this->getData(self::BSS_FRONTEND_LABEL));
        if ($storeId == null) {
            $storeId = 0;
        }
        if (isset($label[$storeId]) && $label[$storeId] != "") {
            return $label[$storeId];
        }
        return $label[0];
    }

    /**
     * @param string $fontendLabel
     * @return Attribute
     */
    public function setFrontendLabel($fontendLabel)
    {
        return $this->setData(
            self::BSS_FRONTEND_LABEL,
            $this->jsonHelper->jsonEncode($fontendLabel)
        );
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend|mixed|void
     */
    public function getFrontend()
    {
        if (!$this->getData(self::BSS_FRONTEND_LABEL)) {
            return;
        }
        return $this->jsonHelper->jsonDecode($this->getData(self::BSS_FRONTEND_LABEL));
    }

    /**
     * Get FRONTEND CLASS.
     *
     * @return varchar
     */
    public function getFrontendClass()
    {
        return $this->getData(self::BSS_FRONTEND_CLASS);
    }

    /**
     * @param string $fontendClass
     * @return Attribute|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function setFrontendClass($fontendClass)
    {
        return $this->setData(self::BSS_FRONTEND_CLASS, $fontendClass);
    }

    /**
     * @return \Bss\CheckoutCustomField\Api\Data\varchar|mixed|string
     */
    public function getFrontendInput()
    {
        return $this->getData(self::BSS_FRONTEND_INPUT);
    }

    /**
     * @param string $fontendInput
     * @return Attribute|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function setFrontendInput($fontendInput)
    {
        return $this->setData(self::BSS_FRONTEND_INPUT, $fontendInput);
    }

    /**
     * Get IS REQUIRED.
     *
     * @return int
     */
    public function getIsRequired()
    {
        return $this->getData(self::BSS_IS_REQUIRED);
    }

    /**
     * @param bool $isRequired
     * @return Attribute|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function setIsRequired($isRequired)
    {
        return $this->setData(self::BSS_IS_REQUIRED, $isRequired);
    }

    /**
     * Get Store ID.
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData(self::BSS_STORE_ID);
    }

    /**
     * @param int $storeId
     * @return Attribute
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::BSS_STORE_ID, explode(',', $storeId));
    }

    /**
     * Get IS USER DEFIEND.
     *
     * @return int
     */
    public function getIsUserDefiend()
    {
        return $this->getData(self::BSS_IS_USER_DEFIEND);
    }

    /**
     * @param $isUserDefiend
     * @return Attribute
     */
    public function setIsUserDefiend($isUserDefiend)
    {
        return $this->setData(self::BSS_IS_USER_DEFIEND, $isUserDefiend);
    }
    
    /**
     * Get DEFAULT VALUE.
     *
     * @return varchar
     */
    public function getDefaultValue()
    {
        return $this->getData(self::BSS_DEFAULT_VALUE);
    }

    /**
     * @param string $defaultValue
     * @return Attribute|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function setDefaultValue($defaultValue)
    {
        return $this->setData(self::BSS_DEFAULT_VALUE, $defaultValue);
    }

    /**
     * Get VISIBLE FRONTEND.
     *
     * @return int
     */
    public function getVisibleFrontend()
    {
        return $this->getData(self::BSS_VISIBLE_FRONTEND);
    }

    /**
     * @param bool $visibleFrontend
     * @return Attribute
     */
    public function setVisibleFrontend($visibleFrontend)
    {
        return $this->setData(self::BSS_VISIBLE_FRONTEND, $visibleFrontend);
    }

    /**
     * Get VISIBLE BACKEND.
     *
     * @return int
     */
    public function getVisibleBackend()
    {
        return $this->getData(self::BSS_VISIBLE_BACKEND);
    }

    /**
     * @param bool $visibleBackend
     * @return Attribute
     */
    public function setVisibleBackend($visibleBackend)
    {
        return $this->setData(self::BSS_VISIBLE_BACKEND, $visibleBackend);
    }

    /**
     * Get SHOW IN.
     *
     * @return int
     */
    public function getShowIn()
    {
        return $this->getData(self::BSS_SHOW_IN_SHIPPING);
    }

    /**
     * @param mixed $showIn
     * @return Attribute
     */
    public function setShowIn($showIn)
    {
        return $this->setData(self::BSS_SHOW_IN_SHIPPING, $showIn);
    }

    /**
     * Get SORT ORDER.
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData(self::BSS_SORT_ORDER);
    }

    /**
     * @param string $sortOrder
     * @return Attribute
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(self::BSS_SORT_ORDER, $sortOrder);
    }

    /**
     * Get SAVE IN FUTURE.
     *
     * @return int
     */
    public function getSaveInFuture()
    {
        return $this->getData(self::BSS_SAVE_IN_FUTURE);
    }

    /**
     * @param mixed $saveInFuture
     * @return Attribute
     */
    public function setSaveInFuture($saveInFuture)
    {
        return $this->setData(self::BSS_SAVE_IN_FUTURE, $saveInFuture);
    }

    /**
     * Get SHOW GIRD.
     *
     * @return int
     */
    public function getShowGird()
    {
        return $this->getData(self::BSS_SHOW_GIRD);
    }

    /**
     * @param string $showGird
     * @return Attribute
     */
    public function setShowGird($showGird)
    {
        return $this->setData(self::BSS_SHOW_GIRD, $showGird);
    }

    /**
     * Get SHOW IN ORDER.
     *
     * @return int
     */
    public function getShowInOrder()
    {
        return $this->getData(self::BSS_SHOW_IN_ORDER);
    }

    /**
     * @param string $showInOrder
     * @return Attribute
     */
    public function setShowInOrder($showInOrder)
    {
        return $this->setData(self::BSS_SHOW_IN_ORDER, $showInOrder);
    }

    /**
     * Get SHOW IN PDF.
     *
     * @return int
     */
    public function getShowInPdf()
    {
        return $this->getData(self::BSS_SHOW_IN_PDF);
    }

    /**
     * @param string $showInPdf
     * @return Attribute
     */
    public function setShowInPdf($showInPdf)
    {
        return $this->setData(self::BSS_SHOW_IN_PDF, $showInPdf);
    }

    /**
     * Get SHOW IN EMAIL.
     *
     * @return int
     */
    public function getShowInEmail()
    {
        return $this->getData(self::BSS_SHOW_IN_EMAIL);
    }

    /**
     * @param string $showInEmail
     * @return Attribute
     */
    public function setShowInEmail($showInEmail)
    {
        return $this->setData(self::BSS_SHOW_IN_EMAIL, $showInEmail);
    }

    /**
     * Detect default value using frontend input type
     *
     * @param string $type frontend_input field name
     * @return string default_value field value
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getDefaultValueByInput($type)
    {
        $field = '';
        switch ($type) {
            case 'select':
            case 'dropdown':
                break;
            case 'multiselect':
                $field = null;
                break;

            case 'text':
                $field = 'default_value_text';
                break;

            case 'textarea':
                $field = 'default_value_textarea';
                break;

            case 'date':
                $field = 'default_value_date';
                break;

            case 'boolean':
                $field = 'default_value_yesno';
                break;

            default:
                break;
        }

        return $field;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCustomFieldChekout()
    {
        $collections = $this->getCollection()
            ->addFieldToFilter(self::BSS_VISIBLE_FRONTEND, ['eq' => 1]);
        return $collections;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCustomFieldCreateOrder()
    {
        $collections = $this->getCollection()
            ->addFieldToFilter(self::BSS_VISIBLE_BACKEND, ['eq' => 1]);
        return $collections;
    }

    /**
     * @param $array
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollectionByCode($array)
    {
        $collections = $this->getCollection()
            ->addFieldToFilter(self::BSS_ATTRIBUTE_CODE, ['in' => $array]);
        return $collections;
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCustomFieldAdminGird()
    {
        $collections = $this->getCollection()
            ->addFieldToFilter(self::BSS_SHOW_GIRD, ['in' => [1,0]]);
        return $collections;
    }
}
