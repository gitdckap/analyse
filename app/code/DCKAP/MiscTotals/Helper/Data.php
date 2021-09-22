<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * Custom fee config path
     */
    const CONFIG_CUSTOM_IS_ENABLED = 'dckapextension/general/status';
    const CONFIG_FEE_LABEL = 'dckapextension/general/display_text';
    protected $QuoteFactory;
    protected $clorasDDIHelper;
    protected $orderApprovalHelper;

    public function __construct(
        Context $context,
        \Cloras\DDI\Helper\Data $clorasDDIHelper,
        \Magento\Quote\Model\QuoteFactory $QuoteFactory,
        \DCKAP\OrderApproval\Helper\Data $orderApprovalHelper
    ) {
        $this->clorasDDIHelper = $clorasDDIHelper;
        $this->QuoteFactory = $QuoteFactory;
        $this->orderApprovalHelper = $orderApprovalHelper;
         parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isModuleEnabled()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return  $this->scopeConfig->getValue(self::CONFIG_CUSTOM_IS_ENABLED, $storeScope);
    }

    /**
     * Get custom fee label
     *
     * @return mixed
     */
    public function getAdultSignatureFeeLabel()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue(self::CONFIG_FEE_LABEL, $storeScope);
    }

    public function getAdultSignatureFee($quote_id)
    {
        $miscamt = 0;
        $quoteitems = $this->QuoteFactory->create()->load($quote_id);
        $params['m_quote_items'] = $quoteitems->getAllVisibleItems();
        $params ['review_type'] = "checkout_review";
        $params ['po_number'] = "123";
        $miscamt = $this->callReview($params, 'checkout_review');
        return $miscamt;
    }

    public function callReview($params, $api)
    {
        list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled($api);
        if (!$status) {
            list($status, $integrationData) = $this->clorasDDIHelper->isServiceEnabled('submit_order');
        }
        if ($status) {
            $checkoutReview = $this->clorasDDIHelper->checkoutReview($integrationData, $params);
            $miscamt = 0;
            if ($checkoutReview['data']['isValid'] == 'yes') {
                if (isset($checkoutReview['data']['orderDetails']['miscellaneousTotal'])) {
                    $miscamt = $checkoutReview['data']['orderDetails']['miscellaneousTotal'];
                    $miscamt = (float)(str_replace('$', '', str_replace(',', '', $miscamt)));
                }
            }
            return $miscamt;
        }
        return 0;
    }
}
