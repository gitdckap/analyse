<?php
namespace Dckap\StorePickup\Plugin\TaxRate;

//use FME\GoogleMapsStoreLocator\Block\Storelocator;
use Magento\Checkout\Model\Session;

/**
 * Class CalculateStorePickupTaxRate
 * package Dckap\StorePickup\Plugin\TaxRate
 */
class CalculateStorePickupTaxRate
{
    /**
     * @var Session
     */
    private $_checkoutSession;
    /**
     * @var Storelocator
     */
    private $storelocator;

    private $registry;

    /**
     * CalculateStorePickupTaxRate constructor.
     * @param Session $_checkoutSession
     * @param Storelocator $storelocator
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Session $_checkoutSession,
        \Magento\Framework\Registry $registry
    ) {
        $this->_checkoutSession = $_checkoutSession;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Tax\Model\Calculation $subject
     * @param $price
     * @param $taxRate
     * @param bool $priceIncludeTax
     * @param bool $round
     * @return array
     */
    /*public function beforeCalcTaxAmount($subject, $price, $taxRate, $priceIncludeTax = false, $round = true)
    {
        $quote = $this->_checkoutSession ? $this->_checkoutSession->getQuote(): "";
        $prefWareHouse = null;

        if( $quote && $quote->getShippingAddress()->getShippingMethod() == 'ddistorepickup_ddistorepickup'){
            $prefWareHouse = $quote->getData('ddi_pref_warehouse');
        }

        if ($this->registry->registry('warehouse_tax')) {
            $prefWareHouse = $this->registry->registry('warehouse_tax');
        }

        if ($prefWareHouse) {
            $warehouseDetails = $this->storelocator->getWarehouseDetails()->getData();

            if ($warehouseDetails && $prefWareHouse){
                foreach ($warehouseDetails as $key => $warehouseDetail) {
                    $wareHouse[$warehouseDetail['store_name']]['tax_rate'] = $warehouseDetail['tax_rate'];
                }
            }
            if (isset($wareHouse[$prefWareHouse]['tax_rate']) && $wareHouse[$prefWareHouse]['tax_rate'] > 0 ){
                $taxRate = $wareHouse[$prefWareHouse]['tax_rate'];
            }
        }

        return [$price, $taxRate, $priceIncludeTax, $round];
    }*/
}
