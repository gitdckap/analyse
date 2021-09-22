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

namespace Cayan\Payment\Block\Adminhtml\Order\Totals;

use Cayan\Payment\Model\CodeFactory;
use Cayan\Payment\Model\CodeHistoryFactory;
use Cayan\Payment\Model\Helper\Discount as CardHelper;
use Cayan\Payment\Helper\Data as GeneralHelper;
use Magento\Framework\DataObject;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\QuoteRepository;
use Magento\Tax\Model\Config;

/**
 * Gift Card Order Total Admin Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class GiftDiscount extends Template
{
    const CAYANCARD_CODE = 'cayancard';

    /**
     * @var \Magento\Tax\Model\Config
     */
    private $taxConfig;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Magento\Framework\DataObject
     */
    private $source;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
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
    private $helper;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $generalHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Cayan\Payment\Model\CodeHistoryFactory $codeHistoryFactory
     * @param \Cayan\Payment\Model\CodeFactory $codeFactory
     * @param \Cayan\Payment\Model\Helper\Discount $cardHelper
     * @param \Cayan\Payment\Helper\Data $generalHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $taxConfig,
        QuoteRepository $quoteRepository,
        CodeHistoryFactory $codeHistoryFactory,
        CodeFactory $codeFactory,
        CardHelper $cardHelper,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->taxConfig = $taxConfig;
        $this->quoteRepository = $quoteRepository;
        $this->codeHistoryFactory = $codeHistoryFactory;
        $this->codeFactory = $codeFactory;
        $this->helper = $cardHelper;
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
     * Get totals source model
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
     * Retrieve gift card amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->helper->getGiftcardTotalInOrder($this->getOrder()->getId());
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

        if ($this->getDiscountAmount() > 0) {
            $cayancard = new DataObject(
                [
                    'code' => self::CAYANCARD_CODE,
                    'strong' => true,
                    'value' => $this->getDiscountAmount() * -1,
                    'label' => __('Gift Card %1', $this->getCodesLabel())
                ]
            );

            $parent->addTotal($cayancard, self::CAYANCARD_CODE);
        }

        return $this;
    }
}
