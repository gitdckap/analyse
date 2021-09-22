<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace DCKAP\Shoppinglist\Controller\Index;

class Updateproducttolist extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var  \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var  \DCKAP\Shoppinglist\Model\ShoppinglistFactory
     */
    protected $shoppinglistFactory;

    /**
     * @var \DCKAP\Shoppinglist\Model\ProductlistFactory
     */
    protected $productlistFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var  \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var  \DCKAP\Shoppinglist\Helper\Data
     */
    protected $shoppingListData;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory
     * @param \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
     * @param \DCKAP\Shoppinglist\Helper\Data $shoppingListData
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory,
        \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \DCKAP\Shoppinglist\Helper\Data $shoppingListData
    ) {

        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->customerSession = $customerSession->create();
        $this->formKeyValidator = $formKeyValidator;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->shoppinglistFactory = $shoppinglistFactory;
        $this->productlistFactory = $productlistFactory;
        $this->productFactory = $productFactory;
        $this->messageManager = $context->getMessageManager();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->shoppingListData = $shoppingListData;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        //print_r($this->getRequest()->getPostValue());exit;
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $shoppingList = $this->shoppinglistFactory->create();

        if ($this->customerSession->isLoggedIn()) {
            if (!$this->formKeyValidator->validate($this->getRequest())) {
                return $resultRedirect->setPath('*/');
            }
            
            $post = $this->getRequest()->getPostValue();
            $optionValue = $this->optionValues($post);
            $shoppingListId = null;
            $storeId = $this->storeManager->getStore()->getId();
            $date = $this->date->gmtDate();
            $product = $this->productFactory->create()->load($post['product']);
            $slid = (isset($post['slid']) && $post['slid'] != '')?$post['slid']:null;
            $flag = null;

            if (isset($post['shopping_list_id']) && $post['shopping_list_choose'] != 'add_new') {
                // Checks whether comma is present if Yes, then those are multi-lists
                $listCheck = strpos($post['shopping_list_id'], ',');

                if ($listCheck !== false) {
                    //splits by comma and stored in an array
                    $shoppingListId = mbsplit(',', $post['shopping_list_id']);
                    $flag = 2;

                    if (!empty($this->customerSession->getExistingShoppingList())) {
                        $this->deleteUnselectedListItem($shoppingListId, $product);
                    }

                    foreach ($shoppingListId as $shoppingId) {
                        //checks product is already present in each list - results are stored in an array
                        $exist[$shoppingId] = $this->checkProductExist($product, $optionValue, $shoppingId, $storeId);
                    }
                } else {
                    //single list
                    $shoppingListId = $post['shopping_list_id'];
                    $flag = 1;

                    //print_r($this->customerSession->getExistingShoppingList());exit;

                    if (!empty($this->customerSession->getExistingShoppingList())) {
                        $this->deleteUnselectedListItem($shoppingListId, $product);
                    }

                    //checks product is already present in list - results are stored in a variable
                    $exist = $this->checkProductExist($product, $optionValue, $shoppingListId, $storeId);
                }

                //Check for Single or Multi list
                if (!is_array($exist)) {
                    //single list
                    if (!$exist && ($slid == null)) {
                        if (!$this->getRequest()->isAjax()) {
                            $this->messageManager->addNoticeMessage(__('The product '.$product->getName().' already exist in the list.'));
                        }

                        if ($this->shoppingListData->isRedirecttoShoppingList()) {
                            return $this->goBack($this->_url->getUrl('shoppinglist/index/index/'));
                        }

                        return $this->goBack($this->_redirect->getRefererUrl());
                    }
                } else {
                    foreach ($exist as $key => $value) {
                        if (!$value && ($slid == null)) {
                            $listName = $shoppingList->getCollection()->addFieldToFilter('list_id', $key)->getFirstItem()->getListName();
                            if (!$this->getRequest()->isAjax()) {
                                $this->messageManager->addNoticeMessage(__('The product '.$product->getName().' already exist in the '.$listName.' list.'));
                            }
                        }
                    }

                    // if all the lists already have same product - return to Url defined from backend
                    if (!in_array(true, $exist)) {
                        if ($this->shoppingListData->isRedirecttoShoppingList()) {
                            return $this->goBack($this->_url->getUrl('shoppinglist/index/index/'));
                        }

                        return $this->goBack($this->_redirect->getRefererUrl());
                    }
                }
            } elseif ($post['shopping_list_choose'] == 'add_new' && trim($post['shopping_list_name']) != '') {
                try {
                    $shoppingListName = $post['shopping_list_name'];
                    $customerId = $this->customerSession->getId();
                    $storeId = $this->storeManager->getStore()->getId();
                    $shoppinglist = $this->shoppinglistFactory->create();
                    $shoppinglistCollection = $shoppinglist->getCollection()
                                        ->addFieldToFilter('list_name', ['eq' => $shoppingListName])
                                        ->addFieldToFilter('customer_id', ['eq' => $customerId])
                                        ->addFieldToFilter('store_id', ['eq' => $storeId]);
                    if ($shoppinglistCollection->getSize()) {
                        if (!$this->getRequest()->isAjax()) {
                            $this->messageManager->addError(sprintf('%s list name already exist.', $shoppingListName));
                        }
                    } else {
                        $shoppinglist = $this->shoppinglistFactory->create();
                        $shoppinglist
                            ->setData('list_name', $shoppingListName)
                            ->setData('customer_id', $customerId)
                            ->setData('store_id', $storeId)
                            ->save();

                        if (!$this->getRequest()->isAjax()) {
                            $this->messageManager->addSuccess(__('Shopping list created successfully.'));
                        }
                        $flag = 0;
                    }
                    $shoppingListId = $shoppinglist->getListId();
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Error occurred while adding list.'));

                    if ($this->shoppingListData->isRedirecttoShoppingList()) {
                        return $this->goBack($this->_url->getUrl('shoppinglist/index/index/'));
                    }

                    return $this->goBack($this->_redirect->getRefererUrl());
                }
            }

            if ($shoppingListId) {
                try {
                    $productName = $product->getName();
                    $qty = (isset($post['qty'])) ? $post['qty'] : 1 ;

                    if ($flag == 0 || $flag == 1) {
                        $productlist = $this->productlistFactory->create();
                        $productlist
                          ->setData('shopping_list_id', $shoppingListId)
                          ->setData('product_id', $product->getId())
                          ->setData('product_type', $product->getTypeId())
                          ->setData('parent_id', null)
                          ->setData('value', $optionValue)
                          ->setData('qty', $qty)
                          ->setData('added_at', $date)
                          ->setData('store_id', $storeId)
                          ->setData('shopping_list_item_id', $slid)
                          ->save();
                        if (!$this->getRequest()->isAjax()) {
                            $this->messageManager->addSuccessMessage(sprintf("%s has added to your shopping list.", $productName));
                        }

                        $this->customerSession->setShoppinglistId($shoppingListId);
                    } elseif ($flag == 2) {
                        $shoppingList = $this->shoppinglistFactory->create();

                        $multiShoppingListId = array_keys($exist, 1);

                        foreach ($multiShoppingListId as $key => $multiId) {
                            $productlist = $this->productlistFactory->create();
                            $productlist
                              ->setData('shopping_list_id', $multiId)
                              ->setData('product_id', $product->getId())
                              ->setData('product_type', $product->getTypeId())
                              ->setData('parent_id', null)
                              ->setData('value', $optionValue)
                              ->setData('qty', $qty)
                              ->setData('added_at', $date)
                              ->setData('store_id', $storeId)
                              ->save();

                            $listName = $shoppingList->getCollection()->addFieldToFilter('list_id', $multiId)
                                                                    ->getFirstItem()
                            
                                                                    ->getListName();


                            if (!$this->getRequest()->isAjax()) {
                                $this->messageManager->addSuccessMessage(sprintf("%s has added to your ".$listName." list.", $productName));
                            }
                        }
                        $this->customerSession->setShoppinglistId(end($multiShoppingListId));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Error occurred while adding product to list.'));
                }
            }

            if ($this->shoppingListData->isRedirecttoShoppingList()) {
                return $this->goBack($this->_url->getUrl('shoppinglist/index/index/'));
            }

            return $this->goBack($this->_redirect->getRefererUrl());
        }

        $this->customerSession->setAfterAuthUrl($this->_redirect->getRefererUrl());
        $this->customerSession->authenticate();

        $resultRedirect->setPath('customer/account/login/');
        return $resultRedirect;
    }

    /**
     * Resolve option values
     *
     * @param array $post
     * @return string $optionValue
     */
    protected function optionValues($post)
    {
          $optionValue = null;
        if (isset($post['super_attribute']) && !empty($post['super_attribute'])) {
              $optionValue = serialize(['super_attribute' => $post['super_attribute']]);
        } elseif (isset($post['bundle_option']) && !empty($post['bundle_option'])) {
              $optionValue = serialize(['bundle_option' => $post['bundle_option'], 'bundle_option_qty' => $post['bundle_option_qty']]);
        } elseif (isset($post['super_group']) && !empty($post['super_group'])) {
              $optionValue = serialize(['super_group' => $post['super_group']]);
        }
          return $optionValue;
    }

    /**
     * Checks whether product exist already in list
     *
     * @param array $product
     * @param string $optionValue
     * @param int $shoppingListId
     * @param int $storeId
     * @return boolean
     */
    protected function checkProductExist($product, $optionValue, $shoppingListId, $storeId)
    {
          $collection = [];
        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual' || $product->getTypeId() == 'downloadable') {
              $checkproductexist = $this->productlistFactory->create();
              $checkproductexist = $checkproductexist->getCollection()
                                      ->addFieldToFilter('product_id', $product->getId())
                                      ->addFieldToFilter('shopping_list_id', $shoppingListId)
                                      ->addFieldToFilter('store_id', $storeId);

              $collection = $checkproductexist->getData();
        } else {
              $checkproductexist = $this->productlistFactory->create();
            if ($product->getTypeId() == "configurable" || $product->getTypeId() == "grouped") {
                  $checkproductexist = $checkproductexist->getCollection()
                                    ->addFieldToFilter('product_id', $product->getId())
                                    ->addFieldToFilter('shopping_list_id', $shoppingListId)
                                    ->addFieldToFilter('value', ['eq' => $optionValue])
                                    ->addFieldToFilter('store_id', $storeId);
            } else {
                  $checkproductexist = $checkproductexist->getCollection()
                                    ->addFieldToFilter('product_id', $product->getId())
                                    ->addFieldToFilter('shopping_list_id', $shoppingListId)
                                    ->addFieldToFilter('store_id', $storeId);
            }

              $collection = $checkproductexist->getData();
        }
        if (!empty($collection)) {
              return false;
        }

          return true;
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl)
    {
        if ($this->getRequest()->isAjax()) {
            $result['backUrl'] = $backUrl;
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setData($result);
            return $resultJson;
        }

        return $this->_response->setRedirect($backUrl);
    }

    /**
     * Delete's unselected item
     *
     * @param int|mixed $shoppingListId
     * @param \Magento\Catalog\Model\ProductFactory $product
     * @return void
     */
    protected function deleteUnselectedListItem($shoppingListId, $product)
    {
        $oldList = $this->customerSession->getExistingShoppingList();
        $slItems = $this->customerSession->getShoppingListItem();

        if (is_array($shoppingListId)) {
            $currentList = $shoppingListId;
        } else {
            $currentList[0] = $shoppingListId;
        }
        $result = array_diff($oldList, $currentList);

        if ($result) {
            $shoppingList = $this->shoppinglistFactory->create();
            foreach ($result as $res) {
                $item = $this->productlistFactory->create()->load($slItems[$res]);
                $listName = $shoppingList->getCollection()->addFieldToFilter('list_id', $res)->getFirstItem()->getListName();
                try {
                    $item->delete();
                    if (!$this->getRequest()->isAjax()) {
                        $this->messageManager->addErrorMessage(__('The product '.$product->getName().' is removed from the '.$listName.' list.'));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                }
            }
        }
    }
}
