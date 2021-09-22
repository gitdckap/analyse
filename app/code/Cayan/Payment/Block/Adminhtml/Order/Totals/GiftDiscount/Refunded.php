<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2018 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Block\Adminhtml\Order\Totals\GiftDiscount;

use Cayan\Payment\Model\CodeFactory;
use Cayan\Payment\Model\CodeHistoryFactory;
use Cayan\Payment\Model\Helper\Discount as CardHelper;
use Cayan\Payment\Helper\Data as GeneralHelper;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Quote\Model\QuoteRepository;

/**
 * Gift Card Admin Order Total Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class Refunded extends Template
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepo;
    /**
     * @var \Cayan\Payment\Model\CodeHistoryFactory
     */
    private $codeHistoryFactory;
    /**
     * @var \Cayan\Payment\Model\CodeFactory
     */
    private $codeFactory;
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $cardHelper;
    /**
     * @var \Magento\Framework\DataObject
     */
    private $source;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $generalHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Cayan\Payment\Model\CodeHistoryFactory $codeHistoryFactory
     * @param \Cayan\Payment\Model\CodeFactory $codeFactory
     * @param \Cayan\Payment\Model\Helper\Discount $cardHelper
     * @param \Cayan\Payment\Helper\Data $generalHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        QuoteRepository $quoteRepository,
        CodeHistoryFactory $codeHistoryFactory,
        CodeFactory $codeFactory,
        CardHelper $cardHelper,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->quoteRepo = $quoteRepository;
        $this->codeHistoryFactory = $codeHistoryFactory;
        $this->codeFactory = $codeFactory;
        $this->cardHelper = $cardHelper;
        $this->generalHelper = $generalHelper;
    }

    /**
     * Check if it is necessary to display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Retrieve totals source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Retrieve store model instance
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->order->getStore();
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Retrieve label properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Retrieve value properties
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Check if gift card usage was refunded
     *
     * @return bool
     */
    public function isCardRefunded()
    {
        $refundedAmount = $this->getRefundedAmount();
        return ($refundedAmount > 0);
    }

    /**
     * Retrieve amount refunded in order
     *
     * @return mixed
     */
    public function getRefundedAmount()
    {
        $orderId = $this->order->getId();
        $usages = $this->codeHistoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id_fk', $orderId)
            ->addFieldToFilter('balance_used', ['lt' => 0]);
        $total = 0;

        foreach ($usages as $usage) {
            $total = $total + $usage->getBalanceUsed();
        }

        return $total * -1;
    }

    /**
     * Retrieve all gift card code labels
     *
     * @return string
     */
    public function getCodesLabel()
    {
        $orderId = $this->order->getId();
        $label = '';
        /** @var \Cayan\Payment\Model\ResourceModel\CodeHistory\Collection $codeHistoryCollection */
        $codeHistoryCollection = $this->codeHistoryFactory->create()->getCollection()
            ->addFieldToFilter('order_id_fk', $orderId)
            ->addFieldToFilter('balance_used', ['lt' => 0])
            ->addFieldToSelect('code_id_fk');
        $codeIds = [];

        foreach ($codeHistoryCollection as $codeHistory) {
            $codeIds[] = $codeHistory->getCodeIdFk();
        }

        /** @var \Cayan\Payment\Model\ResourceModel\Code\Collection $codeCollection */
        $codeCollection = $this->codeFactory->create()->getCollection()
            ->addFieldToFilter('code_id', ['in' => $codeIds])
            ->addFieldToSelect('code');

        foreach ($codeCollection as $code) {
            if ($label == '') {
                $label = $this->generalHelper->maskGiftCard($code->getCode());
            } else {
                $label .= ',' . $this->generalHelper->maskGiftCard($code->getCode());
            }
        }

        return $label;
    }

    /**
     * Initialize all order totals with tax
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();

        $this->order = $parent->getOrder();
        $this->source = $parent->getSource();

        if ($this->isCardRefunded()) {
            $cayancard = new DataObject(
                [
                    'code' => 'cayancard_refund',
                    'strong' => true,
                    'value' => $this->getRefundedAmount(),
                    'label' => __('Gift Card Refunded (%1)', $this->getCodesLabel())
                ]
            );

            $parent->addTotal($cayancard, 'cayancard_refund');
        }

        return $this;
    }
}
