<?php

namespace DCKAP\CheckoutCustomisation\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;
use DCKAP\Extension\Helper\Data as ExtensionHelper;
use DCKAP\CheckoutCustomisation\Helper\Data as CheckoutCustomisationHelper;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface  */
    protected $_layout;
    protected $extensionhelper;
    protected $checkoutCustomisationHelper;
    protected $orderApprovalHelper;

    public function __construct(
        LayoutInterface $layout,
        ExtensionHelper $extensionhelper,
        CheckoutCustomisationHelper $checkoutCustomisationHelper,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper
    ) {
        $this->_layout = $layout;
        $this->extenstionhelper = $extensionhelper;
        $this->_checkoutCustomisationHelper = $checkoutCustomisationHelper;
        $this->orderApprovalHelper = $orderApprovalHelper;
    }

    public function getConfig()
    {
        return [
            'my_block_content' => $this->_layout->createBlock('Magento\Framework\View\Element\Template')->setTemplate('DCKAP_CheckoutCustomisation::checkoutcustomscript.phtml')->toHtml(),
            'pickupdate' =>$this->pickupdateVisibleconfig(),
            'pickupdatereq' =>$this->pickupdateRequiredconfig(),
            'disable_dates'=>$this->getDisabledDates(),
            'enable_saturday'=>$this->getEnabledSaturday(),
            'enable_sunday'=>$this->getEnabledSunday(),
            'pickup_option'=>$this->getPickupDeliveryOption(),
            'is_shipto_based_price_enable' => $this->getisShiptoBasedPrice(),
            'is_edit_order' => $this->isEditOrder(),
	        'is_misc_amount'=>$this->getisMiscAmount()
        ];
    }

    public function pickupdateVisibleconfig()
    {
        return $this->extenstionhelper->getPickupDateOption();
    }
    public function pickupdateRequiredconfig()
    {
        return $this->extenstionhelper->getPickupRequired();
    }
    public function getDisabledDates()
    {
        return $this->extenstionhelper->getdisableDates();
    }

    public function getEnabledSaturday()
    {
        return $this->extenstionhelper->getenableSaurday();
    }
    public function getEnabledSunday()
    {
        return $this->extenstionhelper->getenableSunday();
    }

    public function getPickupDeliveryOption()
    {
        return $this->_checkoutCustomisationHelper->getShippingMethods();
    }

    public function getisShiptoBasedPrice(){
        return 1;
//        return $this->extenstionhelper->getIsShiptoBasedPrice();
    }
	public function getisMiscAmount(){
		return $this->extenstionhelper->getIsMiscamt();
	}

    /* get order edit details */
    public function isEditOrder()
    {
        return $this->orderApprovalHelper->isEditOrder();
    }
}
