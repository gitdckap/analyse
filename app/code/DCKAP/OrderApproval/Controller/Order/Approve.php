<?php
/**
 * Copyright © 2016 DCKAP. All rights reserved.
 */

namespace DCKAP\OrderApproval\Controller\Order;

/**
 * Class Approve
 * @package DCKAP\OrderApproval\Controller\Order
 */
class Approve extends \Magento\Framework\App\Action\Action {
	const DEFAULT_SHIP_TO_NUMBER = '999999999';

	/**
	 * @var \Magento\Framework\Controller\Result\JsonFactory
	 */
	protected $jsonFactory;

	/**
	 * @var \Magento\Sales\Api\OrderRepositoryInterface
	 */
	protected $orderRepository;

	/**
	 * @var \Cloras\DDI\Helper\Data
	 */
	protected $clorasDDIHelper;

	/**
	 * @var \Magento\Framework\Serialize\Serializer\Json
	 */
	protected $serializer;

	/**
	 * @var \Dckap\ShippingAdditionalFields\Helper\Data
	 */
	protected $storePickupHelper;

	/**
	 * @var \Magento\Quote\Model\QuoteFactory
	 */
	protected $quoteFactory;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $customerSession;

    /**
     * @var \DCKAP\Extension\Helper\Data
     */
    protected $extensionHelper;

	/**
	 * Approve constructor.
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
	 * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
	 * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
	 * @param \Magento\Framework\Serialize\Serializer\Json $serializer
	 * @param \Dckap\ShippingAdditionalFields\Helper\Data $storePickupHelper
	 * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper
	 * @param \DCKAP\Extension\Helper\Data $extensionHelper
	 */
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Dckap\ShippingAdditionalFields\Helper\Data $storePickupHelper,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Customer\Model\Session $customerSession,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper
	) {
		$this->jsonFactory = $jsonFactory;
		$this->orderRepository = $orderRepository;
		$this->clorasDDIHelper = $clorasDDIHelper;
		$this->serializer = $serializer;
		$this->storePickupHelper = $storePickupHelper;
		$this->quoteFactory = $quoteFactory;
		$this->customerSession = $customerSession;
		$this->_orderApprovalHelper = $orderApprovalHelper;
        $this->extensionHelper = $extensionHelper;
		parent::__construct($context);
	}

	/**
	 * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
	 */
	public function execute() {
		if (!$this->customerSession->isLoggedIn()) {
			$resultRedirect = $this->resultRedirectFactory->create();
			$this->messageManager->addNotice(__("Login required to approve the order."));
			$loginUrl = $this->_url->getUrl('customer/account/login');
			return $resultRedirect->setPath($loginUrl);
		}
		$params = $this->getRequest()->getParams();
		$orderId = $params['order_id'];
		$data = [];
		try {
			$order = $this->orderRepository->get($orderId);
			list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_order');
			if ($status) {
				$lineItemData = [];
				foreach ($order->getAllVisibleItems() as $item) {
					$itemData = [];
					$uom = 'EA';
					if (isset($item->getProductOptions()['info_buyRequest'])) {
						$uom = isset($item->getProductOptions()['info_buyRequest']['custom_uom']) ? $item->getProductOptions()['info_buyRequest']['custom_uom']:'EA';
					}
					$itemData['stockNum'] = $item->getSku();
					$itemData['qty'] = (string) $item->getQtyOrdered();
					$itemData['uom'] = $uom;
					$itemData['price'] = (string) number_format($item->getPrice(), 4);
					$itemData['mfgNum'] = '';
					$itemName = preg_replace('/[^A-Za-z0-9_., -]/', '', $item->getName());
					$itemData['description'] = $itemName;

					/* commented existing $0 lineitem removal code */
					/*if ($item->getPrice() > 0) {
//                    if ($item->getPrice() > 0 && $item->getProductType() != 'configurable') {
						$lineItemData[] = $itemData;
					}*/
                    /* Added new code to allow $0 lineitem */
                    $allowZero = $this->extensionHelper->getProceedToCheckout();
                    if ($allowZero == '0') {
                        $superAttribute = "0";
                        if (isset($item->getProductOptions()['info_buyRequest'])) {                            $superAttribute = isset($item->getProductOptions()['info_buyRequest']['super_attribute']) ? $item->getProductOptions()['info_buyRequest']['super_attribute'] : '0';
                        }
                        if ($superAttribute != "0" && $item->getParentItem() != null) {
                            if ($item->getPrice() > 0) {
                                $lineItemData[] = $itemData;
                            }
                        } else {
                            $lineItemData[] = $itemData;
                        }
                    } else {
                        if ($item->getPrice() > 0) {
                            $lineItemData[] = $itemData;
                        }
                    }
				}
				$shippingAddress = $order->getShippingAddress();
				$shipToNumber = ($order->getShipToNumber() && $order->getShipToNumber()!='') ? $order->getShipToNumber():self::DEFAULT_SHIP_TO_NUMBER;
                $street = $shippingAddress->getStreet();
				$orderData = [
						"shipAddress" => [
								"shipId" => $shipToNumber,
								"shipCompanyName" => ($shippingAddress->getCompany()) ? $shippingAddress->getCompany():"",
								"shipAddress1" => (isset($street[0])) ? $street[0] : "",
								"shipAddress2" => (isset($street[1])) ? $street[1] : "",
								"shipAddress3" => (isset($street[2])) ? $street[2] : "",
								"shipCity" => $shippingAddress->getCity(),
								"shipState" => $shippingAddress->getRegionCode(),
								"shipPostCode" => (string) $shippingAddress->getPostcode(),
								"shipCountry" => $shippingAddress->getCountryId(),
								"shipPhone" => (string) $shippingAddress->getTelephone(),
								"shipFax" => "",
								"shipAttention" => $shippingAddress->getFirstname() . " " . $shippingAddress->getLastname(),
								"quoteRequest" => "N",
								"validateOnly" => "N"
						],
						"lineItems" => [
								"itemData" => $lineItemData
						],
						"shippingMethod" => $order->getShippingMethod(),
						"shippingAmount" => (string) $order->getShippingAmount()
				];

				$orderData['branch'] = '';
				if ($order->getShippingMethod()=='ddistorepickup_ddistorepickup') {
					$ddiPrefWarehouse = $order->getDdiPrefWarehouse();
					$warehouseList = $this->storePickupHelper->getWarehouseDetail();
					if ($warehouseList && !empty($warehouseList->getData())) {
						foreach ($warehouseList->getData() as $warehouse) {
							if ($ddiPrefWarehouse==$warehouse['store_name']) {
								$orderData['branch'] = $warehouse['store_description'];
							}
						}
					}
				}
				$quote = $this->quoteFactory->create()->loadByIdWithoutStore($order->getQuoteId());
				$orderData['special_instructions'] = "";
				$orderData['purchase_order_number'] = "";
				$orderData['expected_delivery_date'] = "";
				$customCheckoutField = [];
				if ($quote->getBssCustomfield()) {
					$customCheckoutField = $this->serializer->unserialize($quote->getBssCustomfield());
					if (isset($customCheckoutField['special_instructions'])) {
						if (isset($customCheckoutField['special_instructions']['value'])) {
							$orderData['special_instructions'] = $customCheckoutField['special_instructions']['value'];
						} else {
							$orderData['special_instructions'] = $customCheckoutField['special_instructions'];
						}
					}
					if (isset($customCheckoutField['purchase_order_number'])) {
						if (isset($customCheckoutField['purchase_order_number']['value'])) {
							$orderData['purchase_order_number'] = $customCheckoutField['purchase_order_number']['value'];
						} else {
							$orderData['purchase_order_number'] = $customCheckoutField['purchase_order_number'];
						}
					}
					if (isset($customCheckoutField['expected_delivery_date'])) {
						if (isset($customCheckoutField['expected_delivery_date']['value'])) {
							$orderData['expected_delivery_date'] = $customCheckoutField['expected_delivery_date']['value'];
						} else {
							$orderData['expected_delivery_date'] = $customCheckoutField['expected_delivery_date'];
						}
					}
				}
				$orderData['delivery_contact_email'] = ($order->getDdiDeliveryContactEmail()) ? $order->getDdiDeliveryContactEmail():"";
				$orderData['delivery_contact_no'] = ($order->getDdiDeliveryContactNo()) ? $order->getDdiDeliveryContactNo():"";
				$orderData['pickup_date'] = ($order->getDdiPickupDate()) ? $order->getDdiPickupDate():"";
				$customerData = [];
				$customerData['email'] = $order->getCustomerEmail();
				$customerData['account_number'] = $order->getAccountNumber();
				$customerData['user_id'] = $order->getUserId();

				$orderPlaced = $this->clorasDDIHelper->submitPendingOrder($integrationData, $orderData, $customerData);
				if (isset($orderPlaced['status']) && $orderPlaced['status'] == 0) {
                    $data['status'] = "Failure";
                    $data['message'] = $orderPlaced['message'];
                } elseif (isset($orderPlaced['data']['isValid']) && $orderPlaced['data']['isValid'] == 'no') {
                    $data['status'] = "Failure";
                    $data['message'] = $orderPlaced['data']['errorMessage'];
                } elseif (isset($orderPlaced['data']['orderNumber'])) {
					try {
						$order->setDdiOrderId($orderPlaced['data']['orderNumber']);
						if (isset($orderPlaced['data']['orderDetails']['taxTotal']) && $orderPlaced['data']['orderDetails']['taxTotal']!='') {
							$order->setTaxAmount((float) str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['taxTotal'])));
							$order->setBaseTaxAmount((float) str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['taxTotal'])));
						} else {
							$order->setTaxAmount(0.0000);
							$order->setBaseTaxAmount(0.0000);
						}
						if (isset($orderPlaced['data']['orderDetails']['freightTotal']) && $orderPlaced['data']['orderDetails']['freightTotal']!='') {
							$order->setShippingAmount((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['freightTotal']))));
							$order->setBaseShippingAmount((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['freightTotal']))));
						} else {
							$order->setShippingAmount(0.0000);
							$order->setBaseShippingAmount(0.0000);
						}
						if (isset($orderPlaced['data']['orderDetails']['merchandiseTotal']) && $orderPlaced['data']['orderDetails']['merchandiseTotal']!='') {
							$order->setSubtotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['merchandiseTotal']))));
							$order->setBaseSubtotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['merchandiseTotal']))));
						}
						if (isset($orderPlaced['data']['orderDetails']['orderTotal']) && $orderPlaced['data']['orderDetails']['orderTotal']!='') {
							$order->setGrandTotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['orderTotal']))));
							$order->setBaseGrandTotal((float) (str_replace('$', '', str_replace(',', '', $orderPlaced['data']['orderDetails']['orderTotal']))));
						}
                        $order->setState("approved")->setStatus("approved");
						$order->save();
                              //For send email
                        $this->_orderApprovalHelper->SendOrderApprovalEmail($order);
						//End Email
						$data['status'] = "Success";
						$data['message'] = __("Order approved successfully. The order number is " . $order->getDdiOrderId());
					} catch (\Exception $e) {
						$data['status'] = "Failure";
						$data['message'] = $e->getMessage();
					}
				}
			}
		} catch (\Exception $e) {
			$data['status'] = "Failure";
			$data['message'] = $e->getMessage();
		}
		if (isset($params['is_ajax']) && $params['is_ajax']=='1') {
			$res = $this->jsonFactory->create();
			$result = $res->setData($data);
			return $result;
		} else {
			$resultRedirect = $this->resultRedirectFactory->create();
			if ($data['status']=="Success") {
				$this->messageManager->addSuccess(__($data['message']));
			} else {
				$this->messageManager->addNotice(__($data['message']));
			}
			$loginUrl = $this->_url->getUrl('orderapproval/index/pendingorders');
			return $resultRedirect->setPath($loginUrl);
		}
	}
}
