<?php

namespace Dckap\Catalog\Block\Product;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected $stockState;
    protected $catalogHelper;
    protected $dckapCatalogHelper;
    protected $cartHelper;
    protected $dckapExtensionHelper;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState,
        \Magento\Catalog\Helper\Output $catalogHelper,
        \DCKAP\Catalog\Helper\Data $dckapCatalogHelper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \DCKAP\Extension\Helper\Data $dckapExtensionHelper,
        array $data = []
    ) {
        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
        $this->stockState = $stockState;
        $this->catalogHelper = $catalogHelper;
        $this->dckapCatalogHelper = $dckapCatalogHelper;
        $this->cartHelper = $cartHelper;
        $this->dckapExtensionHelper = $dckapExtensionHelper;
    }

    /**
     * Get Item Qty
     *
     * @param $product
     * @return float
     */
    public function getAvailableQty($product)
    {
        return $this->stockState->getStockQty($product->getId(), $product->getStore()->getWebsiteId());
    }

    public function getCatalogHelper()
    {
        return $this->catalogHelper;
    }

    public function getDckapCatalogHelper()
    {
        return $this->dckapCatalogHelper;
    }

    public function getCartHelper()
    {
        return $this->cartHelper;
    }

    public function getDckapExtensionHelper()
    {
        return $this->dckapExtensionHelper;
    }
}
