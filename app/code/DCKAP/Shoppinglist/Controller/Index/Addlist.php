<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Controller\Index;

class Addlist extends \Magento\Framework\App\Action\Action
{

    /**
     * @var  \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    
    /**
     * @var  \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var  \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var  \DCKAP\Shoppinglist\Model\ShoppinglistFactory
     */
    protected $shoppinglistFactory;
    
    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var  \Magento\Framework\UrlInterface
     */
    protected $url;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory
    ) {

        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->shoppinglistFactory = $shoppinglistFactory;
        $this->messageManager = $context->getMessageManager();
        $this->url = $context->getUrl();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $customerSession = $this->customerSession->create();

        if ($customerSession->isLoggedIn()) {
            $customerSession->setShoppinglistId('');

            $shoppingListName = $this->getRequest()->getParam('shopping_list_name');
            if (trim($shoppingListName) != '') {
                $customerId = $customerSession->getId();
                $storeId = $this->storeManager->getStore()->getId();

                $shoppinglist = $this->shoppinglistFactory->create();
                $shoppinglistCollection = $shoppinglist->getCollection()
                                        ->addFieldToFilter('list_name', ['eq' => $shoppingListName])
                                        ->addFieldToFilter('customer_id', ['eq' => $customerId])
                                        ->addFieldToFilter('store_id', ['eq' => $storeId]);

                if ($shoppinglistCollection->getSize()) {
                    $this->messageManager->addError(sprintf('%s list name already exist.', $shoppingListName));
                } else {
                    try {
                        $shoppinglist = $this->shoppinglistFactory->create();
                        $shoppinglist
                            ->setData('list_name', $shoppingListName)
                            ->setData('customer_id', $customerId)
                            ->setData('store_id', $storeId)
                            ->save();

                        $this->messageManager->addSuccess(__('Shopping list created successfully.'));
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('Error occurred while adding list.'));
                    }
                }
            } else {
                $this->messageManager->addError(__('Error occurred while adding list.'));
            }
            

            $resultRedirect->setPath($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }

        $resultRedirect->setPath('customer/account/login/');

        $customerSession->setAfterAuthUrl($this->url->getCurrentUrl());
        $customerSession->authenticate();
        
        return $resultRedirect;
    }
}
