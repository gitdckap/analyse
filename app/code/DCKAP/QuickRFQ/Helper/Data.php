<?php

namespace Dckap\QuickRFQ\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Data
 * @package Dckap\QuickRFQ\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Cloras\Base\Helper\Data
     */
    protected $clorasHelper;
    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $_transportBuilder;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;
    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $_countryFactory;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var
     */
    protected $orderCollectionFactory;
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    protected $customerFactory;
    protected $_storeManager;
    protected $_customerSession;

    /**
     * @param Magento\Framework\App\Helper\Context $context
     * @param Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Magento\Catalog\Model\Product $product
     * @param Magento\Framework\Data\Form\FormKey $formKey $formkey,
     * @param Magento\Quote\Model\Quote $quote ,
     * @param Magento\Customer\Model\CustomerFactory $customerFactory ,
     * @param Magento\Sales\Model\Service\OrderService $orderService ,
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formkey,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Sales\Model\Service\OrderService $orderService,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $_transportBuilder,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $CustomerSession

    )
    {
        $this->_storeManager = $storeManager;
        $this->_product = $product;
        $this->_formkey = $formkey;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->orderService = $orderService;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->_transportBuilder = $_transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->_countryFactory = $countryFactory;
        $this->json = $json;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $CustomerSession;
        parent::__construct($context);
    }

    /**
     * Create Order On Your Store
     *
     * @param array $orderData
     * @return array
     *
     */
    public function createMageOrder($orderData, $quoteId)
    {
        try {
            /*$store = $this->_storeManager->getStore();
            $websiteId = $this->_storeManager->getStore()->getWebsiteId();
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($orderData['email']);// load customet by email address
            if (!$customer->getEntityId()) {
                //If not avilable then create this customer
                $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($orderData['shipping_address']['firstname'])
                    ->setLastname($orderData['shipping_address']['lastname'])
                    ->setEmail($orderData['email'])
                    ->setPassword($orderData['email']);
                $customer->save();
            }
            $quote = $this->quote->create(); //Create object of quote
            $quote->setStore($store); //set store for which you create quote
            // if you have allready buyer id then you can load customer directly
            $customer = $this->customerRepository->getById($customer->getEntityId());
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer
//        var_dump($customer->getId());die;

            //add items in quote
            foreach ($orderData['items'] as $item) {
                $product = $this->_product->load($item['product_id']);
//                $product->setPrice($item['price']);
                $quote->addProduct(
                    $product,
                    intval($item['qty'])
                );
            }

            //Set Address to quote
            $quote->getBillingAddress()->addData($orderData['shipping_address']);*/

            $quote = $this->quote->create()->load($quoteId);
//            $quote->getShippingAddress()->addData($orderData['shipping_address']);
//            $quote->getBillingAddress()->addData($orderData['shipping_address']);

            // Collect Rates and Set Shipping & Payment Method

//            $shippingAddress = $quote->getShippingAddress();
//            $shippingAddress->setCollectShippingRates(true)
//                ->collectShippingRates()
//                ->setShippingMethod('freeshipping_freeshipping'); //shipping method
            $quote->setPaymentMethod('checkmo'); //payment method
            $quote->setInventoryProcessed(false); //not effetc inventory
            /* save custom checkout data */
            $quote->setBssCustomfield($orderData['bss_customfield']);
            $quote->setDdiDeliveryContactEmail($orderData['ddi_delivery_contact_email']);
            $quote->setDdiDeliveryContactNo($orderData['ddi_delivery_contact_no']);
            $quote->setDdiPrefWarehouse($orderData['ddi_pref_warehouse']);
            $quote->setDdiPickupDate($orderData['ddi_pickup_date']);

            $quote->save(); //Now Save quote and your quote is ready

            // Set Sales Order Payment
            $quote->getPayment()->importData(['method' => 'checkmo']);

            // Collect Totals & Save Quote
            $quote->collectTotals()->save();

            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);

            //$order->setEmailSent(1);
            $order->setEmailSent(0);

            $templateOptions = array('area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->_storeManager->getStore()->getId());
            $shipping_address = $order->getShippingAddress();
            $country = $this->_countryFactory->create()->loadByCode($shipping_address->getData("country_id"));
            $country_name = $country->getName();
            $customCheckoutField = $this->json->unserialize($order->getBssCustomfield());
            if (isset($customCheckoutField['purchase_order_number'])) {
                if (isset($customCheckoutField['purchase_order_number']['value'])) {
                    $poNumber = $customCheckoutField['purchase_order_number']['value']['value'];
                } else {
                    $poNumber = $customCheckoutField['purchase_order_number'];
                }
            }
            $poNumber = !empty($poNumber) ? $poNumber : '';
            $templateVars = [
                'store' => $this->_storeManager->getStore(),
                'customer_name' => $orderData['shipping_address']['firstname'],
                'order' => $order,
                'name' => $shipping_address->getData("firstname") . ' ' . $shipping_address->getData("lastname"),
                'company' => $shipping_address->getData("company"),
                'street' => $shipping_address->getData("street"),
                'city' => $shipping_address->getData("city") . ',' . $shipping_address->getData("region") . ',' . $shipping_address->getData("postcode"),
                'country' => $country_name,
                'telephone' => "T: " . $shipping_address->getData("telephone"),
                'poNumber' => $poNumber
            ];
          try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $email = $this->scopeConfig->getValue('trans_email/ident_support/email', $storeScope, $order->getStoreId());
            $name = $this->scopeConfig->getValue('trans_email/ident_support/name', $storeScope, $order->getStoreId());

            $from = ['email' => $email, 'name' => $name];

            $this->inlineTranslation->suspend();
            $transport = $this->_transportBuilder->setTemplateIdentifier('request_quote_template')
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)//$templateVars
                ->setFrom($from)
                ->addTo($order->getCustomerEmail())
		            ->addBcc($email)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

          }catch (\Exception $e) {
            $result = ['error' => 1, 'msg' => 'Your custom message'];
          }
            $increment_id = $order->getRealOrderId();
            $order->setState("holded")->setStatus("quote_request");
            $order->setBssCustomfield($orderData['bss_customfield']);
            $order->save();

            if ($order->getEntityId()) {
                $result['order_id'] = $order->getRealOrderId();
            } else {
                $result = ['error' => 1, 'msg' => 'Your custom message'];
            }
        } catch (\Exception $e) {
          $result = ['error' => 1, 'msg' => 'Your custom message'];

        }
        return $result;
    }

    /**
     * @param bool $filterData
     * @return array
     */
    public function getRecentOrders($filterData = false)
    {
        $returnData = array();
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('order_list');
        if ($status) {
            $responseData = $this->clorasDDIHelper->getOrderList($integrationData, $filterData);
            if ($responseData && isset($responseData) && count($responseData['orderList'])) {
                foreach ($responseData['orderList'] as $key => $order) {
                    if ($order['orderStatus'] == 'Requested' || $order['orderStatus'] == 'Quoted') {
                        unset($responseData['orderList'][$key]);
                    } else {
                        continue;
                    }
                }
                $data = $responseData['orderList'];
                $sortField = 'orderDate';
                $sortOrder = SORT_DESC;
                $fieldColumn = array_column($data, $sortField);
                array_multisort($fieldColumn, $sortOrder, $data);

                $returnData = array_slice($data, 0, 5);
                $responseData['orderList'] = $returnData;
                return $responseData;
            } else {
                return $responseData;
            }
        }
        return $returnData;
    }

    /**
     * @return bool|int
     */
    public function getShipToItems()
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('ship_to');
        if ($status) {
            $responseData = $this->clorasDDIHelper->getShiptoItems($integrationData);
            if ($responseData && !empty($responseData)) {
                return $responseData;
            }
        }
        return false;
    }

    /**
     * @param $incrementId
     * @return \Magento\Framework\DataObject
     */
    public function getMagentoPaymentDetails($incrementId)
    {
        $orderId = null;
        $PaymentData = [];
        if ($incrementId) {
            $collection = $this->_orderCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('ddi_order_id', ['like' => $incrementId])
                ->setPageSize(1)
                ->setCurPage(1)
                ->getFirstItem(); //Add condition if you wish

            $orderId = $collection->getEntityId();
            if ($orderId) {
                $order = $this->orderRepository->get($orderId);
                $payment = $order->getPayment();
                $payment_method = $payment->getMethod();
                $method = $payment->getMethodInstance();

                if ($payment_method == 'authorizenet_acceptjs') {
                    $PaymentData['title'] = $method->getTitle();
                    $PaymentData['auth_amount'] = $payment->getAmountAuthorized();
                    $PaymentData['ref_number'] = $payment->getLastTransId();

                } elseif ($payment_method == 'anet_creditcard') {
                    $PaymentData['title'] = $method->getTitle();
                    $PaymentData['auth_amount'] = $payment->getAmountAuthorized();
                    $PaymentData['ref_number'] = $payment->getLastTransId();
                } elseif ($payment_method == 'elementpayment') {
                    $PaymentData['title'] = $method->getTitle();
                    $addtionalOptions = $order->getPayment()->getAdditionalInformation();
                    if (!empty($addtionalOptions)) {
                        $PaymentData['auth_amount'] = $addtionalOptions['cc_amount_approved'];
                        $PaymentData['ref_number'] = $addtionalOptions['cc_token'];
                    } else {
                        $PaymentData['auth_amount'] = "";
                        $PaymentData['ref_number'] = "";
                    }
                } else {
                    $PaymentData['title'] = $method->getTitle();
                    $PaymentData['auth_amount'] = "";
                    $PaymentData['ref_number'] = "";

                }
            }


        }


        return $PaymentData;
    }

    public function getCustomerAddress($customerId)
    {
        $customer =  $this->customerFactory->create();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customerModel = $customer->load($customerId);
        $customerAddress = [];

        if ($customerModel->getAddresses() != null)
        {
            foreach ($customerModel->getAddresses() as $address) {
                $customerAddress[] = $address->toArray();
            }
        }

        return $customerAddress;
    }
    public function UnsetRetainShipTo(){
        $this->_customerSession->unsCustomValue();

    }
}
