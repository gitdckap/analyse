<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
namespace DCKAP\Shoppinglist\Controller\Index;

class Addproducttolist extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var  \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var  \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \DCKAP\Shoppinglist\Model\ProductlistFactory
     */
    protected $productlistFactory;

    /**
     * @var \DCKAP\Shoppinglist\Block\Shoppinglist
     */
    protected $shoppinglist;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $productImage;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
     * @param \DCKAP\Shoppinglist\Block\Shoppinglist $shoppinglist
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Helper\Image $productImage
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory,
        \DCKAP\Shoppinglist\Block\Shoppinglist $shoppinglist,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $productImage
    ) {

        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->productlistFactory = $productlistFactory;
        $this->shoppinglist = $shoppinglist;
        $this->productFactory = $productFactory;
        $this->productImage = $productImage;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function execute()
    {
        $customerSession = $this->customerSession->create();
        $result['status'] = 'fail';
        if ($customerSession->isLoggedIn()) {
            $productId = $this->getRequest()->getParam('product_id');
            $shoppingListId = $this->getRequest()->getParam('shopping_list_id');
            $storeId = $this->storeManager->getStore()->getId();
            $date = $this->date->gmtDate();

            $customerSession->setShoppinglistId($shoppingListId);

            $productlistModel = $this->productlistFactory->create();
            $productlistModelCollection = $productlistModel->getCollection()
                                            ->addFieldToFilter('shopping_list_id', $shoppingListId)
                                            ->addFieldToFilter('product_id', $productId)
                                            ->addFieldToFilter('store_id', $storeId);
            $collection = $productlistModelCollection->getData();

            $product = $this->productFactory->create()->load($productId);

            if (!$product->getId()) {
                $result['message'] = __('Wrong product selection.');
            } elseif (empty($collection) || ($product->getTypeId() != 'simple' && $product->getTypeId() != 'virtual' && $product->getTypeId() != 'downloadable')) {
                try {
                    $productlist = $this->productlistFactory->create();
                    $productlist
                        ->setData('shopping_list_id', $shoppingListId)
                        ->setData('product_id', $productId)
                        ->setData('product_type', $product->getTypeId())
                        ->setData('value', null)
                        ->setData('qty', 1)
                        ->setData('added_at', $date)
                        ->setData('store_id', $storeId)
                        ->save();

                    $lastid = $productlist->getShoppingListItemId();

                    if ($lastid) {
                        $result['status'] = 'success';
                        $result['shopping_list_item_id'] = $lastid;
                        $result['product_id'] = $product->getId();
                        $result['sku'] = $product->getSku();
                        $result['name'] = $product->getName();
                        $result['type'] = $product->getTypeId();
                        if ($product->getTypeId() == 'simple' || $product->getTypeId() == 'virtual' || $product->getTypeId() == 'downloadable') {
                            $result['qtystatus'] = 0;
                            $chkstock = $this->shoppinglist->getProductInfo($product->getId());
                            $stockstatus = $chkstock->isAvailable();
                            if (!$stockstatus) {
                                $result['qtystatus'] = 1;
                            }
                        } else {
                            $result['qtystatus'] = 0;
                        }
                        $result['product_url'] = $product->getUrlModel()->getUrl($product);
                        $result['qty'] = 1;
                        $result['image'] = $this->productImage->init($product, 'category_page_list', ['height' => '100' , 'width'=> '100'])->getUrl();
                    }
                } catch (\Exception $e) {
                    $result['message'] = __('Error occurred while adding product to list.');
                }
            } else {
                $result['message'] = sprintf('The product "%s" has added already in the list.', $product->getName());
            }
        } else {
            $result['message'] = __('your session has timed out.');
        }
        return $this->resultJsonFactory->create()->setData($result);
    }
}
