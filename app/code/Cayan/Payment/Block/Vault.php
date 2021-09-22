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

namespace Cayan\Payment\Block;

use Cayan\Payment\Helper\Data as CayanHelper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Vault Block
 *
 * @package Cayan\Payment\Block
 * @author Igor Miura
 */
class Vault extends Template
{
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CayanHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CayanHelper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
    }

    /**
     * Return flag image url by type.
     * @param int $type
     * @return mixed
     */
    public function getFlagImage($type)
    {
        $flagImages = $this->helper->getFlagImages();

        return $flagImages[$type];
    }
}
