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
namespace Bss\CheckoutCustomField\Controller\Express;

use Magento\Framework\Json\Helper\Data as JsonHelper;

class SaveCustomField extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * SaveCustomField constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param JsonHelper $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Helper\Cart $cartHelper,
        JsonHelper $jsonHelper
    ) {
        parent::__construct($context);
        $this->cartHelper = $cartHelper;
        $this->resultJsonFactory=$resultJsonFactory;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->resultJsonFactory->create();
            $customAttr = $this->getRequest()->getParams();
            $quote = $this->cartHelper->getQuote();
            $data = [];
            $customField = $quote->getBssCustomfield();
            if ($customField) {
                $data = $this->jsonHelper->jsonDecode($customField);
            }
            if (is_array($data) && !empty($customAttr)) {
                foreach ($customAttr as $key => $val) {
                    $data[$key] = $val;
                }
            }
            $quote->setBssCustomfield($this->jsonHelper->jsonEncode($data));
            $quote->save();
        } catch (\Exception $e) {
            return $result->setData($e->getMessage());
        }
        return $result->setData('done');
    }
}
