<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Block\Adminhtml\Sales\Order\Creditmemo;

use Magento\Framework\DataObject;

/**
 * Class Totals
 * @package DCKAP\MiscTotals\Block\Adminhtml\Sales\Order\Creditmemo
 */
class Totals extends \Magento\Framework\View\Element\Template
{
    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Creditmemo|null
     */
    protected $_creditmemo = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @var \DCKAP\MiscTotals\Helper\Data
     */
    protected $_dataHelper;
    /**
     * @var DataObject
     */
    protected $dataobj;

    /**
     * Totals constructor.
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
        $this->dataobj = $dataObject;
        parent::__construct($context, $data);
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return mixed
     */
    public function getCreditmemo()
    {
        return $this->getParentBlock()->getCreditmemo();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getCreditmemo();
        $this->getSource();

        if ((!$this->getSource()->getAdultSignatureFee()) ||
            ($this->getSource()->getAdultSignatureFee() == 0)
        ) {
            return $this;
        }

        $fee = $this->dataobj->setData(
            [
                'code' => 'adult_signature_fee',
                'strong' => false,
                'value' => $this->getSource()->getAdultSignatureFee(),
                'label' => $this->_dataHelper->getAdultSignatureFeeLabel(),
            ]
        );

        $this->getParentBlock()->addTotalBefore($fee, 'grand_total');

        return $this;
    }
}
