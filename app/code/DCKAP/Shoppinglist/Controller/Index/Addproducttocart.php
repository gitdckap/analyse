<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Controller\Index;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Session;

class Addproducttocart extends \Magento\Framework\App\Action\Action
{
    /**
     * @var  \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var  \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \DCKAP\Shoppinglist\Model\ProductlistFactory
     */
    protected $productlistFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var  \DCKAP\Shoppinglist\Helper\Data
     */
    protected $shoppinglistHelper;

    protected $listItemId;

    protected $quoteRepository;

    protected $dckapCatalogHelper;
    protected $serializer;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Locale\ResolverInterface $resolverInterface
     * @param \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \DCKAP\Shoppinglist\Helper\Data $shoppinglistHelper
     * @param \DCKAP\Shoppinglist\Helper\Data $shoppinglistHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \DCKAP\Shoppinglist\Helper\Data $listItemId,
        \DCKAP\Shoppinglist\Helper\Data $shoppinglistHelper,
        Session $session,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \DCKAP\Catalog\Helper\Data $dckapCatalogHelper,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {

        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->storeManager = $storeManager;
        $this->productlistFactory = $productlistFactory;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->cartHelper = $cartHelper;
        $this->messageManager = $context->getMessageManager();
        $this->shoppinglistHelper = $shoppinglistHelper;
        $this->listItemId = $listItemId;
        $this->_session = $session;
        $this->quoteRepository = $quoteRepository;
        $this->dckapCatalogHelper = $dckapCatalogHelper;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $customerSession = $this->customerSession->create();

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setPath('*/');
        }

        if ($customerSession->isLoggedIn()) {
            //get cart id
            $cartId=$this->cart->getQuote()->getId();

            //get exist cart items
            $items = $this->_session->getQuote()->getAllVisibleItems();
            $existItem=$existItemQty=$existItemName=[];
            if (isset($items) && $items) {
                foreach ($items as $item) {
                    $existItem[$item->getProductId()]= $item->getItemId();
                    $existItemQty[$item->getProductId()]= $item->getQty();
                    $existItemQty[$item->getProductId()]= $item->getName();
                }
            }
            $post = $this->getRequest()->getPostValue();

            $productlistModelCollection = $this->getShoppingListCollection($post);

            $collection = $productlistModelCollection;
            $successReport = [];
            $storeId = $this->storeManager->getStore()->getId();

            try {
                foreach ($collection as $collectionItem) {
                    $params = [];
                    $productId = $this->getProductIdCollection($collectionItem);


                    $params['product'] = $productId;
                     /* customized code */
                    $product = $this->productRepository->getById($productId);
                     $sku = $product->getSku();
                   
                 /*  $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/shoppinglistuom.log');
                    $logger = new \Zend\Log\Logger();
                    $logger->addWriter($writer);
                    $logger->info($uom);*/
                    $uom = 'CS';
                        $erpProductData = $this->dckapCatalogHelper->getSessionProductData($sku);
                    if (isset($erpProductData['lineItem']['uom']['uomCode'])) {
                        $uom = $erpProductData['lineItem']['uom']['uomCode'];
                    }
                       $additionalOptions['custom_uom'] = [
                          'label' => 'UOM',
                          'value' => $uom
                        ];

                        //print_r($additionalOptions);exit;
                       $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
       
                       $params['related_product'] = $params['selected_configurable_option'] = '';
                       if (isset($post['qty']) && $post['qty'][$collectionItem->getData('shopping_list_item_id')]) {
                           $params['qty'] = $post['qty'][$collectionItem->getData('shopping_list_item_id')];
                       } else {
                           $params['qty'] = $collectionItem->getQty();
                       }

                       if (isset($params['qty'])) {
                           $params['qty'] = round($params['qty']);
                           // $filter = new \Zend_Filter_LocalizedToNormalized(
                           //     ['locale' => $this->resolverInterface->getLocale()]
                           // );
                           // $params['qty'] = $filter->filter($params['qty']);
                       }
                       if (!empty($existItem)) {
                           if (in_array($productId, $existItem)) {
                               $itemId = $existItem[$productId];
                               $itemQty = $collectionItem->getQty()+$existItemQty[$productId];

                               $quote = $this->quoteRepository->getActive($cartId);
                               $cartitems = $this->cart->getQuote()->getAllItems();
                               $cartitems->setquoteId($cartId);
                               $cartitems->setitemId($itemId);
                               $cartitems->setqty($itemQty);

                               $quoteItems[] = $cartitems;
                               $quote->setItems($quoteItems);
                               $this->quoteRepository->save($quote);
                               $quote->collectTotals();
                               $this->messageManager->addSuccess(sprintf('%s has added to shopping cart.', $existItemName[$productId]));
                               continue;
                           }
                       }

                       try {
                           $product = $this->productRepository->getById($productId, false, $storeId, true);
                       } catch (NoSuchEntityException $e) {
                           $this->messageManager->addError($e->getMessage());
                           continue;
                       }

                       if ($product->isSalable() != true && $product->isAvailable() != true) {
                           $this->messageManager->addError(sprintf('%s is out of stock.', $product->getName()));
                           continue;
                       }

                       $uom = 'EA';
                       $sku = $product->getSku();
                       $sessionProductData = $this->dckapCatalogHelper->getSessionProductsData($sku);
                       if ($sessionProductData && isset($sessionProductData[$sku]) && isset($sessionProductData[$sku]['lineItem']['uom']['uomCode'])) {
                           $uom = $sessionProductData[$sku]['lineItem']['uom']['uomCode'];
                       }

                       $params['custom_uom'] = $uom;
                       $params = $this->nestedLoopAvoid($collectionItem, $params);
                       $this->cart->addProduct($product, $params);

                       if ($this->cart->getQuote()->getHasError()) {
                           $this->messageManager->addError(sprintf('Some problem while adding the product %s.', $product->getName()));
                           continue;
                       }

                       $this->deleteCollectinItems($collectionItem);

                       $this->messageManager->addSuccess(sprintf('%s has added to shopping cart.', $product->getName()));
                }

                $this->cart->save()->getQuote()->collectTotals();
                $this->_eventManager->dispatch(
                    'checkout_cart_add_product_complete',
                    ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            if ($this->shoppinglistHelper->isRedirecttoCart()) {
                $resultRedirect->setPath($this->cartHelper->getCartUrl());
            } else {
                $resultRedirect->setPath('shoppinglist/index/index/');
            }
            return $resultRedirect;
        }

        $resultRedirect->setPath('customer/account/login/');
        return $resultRedirect;
    }

    protected function getShoppingListCollection($post)
    {

        if ((isset($post['bulk']) != true) || (empty($post['bulk']) == true)) {
            return null;
        }
        $productlistModelNew = $this->productlistFactory->create();
        $productlistModelCollection = $productlistModelNew->getCollection()
            ->addFieldToSelect(['shopping_list_item_id','shopping_list_id','product_id','product_type','parent_id','value','store_id','qty','store_id'])
            ->addFieldToFilter('shopping_list_item_id', $post['bulk']);
        return $productlistModelCollection;
    }

    protected function nestedLoopAvoid($collectionItem, $params)
    {

        if ($collectionItem->getProductType() == 'configurable') {
            if ($superAttribute = $collectionItem->getValue()) {
                $superAttribute = unserialize($superAttribute);
                $params['super_attribute'] = (isset($superAttribute['super_attribute']))? $superAttribute['super_attribute']:'';
            }
        } elseif ($collectionItem->getProductType() == 'bundle') {
            if ($bundleOption = $collectionItem->getValue()) {
                $bundleOption = unserialize($bundleOption);
                if (isset($bundleOption['bundle_option'])) {
                    $params['bundle_option'] = $bundleOption['bundle_option'];
                }

                if (isset($bundleOption['bundle_option_qty'])) {
                    $params['bundle_option_qty'] = $bundleOption['bundle_option_qty'];
                }
            }
        } elseif ($collectionItem->getProductType() == 'grouped') {
            if ($superGroup = $collectionItem->getValue()) {
                $superGroup = unserialize($superGroup);
                //multiply each simple product with quantity entered in qty box.
                foreach ($superGroup['super_group'] as $key => $value) {
                    $superGroup['super_group'][$key] = $value * $params['qty'];
                }
                $params['super_group'] = $superGroup['super_group'];
            }
        }
        return $params;
    }

    protected function deleteCollectinItems($collectionItem)
    {
        if (!$this->shoppinglistHelper->isMaintainItemAfterAddtoCart()) {
            $this->listItemId->deleteItems($collectionItem);
        }
    }

    protected function getProductIdCollection($collectionItem)
    {

        $productId = $collectionItem->getProductId();
        if ($collectionItem->getParentId()) {
            $productId = $collectionItem->getParentId();
        }
        return $productId;
    }
}
