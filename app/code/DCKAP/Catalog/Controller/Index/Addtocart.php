<?php
namespace DCKAP\Catalog\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Addtocart extends Action
{
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $productModel,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->productModel = $productModel;
        $this->cart = $cart;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    public function execute()
    {
        /**
         * 1. Loop params with child product id
         * 2. Add product to cart with configurable option
         * 3. redirect to cart page
         */
        $params = $this->getRequest()->getParams();
        $productId = $params['product'];
        if (!empty($params['super_attr_qty'])) {
            try {
                foreach ($params['super_attr_qty'] as $childId => $qty) {
                    $product = $this->productLoad($productId);
                    if ((int)$qty > 0) {
                        $cartParams = [];
                        $cartParams['product'] = $productId;
                        $cartParams['qty'] = $qty;
                        $options = [];
                        if (isset($params['super_attr']) && !empty($params['super_attr']) > 0) {
                            foreach ($params['super_attr'][$childId] as $attrId => $optionId) {
                                $options[$attrId] = (int)$optionId;
                            }
                        }
//                        var_dump($options);
                        $cartParams['super_attribute'] = $options;
                        $additionalOptions['custom_uom'] = [
                            'label' => 'UOM',
                            'value' => 'EA',
                        ];
                        $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));

//                        $cartParams['options'] = array('UOM' => 'EA');
//                        var_dump($params);
                        $this->cart->addProduct($product, $cartParams);
                    }
                }
                $this->cart->save();
                $this->_redirect('checkout/cart');
            } catch (\Exception $e) {
                $this->getResponse()
                    ->clearHeaders()
                    ->setHeader('Content-Type', 'text/xml')
                    ->setBody($e->getMessage());
            }
        }
    }

    public function productLoad($productId)
    {
        $product = $this->productModel->create()->load($productId);
        return $product;
    }
}
