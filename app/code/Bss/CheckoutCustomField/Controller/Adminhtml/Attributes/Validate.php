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

use Magento\Framework\DataObject;

class Validate extends \Magento\Backend\App\Action
{
    const DEFAULT_MESSAGE_KEY = 'message';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Bss\CheckoutCustomField\Model\Attribute
     */
    protected $attribute;

    /**
     * @var DataObject
     */
    protected $response;

    /**
     * @var \Magento\Catalog\Model\Product\Url
     */
    protected $productUrl;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Bss\CheckoutCustomField\Model\Attribute $attribute,
        \Magento\Catalog\Model\Product\Url $productUrl,
        DataObject $response
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->attribute = $attribute;
        $this->response = $response;
        $this->productUrl = $productUrl;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->response->setError(false);
        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $frontendLabel = $this->getRequest()->getParam('frontend_label');
        $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
        $attributeId = $this->getRequest()->getParam('id');
        $attributes = $this->attribute->getCollection()->addFieldToFilter("attribute_code", ['eq' => $attributeCode]);
        $count = count($attributes);
        if ($count > 0 && !$attributeId) {
            $message = strlen($this->getRequest()->getParam('attribute_code'))
                ? __('An attribute with this code already exists.')
                : __('An attribute with the same code (%1) already exists.', $attributeCode);

            $this->setMessageToResponse($this->response, [$message]);

            $this->response->setError(true);
        }
        return $this->resultJsonFactory->create()->setJsonData($this->response->toJson());
    }

    /**
     * Set message to response object
     *
     * @param DataObject $response
     * @param string[] $messages
     * @return DataObject
     */
    private function setMessageToResponse($response, $messages)
    {
        $messageKey = $this->getRequest()->getParam('message_key', static::DEFAULT_MESSAGE_KEY);
        if ($messageKey === static::DEFAULT_MESSAGE_KEY) {
            $messages = reset($messages);
        }
        return $response->setData($messageKey, $messages);
    }

    /**
     * @param string $label
     * @return bool|string
     */
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->productUrl->formatUrlKey($label)
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
