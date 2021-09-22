<?php

namespace Dckap\Checkout\Controller\Index;

class Payinvoice extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $customerSession;
    protected $tansactionSaleHelper;
    protected $dckapCheckoutHelper;
    protected $clorasHelper;
    protected $clorasDDIHelper;
    protected $extensionHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Dckap\Checkout\Helper\TransactionSale $tansactionSaleHelper,
        \Dckap\Checkout\Helper\Data $dckapCheckoutHelper,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \DCKAP\Extension\Helper\Data $extensionHelper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerSession = $customerSession;
        $this->tansactionSaleHelper = $tansactionSaleHelper;
        $this->dckapCheckoutHelper = $dckapCheckoutHelper;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->extensionHelper = $extensionHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $responseData = $data = [];
        try {
            $params = $this->getRequest()->getParams();
            if (isset($params['order_numbers']) && $params['order_numbers'] != '') {
                $customerId = $this->customerSession->getCustomerId();
                $invoice = $this->dckapCheckoutHelper->getInvoices(['data' => $params['order_numbers']]);

                $PaymentData = [
                    "Source" => "Vault",
                    "VaultToken" => $params['token'],
                ];
                $request = [
                    "Amount" => $params['amount'],
                    "TaxAmount" => "0",
                    "InvoiceNumber" => explode('__', $params['order_numbers'])[0],
                    "CustomerCode" => $customerId,
                    "MerchantTransactionId" => "99"
                ];
                $options = [
                    "storeInVaultOnSuccess" => true
                ];
                $requestData = [];
                $requestData['PaymentData'] = $PaymentData;
                $requestData['Request'] = $request;
                $requestData['options'] = $options;
                $processPayment = $this->tansactionSaleHelper->placeRequest($requestData);
                if ($processPayment && !empty($processPayment)) {
                    $data['requestData'] = $requestData;
                    $data['requestData']['invoice'] = $invoice;
                    $data['responseData'] = $processPayment;
                    $data['responseData']['cc_type'] = $params['cayancc_cc_type'];
                    $data['responseData']['cc_holder'] = $params['cayancc_cc_holder'];
                    $erpResponseData = $this->submitPayInvoice($data);
                    if (isset($erpResponseData['data']['isValid']) && $erpResponseData['data']['isValid'] == 'yes') {
                        $responseData['status'] = 'success';
                        $responseData['msg'] = 'Payment submitted successfully';
                    } else {
                        $responseData['status'] = 'failure';
                        $errorMsg = is_string($erpResponseData)?$erpResponseData:'Something went wrong';
                        $responseData['msg'] = isset($erpResponseData['data']['errorMessage'])?$erpResponseData['data']['errorMessage']:$errorMsg;
                    }
                }
            } else {
                $responseData['status'] = 'failure';
                $responseData['msg'] = 'Invalid invoice details';
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $resultJson->setData($responseData);
    }

    protected function submitPayInvoice($data)
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('pay_invoice');
        if ($status) {
            $responseData = $this->clorasDDIHelper->submitPayment($integrationData, $data);
            if ($responseData && !empty($responseData)) {
                return $responseData;
            }
        }
        return false;
    }
}
