<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\QuickRFQ\Block\Customer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magento\Framework\App\ObjectManager;
use \Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Setup\Exception;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'Dckap_QuickRFQ::orderpad.phtml';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Dckap\Theme\Helper\Data
     */
    protected $themeHelper;
    /**
     * @var \Cloras\Base\Helper\Data
     */
    protected $clorasHelper;
    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;
    /**
     * @var \DCKAP\Extension\Model\Shipto
     */
    protected $shiptoModel;
    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;
    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;
    protected $quickrfqHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Dckap\Theme\Helper\Data $themeHelper,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \DCKAP\Extension\Model\Shipto $shiptoModel,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Dckap\QuickRFQ\Helper\Data $quickrfqHelper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->productRepository = $productRepository;
        $this->themeHelper = $themeHelper;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->shiptoModel = $shiptoModel;
        $this->collectionFactory = $collectionFactory;
        $this->_registry = $registry;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->quickrfqHelper = $quickrfqHelper;

        parent::__construct($context, $data);
    }

    /**
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Order Pad'));
    }

    /**
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->themeHelper->getOrderPadView();
    }

    /**
     * @return mixed
     */
    public function getOrderpadItems()
    {
        $orderPadItems = $this->_registry->registry('orderpad_items');
        return $orderPadItems;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        $handle = $this->_registry->registry('handle');
        return $handle;
    }

    /**
     * @return mixed
     */
    public function getShiptoConfig()
    {
        $shiptoConfig = $this->_registry->registry('config');
        return $shiptoConfig;
    }

    /**
     * @return array|bool
     */
    public function getShiptoItems()
    {
        $arrCustomerAddresses = $this->getCustomerShipToAddresses();
        if(isset($arrCustomerAddresses)){
           return $arrCustomerAddresses;
        }
        return false;
    }

    /**
     * @param bool $sku
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductDetails($sku = false)
    {
        if ($sku) {
            return $this->productRepository->get($sku);
        }
        return false;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * @param $sku
     * @return string
     */
    public function getProductImageUrl($sku)
    {
        $imageUrl = '';
        try {
//            $sku = 'RP12SP';
            $product = $this->productRepository->get($sku);
            if ($product) {
                $imageUrl = $this->imageHelperFactory->create()->init($product, 'product_small_image')->getUrl();
                return $imageUrl;
            }
        } catch (\Exception $e) {
//            return '';
        }
        if ($imageUrl == '') {
            $imageUrl = $this->imageHelperFactory->create()->getDefaultPlaceholderUrl('image');
        }
        return $imageUrl;
    }

    /**
     * @param $sku
     * @return string
     */
    public function getProductUrl($sku)
    {
        $productUrl = "#";
        try {
            $product = $this->productRepository->get($sku);
            if ($product) {
                return $product->getProductUrl();
            }
        } catch (\Exception $e) {
            return "#";
        }
        return $productUrl;
    }

    public function getCustomerShipToAddresses(){

        $strCustomerAddress = '';
        $arrCustomerAddresses = [];
        $resArray = array();
        $customerId = $this->_customerSession->getCustomerId();
        $arrCustomerAddress = $this->quickrfqHelper->getCustomerAddress($customerId);
        $customerSessionData = $this->_customerSession->getCustomData();
        $accountNumber = $customerSessionData['accountNumber'];
        $shiptoItems = $this->shiptoModel->toArray();
        if ($arrCustomerAddress && count($arrCustomerAddress)) {
            foreach ($arrCustomerAddress as $CustomerAddres) {
                if ( (true == $CustomerAddres['is_active']) && (false == empty($CustomerAddres['ddi_ship_number'])) && ($CustomerAddres['erp_account_number'] == $accountNumber)  && array_key_exists($CustomerAddres['ddi_ship_number'],$shiptoItems)) {
                    $street = trim(preg_replace('/\s+/', ' ', $CustomerAddres['street']));
                    $strCustomerAddress = $CustomerAddres['firstname'] . ' ' . $CustomerAddres['lastname'] . ', ' . $CustomerAddres['company'] . ', ' . $street . ', ' . $CustomerAddres['city'] . ', ' . $CustomerAddres['region'] . ', ' . strtoupper($CustomerAddres['country_id']) . ' - ' . $CustomerAddres['postcode'];
                    $arrCustomerAddresses['label'] = $strCustomerAddress;
                    $arrCustomerAddresses['value'] = $CustomerAddres['ddi_ship_number'];
                    $resArray[] = $arrCustomerAddresses;
                }
            }
        }
        return $resArray;
    }

    public function RetainShipTo(){
        $strSelectedShipto = false;
        $CustomerData = $this->_customerSession->getCustomValue();
        if(isset($CustomerData['SelectedShipTo'])){
            $strSelectedShipto = $CustomerData['SelectedShipTo'];
        }
        return $strSelectedShipto;
    }
}
