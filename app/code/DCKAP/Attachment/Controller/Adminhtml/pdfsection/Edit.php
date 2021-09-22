<?php

namespace Dckap\Attachment\Controller\Adminhtml\pdfsection;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $pdfSection;
    protected $session;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Dckap\Attachment\Model\Pdfsection $pdfSection,
        \Magento\Backend\Model\Session $session
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->pdfSection = $pdfSection;
        $this->session = $session;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return true;
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Dckap_Attachment::Pdfsection')
            ->addBreadcrumb(__('Dckap Attachment'), __('Dckap Attachment'))
            ->addBreadcrumb(__('Manage Item'), __('Manage Item'));
        return $resultPage;
    }

    /**
     * Edit Item
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');

        // 2. Initial checking
        if ($id) {
            $this->pdfSection->load($id);
            if (!$this->pdfSection->getId()) {
                $this->messageManager->addError(__('This item no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // 3. Set entered data if was error when we do save
        $data = $this->session->getFormData(true);
        if (!empty($data)) {
            $this->pdfSection->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('pdfsection', $this->pdfSection);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->setActiveMenu('Dckap_Attachment::pdfsection');
        $resultPage->addBreadcrumb(__('Dckap'), __('Dckap'));
        $resultPage->addBreadcrumb(
            $id ? __('Edit Section') : __('New Section'),
            $id ? __('Edit Section') : __('New Section')
        );
        $resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Section') : __('New Section'));

        return $resultPage;
    }
}
