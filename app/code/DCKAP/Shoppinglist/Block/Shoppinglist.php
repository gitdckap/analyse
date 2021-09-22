<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Block;

class Shoppinglist extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var DCKAP\Shoppinglist\Helper\Data
     */
    protected $shoppinglistHelper;

    /**
     * @var DCKAP\Shoppinglist\Model\ProductlistFactory
     */
    protected $productlistFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $productImage;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \DCKAP\Shoppinglist\Helper\Data $shoppinglistHelper,
        \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $productImage
    ) {
        $this->customerSession = $customerSession;
        $this->storeManager = $context->getStoreManager();
        $this->request = $request;
        $this->shoppinglistHelper = $shoppinglistHelper;
        $this->productlistFactory = $productlistFactory;
        $this->productFactory = $productFactory;
        $this->productImage = $productImage;
        parent::__construct($context);
    }

    public function getCustomerSession()
    {
        return $this->customerSession->create();
    }
    
    public function getShoppinglistId()
    {

        $customerSession = $this->customerSession->create();
        $postData = $this->request->getPost();

        if ($postData['shopping_list_id']) {
            $customerSession->setShoppinglistId($postData['shopping_list_id']);
            return $postData['shopping_list_id'];
        } elseif ($customerSession->getShoppinglistId()) {
            return $customerSession->getShoppinglistId();
        }
        
        return 0;
    }

    public function getShoppinglist()
    {

        return $this->shoppinglistHelper->getCustomerShoppingList();
    }

    public function getShoppinglistProduct($shopping_list_id)
    {
          
        // Get Shopping List Item collection
        $productlistModel = $this->productlistFactory->create();
        $storeId = $this->storeManager->getStore()->getId();

        $productlistModelCollection = $productlistModel->getCollection()
                                                        ->addFieldToFilter('shopping_list_id', $shopping_list_id)
                                                        ->addFieldToFilter('store_id', $storeId);
        $collection = $productlistModelCollection->getData();
        
        return $collection;
    }

    public function getProductInfo($productId)
    {

        return $this->productFactory->create()->load($productId);
    }

    public function getProductImage($product)
    {

        return $this->productImage->init($product, 'category_page_list', ['height' => '100' , 'width'=> '100'])->getUrl();
    }

    public function getConfigurableOptionList($productId)
    {
        $product = $this->getProductInfo($productId);

        $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
        $attributeOptions = [];

        foreach ($productAttributeOptions as $productAttribute) {
            $parentId = $productAttribute['attribute_id'];
            $attributeOptions[$parentId]['label'] = $productAttribute['label'];

            foreach ($productAttribute['values'] as $attribute) {
                $childId = $attribute['value_index'];
                $attributeOptions[$parentId]['data'][$childId] = $attribute['store_label'];
            }
        }
        return $attributeOptions;
    }

    public function getGroupedOptionList($shoppingListItem)
    {
        $superGroup = unserialize($shoppingListItem['value']);
        if (isset($superGroup['super_group'])) {
            $superGroupOption = [];
            $i = 0;
            foreach ($superGroup['super_group'] as $key => $value) {
                if ($value > 0 && $productName = $this->getProductInfo($key)->getName()) {
                    $superGroupOption[$i]['product_name'] = $productName;
                    $superGroupOption[$i]['qty'] = $value;
                    $i++;
                }
            }
            return $superGroupOption;
        }
        return null;
    }

    public function getBundleOptionList($shoppingListItem)
    {
        $bundleOption = unserialize($shoppingListItem['value']);
        if (isset($bundleOption['bundle_option'])) {
            $product = $productName = $this->getProductInfo($shoppingListItem['product_id']);

            //get all options of product
            $optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
            foreach ($optionsCollection as $options) {
                $optionArray[$options->getOptionId()]['option_title'] = $options->getDefaultTitle();
                // $optionArray[$options->getOptionId()]['option_type'] = $options->getType();
            }

            //get all the selection products used in bundle product.
            $selectionCollection = $product->getTypeInstance(true)
                                        ->getSelectionsCollection(
                                            $product->getTypeInstance(true)->getOptionsIds($product),
                                            $product
                                        );
            foreach ($selectionCollection as $proselection) {
                $selectionArray = [];
                $selectionArray['selection_product_name'] = $proselection->getName();
                $selectionArray['selection_product_quantity'] = $proselection->getPrice();
                $selectionArray['selection_product_price'] = $proselection->getSelectionQty();
                $selectionArray['selection_product_id'] = $proselection->getProductId();
                $productsArray[$proselection->getOptionId()][$proselection->getSelectionId()] = $selectionArray;
            }

            $bundleOptions = [];
            foreach ($bundleOption['bundle_option'] as $key => $value) {
                if (isset($optionArray[$key]) && isset($productsArray[$key][$value])) {
                    $bundleOptions[$key]['option_title'] = $optionArray[$key]['option_title'];
                    if (isset($bundleOption['bundle_option_qty'][$key])) {
                        $bundleOptions[$key]['selection_qty'] = $bundleOption['bundle_option_qty'][$key];
                    } else {
                        $bundleOptions[$key]['selection_qty'] = 0;
                    }
                    $bundleOptions[$key]['selection_product_name'] = $productsArray[$key][$value]['selection_product_name'];
                    $bundleOptions[$key]['selection_product_price'] = $bundleOption['bundle_option_qty'][$key] * ($productsArray[$key][$value]['selection_product_price'] * $productsArray[$key][$value]['selection_product_quantity']);
                }
            }
            return $bundleOptions;
        }
        return null;
    }

    public function getValidateUserData()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->getCustomData()) {
            $customerData = $customerSession->getCustomData();
            return $customerData;
        }
        return false;
    }


	/**
	 * @return $this
	 */
	protected function _prepareLayout()
	{
		parent::_prepareLayout();

		$collection = $this->getShoppinglistProductCollection($this->getShoppinglistId());
		if ($collection) {
			/** @var \Magento\Theme\Block\Html\Pager */
			$pager = $this->getLayout()->createBlock(
					'Magento\Theme\Block\Html\Pager',
					'shopping_list.pager'
			);
			$pager->setAvailableLimit([5 => 5, 10 => 10, 15 => 15, 20 => 20])
					->setShowPerPage(true)
					->setCollection($collection);
			$this->setChild('pager', $pager);
			$collection->load();
		}
		return $this;
	}

	/**
	 * @return string
	 */
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}


	public function getShoppinglistProductCollection($shopping_list_id)
	{
		$page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
		$pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 5;
		// Get Shopping List Item collection
		$productlistModel = $this->productlistFactory->create();
		$storeId = $this->storeManager->getStore()->getId();

		$collection = $productlistModel->getCollection()
				->addFieldToFilter('shopping_list_id', $shopping_list_id)
				->addFieldToFilter('store_id', $storeId);
		$collection->setPageSize($pageSize);
		$collection->setCurPage($page);
		return $collection;
	}

}
