<?php

namespace Dckap\QuickRFQ\Controller\Quote;

class Submit extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    protected $formKey;

    protected $productRepository;

    protected $jsonFactory;
    protected $quickrfqHelper;
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Dckap\QuickRFQ\Helper\Data $quickrfqHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->cart = $cart;
        $this->product = $product;
        $this->formKey = $formKey;
        $this->productRepository = $productRepository;
        $this->jsonFactory = $jsonFactory;
        $this->quickrfqHelper = $quickrfqHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
//        var_dump($params);die;
//        var_dump(json_decode($datas['shipping_address']));
//        $array = (array)json_decode($datas['quote_id']);
//        var_dump((int)$datas['quote_id']);
//        die;
        try {
            $tempOrder = [
                'currency_id' => 'USD',
                'email' => 'vignesvaran@mailinator.com', //buyer email id
                'shipping_address' => [
                    'firstname' => 'John', //address Details
                    'lastname' => 'Doe',
                    'street' => '123 Demo',
                    'city' => 'Newyork',
                    'country_id' => 'US',
                    'region' => 'New York',
                    'postcode' => '10001',
                    'telephone' => '0123456789',
                    'fax' => '32423',
                    'save_in_address_book' => 1
                ],
                'items' => [ //array of product which order you want to create
                    ['product_id' => '1', 'qty' => 1],
                    ['product_id' => '2', 'qty' => 2]
                ]
            ];
            $defaultDataArr = ["special_instructions" => [
                "show_gird" => "0",
                "show_in_order" => "1",
                "show_in_pdf" => "1",
                "show_in_email" => "1",
                "frontend_label" => "Special Instrucutions",
                "value" => (isset($params['special_ins'])) ? $params['special_ins'] : "",
                "val" => (isset($params['special_ins'])) ? $params['special_ins'] : "",
                "type" => "textarea"
            ],
                "expected_delivery_date" => [
                "show_gird" => "0",
                "show_in_order" => "1",
                "show_in_pdf" => "1",
                "show_in_email" => "1",
                "frontend_label" => "Expected Delivery Date",
                "value" => (isset($params['exp_delivery_date']) && $params['exp_delivery_date'] != 'undefined') ? $params['exp_delivery_date'] : "",
                "val" => (isset($params['exp_delivery_date']) && $params['exp_delivery_date'] != 'undefined') ? $params['exp_delivery_date'] : "",
                "type" => "date"
            ],
                "purchase_order_number" => [
                "show_gird" => "0",
                "show_in_order" => "1",
                "show_in_pdf" => "1",
                "show_in_email" => "1",
                "frontend_label" => "Purchase Order Number",
                "value" => (isset($params['po_number'])) ? $params['po_number'] : "",
                "val" => (isset($params['po_number'])) ? $params['po_number'] : "",
                "type" => "text"
            ]
                ];
            $defaultData = json_encode((object)$defaultDataArr);
            $tempOrder['bss_customfield'] = $defaultData;
            $tempOrder['ddi_delivery_contact_email'] = (isset($params['storepickup_email'])) ? $params['storepickup_email'] : "";
            $tempOrder['ddi_delivery_contact_no'] = (isset($params['storepickup_no'])) ? $params['storepickup_no'] : "";
            $tempOrder['ddi_pref_warehouse'] = (isset($params['storepickup_warehouse'])) ? $params['storepickup_warehouse'] : "";
            $tempOrder['ddi_pickup_date'] = (isset($params['storepickup_date']) && $params['storepickup_date'] != 'undefined') ? $params['storepickup_date'] : "";

//            var_dump($tempOrder);die;
            $quoteId = (int)$params['quote_id'];
            $order = $this->quickrfqHelper->createMageOrder($tempOrder, $quoteId);
//            var_dump($order);die;
//            $this->messageManager->addSuccess(__('Order created successfully.'));

        } catch (\Exception $e) {
//            $this->messageManager->addException($e, __('error occurred'));

        }
        $res = $this->jsonFactory->create();
        $sessionMessage = $this->customerSession->getErrorMessage();
        $result = $res->setData(['order' => $order, 'ErpMessage' => $sessionMessage]);
        return $result;

    }
}
