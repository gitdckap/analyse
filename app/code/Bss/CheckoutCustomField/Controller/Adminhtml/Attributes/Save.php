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
namespace Bss\CheckoutCustomField\Controller\Adminhtml\Attributes;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $resigtry;

    /**
     * @var \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $model;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $catalogUrl;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param JsonHelper $jsonHelper
     * @param \Bss\CheckoutCustomField\Model\Attribute $model
     * @param \Magento\Catalog\Model\Product\Url $catalogUrl
     * @param \Magento\Framework\Registry $resigtry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        JsonHelper $jsonHelper,
        \Bss\CheckoutCustomField\Model\Attribute $model,
        \Magento\Catalog\Model\Product\Url $catalogUrl,
        \Magento\Framework\Registry $resigtry,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->validatorFactory = $validatorFactory;
        $this->layoutFactory = $layoutFactory;
        $this->resigtry = $resigtry;
        $this->model = $model;
        $this->catalogUrl = $catalogUrl;
        $this->jsonHelper = $jsonHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if (isset($data['option'])) {
            $this->setRegisterKey($data);
        }
        $attributeId = $this->getRequest()->getParam('id');
        $attributeCode = $this->getAttributeCode();
        $strlen = strlen($attributeCode);
        
        //validate frontend_input
        if ($this->checkValidateFrontInput($data, $attributeId)) {
            return $this->checkValidateFrontInput($data, $attributeId);
        }

        if ($attributeId) {
            $this->model->load($attributeId);
            if (!$this->model->getId()) {
                $this->messageManager->addErrorMessage(__('This attribute no longer exists.'));
                return $this->returnResult('bss_customfield/*/', [], ['error' => true]);
            }

            $data['attribute_code'] = $this->model->getAttributeCode();
            $data['frontend_input'] = $this->model->getFrontendInput();
        } else {
            if ($strlen > 0) {
                if (!$this->isValid('/^[a-z][a-z_0-9]{0,30}$/', $attributeCode)) {
                    $this->messageManager->addErrorMessage(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    return $this->returnResult(
                        'bss_customfield/*/edit',
                        ['id' => $attributeId, '_current' => true],
                        ['error' => true]
                    );
                }
            }
            $data['attribute_code'] = $attributeCode;
        }
        $singleStoreId = $this->storeManager->getStore()->getId();
        $data['store_id'] = $this->getDataStoreId($data, $singleStoreId);
        $label = $this->getRequest()->getParam('frontend_label');
        $data['backend_label'] = $label[0];
        $data['frontend_label'] = $this->jsonHelper->jsonEncode($label);
        $defaultValueField = $this->model->getDefaultValueByInput($data['frontend_input']);

        $data = $this->setDataDefaultValue($data, $defaultValueField);
        $this->model->setData($data);
        $this->setId($data);
        try {
            $this->model->save();
            $this->_eventManager->dispatch(
                'bss_customfield_create',
                ['attribute'=>$this->model]
            );
            $this->messageManager->addSuccessMessage(__('You saved the checkout attribute.'));
            if ($this->getRequest()->getParam('back', false)) {
                return $this->returnResult(
                    'bss_customfield/*/edit',
                    ['id' => $this->model->getId(), '_current' => true],
                    ['error' => false]
                );
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_session->setAttributeData($data);
            return $this->returnResult(
                'bss_customfield/*/edit',
                ['id' => $attributeId, '_current' => true],
                ['error' => true]
            );
        }
        return $this->returnResult('bss_customfield/*/', [], ['error' => true]);
    }

    /**
     * @return bool|mixed|string
     * @throws \Zend_Validate_Exception
     */
    private function getAttributeCode()
    {
        if ($this->getRequest()->getParam('attribute_code')) {
            return $this->getRequest()->getParam('attribute_code');
        }
        return $this->generateCode($this->getRequest()->getParam('frontend_label')[0]);
    }

    /**
     * @param array $data
     * @param string $singleStoreId
     * @return string
     */
    private function getDataStoreId($data, $singleStoreId)
    {
        if (isset($data['store_id'])) {
            return implode(',', $data['store_id']);
        }
        return $singleStoreId;
    }

    /**
     * @param array $data
     */
    private function setId($data)
    {
        if (isset($data['id'])) {
            $this->model->setId($data['id']);
        }
    }

    /**
     * @param array $data
     * @param string $defaultValueField
     * @return array
     */
    private function setDataDefaultValue($data, $defaultValueField)
    {
        if ($defaultValueField) {
            $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
        }
        return $data;
    }

    /**
     * @param array $data
     * @param string $attributeId
     * @return bool|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\Controller\Result\Json
     */
    private function checkValidateFrontInput($data, $attributeId)
    {
        if (isset($data['frontend_input'])) {
            /** @var $inputType \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator */
            $inputType = $this->validatorFactory->create();
            if (!$inputType->isValid($data['frontend_input'])) {
                foreach ($inputType->getMessages() as $message) {
                    $this->messageManager->addError($message);
                }
                return $this->returnResult(
                    'catalog/*/edit',
                    ['id' => $attributeId, '_current' => true],
                    ['error' => true]
                );
            }
        }
        return false;
    }

    /**
     * @param array $data
     */
    private function setRegisterKey($data)
    {
        if (isset($data['default'])) {
            $this->resigtry->register('attribute_option_default', $data['default']);
        }
        $this->resigtry->register('attribute_option', $data['option']);
    }

    /**
     * @param string $path
     * @param array $params
     * @param array $response
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Backend\Model\View\Result\Redirect
     */
    private function returnResult($path = '', array $params = [], array $response = [])
    {
        if ($this->isAjax()) {
            $layout = $this->layoutFactory->create();
            $layout->initMessages();

            $response['messages'] = [$layout->getMessagesBlock()->getGroupedHtml()];
            $response['params'] = $params;
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath($path, $params);
    }

    /**
     * Define whether request is Ajax
     *
     * @return boolean
     */
    private function isAjax()
    {
        return $this->getRequest()->getParam('isAjax');
    }

    /**
     * @param string $label
     * @return bool|string
     * @throws \Zend_Validate_Exception
     */
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->catalogUrl->formatUrlKey($label)
            ),
            0,
            30
        );
        if (!$this->isValid('/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/', $code)) {
            $code = 'attr_' . ($code ?: substr(time(), 0, 8));
        }
        return $code;
    }

    /**
     * @param string $pattern
     * @param string $value
     * @return bool
     */
    private function isValid($pattern, $value)
    {
        if (!is_string($value) && !is_int($value) && !is_float($value)) {
            return false;
        }

        $status = preg_match($pattern, $value);
        if (false === $status) {
            return false;
        }

        if (!$status) {
            return false;
        }

        return true;
    }
}
