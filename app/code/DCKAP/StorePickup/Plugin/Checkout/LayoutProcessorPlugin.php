<?php

namespace Dckap\StorePickup\Plugin\Checkout;

//use FME\GoogleMapsStoreLocator\Block\Storelocator;
use Magento\Checkout\Block\Checkout\LayoutProcessor;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Store\Model\StoreManagerInterface;

//use Dckap\GoogleMapsApiConfiguration\Helper\GoogleMapsApi;

/**
 * Class LayoutProcessorPlugin
 *
 * package Dckap\StorePickup\Plugin\Checkout
 */
class LayoutProcessorPlugin
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonHelper
     */
    protected $jsonHelper;

    /**
     * @var GoogleMapsApi
     */
    //protected $googleMapsHelper;
    /**
     * @var Storelocator
     */
    //private $storelocator;

    /**
     * LayoutProcessorPlugin constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param JsonHelper            $jsonHelper
     * @param GoogleMapsApi         $googleMapsHelper
     * @param Storelocator          $storelocator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        JsonHelper $jsonHelper
    ) {
        $this->storeManager = $storeManager;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param LayoutProcessor $subject
     * @param array           $jsLayout
     * @return array
     */
    public function afterProcess(
        LayoutProcessor $subject,
        array $jsLayout
    ) {

        $validation['required-entry'] = true;

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['ddi-store-pickup']['children']['ddi_pref_warehouse'] = [
            'component' => "Dckap_StorePickup/js/form/element/select",
            'config' => [
                'customScope' => 'ddiStorePickupShippingMethod',
                'template' => 'ui/form/field',
                'elementTmpl' => "ui/form/element/select",
                'id' => "ddi_pref_warehouse"
            ],
            'dataScope' => 'ddiStorePickupShippingMethod.ddi_store_pickup[ddi_pref_warehouse]',
            'label' => "Preferred Warehouse",
            'options' => $this->getWarehouseOptions(),
            'caption' => 'Please Select',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'validation' => $validation,
            'sortOrder' => 4,
            'id' => 'ddi_store_pickup[ddi_pref_warehouse]'
        ];

        return $jsLayout;
    }

    /*protected function getWarehouseOptions()
    {
        $storeDetails = $this->storelocator->getWarehouseDetails()->getData();

        $items = $options = [];
        foreach ($storeDetails as $storeDetail => $detail) {
            $items[$detail['store_code']]["value"] = $detail['store_name'];
            $items[$detail['store_code']]["label"] = $detail['store_name'];
        }

        $options = $items;

        return $options;
    }*/
}
