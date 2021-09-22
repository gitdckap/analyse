<?php

namespace Dckap\Checkout\Controller\Ajax;

class Update extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $clorasHelper;
    protected $clorasDDIHelper;
    protected $quote;
    protected $customerSession;
    protected $_checkoutSession;
    protected $quoteRepository;
    private $serializer;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cloras\Base\Helper\Data $clorasHelper,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Quote\Model\QuoteFactory $quote,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\SessionFactory $_checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->clorasHelper = $clorasHelper;
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->quote = $quote;
        $this->customerSession = $customerSession;
        $this->_checkoutSession = $_checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $data = [];
        try {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/checkoutReview_1.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            //get Params from Ajax call

            $params = $this->getRequest()->getParams();
            $quote = $this->quote->create()->loadByIdWithoutStore($params['quote_id']);
            $params['m_quote_items'] = $quote->getAllItems();
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_order');
            if ($status) {
                $checkoutReview = $this->clorasDDIHelper->UpdateErpPrice($integrationData, $params, $params['review_type']);
                if ($checkoutReview['data']['isValid']=='yes') {
                    $checkoutSession = $this->_checkoutSession->create();
                    $MiscAmt =0;
                    if (isset($checkoutReview['data']['orderDetails']['miscellaneousTotal'])) {
                        $miscamt = $checkoutReview['data']['orderDetails']['miscellaneousTotal'];
                        $MiscAmt = (float)(str_replace('$', '', str_replace(',', '', $miscamt)));
                    }
                    $checkoutSession->setMiscTotal($MiscAmt);
                    $lineItems = $checkoutReview['data']['lineItems']['lineData'];
                    $data['shipto'] = ' ';
                    foreach ($quote->getAllItems() as $quoteitem) {
                        $uom = 'EA';
                        if ($additionalOptions = $quoteitem->getOptionByCode('additional_options')) {
                            $additionalOption = (array) $this->serializer->unserialize($additionalOptions->getValue());
                            if (isset($additionalOption['custom_uom'])) {
                                $uom = $additionalOption['custom_uom']['value'];
                            }
                        }
                        $base_price = (float)$quoteitem->getPrice();
                        foreach ($lineItems as $key => $itemData) {
                            if ($quoteitem->getSku() == $itemData['stockNum'] && $uom == $itemData['uom']) {
                                $price = $itemData['netPrice'];
                                $price = str_replace("$", "", $price);
                                $price = (float) $price;
                                $custom_price = (float)$quoteitem->getCustomPrice();
                                if ($custom_price ==" " || $custom_price == null) {
                                    $custom_price = 0;
                                }
                                if ($base_price  < $price || $base_price > $price && $custom_price == 0) {
                                    $data['shipto'] = 'changed';
                                }
                                if ($custom_price  < $price || $custom_price  > $price && $custom_price != 0) {
                                    $data['shipto'] = 'changed';
                                }
                                $quoteitem->setCustomPrice($price);
                                $quoteitem->setOriginalCustomPrice($price);
                                $quoteitem->getProduct()->setIsSuperMode(true);
                                $quoteitem->save();
                            }
                        }
                    }
                }
                $data['response'] = $checkoutReview['data'];
            }

	        if(isset($params['ship_to']) && $params['ship_to']==1 ) {
		        if ($status) {
			        $checkoutReview = $this->clorasDDIHelper->UpdateErpPrice($integrationData, $params, $params['review_type']);
			        if ($checkoutReview['data']['isValid']=='yes') {
				        $checkoutSession = $this->_checkoutSession->create();
				        $MiscAmt = 0;
				        if (isset($checkoutReview['data']['orderDetails']['miscellaneousTotal'])) {
					        $miscamt = $checkoutReview['data']['orderDetails']['miscellaneousTotal'];
					        $MiscAmt = (float) (str_replace('$', '', str_replace(',', '', $miscamt)));
				        }
				        $checkoutSession->setMiscTotal($MiscAmt);
				        $lineItems = $checkoutReview['data']['lineItems']['lineData'];
				        $data['shipto'] = ' ';
				        foreach ($quote->getAllItems() as $quoteitem) {
                            if ($quoteitem->getParentItem() == null) {
                                $uom = 'EA';
                                if ($additionalOptions = $quoteitem->getOptionByCode('additional_options')) {
                                    $additionalOption = (array)$this->serializer->unserialize($additionalOptions->getValue());
                                    if (isset($additionalOption['custom_uom'])) {
                                        $uom = $additionalOption['custom_uom']['value'];
                                    }
                                }
                                $base_price = (float)$quoteitem->getPrice();
                                foreach ($lineItems as $key => $itemData) {
                                    if ($quoteitem->getSku() == $itemData['stockNum'] && $uom == $itemData['uom']) {
                                        $price = $itemData['netPrice'];
                                        $price = str_replace("$", "", $price);
                                        $price = (float)$price;
                                        $custom_price = (float)$quoteitem->getCustomPrice();
                                        $logger->info("prices");
                                        $logger->info(print_r($base_price, true));
                                        $logger->info(print_r($price, true));
                                        $logger->info(print_r($custom_price, true));
                                        if ($custom_price == " " || $custom_price == NULL) {
                                            $custom_price = 0;
                                        }
                                        $logger->info(print_r($custom_price, true));
                                        if ($base_price < $price || $base_price > $price && $custom_price == 0) {
                                            $data['shipto'] = 'changed';
                                        }
                                        if ($custom_price < $price || $custom_price > $price && $custom_price != 0) {
                                            $data['shipto'] = 'changed';
                                        }
                                        if ($price === $custom_price) {
                                            $data['shipto'] = '';
                                        }
                                        $quoteitem->setCustomPrice($price);
                                        $quoteitem->setOriginalCustomPrice($price);
                                        $quoteitem->getProduct()->setIsSuperMode(true);
                                        $quoteitem->save();
                                    }
                                }
                            }
				        }
			        }
			        $data['response'] = $checkoutReview['data'];
		        }
	        }
            $data['status'] = 'SUCCESS';
            $data['data'] = $params;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $resultJson->setData($data);
    }
}
