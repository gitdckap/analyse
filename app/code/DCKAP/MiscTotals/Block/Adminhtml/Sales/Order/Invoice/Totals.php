<?php
/**
 * @author     DCKAP
 * @package    DCKAP_MiscTotals
 * @copyright  Copyright (c) 2020 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\MiscTotals\Block\Adminhtml\Sales\Order\Invoice;

use Magento\Framework\DataObject;;

/**
 * Class Totals
 * @package DCKAP\MiscTotals\Block\Adminhtml\Sales\Order\Invoice
 */
class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \DCKAP\MiscTotals\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Invoice|null
     */
    protected $_invoice = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;
    /**
     * @var DataObject
     */
    protected $dataObj;

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
        $this->dataObj = $dataObject;
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
    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
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
