<?php

namespace DCKAP\Extension\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    const DDI_ERP_IS_B2C_CONFIGURATION = 'dckapextension/BitExpert_ForceCustomerLogin/is_b2c';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    protected $sessionFactory;
    protected $checkoutSession;
    protected $regioncollectionFactory;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        SessionFactory $sessionFactory,
        Session $checkoutSession,
        RegionCollectionFactory $regioncollectionFactory,
        Image $imageHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->sessionFactory = $sessionFactory;
        $this->checkoutSession = $checkoutSession;
	    $this->regioncollectionFactory = $regioncollectionFactory;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Return store
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Return WebsiteId
     *
     * @return int
     * @throws NoSuchEntityException
     */
    public function getCurrentWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    public function getItemImage($productId)
    {
        try {
            $_product = $this->productRepository->getById($productId);
        } catch (NoSuchEntityException $e) {
            return 'product not found';
        }
        $image_url = $this->imageHelper->init($_product, 'product_base_image')->getUrl();
        return $image_url;
    }
    /**
     * Return store configuration value of your template field that which id you set for template
     *
     * @param string $path
     * @param int $storeId
     * @return mixed
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store config value
     *
     * @return Boolean
     */
    public function checkIsB2c()
    {
        return $this->getConfigValue(
            self::DDI_ERP_IS_B2C_CONFIGURATION,
            $this->getStore()->getStoreId()
        );
    }

    public function getWebsiteMode()
    {
        return $this->scopeConfig->getValue(
            'themeconfig/mode_config/website_mode',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getErpEcommUserData()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->getEcommData()) {
            $customerData = $customerSession->getEcommData()[0];
            return $customerData;
        }
        return false;
    }

    public function getValidateUserData()
    {
        $customerSession = $this->sessionFactory->create();
        if ($customerSession->getCustomData()) {
            $customerData = $customerSession->getCustomData();
            return $customerData;
        }
        return false;
    }

    public function getDeliverydateshow()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/dckap_delivery/enable_deliverydate',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getViewInventoryByLocation()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/dckap_inventory/enable_inventory_location',
            ScopeInterface::SCOPE_STORE
        );
    }

	public function getpayflowproActionMethod(){
		return  $this->scopeConfig->getValue(
				'payment/payflowpro/payment_action',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}


	public function getpayflowproAllowCreditCards(){
		return  $this->scopeConfig->getValue(
				'payment/payflowpro/cctypes',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

    public function getDetailTabTitle(){
        return  $this->scopeConfig->getValue(
            'dckapextension/dckap_inventory/detail_title',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getShiptoConfig()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_shipto/default_shipto',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getTagLine()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_tagline/default_tagline',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCallUs()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_tagline/default_callus',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getStoreName()
    {
        return  $this->scopeConfig->getValue(
            'general/store_information/name',
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getIsLogger()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_log/default_logger',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getCallUsText()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_tagline/default_callus_text',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDisplayReviews()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/dckap_inventory/display_review',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getWorkingTime()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_tagline/working_time',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getGuestPriceDisplay()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/BitExpert_ForceCustomerLogin/price_display',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getGuestStockDisplay()
    {
        $customerSession = $this->sessionFactory->create();
        if (!$customerSession->isLoggedIn()) {
            return  $this->scopeConfig->getValue(
                'dckapextension/BitExpert_ForceCustomerLogin/stock_display',
                ScopeInterface::SCOPE_STORE
            );
        }
        return 1;
    }

    public function getSearchEngine()
    {
        return  $this->scopeConfig->getValue(
            'catalog/search/engine',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPayInvoicePayment()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_payinvoice/default_payment',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPickupDateOption()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_pickupcustomize/pickup_date',
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getPickupRequired()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_pickupcustomize/pickup_required',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getdisableDates()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_pickupcustomize/disable_dates',
            ScopeInterface::SCOPE_STORE
        );

    }

    public function getenableSaurday()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_pickupcustomize/enable_saturday',
            ScopeInterface::SCOPE_STORE
        );

    }

    public function getenableSunday()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_pickupcustomize/enable_sunday',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getErpBranch()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_branch/branch_code',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getDefaultCaptcha()
    {
        $defautCaptchaStatus = $this->scopeConfig->getValue(
            'customer/captcha/enable',
            ScopeInterface::SCOPE_STORE
        );
        $defautCaptchaForms = $this->scopeConfig->getValue(
            'customer/captcha/forms',
            ScopeInterface::SCOPE_STORE
        );
        $defautCaptchaForms = explode(',', $defautCaptchaForms);
        if ($defautCaptchaStatus && in_array('user_login', $defautCaptchaForms)) {
            return true;
        }
        return false;
    }

    public function getGoogleCaptcha()
    {
        return  $this->scopeConfig->getValue(
            'msp_securitysuite_recaptcha/frontend/enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getIsShiptoBasedPrice()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_shitpto_price/shitpto_price',
            ScopeInterface::SCOPE_STORE
        );
    }

	public function getIsMiscamt(){
		return  $this->scopeConfig->getValue(
				'dckapextension/general/status',
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

     public function getIsAkeneoVisbilitySync(){
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_akeneo_sync_setting/ddi_akeneo_visibility',
            ScopeInterface::SCOPE_STORE
        );
     }

    public function getIsAkeneoStatusSync()
    {
        return  $this->scopeConfig->getValue(
            'dckapextension/ddi_akeneo_sync_setting/ddi_akeneo_status',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getProceedToCheckout()
    {
        $flag = 0;
        $disableCheckout = $this->scopeConfig->getValue(
            'dckapextension/dckap_checkout/disable_checkout',
            ScopeInterface::SCOPE_STORE
        );
        if ($disableCheckout) {
            $itemsVisible = $this->checkoutSession->getQuote()->getAllVisibleItems();
//        $items = $this->checkoutSession->getQuote()->getAllItems();
            if (!empty($itemsVisible)) {
                foreach ($itemsVisible as $item) {
                    if ($item->getPrice() == 0) {
                        $flag = 1;
                    }
                }
            }
        }
        return $flag;
    }

    public function getRegionCodeDetials($region, $country)
    {
        $regionCode = null;
        try {
            $regiondata = $this->regioncollectionFactory->create()
                ->addFieldToFilter('code', ['like' => $region])
                ->addFieldToFilter('country_id', ['like' => $country])
                ->addFieldToSelect('region_id')
                ->getData();
            $regionCode = $regiondata[0];
        } catch (\Exception $e) {
            return false;
        }
        return $regionCode;
    }
}
