<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */
namespace DCKAP\OrderApproval\Controller\Order;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class Edit
 * @package DCKAP\OrderApproval\Controller\Order
 */
class Edit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    protected $cart;
    protected $orderFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    protected $productRepository;
    protected $serializer;
    protected $logger;
    protected $_orderApprovalHelper;
    protected $addressCollection;
    protected $productFactory;
    protected $configurable;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;
    /**
     * Edit constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
        $this->orderFactory = $orderFactory;
        $this->cart = $cart;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
        $this->_orderApprovalHelper = $orderApprovalHelper;
        $this->addressCollection = $addressCollection;
        $this->productFactory = $productFactory;
        $this->configurable = $configurable;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addNotice(__("Login required to edit the order."));
            $loginUrl = $this->_url->getUrl('customer/account/login');
            return $resultRedirect->setPath($loginUrl);
        }

        if(!$this->_orderApprovalHelper->getOrderEditAllow()){
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addNotice(__("Edit order is not Enabled."));
            $strPendingOrderUrl = $this->_url->getUrl('orderapproval/index/pendingorders');
            return $resultRedirect->setPath($strPendingOrderUrl);
        }

        $arrRequestParams = $this->getRequest()->getParams();
        $intOrderId = $arrRequestParams['order_id'];

        try {
            $arrObjCustomerCartItem = $this->checkoutSession->getQuote()->getAllVisibleItems();
            if (false == empty($arrObjCustomerCartItem) && is_array($arrObjCustomerCartItem)) {
                $this->checkoutSession->getQuote()->removeAllItems();
            }
            $arrObjOrderItems = $this->orderFactory->create()->load($intOrderId)->getAllVisibleItems();

            foreach ($arrObjOrderItems as $objItem) {
                $ObjProductDetails = $this->productRepository->get($objItem->getSKU() , false, null, true);
                $Status = $ObjProductDetails->getStatus();

                if($Status != 1){
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $this->messageManager->addNotice(__( $objItem->getSKU(). " - This product temporarily unavailable."));
                    $strPendingOrderUrl =  $this->_url->getUrl( 'orderapproval/order/view', ['id' => $intOrderId, 'from' => 'pending']);
                    return $resultRedirect->setPath($strPendingOrderUrl);
                }

                if(isset($objItem->getProductOptions()['info_buyRequest']) && isset($objItem->getProductOptions()['info_buyRequest']['product']) && isset($objItem->getProductOptions()['info_buyRequest']['selected_configurable_option'])){
                    $childId = $objItem->getProductOptions()['info_buyRequest']['selected_configurable_option'];
                    $parentId = $objItem->getProductOptions()['info_buyRequest']['product'];
                    $this->addConfigurableProductInCart($childId,$parentId, $objItem);
                }else {
                    $arrParams = [
                        'qty' => $objItem->getQtyOrdered(),
                        'product' => $ObjProductDetails->getId(),
                        'price' => $ObjProductDetails->getPrice()
                    ];
                    $additionalOptions['custom_uom'] = [
                        'label' => 'UOM',
                        'value' => isset($objItem->getProductOptions()['info_buyRequest']['custom_uom']) ? $objItem->getProductOptions()['info_buyRequest']['custom_uom'] : 'EA'
                    ];

                    $arrParams['custom_uom'] = isset($objItem->getProductOptions()['info_buyRequest']['custom_uom']) ? $objItem->getProductOptions()['info_buyRequest']['custom_uom'] : 'EA';
                    $ObjProductDetails->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
                    $this->cart->addProduct($ObjProductDetails, $arrParams);
                }
            }
            $this->cart->save();
            $quote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());
            $quote->setData('order_id', $intOrderId);
            $this->quoteRepository->save($quote);
            $strCheckOutCartUrl = $this->_url->getUrl('checkout/cart');
            return $this->resultRedirectFactory->create()->setPath($strCheckOutCartUrl);
        } catch (\Exception $e) {
            $arrExceptionDetails = [
                'status' => 'Failure',
                'message' => $e->getMessage()
            ];
            $this->logger->error(print_r($arrExceptionDetails, true));
        }

    }

    public function addConfigurableProductInCart($childId,$parentId,$objOrderItem)
    {
        try {
            $params = $options = [];
            $product = $this->productFactory->create()->load($parentId);
            $childProduct = $this->productFactory->create()->load($childId);
            $params = [
                'qty' => $objOrderItem->getQtyOrdered(),
                'product' => $product->getId(),
                'price' => $product->getPrice()
            ];

            $productAttributeOptions = (array) $this->configurable->getConfigurableAttributesAsArray($product);
            foreach ($productAttributeOptions as $option) {
                $options[$option['attribute_id']] = $childProduct->getData($option['attribute_code']);
            }

            $params['super_attribute'] = $options;
            $params['custom_uom'] = isset($objOrderItem->getProductOptions()['info_buyRequest']['custom_uom']) ? $objOrderItem->getProductOptions()['info_buyRequest']['custom_uom'] : 'EA';
            $additionalOptions['custom_uom'] = [
                'label' => 'UOM',
                'value' => isset($objOrderItem->getProductOptions()['info_buyRequest']['custom_uom']) ? $objOrderItem->getProductOptions()['info_buyRequest']['custom_uom'] : 'EA'
            ];
            $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
            $this->cart->addProduct($product, $params);
        } catch(\Exception $e){
            $arrExceptionDetails = [
                'status' => 'Failure',
                'message' => $e->getMessage()
            ];
            $this->logger->error(print_r($arrExceptionDetails, true));
        }
    }

}
