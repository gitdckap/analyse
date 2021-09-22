<?php

namespace Cloras\Base\Model\Fields;

use Cloras\Base\Api\ProductFieldsInterface;

class Products implements ProductFieldsInterface
{
    private $config;

    private $productFactory;

    private $productExtFactory;

    private $stockItemFactory;

    private $stockItemCollectionFactory;

    public function __construct(
        \Magento\Framework\Config\DataInterface $config,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productFactory,
        \Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory $productExtFactory,
        \Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory $stockItemFactory,
        \Magento\CatalogInventory\Api\Data\StockItemCollectionInterfaceFactory $stockItemCollectionFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistryInterface,
        \Magento\Catalog\Model\Product $productModel
    ) {
        $this->config            = $config;
        $this->productFactory    = $productFactory;
        $this->productExtFactory = $productExtFactory;
        $this->stockItemFactory  = $stockItemFactory;
        $this->stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->stockRegistryInterface     = $stockRegistryInterface;
        $this->productModel               = $productModel;
    }//end __construct()

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface
     */
    public function getFields()
    {
        $productAttributes = $this->productModel->getAttributes();

        $productAttribute = [];
        foreach ($productAttributes as $attribute) {
            $productAttribute[$attribute->getAttributeCode()] = '';
        }

        $extensionAttributes = $this->config->get('Magento\Catalog\Api\Data\ProductInterface');

        $attributes = $this->productExtFactory->create();

        $extensionAttribute = [];
        $dummyStockItem     = [
            'item_id'                           => 0,
            'product_id'                        => 0,
            'stock_id'                          => 0,
            'qty'                               => 0,
            'is_in_stock'                       => 'false',
            'is_qty_decimal'                    => 'false',
            'show_default_notification_message' => 'false',
            'use_config_min_qty'                => 'false',
            'min_qty'                           => 0,
            'use_config_min_sale_qty'           => 0,
            'min_sale_qty'                      => 0,
            'use_config_max_sale_qty'           => 'false',
            'max_sale_qty'                      => 0,
            'use_config_backorders'             => 'false',
            'backorders'                        => 0,
            'use_config_notify_stock_qty'       => 'false',
            'notify_stock_qty'                  => 0,
            'use_config_qty_increments'         => 'false',
            'qty_increments'                    => 0,
            'use_config_enable_qty_inc'         => 'false',
            'enable_qty_increments'             => 'false',
            'use_config_manage_stock'           => 'false',
            'manage_stock'                      => 'false',
            'low_stock_date'                    => null,
            'is_decimal_divided'                => 'false',
            'stock_status_changed_auto'         => 0,
        ];

        foreach ($extensionAttributes as $attribute => $value) {
            if ($attribute == 'stock_item') {
                $extensionAttribute[][$attribute] = $dummyStockItem;
            } else {
                $extensionAttribute[] = $attribute;
            }
        }

        $productAttribute['extension_attributes'] = $extensionAttribute;

        $product = [$productAttribute];

        return $product;
    }//end getFields()
}//end class
