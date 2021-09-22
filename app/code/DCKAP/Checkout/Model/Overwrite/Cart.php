<?php
namespace Dckap\Checkout\Model\Overwrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;

class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * @var \DCKAP\Catalog\Helper\Data
     */
    protected $dckapCatalogHelper;

    /**
     * Cart constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\ResourceModel\Cart $resourceCart
     * @param Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockStateInterface $stockState
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param ProductRepositoryInterface $productRepository
     * @param \DCKAP\Catalog\Helper\Data $dckapCatalogHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\ResourceModel\Cart $resourceCart,
        Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        ProductRepositoryInterface $productRepository,
        \DCKAP\Catalog\Helper\Data $dckapCatalogHelper,
        array $data = []
    ) {
        parent::__construct($eventManager, $scopeConfig, $storeManager, $resourceCart, $checkoutSession, $customerSession, $messageManager, $stockRegistry, $stockState, $quoteRepository, $productRepository, $data);
        $this->dckapCatalogHelper = $dckapCatalogHelper;
    }

    /**
     * Convert order item to quote item Overwrites
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param true|null $qtyFlag if is null set product qty like in order
     * @return $this
     */
    public function addOrderItem($orderItem, $qtyFlag = null)
    {
        /* @var $orderItem \Magento\Sales\Model\Order\Item */
        if ($orderItem->getParentItem() === null) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /**
                 * We need to reload product in this place, because products
                 * with the same id may have different sets of order attributes.
                 */
                $product = $this->productRepository->getById($orderItem->getProductId(), false, $storeId, true);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return $this;
            }
            $info = $orderItem->getProductOptionByCode('info_buyRequest');
            $info = new \Magento\Framework\DataObject($info);
            if ($qtyFlag === null) {
                $info->setQty($orderItem->getQtyOrdered());
            } else {
                $info->setQty(1);
            }
            
            $uom = 'EA';

            $sku = $orderItem->getSku();
            $sessionProductData = $this->dckapCatalogHelper->getSessionProductsData($sku);
            if ($sessionProductData && isset($sessionProductData[$sku]) && isset($sessionProductData[$sku]['lineItem']['uom']['uomCode'])) {
                $uom = $sessionProductData[$sku]['lineItem']['uom']['uomCode'];
            }
            $uom = ($info->getCustomUom()) ? $info->getCustomUom() : $uom;
            $info->setCustomUom($uom);
            $additionalOptions['custom_uom'] = [
                'label' => 'UOM',
                'value' => $uom,
            ];
            $product->addCustomOption('additional_options', json_encode($additionalOptions));
            $this->addProduct($product, $info);
        }
        return $this;
    }
}
