<?php

namespace Dckap\Attachment\Block\Adminhtml\Pdfattachment\Edit\Tab;

/**
 * Pdfattachment edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Dckap\Attachment\Model\Status
     */
    protected $_status;

    /**
     * @var \Dckap\Attachment\Block\Adminhtml\Pdfattachment\Grid
     */
    protected $_grid;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Dckap\Attachment\Model\Status $status,
        \Dckap\Attachment\Block\Adminhtml\Pdfattachment\Grid $grid,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_status = $status;
        $this->_grid = $grid;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Dckap\Attachment\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('pdfattachment');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Attachment Information')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'sku',
            'text',
            [
                'name' => 'sku',
                'label' => __('SKU'),
                'title' => __('SKU'),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'section_id',
            'select',
            [
                'label' => __('Section'),
                'title' => __('Section'),
                'name' => 'section_id',

                'options' => $this->_grid->getSectionOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );
                    
        /*$fieldset->addField(
            'section_id',
            'text',
            [
                'name' => 'section_id',
                'label' => __('Section'),
                'title' => __('Section'),

                'disabled' => $isElementDisabled
            ]
        );*/
                    
        $fieldset->addField(
            'attachment',
            'image',
            [
                'name' => 'attachment',
                'label' => __('Attachment'),
                'title' => __('Attachment'),
                
                'disabled' => $isElementDisabled
            ]
        );
                    
        /*$fieldset->addField(
            'file_type',
            'text',
            [
                'name' => 'file_type',
                'label' => __('File Type'),
                'title' => __('File Type'),
                'disabled' => $isElementDisabled
            ]
        );*/

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
        
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Attachment Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Attachment Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    public function getTargetOptionArray()
    {
        return [
            '_self' => "Self",
            '_blank' => "New Page"
        ];
    }
}
