<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_CheckoutCustomField
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\CheckoutCustomField\Model\Plugin\Email;

class BackendTemplate
{
    /**
     * @var \Bss\CheckoutCustomField\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $dataObject;

    /**
     * BackendTemplate constructor.
     * @param \Bss\CheckoutCustomField\Helper\Data $helper
     * @param \Magento\Framework\DataObject $dataObject
     */
    public function __construct(
        \Bss\CheckoutCustomField\Helper\Data $helper,
        \Magento\Framework\DataObject $dataObject
    ) {
        $this->helper = $helper;
        $this->dataObject = $dataObject;
    }

    /**
     * @param \Magento\Email\Model\Template $subject
     * @param callable $proceed
     * @param bool $withGroup
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return array
     */
    public function aroundGetVariablesOptionArray(
        \Magento\Email\Model\Template $subject,
        callable $proceed,
        $withGroup = false
    ) {
        if ($this->helper->moduleEnabled()) {
            $optionArray = [];
            $variables = $this->helper->checkSerialize($this->dataObject->getData('orig_template_variables'));
            $variables['var bss_custom_field'] = 'Checkout CustomField';
            if ($variables) {
                foreach ($variables as $value => $label) {
                    $optionArray[] = ['value' => '{{' . $value . '|raw}}', 'label' => __('%1', $label)];
                }
                if ($withGroup) {
                    $optionArray = ['label' => __('Template Variables'), 'value' => $optionArray];
                }
            }
            return $optionArray;
        }
        return $proceed();
    }
}
