<?php

namespace Dckap\Checkout\Model\Tax\Total\Quote;

use Magento\Customer\Api\Data\AddressInterfaceFactory as CustomerAddressFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory as CustomerAddressRegionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Tax\Api\Data\QuoteDetailsItemExtensionInterfaceFactory;
use Magento\Tax\Api\Data\TaxClassKeyInterface;
use Magento\Tax\Helper\Data as TaxHelper;
use Magento\Tax\Model\Calculation;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;

class Tax extends \Magento\Tax\Model\Sales\Total\Quote\Tax
{
    private $serializer;
    protected $checkoutSession;
    protected $customerSession;

    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculationService,
        \Magento\Tax\Api\Data\QuoteDetailsInterfaceFactory $quoteDetailsDataObjectFactory,
        \Magento\Tax\Api\Data\QuoteDetailsItemInterfaceFactory $quoteDetailsItemDataObjectFactory,
        \Magento\Tax\Api\Data\TaxClassKeyInterfaceFactory $taxClassKeyDataObjectFactory,
        CustomerAddressFactory $customerAddressFactory,
        CustomerAddressRegionFactory $customerAddressRegionFactory,
        \Magento\Tax\Helper\Data $taxData,
        CheckoutSession $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        Json $serializer = null
    ) {
        $this->setCode('tax');
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        parent::__construct($taxConfig, $taxCalculationService, $quoteDetailsDataObjectFactory, $quoteDetailsItemDataObjectFactory, $taxClassKeyDataObjectFactory, $customerAddressFactory, $customerAddressRegionFactory, $taxData, $serializer);
    }

    //Customized core Tax collect function
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->clearValues($total);
        if (!$shippingAssignment->getItems()) {
            return $this;
        }

        $baseTaxDetails = $this->getQuoteTaxDetails($shippingAssignment, $total, true);
        $taxDetails = $this->getQuoteTaxDetails($shippingAssignment, $total, false);

        //Populate address and items with tax calculation results
        $itemsByType = $this->organizeItemTaxDetailsByType($taxDetails, $baseTaxDetails);
        if (isset($itemsByType[self::ITEM_TYPE_PRODUCT])) {
            $this->processProductItems($shippingAssignment, $itemsByType[self::ITEM_TYPE_PRODUCT], $total);
        }

        if (isset($itemsByType[self::ITEM_TYPE_SHIPPING])) {
            $shippingTaxDetails = $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_ITEM];
            $baseShippingTaxDetails =
                $itemsByType[self::ITEM_TYPE_SHIPPING][self::ITEM_CODE_SHIPPING][self::KEY_BASE_ITEM];
            $this->processShippingTaxInfo($shippingAssignment, $total, $shippingTaxDetails, $baseShippingTaxDetails);
        }

        //Process taxable items that are not product or shipping
        $this->processExtraTaxables($total, $itemsByType);

        //Save applied taxes for each item and the quote in aggregation
        $this->processAppliedTaxes($total, $shippingAssignment, $itemsByType);

        if ($this->includeExtraTax()) {
            $total->addTotalAmount('extra_tax', $total->getExtraTaxAmount());
            $total->addBaseTotalAmount('extra_tax', $total->getBaseExtraTaxAmount());
        }
        $checkoutData = $this->customerSession->getCheckoutData();
        if ($checkoutData && !empty($checkoutData)) {
            $quote = $this->checkoutSession->create()->getQuote();
            /*To set DDI Tax into Magento Tax*/
            $ddiTaxAmount = 0.00;
            if (isset($checkoutData[$quote->getId()])) {
                $ddiTaxAmount = $checkoutData[$quote->getId()];
            }
            $total->setGrandTotal($total->getGrandTotal() + $ddiTaxAmount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() + $ddiTaxAmount);
            $total->setTaxAmount($ddiTaxAmount);
            $total->setBaseTaxAmount($ddiTaxAmount);

            $this->customerSession->unsCheckoutData();
        }
        return $this;
    }

    //Customized core Tax fetch function
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $totals = [];
        $store = $quote->getStore();
        $applied = $total->getAppliedTaxes();
        if (is_string($applied)) {
            $applied = $this->serializer->unserialize($applied);
        }
        $amount = $total->getTaxAmount();
        if ($amount === null) {
            $amount = 0.00;
        }

        $area = null;
        if ($this->_config->displayCartTaxWithGrandTotal($store) && $total->getGrandTotal()) {
            $area = 'taxes';
        }

        $totals[] = [
            'code' => $this->getCode(),
            'title' => __('Tax'),
            'full_info' => $applied ? $applied : [],
            'value' => $amount,
            'area' => $area,
        ];

        if (empty($totals)) {
            return null;
        }
        return $totals;
    }
}
