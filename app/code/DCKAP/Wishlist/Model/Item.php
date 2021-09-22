<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace DCKAP\Wishlist\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Wishlist\Model\Item\Option;
use Magento\Wishlist\Model\Item\OptionFactory;
use Magento\Wishlist\Model\ResourceModel\Item\Option\CollectionFactory;
use Magento\Catalog\Model\Product\Exception as ProductException;
use Magento\Wishlist\Model\Item as ParentItem;
use DCKAP\Catalog\Helper\Data as CatalogHelper;

/**
 * Wishlist item model
 *
 * @method int getWishlistId()
 * @method \Magento\Wishlist\Model\Item setWishlistId(int $value)
 * @method int getProductId()
 * @method \Magento\Wishlist\Model\Item setProductId(int $value)
 * @method int getStoreId()
 * @method \Magento\Wishlist\Model\Item setStoreId(int $value)
 * @method string getAddedAt()
 * @method \Magento\Wishlist\Model\Item setAddedAt(string $value)
 * @method string getDescription()
 * @method \Magento\Wishlist\Model\Item setDescription(string $value)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class Item extends ParentItem
{
    /**
     * Custom path to download attached file
     * @var string
     */
    protected $_customOptionDownloadUrl = 'wishlist/index/downloadCustomOption';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wishlist_item';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getItem() in this case
     *
     * @var string
     */
    protected $_eventObject = 'item';

    /**
     * Item options array
     *
     * @var Option[]
     */
    protected $_options = [];

    /**
     * Item options by code cache
     *
     * @var array
     */
    protected $_optionsByCode = [];

    /**
     * Not Represent options
     *
     * @var string[]
     */
    protected $_notRepresentOptions = ['info_buyRequest'];

    /**
     * Flag stating that options were successfully saved
     *
     * @var bool|null
     */
    protected $_flagOptionsSaved = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $_catalogUrl;

    /**
     * @var OptionFactory
     */
    protected $_wishlistOptFactory;

    /**
     * @var CollectionFactory
     */
    protected $_wishlOptionCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $productTypeConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;
    protected $catalogHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param OptionFactory $wishlistOptFactory
     * @param CollectionFactory $wishlOptionCollectionFactory
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        OptionFactory $wishlistOptFactory,
        CollectionFactory $wishlOptionCollectionFactory,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        CatalogHelper $catalogHelper,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        $this->productTypeConfig = $productTypeConfig;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_catalogUrl = $catalogUrl;
        $this->_wishlistOptFactory = $wishlistOptFactory;
        $this->_wishlOptionCollectionFactory = $wishlOptionCollectionFactory;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        parent::__construct(
            $context,
            $registry,
            $storeManager,
            $date,
            $catalogUrl,
            $wishlistOptFactory,
            $wishlOptionCollectionFactory,
            $productTypeConfig,
            $productRepository,
            $resource,
            $resourceCollection,
            $data
        );
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Add or Move item product to shopping cart
     *
     * Return true if product was successful added or exception with code
     * Return false for disabled or unvisible products
     *
     * @param \Magento\Checkout\Model\Cart $cart
     * @param bool $delete  delete the item after successful add to cart
     * @return bool
     * @throws \Magento\Catalog\Model\Product\Exception
     */
    public function addToCart(\Magento\Checkout\Model\Cart $cart, $delete = false)
    {
        $product = $this->getProduct();

        $storeId = $this->getStoreId();

        if ($product->getStatus() != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            return false;
        }

        if (!$product->isVisibleInSiteVisibility()) {
            if ($product->getStoreId() == $storeId) {
                return false;
            }
            $urlData = $this->_catalogUrl->getRewriteByProductStore([$product->getId() => $storeId]);
            if (!isset($urlData[$product->getId()])) {
                return false;
            }
            $product->setUrlDataObject(new \Magento\Framework\DataObject($urlData));
            $visibility = $product->getUrlDataObject()->getVisibility();
            if (!in_array($visibility, $product->getVisibleInSiteVisibilities())) {
                return false;
            }
        }

        if (!$product->isSalable()) {
            throw new ProductException(__('Product is not salable.'));
        }
        $uom ="EA";
        $erpProductData = $this->catalogHelper->getSessionProductData($product->getSku());
        if (isset($erpProductData['lineItem']['uom']['uomCode'])) {
            $uom = $erpProductData['lineItem']['uom']['uomCode'];
        }
        $additionalOptions['custom_uom'] = [
            'label' => 'UOM',
            'value' => 'EA',
        ];
        $product->addCustomOption('additional_options', $this->serializer->serialize($additionalOptions));
        $buyRequest = $this->getBuyRequest();
        $buyRequest['custom_uom'] = $uom;
        $cart->addProduct($product, $buyRequest);
        if (!$product->isVisibleInSiteVisibility()) {
            $cart->getQuote()->getItemByProduct($product)->setStoreId($storeId);
        }

        if ($delete) {
            $this->delete();
        }

        return true;
    }
}
