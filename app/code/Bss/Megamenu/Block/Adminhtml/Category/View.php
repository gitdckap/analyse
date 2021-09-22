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
namespace Bss\Megamenu\Block\Adminhtml\Category;

class View extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Edit constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize product Content  edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'category_store_id';
        $this->_blockGroup = 'Bss_Megamenu';
        $this->_controller = 'adminhtml_category';
        parent::_construct();
        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
        $this->buttonList->update('back', 'label', __('Close this'));
        $this->buttonList->add(
            'delete_menu',
            [
                'label' => __('Delete'),
                'onclick' => 'window.location.href=\'' . $this->getDeleteUrl() . '\'',
                'class' => 'delete'
            ]
        );
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $blogComment = $this->coreRegistry->registry('current_megamenu_store');
        if ($blogComment->getId()) {
            return __("Edit Root category #%1", $blogComment->getName());
        }
        return __('New root menu');
    }

    public function getDeleteUrl()
    {
        return $this->getUrl(
            'megamenu/category/delete',
            ['id' => $this->getRequest()->getParam('id')]
        );
    }
}
