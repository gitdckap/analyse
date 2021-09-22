<?php

	namespace Dckap\Checkout\Controller\Payinvoice;


	class Payflow extends \Magento\Framework\App\Action\Action {

		const METHOD_PAYFLOWPRO = 'payflowpro';
		protected $resultJsonFactory;
		protected $customerSession;
		protected $tansactionSaleHelper;
		protected $dckapCheckoutHelper;
		protected $clorasHelper;
		protected $clorasDDIHelper;
		protected $extensionHelper;
		protected $payflowpro;
		protected $payflowConfig;
		protected $paymentData;
		protected $_paymentHelper;
		protected $paypalConfig;


		public function __construct(
				\Magento\Framework\App\Action\Context $context,
				\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
				\Magento\Customer\Model\Session $customerSession,
				\Dckap\Checkout\Helper\TransactionSale $tansactionSaleHelper,
				\Dckap\Checkout\Helper\Data $dckapCheckoutHelper,
				\Cloras\Base\Helper\Data $clorasHelper,
				\Cloras\DDI\Helper\Data $clorasDDIHelper,
				\DCKAP\Extension\Helper\Data $extensionHelper,
				\Magento\Paypal\Model\Payflowpro $payflowpro,
				\Magento\Paypal\Model\PayflowConfig $payflowConfig,
				\Magento\Payment\Helper\Data $paymentHelper,
				\Magento\Payment\Helper\Data $paymentData,
				\Magento\Paypal\Model\Config $paypalConfig

		) {
			$this->resultJsonFactory = $resultJsonFactory;
			$this->customerSession = $customerSession;
			$this->tansactionSaleHelper = $tansactionSaleHelper;
			$this->dckapCheckoutHelper = $dckapCheckoutHelper;
			$this->clorasHelper = $clorasHelper;
			$this->clorasDDIHelper = $clorasDDIHelper;
			$this->extensionHelper = $extensionHelper;
			$this->payflowpro = $payflowpro;
			$this->payflowConfig = $payflowConfig;
			$this->_paymentHelper = $paymentHelper;
			$this->paymentData = $paymentData;
			$this->paypalConfig = $paypalConfig;
			parent::__construct($context);
		}

		public function execute() {
			$responseData = $data = array();
			$resultRedirect = $this->resultRedirectFactory->create();
			try {
				$params = $this->getRequest()->getParams();

				$writer = new \Zend\Log\Writer\Stream(BP . "/var/log/Payflowpro_Payinvoice.log");
				$logger = new \Zend\Log\Logger();
				$logger->addWriter($writer);
				$logger->info("payflow form Request Data");
				$logger->info(print_r($params, true));

				//$IsMethodActive = $this->payflowpro->isActive();
				$IsMethodActive = $this->payflowConfig->isMethodActive('payflowpro');
				$requestData = $this->payflowpro->buildBasicRequest();
				$requestDataBasic = $requestData->getData();
				$cctypecheck = $this->extensionHelper->getpayflowproAllowCreditCards();
				$cctypecheck = preg_split ("/\,/", $cctypecheck);
				if (!in_array($params['payment']['cc_type'], $cctypecheck))
				{
					$this->messageManager->addErrorMessage("This credit card type is currently unavailable; please try a different card. ");
					$redirectUrl = $this->_url->getUrl('quickrfq/invoice/summary');
					return $resultRedirect->setPath($redirectUrl);
				}

				$paymentConfig = $this->paypalConfig;
				$paymentMode = $this->paymentData->getMethodInstance($paymentConfig::METHOD_PAYFLOWPRO)
						->getConfigData('sandbox_flag');
				$paymentAction = $this->extensionHelper->getpayflowproActionMethod();

				if ($paymentAction = "Authorization") {
					$paymentAction = "A";
				} else {
					$paymentAction = "S";
				}
				$logger->info("Payment Mode". $paymentMode);
				$logger->info("Payment Action".$paymentAction);

				if ($paymentMode) {
					$endpoint = "https://pilot-payflowpro.paypal.com/";
				} else {
					$endpoint = "https://payflowpro.paypal.com";
				}

				$cardNumber = $params['payment']['cc_number'];
				$expDate = $params['payment']['cc_exp_month'] . $params['payment']['cc_exp_year'];
				$cvv = $params['payment']['cc_cid'];
				$invoiceNumber = $params['invoice'];

				if (isset($params['invoice']) && $params['invoice']!='') {
					$logger->info("Invoice Numbers");
					$logger->info(print_r($params['invoice'], true));
					$customerId = $this->customerSession->getCustomerId();
					$customerName = $this->customerSession->getCustomer()->getName();
					$customerEmail = $this->customerSession->getCustomer()->getEmail();
					$billingAddress = $this->customerSession->getCustomer()->getDefaultBillingAddress();
					$shipingAddress = $this->customerSession->getCustomer()->getDefaultShippingAddress();
					$data['invoice'] = $this->dckapCheckoutHelper->getInvoices(array('data' => $params['invoice']));

					$amount = $data['invoice']['total'];
					$logger->info("Total Amount");
					$logger->info(print_r($amount, true));

					$request = array(
							"PARTNER" => $requestDataBasic['partner'],
							"VENDOR" => $requestDataBasic['vendor'],
							"USER" => $requestDataBasic['user'],
							"PWD" => $requestDataBasic['pwd'],
							"TENDER" => $requestDataBasic['tender'],
							"TRXTYPE" => $paymentAction,
							'BUTTONSOURCE' => $requestDataBasic['BUTTONSOURCE'],
							"CURRENCY" => "USD",
							"AMT" => $amount,
							'verbosity' => $requestDataBasic['verbosity'],
							"ACCT" => $cardNumber,
							"EXPDATE" => $expDate,
							"CVV2" => $cvv,
							"BILLTOFIRSTNAME" => $billingAddress->getFirstname(),
							"BILLTOLASTNAME" => $billingAddress->getLastname(),
							"BILLTOSTREET" => $billingAddress->getStreetFull(),
							"BILLTOCITY" => $billingAddress->getCity(),
							"BILLTOSTATE" => $billingAddress->getRegionCode(),
							"BILLTOZIP" => $billingAddress->getPostcode(),
							"BILLTOCOUNTRY" => $billingAddress->getCountryId(),
							"SHIPTOFIRSTNAME" => $shipingAddress->getFirstname(),
							"SHIPTOLASTNAME" => $shipingAddress->getLastname(),
							"SHIPTOSTREET" => $shipingAddress->getStreetFull(),
							"SHIPTOCITY" => $shipingAddress->getCity(),
							"SHIPTOSTATE" => $shipingAddress->getRegionCode(),
							"SHIPTOZIP" => $shipingAddress->getPostcode(),
							"SHIPTOCOUNTRY" => $shipingAddress->getCountryId(),

					);
					$logger->info("payflow payment request");
					$logger->info(print_r($request, true));
					//Run request and get the response

					$paramList = array();
					foreach ($request as $index => $value) {
						$paramList[] = $index . "[" . strlen($value) . "]=" . $value;
					}
					$apiStr = implode("&", $paramList);
					$logger->info(print_r($apiStr, true));

					// Initialize our cURL handle.
					$curl = curl_init($endpoint);

					curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);

					// If you get connection errors, it may be necessary to uncomment
					// the following two lines:
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

					curl_setopt($curl, CURLOPT_POST, TRUE);
					curl_setopt($curl, CURLOPT_POSTFIELDS, $apiStr);

					$result = curl_exec($curl);


					if ($result===FALSE) {
						$err = curl_error($curl);
						$this->messageManager->addErrorMessage($err);
						$redirectUrl = $this->_url->getUrl('dckapcheckout/index/index');
						$redirectUrl .= '?data=' . $invoiceNumber;
						return $resultRedirect->setPath($redirectUrl);
					} else {
						$paymentData = $this->parse_payflow_string($result);
						$logger->info("payflow payment  response");
						$logger->info(print_r($paymentData, true));
						if (isset($paymentData['RESULT']) && $paymentData['RESULT']!=0) {
							if($paymentData['RESULT'] == 12){
								$this->messageManager->addErrorMessage($paymentData['RESPMSG']. " Please double-check your CVV or confirm that there is enough money in your account." );
							}else{
								$this->messageManager->addErrorMessage($paymentData['RESPMSG']);
							}
							$redirectUrl = $this->_url->getUrl('dckapcheckout/index/index');
							$redirectUrl .= '?data=' . $invoiceNumber;
							return $resultRedirect->setPath($redirectUrl);
						} else {
							$responseAmount = $paymentData['AMT'];
							if( $amount  > $responseAmount){
								$logger->info("Amount is greater than response amount.");
							}

							$data['cc_amount_approved'] = (string) $paymentData['AMT'];
							$ccType = '';
							if ($paymentData['CARDTYPE']=='0') {
								$ccType = '4';
							} elseif ($paymentData['CARDTYPE']=='1') {
								$ccType = '3';
							} elseif ($paymentData['CARDTYPE']=='2') {
								$ccType = '1';
							} elseif ($paymentData['CARDTYPE']=='3') {
								$ccType = '3';
							}
							$data['cc_type'] = $ccType;
							$data['cc_number'] =  $paymentData['ACCT'];
							$data['cc_holder'] = $customerName;
							$data['cc_token'] = $paymentData['PNREF'];
							$logger->info("Request Data For Invoice Submit");
							$logger->info(print_r($data, true));

							$erpResponseData = $this->submitPayInvoice($data);
							//$erpResponseData=[];
							$logger->info("invoice ERP response");
							$logger->info(print_r($erpResponseData, true));

							if (isset($erpResponseData['data']['isValid']) && $erpResponseData['data']['isValid']=='yes') {
								$responseData['status'] = 'success';
								$responseData['msg'] = 'Payment submitted successfully';
							} else {
								$responseData['status'] = 'failure';
                                $errorMsg = is_string($erpResponseData)?$erpResponseData:'Something went wrong';
                                $responseData['msg'] = isset($erpResponseData['data']['errorMessage'])?$erpResponseData['data']['errorMessage']:$errorMsg;
							}
						}
					}
				} else {
					$responseData['status'] = 'failure';
					$responseData['msg'] = 'Invalid invoice details';
				}

			} catch (\Exception $e) {
				$responseData['status'] = 'failure';
				$responseData['msg'] = 'Invalid invoice details';
			}

			if ($responseData['status']=='success') {
				$this->messageManager->addSuccessMessage($responseData['msg']);
			} elseif ($responseData['status']=='failure') {
				$this->messageManager->addErrorMessage($responseData['msg']);
			} else {
				$this->messageManager->addErrorMessage(__("Something went wrong. Try again later"));
			}
			$redirectUrl = $this->_url->getUrl('quickrfq/invoice/summary');
			return $resultRedirect->setPath($redirectUrl);
		}

		protected function submitPayInvoice($data) {
			list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('pay_invoice');
			if ($status) {
				$responseData = $this->clorasDDIHelper->submitPayment($integrationData, $data);
				if ($responseData && count($responseData)) {
					return $responseData;
				}
			}
			return false;
		}

		public function parse_payflow_string($str) {
			$workstr = $str;
			$out = array();

			while (strlen($workstr) > 0) {
				$loc = strpos($workstr, '=');
				if ($loc===FALSE) {
					// Truncate the rest of the string, it's not valid
					$workstr = "";
					continue;
				}

				$substr = substr($workstr, 0, $loc);
				$workstr = substr($workstr, $loc + 1); // "+1" because we need to get rid of the "="

				if (preg_match('/^(\w+)\[(\d+)]$/', $substr, $matches)) {
					// This one has a length tag with it.  Read the number of characters
					// specified by $matches[2].
					$count = intval($matches[2]);

					$out[$matches[1]] = substr($workstr, 0, $count);
					$workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
				} else {
					// Read up to the next "&"
					$count = strpos($workstr, '&');
					if ($count===FALSE) { // No more "&"'s, read up to the end of the string
						$out[$substr] = $workstr;
						$workstr = "";
					} else {
						$out[$substr] = substr($workstr, 0, $count);
						$workstr = substr($workstr, $count + 1); // "+1" because we need to get rid of the "&"
					}
				}
			}

			return $out;
		}



	}
