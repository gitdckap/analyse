<?php

namespace Dckap\QuickRFQ\Controller\Quote;

/**
 * Class Order
 * @package Dckap\QuickRFQ\Controller\Quote
 */
class Order extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \Cloras\Base\Helper\Data
     */
    protected $clorasHelper;
    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;
    /**
     * @var \DCKAP\Extension\Helper\Data
     */
    protected $extensionHelper;
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    protected $cartItem;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $serializer;
    /**
     * @var
     */
    protected $messageManager;


    /**
     * Order constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Cloras\Base\Helper\Data $clorasHelper
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \DCKAP\Extension\Helper\Data $extensionHelper
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Model\Quote\Item $cartItem
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Customer\Model\Session $customerSession,
    \Cloras\Base\Helper\Data $clorasHelper,
    \Cloras\DDI\Helper\Data $clorasDDIHelper,
    \DCKAP\Extension\Helper\Data $extensionHelper,
    \Magento\Checkout\Model\Cart $cart,
    \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Quote\Model\Quote\Item $cartItem,
    \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    \Magento\Catalog\Model\ProductFactory $productFactory,
    \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
    \Magento\Framework\Serialize\Serializer\Json $serializer,
    \Magento\Framework\Message\ManagerInterface $messageManager
  )
  {
    parent::__construct($context);
    $this->customerSession = $customerSession;
    $this->clorasHelper = $clorasHelper;
    $this->clorasDDIHelper = $clorasDDIHelper;
    $this->extensionHelper = $extensionHelper;
    $this->cart = $cart;
    $this->redirectFactory = $redirectFactory;
    $this->checkoutSession = $checkoutSession;
    $this->cartItem = $cartItem;
    $this->productRepository = $productRepository;
    $this->productFactory = $productFactory;
    $this->jsonFactory = $jsonFactory;
    $this->serializer = $serializer;
  }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
  {
    /* if (!$this->customerSession->isLoggedIn()) {
         $resultRedirect = $this->resultRedirectFactory->create();
         $this->messageManager->addNotice(__("Login Required to view quote detail."));
         $loginUrl = $this->_url->getUrl('customer/account/login');
         return $resultRedirect->setPath($loginUrl);
     }*/
    $res = $this->jsonFactory->create();
    $params = $this->getRequest()->getParams();
    // $orderData = $this->getOrderData($params['id']);
    list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('order_detail');
    if ($status) {
      $responseData = $this->clorasDDIHelper->getOrderDetail($integrationData, $params['id']);
      if ($responseData && !empty($responseData)) {
        $orderData = $responseData;
      }
    }

    // var_dump($orderData);die;
    /* old cart clear functionality */
    /*$checkoutSession = $this->checkoutSession;
    $allItems = $checkoutSession->getQuote()->getAllVisibleItems();
    foreach ($allItems as $item)
    {
        $cartItemId = $item->getItemId();
        $itemObj=$this->cartItem->load($cartItemId);
        $itemObj->delete();
    }*/

    /* new cart clear functionality */
    $cart = $this->cart;
    $cart->truncate();

    try {
      $nonExist = [];
      if (isset($orderData['lineItems']['lineData']) && !empty($orderData['lineItems']['lineData'])) {
        foreach ($orderData['lineItems']['lineData'] as $item_single) {
          $sku = $item_single['stockNum'];
          $uom = $item_single['uom'];
          $data['qty'] = $item_single['qty'];
          $data['price'] = $item_single['netPrice'];
          $data['custom_uom'] = $item_single['uom'];

          $pros = $this->productRepository->get($sku);
          $proId = $pros->getId();
          if ($proId) {

            $itemData = $this->customerSession->getQuoteProductData();
            $itemData[$sku . '_' . $uom] = $item_single;
            $this->customerSession->setQuoteProductData($itemData);
            $itemData = $this->customerSession->getQuoteProductData();
            $pros = $this->productFactory->create()->load($proId);

            $additionalOptions['quote'] = [
              'label' => 'quote_id',
              'value' => $params['id'],
            ];
            $additionalOptions['custom_uom'] = [
              'label' => 'UOM',
              'value' => $item_single['uom'],
            ];
            $params['product'] = $proId;

            $pros->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
            $this->cart->addProduct($pros, $data);
          } else {
            $nonExist[] = $sku;
          }
        }
      }
      $this->cart->saveQuote();
      $msg = '';

      if (count($nonExist) > 1) {
        $msg = implode(', ', $nonExist) . " are not available in Magento. All other quote products added to cart successfully.";
      } elseif (count($nonExist) == 1) {
        $msg = implode(', ', $nonExist) . " is not available in Magento. All other quote products added to cart successfully.";
      } else {
        $msg = "All quote products added to cart successfully.";
      }
      $this->messageManager->addSuccess(__($msg));
    } catch (\Exception $e) {
      $this->messageManager->addError('Error occurred while adding product to cart.' . $e->getMessage());
      $result = $res->setData(['backurl' => 'quickrfq/quote/index/']);
      return $result;
    }
    $result = $res->setData(['backurl' => 'checkout/cart/']);

    return $result;
  }

    /**
     * @param bool $orderNumber
     * @return bool|int
     */
    protected function getOrderData($orderNumber = false)
  {
    list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('order_detail');
    if ($status) {
      $responseData = $this->clorasDDIHelper->getOrderDetail($integrationData, $orderNumber);
      if ($responseData && !empty($responseData)) {
        return $responseData;
      }
    }
    return false;
  }
}
