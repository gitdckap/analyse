<?php
/**
 * @author     DCKAP <extensions@dckap.com>
 * @package    DCKAP_Shoppinglist
 * @copyright  Copyright (c) 2016 DCKAP Inc (http://www.dckap.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace DCKAP\Shoppinglist\Controller\Index;

class Deletelistitem extends \Magento\Framework\App\Action\Action
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
     * @var DCKAP\Shoppinglist\Model\ShoppinglistFactory
     */
    protected $shoppinglistFactory;

    /**
     * @var DCKAP\Shoppinglist\Model\ProductlistFactory
     */
    protected $productlistFactory;

    protected $listItemId;

    /**
     * @var  \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory
     * @param \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \DCKAP\Shoppinglist\Model\ShoppinglistFactory $shoppinglistFactory,
        \DCKAP\Shoppinglist\Helper\Data $listItemId,
        \DCKAP\Shoppinglist\Model\ProductlistFactory $productlistFactory
    ) {

        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->customerSession = $customerSession;
        $this->shoppinglistFactory = $shoppinglistFactory;
        $this->productlistFactory = $productlistFactory;
        $this->listItemId = $listItemId;
        $this->url = $context->getUrl();
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $customerSession = $this->customerSession->create();
        if ($customerSession->isLoggedIn()) {
            $shoppingListId = $this->getRequest()->getParam('shoppinglist_id');
            if ($shoppingListId) {
                $productlistModel = $this->productlistFactory->create();
                $productlistModelCollection = $productlistModel->getCollection()
                                                ->addFieldToSelect(['shopping_list_item_id'])
                                                ->addFieldToFilter('shopping_list_id', $shoppingListId);
                $collection = $productlistModelCollection->getData();
                $shoppingListItems = $this->productlistFactory->create()->getCollection();
                $shoppingListItem = $this->productlistFactory->create()->getCollection();

                foreach ($shoppingListItem as $listItem) {
                    if ($listItem->getShippingListId() == $shoppingListId) {
                        $this->listItemId->deleteListItems($listItem);
                    }
                }
                 
                $shoppingList = $this->shoppinglistFactory->create()->load($shoppingListId);
                try {
                    $shoppingList->delete();

                    $customerSession->setShoppinglistId('');
                    $this->messageManager->addSuccess(__('Shopping list deleted successfully.'));
                } catch (\Exception $e) {
                    $this->messageManager->addSuccess(__('Something went wrong.'));
                }
            }

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('shoppinglist/index/index/');
            return $resultRedirect;
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('customer/account/login/');

        $customerSession->setAfterAuthUrl($this->url->getCurrentUrl());
        $customerSession->authenticate();
        
        return $resultRedirect;
    }
}
