<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Block\Adminhtml\Order\Item;

use Cayan\Payment\Block\Adminhtml\Order\Totals\GiftDiscount;
use Cayan\Payment\Model\CodeFactory;
use Cayan\Payment\Model\CodeHistoryFactory;
use Cayan\Payment\Helper\Data as GeneralHelper;
use Magento\Sales\Block\Adminhtml\Order\Totals\Item;
use Magento\Sales\Helper\Admin;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Order Item Discount Admin Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class Discount extends Item
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;
    /**
     * @var \Cayan\Payment\Block\Adminhtml\Order\Totals\GiftDiscount
     */
    private $giftDiscountBlock;
    /**
     * @var \Cayan\Payment\Model\CodeHistoryFactory
     */
    private $codeHistoryFactory;
    /**
     * @var \Cayan\Payment\Model\CodeFactory
     */
    private $codeFactory;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $generalHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Sales\Helper\Admin $adminHelper
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Cayan\Payment\Block\Adminhtml\Order\Totals\GiftDiscount $giftDiscount
     * @param \Cayan\Payment\Model\CodeHistoryFactory $codeHistoryFactory
     * @param \Cayan\Payment\Model\CodeFactory $codeFactory
     * @param \Cayan\Payment\Helper\Data $generalHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        QuoteRepository $quoteRepository,
        PriceHelper $priceHelper,
        GiftDiscount $giftDiscount,
        CodeHistoryFactory $codeHistoryFactory,
        CodeFactory $codeFactory,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);

        $this->quoteRepository = $quoteRepository;
        $this->priceHelper = $priceHelper;
        $this->giftDiscountBlock = $giftDiscount;
        $this->codeFactory = $codeFactory;
        $this->codeHistoryFactory = $codeHistoryFactory;
        $this->generalHelper = $generalHelper;
    }

    /**
     * Retrieve applied Cayan Gift Card amount
     *
     * @return float
     */
    public function getGiftCardAmount()
    {
        $quote = $this->quoteRepository->get($this->getOrder()->getQuoteId());

        return !is_null($quote->getCayanGiftcardAmount()) ? (float)$quote->getCayanGiftcardAmount() * -1 : 0;
    }

    /**
     * Retrieve formatted applied Cayan Gift Card amount
     *
     * @return float
     */
    public function getGiftCardAmountFormatted()
    {
        return $this->priceHelper->currency($this->getGiftCardAmount());
    }

    /**
     * Retrieve all gift card code labels
     *
     * @return string
     */
    public function getCodesLabel()
    {
        $label = '';
        /** @var \Cayan\Payment\Model\ResourceModel\CodeHistory\Collection $codeHistoryCollection */
        $codeHistoryCollection = $this->codeHistoryFactory->create()->getCollection()
            ->addFieldToFilter('order_id_fk', $this->getOrder()->getId())
            ->addFieldToSelect('code_id_fk');
        $codeIds = [];

        foreach ($codeHistoryCollection as $codeHistory) {
            $codeIds[] = $codeHistory->getCodeIdFk();
        }

        /** @var \Cayan\Payment\Model\ResourceModel\Code\Collection $codeCollection */
        $codeCollection = $this->codeFactory->create()->getCollection()
            ->addFieldToFilter('code_id', ['in' => $codeIds])
            ->addFieldToSelect('code');

        if ($codeCollection->count() > 0) {
            $label = '(';

            foreach ($codeCollection as $code) {
                if ($label === '(') {
                    $label = $label . $this->generalHelper->maskGiftCard($code->getCode());
                } else {
                    $label .= ',' . $this->generalHelper->maskGiftCard($code->getCode());
                }
            }

            $label = $label . ')';
        }

        return $label;
    }
}
