<?php
namespace DCKAP\Wishlist\Helper;

class Data extends \Magento\Wishlist\Helper\Data
{
    protected $_currentCustomer;

    protected $_wishlist;

    protected $_productCollection;

    protected $_wishlistItemCollection;

    protected $_coreRegistry;

    protected $_customerSession;

    protected $_wishlistFactory;

    protected $_storeManager;

    protected $_postDataHelper;

    protected $_customerViewHelper;

    protected $wishlistProvider;

    protected $productRepository;

    protected $dckapCatalogHelper;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \DCKAP\Catalog\Helper\Data $dckapCatalogHelper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_storeManager = $storeManager;
        $this->_postDataHelper = $postDataHelper;
        $this->_customerViewHelper = $customerViewHelper;
        $this->wishlistProvider = $wishlistProvider;
        $this->productRepository = $productRepository;
        $this->dckapCatalogHelper = $dckapCatalogHelper;
        parent::__construct(
            $context,
            $coreRegistry,
            $customerSession,
            $wishlistFactory,
            $storeManager,
            $postDataHelper,
            $customerViewHelper,
            $wishlistProvider,
            $productRepository
        );
    }

    protected function _getCartUrlParameters($item)
    {
        $params = [
            'item' => is_string($item) ? $item : $item->getWishlistItemId(),
        ];
        if ($item instanceof \Magento\Wishlist\Model\Item) {
            $params['qty'] = $item->getQty();
            $product = $item->getProduct();
            $sku = $product->getSku();
            $params['custom_uom'] = 'CS';
            $erpProductData = $this->dckapCatalogHelper->getSessionProductData($sku);
            if (isset($erpProductData['lineItem']['uom']['uomCode'])) {
                $params['custom_uom'] = $erpProductData['lineItem']['uom']['uomCode'];
            }
        }

        return $params;
    }
}
