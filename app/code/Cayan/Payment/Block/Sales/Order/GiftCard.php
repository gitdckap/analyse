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

namespace Cayan\Payment\Block\Sales\Order;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObject;
use Magento\Quote\Model\QuoteRepository;
use Cayan\Payment\Model\CodeFactory;
use Cayan\Payment\Model\CodeHistoryFactory;
use Cayan\Payment\Helper\Data as GeneralHelper;

/**
 * Gift Card Order Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class GiftCard extends Template
{
    /**
     * @var \Cayan\Payment\Model\CodeHistoryFactory
     */
    protected $codeHistoryFactory;
    /**
     * @var \Cayan\Payment\Model\CodeFactory
     */
    protected $codeFactory;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    protected $generalHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Cayan\Payment\Model\CodeFactory $codeFactory
     * @param \Cayan\Payment\Model\CodeHistoryFactory $codeHistoryFactory
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     * @param \Cayan\Payment\Helper\Data $generalHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CodeFactory $codeFactory,
        CodeHistoryFactory $codeHistoryFactory,
        QuoteRepository $quoteRepository,
        GeneralHelper $generalHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_isScopePrivate = true;
        $this->codeHistoryFactory = $codeHistoryFactory;
        $this->codeFactory = $codeFactory;
        $this->quoteRepository = $quoteRepository;
        $this->generalHelper = $generalHelper;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Retrieve the applied gift card amount
     *
     * @return float
     */
    public function getGiftCardAmount()
    {
        try {
            $quoteId = $this->getOrder()->getQuoteId();
            $quote = $this->quoteRepository->get($quoteId);

            return !is_null($quote->getCayanGiftcardAmount()) ? $quote->getCayanGiftcardAmount() * -1 : 0;
        } catch (\Exception $ex) {
        }

        return 0;
    }

    /**
     * Initialize Cayan gift card order total
     *
     * @return $this
     */
    public function initTotals()
    {
        if ($this->getGiftCardAmount() > 0) {
            $total = new DataObject(
                [
                    'code' => 'cayancard',
                    'label' => __('Gift Card %1', $this->getCodesLabel()),
                    'value' => $this->getGiftCardAmount(),
                    'base_value' => $this->getGiftCardAmount()
                ]
            );

            $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        }

        return $this;
    }

    /**
     * Retrieve the label properties
     *
     * @return string
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Retrieve the value properties
     *
     * @return string
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Retrieve all gift card code labels
     *
     * @return string
     */
    protected function getCodesLabel()
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
