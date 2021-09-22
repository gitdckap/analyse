<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Block\Sales\Totals;

use Magento\Framework\DataObject;

/**
 * Class AdultSignatureFee
 * @package DCKAP\MiscTotals\Block\Sales\Totals
 */
class AdultSignatureFee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \DCKAP\MiscTotals\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;
    /**
     * @var DataObject
     */
    protected $dataObj;

    /**
     * AdultSignatureFee constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \DCKAP\MiscTotals\Helper\Data $dataHelper
     * @param DataObject $dataObject
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \DCKAP\MiscTotals\Helper\Data $dataHelper,
        DataObject $dataObject,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        $this->dataObj = $dataObject;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @return mixed
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();

        if (!$this->_source->getAdultSignatureFee()) {
            return $this;
        }
        $extrafee = (float)$this->_source->getAdultSignatureFee();
        if ($extrafee == 0) {
            return $this;
        }
        $adultSignatureFee = $this->dataObj->setData(
            [
                'code' => 'adult_signature_fee',
                'strong' => false,
                'value' => $this->_source->getAdultSignatureFee(),
                'label' => $this->_dataHelper->getAdultSignatureFeeLabel(),
            ]
        );
        $parent->addTotal($adultSignatureFee, 'adult_signature_fee');
        return $this;
    }
}
