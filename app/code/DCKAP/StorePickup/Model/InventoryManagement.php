<?php
namespace Dckap\StorePickup\Model;

//use FME\GoogleMapsStoreLocator\Block\Storelocator;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;

/**
 * Class InventoryManagement
 * package Dckap\StorePickup\Model
 */

class InventoryManagement
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * InventoryManagement constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param Storelocator               $storelocator
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        Cart $cart
    ) {
        $this->productRepository = $productRepository;http://m2.dckap.net/ddihvac/customer/account/
        $this->cart = $cart;
    }

    /**
     * @return array|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getWarehouseStock()
    {

        $quote = $this->cart->getQuote();
        $items = $quote->getItemsCollection();

        $warehouseDetails = [];
       
        $data = [];

        foreach ($items as $item) {
            $productInfo = $this->productRepository->getById($item->getProductId());
            foreach ($warehouseDetails as $warehouse) {
                $warehouseCode = $warehouse['store_code'];
                $warehouseName = $warehouse['store_name'];
                $warehouseInventory = $productInfo->getCustomAttribute($warehouseCode) ?
                $productInfo->getCustomAttribute($warehouseCode)->getValue() : 0;
                if ($warehouseInventory == 0) {
                    if (isset($data[$warehouseName])) {
                        $data[$warehouseName] .= ','.$productInfo->getSku();
                    } else {
                        $data[$warehouseName] = $productInfo->getSku();
                    }
                }
            }
        }

        $details = [];
        if (!empty($data)) {
            $details = $this->setStockData($data);
        }
        return $details;
    }

    /**
     * @param $data
     * @return string
     */
    public function setStockData($data)
    {
        $details = [];
        $no = 1;
        foreach ($data as $warehouse => $productsSku) {
            $details[$warehouse] = "These products [ ".$productsSku." ] are not available in ".$warehouse." warehouse.";
            $no++;
        }
        return $details;
    }
}
