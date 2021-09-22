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
 * @package    Bss_MegaMenu
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Megamenu\Block\Adminhtml\Category\Edit\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;

class General extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface
{
    /**
     *
     * @var \Bss\Megamenu\Model\Source\Stores
     */
    protected $storeGroups;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Bss\Megamenu\Model\Source\Stores $storeGroups
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Bss\Megamenu\Model\Source\Stores $storeGroups,
        $data = []
    ) {
        $this->storeGroups = $storeGroups;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        $megamenuStore = $this->_coreRegistry->registry('current_megamenu_store');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('megamenu_store_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class'  => 'fieldset-wide'
            ]
        );

        if ($megamenuStore && $megamenuStore->getId()) {
            $fieldset->addField(
                'category_store_id',
                'hidden',
                ['name' => 'category_store_id']
            );
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Root Menu Name'),
                'title' => __('Root Menu Name'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'store_id',
            'select',
            [
                'name'  => 'store_id',
                'label' => __('Choose store'),
                'title' => __('Choose store'),
                'values' => $this->storeGroups->toOptionArray(),
                'required' => true,
            ]
        );

        $megamenuStoreData = $this->_session->getData('bss_megamenu_store_data', true);
        if ($megamenuStoreData) {
            $megamenuStore->addData($megamenuStoreData);
        }

        if ($megamenuStore && $megamenuStore->getData()) {
            $form->addValues($megamenuStore->getData());
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('General');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
