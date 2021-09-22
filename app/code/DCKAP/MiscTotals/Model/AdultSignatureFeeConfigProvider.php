<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class AdultSignatureFeeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \DCKAP\MiscTotals\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    protected $checkoutSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \DCKAP\MiscTotals\Helper\Data $dataHelper
     * @param \Magento\Checkout\Model\SessionFactory $checkoutSession
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \DCKAP\MiscTotals\Helper\Data $dataHelper,
        \Magento\Checkout\Model\SessionFactory $checkoutSession,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dataHelper = $dataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        $ExtrafeeConfig = [];
        $enabled = $this->dataHelper->isModuleEnabled();
        $ExtrafeeConfig['adult_signature_fee_label'] = $this->dataHelper->getAdultSignatureFeeLabel();
        $quote = $this->checkoutSession->create()->getQuote();
        $ExtrafeeConfig['adult_signature_fee_amount'] = ($quote->getAdultSignatureFee()) ? $quote->getAdultSignatureFee() : null;
        $ExtrafeeConfig['show_hide_adult_signature_fee_block'] = ($enabled && $quote->getAdultSignatureFee()) ? true : false;
        $ExtrafeeConfig['show_hide_adult_signature_fee_shipblock'] = ($enabled) ? true : false;
        return $ExtrafeeConfig;
    }
}
