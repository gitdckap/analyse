<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Block\Adminhtml\Sales;

use Akeneo\Connector\Helper\Data;
use Magento\Framework\DataObject;

/**
 * Class Totals
 * @package DCKAP\MiscTotals\Block\Adminhtml\Sales
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \DCKAP\MiscTotals\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;
    /**
     * @var DataObject
     */
    protected $dataObj;

    /**
     * Totals constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \DCKAP\MiscTotals\Helper\Data $dataHelper
     * @param \Magento\Directory\Model\Currency $currency
     * @param DataObject $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \DCKAP\MiscTotals\Helper\Data $dataHelper,
        \Magento\Directory\Model\Currency $currency,
        DataObject $dataObject,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->_currency = $currency;
        $this->dataObj = $dataObject;
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
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();

        if ((!$this->getSource()->getAdultSignatureFee()) ||
            ($this->getSource()->getAdultSignatureFee() == 0)
        ) {
            return $this;
        }

        $total = $this->dataObj->setData(
            [
                'code' => 'adult_signature_fee',
                'value' => $this->getSource()->getAdultSignatureFee(),
                'label' => $this->_dataHelper->getAdultSignatureFeeLabel(),
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
