<?php
/**
 * Copyright © DCKAP Inc. All rights reserved.
 */
namespace Dckap\Checkout\Controller\Ajax;

/**
 * Class Index
 * @package Dckap\Checkout\Controller\Ajax
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Cloras\Base\Helper\Data
     */
    protected $clorasHelper;

    /**
     * @var \Cloras\DDI\Helper\Data
     */
    protected $clorasDDIHelper;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quote;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $_checkoutSession;

    /**
     * Index constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Cloras\Base\Helper\Data $clorasHelper
     * @param \Cloras\DDI\Helper\Data $clorasDDIHelper
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\SessionFactory $_checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\SessionFactory $_checkoutSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->quote = $quote;
        $this->customerSession = $customerSession;
        $this->_checkoutSession = $_checkoutSession;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|string
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = [];
        try {
            $params = $this->getRequest()->getParams();

            $quote = $this->quote->create()->loadByIdWithoutStore($params['quote_id']);
            $params['m_quote_items'] = $quote->getAllItems();

            if ($params['review_type'] == 'checkout_review') {
                $data['response'] = $this->callReview($params, 'checkout_review');
            } else {
                $data['response'] = $this->callReview($params, 'review_quote');
            }

            $data['status'] = 'SUCCESS';
            $data['data'] = $params;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $resultJson->setData($data);
    }

    /**
     * @param $params
     * @param $api
     * @return bool|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function callReview($params, $api)
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled($api);
        if (!$status) {
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_order');
        }
        if ($status) {
            $checkoutReview = $this->clorasDDIHelper->checkoutReview($integrationData, $params);

            /**
             * Code to set tax amount into quote item
             *
             * 1.Set tax amount in session
             * 2.In total calculation this taxAmount will be added in total
             * 3.After added to total particular session data cleared
             *
             * To refer -> app/code/Dckap/Checkout/Model/Total/Taxfee.php
             */
            if ($checkoutReview['data']['isValid'] == 'yes') {
                if (isset($checkoutReview['data']['orderDetails']['taxTotal'])) {
                    $checkoutSession = $this->_checkoutSession->create();
                    $quote = $checkoutSession->getQuote();
                    $taxAmount = $checkoutReview['data']['orderDetails']['taxTotal'];
                    $taxAmount = (float)(str_replace('$', '', str_replace(',', '', $taxAmount)));
                    $totalAmount = $checkoutReview['data']['orderDetails']['orderTotal'];
                    $totalAmount = (float)(str_replace('$', '', str_replace(',', '', $totalAmount)));
                    $dat = [
                        $quote->getId() => $taxAmount,
                        "order_total" => $totalAmount
                    ];
                    $checkoutSession->setCheckoutData($dat);
                }
            }
            return $checkoutReview;
        }
        return false;
    }
}
