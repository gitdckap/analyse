<?php

namespace Dckap\Attachment\Block\Adminhtml\Pdfattachment;

/**
 * Class Grid
 * @package Dckap\Attachment\Block\Adminhtml\Pdfattachment
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Dckap\Attachment\Model\pdfattachmentFactory
     */
    protected $_pdfattachmentFactory;

    /**
     * @var \Dckap\Attachment\Model\pdfsectionFactory
     */
    protected $_PdfsectionFactory;

    /**
     * @var \Dckap\Attachment\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Dckap\Attachment\Model\pdfattachmentFactory $pdfattachmentFactory
     * @param \Dckap\Attachment\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Dckap\Attachment\Model\PdfattachmentFactory $PdfattachmentFactory,
        \Dckap\Attachment\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        \Dckap\Attachment\Model\PdfsectionFactory $PdfsectionFactory,
        array $data = []
    ) {
        $this->_pdfattachmentFactory = $PdfattachmentFactory;
        $this->_PdfsectionFactory = $PdfsectionFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_pdfattachmentFactory->create()->getCollection();
        $this->setCollection($collection);

        parent::_prepareCollection();

        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'sku',
            [
                'header' => __('sku'),
                'index' => 'sku',
            ]
        );

        /*$this->addColumn(
            'section_id',
            [
                'header' => __('section_id'),
                'index' => 'section_id',
            ]
        );*/

        $this->addColumn(
            'section_id',
            [
                'header' => __('Section'),
                'index' => 'section_id',
                'type' => 'options',
                'options' => $this->getSectionOptionArray()
            ]
        );

        $this->addColumn(
            'attachment',
            [
                'header' => __('attachment'),
                'index' => 'attachment',
            ]
        );

        $this->addColumn(
            'file_type',
            [
                'header' => __('file_type'),
                'index' => 'file_type',
            ]
        );

        //$this->addColumn(
        //'edit',
        //[
        //'header' => __('Edit'),
        //'type' => 'action',
        //'getter' => 'getId',
        //'actions' => [
        //[
        //'caption' => __('Edit'),
        //'url' => [
        //'base' => '*/*/edit'
        //],
        //'field' => 'id'
        //]
        //],
        //'filter' => false,
        //'sortable' => false,
        //'index' => 'stores',
        //'header_css_class' => 'col-action',
        //'column_css_class' => 'col-action'
        //]
        //);

        $this->addExportType($this->getUrl('attachment/*/exportCsv', ['_current' => true]), __('CSV'));
        $this->addExportType($this->getUrl('attachment/*/exportExcel', ['_current' => true]), __('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('id');

        $this->getMassactionBlock()->setFormFieldName('pdfattachment');

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('attachment/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );

        $statuses = $this->_status->getOptionArray();

        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('attachment/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );

        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('attachment/*/index', ['_current' => true]);
    }

    /**
     * @param \Dckap\Attachment\Model\pdfattachment|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {

        return $this->getUrl(
            'attachment/*/edit',
            ['id' => $row->getId()]
        );
    }

    /**
     * @return array
     */
    public function getSectionOptionArray()
    {
        $data_array = [];
        $data_array[0] = '';
        $sectionCollection = $this->_PdfsectionFactory->create()->getCollection();
        if ($sectionCollection && !empty($sectionCollection->getData())) {
            foreach ($sectionCollection as $section) {
                $data_array[$section->getId()] = $section->getSectionName();
            }
        }
        return ($data_array);
    }

    /**
     * @return array
     */
    public function getSectionValueArray()
    {
        $data_array = [];
        foreach ($this->getSectionOptionArray() as $k => $v) {
            $data_array[] = ['value' => $k, 'label' => $v];
        }
        return ($data_array);
    }
}
