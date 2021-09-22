<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const SHOPPINGLIST_SECTION_GENERAL_ENABLED = 'shoppinglist_section/general/enabled';

    const SHOPPINGLIST_SECTION_GENERAL_MAINTAIN_AFTER_ADD_TO_CART = 'shoppinglist_section/general/maintain_after_add_to_cart';

    const SHOPPINGLIST_SECTION_GENERAL_REDIRECT_TO_CART = 'shoppinglist_section/general/redirect_to_cart';

    const SHOPPINGLIST_SECTION_GENERAL_REDIRECT_TO_SHOPPINGLIST = 'shoppinglist_section/general/redirect_to_shoppinglist';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var DCKAP\Shoppinglist\Model\ShoppinglistFactory
     */
    protected $shoppinglistFactory;

    /**
     * @var DCKAP\Shoppinglist\Model\ProductlistFactory
     */
    protected $productlistFactory;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    protected $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory
     * @param \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory,
        \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->shoppinglistFactory = $shoppinglistFactory;
        $this->productlistFactory = $productlistFactory;
        $this->postDataHelper = $postDataHelper;
        $this->jsonEncoder = $jsonEncoder;
        $this->logger = $context->getLogger();
        parent::__construct($context);
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store config value
     *
     * @return Boolean
     */
    public function isShowShoppinglistAddOption()
    {
        return $this->getConfigValue(
            self::SHOPPINGLIST_SECTION_GENERAL_ENABLED,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return store config value
     *
     * @return Boolean
     */
    public function isMaintainItemAfterAddtoCart()
    {
        return $this->getConfigValue(
            self::SHOPPINGLIST_SECTION_GENERAL_MAINTAIN_AFTER_ADD_TO_CART,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return store config value
     *
     * @return Boolean
     */
    public function isRedirecttoCart()
    {
        return $this->getConfigValue(
            self::SHOPPINGLIST_SECTION_GENERAL_REDIRECT_TO_CART,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return store config value
     *
     * @return Boolean
     */
    public function isRedirecttoShoppingList()
    {
        return $this->getConfigValue(
            self::SHOPPINGLIST_SECTION_GENERAL_REDIRECT_TO_SHOPPINGLIST,
            $this->getStore()->getStoreId()
        );
    }

    /**
     * Return customer log-in status
     *
     * @return Boolean
     */
    public function isCustomerLoggedin()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            return true;
        }

        return false;
    }

    /**
     * Return customer shopping list
     *
     * @return Array
     */
    public function getCustomerShoppingList()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            $customerId = $customerSession->getId();
            $storeId = $this->storeManager->getStore()->getId();

            // Get Shopping List collection
            $shoppinglistModel = $this->shoppinglistFactory->create();
            $shoppinglistModelCollection = $shoppinglistModel->getCollection()
                                                        ->addFieldToFilter('customer_id', $customerId)
                                                        ->addFieldToFilter('store_id', $storeId);

            $collection = $shoppinglistModelCollection->getData();
            return $collection;
        }

        return null;
    }

    /**
     * Retrieve params for adding product to shoppinglist
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Shoppinglist\Model\Item $item
     * @param array $params
     * @return string
     */
    public function getAddParams($item, array $params = [])
    {
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = $item->getEntityId();
        }

        $url = $this->_getUrlStore($item)->getUrl('shoppinglist/index/updateproducttolist');
        if ($productId) {
            $params['product'] = $productId;
        }

        return $this->postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Shoppinglist\Model\Item $item
     * @return \Magento\Store\Model\Store
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else {
                if ($product->hasUrlDataObject()) {
                    $storeId = $product->getUrlDataObject()->getStoreId();
                }
            }
        }
        return $this->storeManager->getStore($storeId);
    }

    public function getShoppinglistOptionsJson($product)
    {

        return $this->jsonEncoder->encode(['productType' => $product->getTypeId()]);
    }

    public function getShoppingListInfo($slid)
    {

        return $this->productlistFactory->create()->load($slid);
    }

    public function deleteListItems($listItem)
    {
        try {
            $listItem->delete();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    public function deleteItems($itemObj)
    {
        
            $itemObj->delete();
    }
}
