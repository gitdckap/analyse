<?php
namespace DCKAP\StorePickup\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class CustomConfigProvider
 * package DCKAP\StorePickup\Model
 */
class CustomConfigProvider implements ConfigProviderInterface
{

    /**
     * @var InventoryManagement
     */
    private $inventory;

    /**
     * CustomConfigProvider constructor.
     * @param InventoryManagement $inventory
     */
    public function __construct(InventoryManagement $inventory)
    {
        $this->inventory = $inventory;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $config = [];
        $config['warehouse'] = $this->inventory->getWarehouseStock();

        return $config;
    }
}
