<?php 
namespace Dckap\MultiAccount\Observer;

use Magento\Customer\Model\SessionFactory;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Checkout\Model\Session;

/**
 * Class Logout
 * @package Dckap\MultiAccount\Observer
 */
class Logout implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $sessionFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * Logout constructor.
     * @param \Magento\Customer\Model\SessionFactory $sessionFactory
     * @param \Magento\Checkout\Model\Cart $cart
     */
    protected $quoteRepository;
    protected $checkoutSession;
    public function __construct(
       	SessionFactory $sessionFactory,
        QuoteRepository $quoteRepository,
        Session $checkoutSession,
        Cart $cart
    ) {
       	$this->sessionFactory = $sessionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->checkoutSession = $checkoutSession;
        $this->cart = $cart;
       
    }
    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	$customerSession = $this->sessionFactory->create();
        $objQuote = $this->checkoutSession->getQuote();
        if ($objQuote->getData('order_id')) {
            $quoteItems = $this->checkoutSession->getQuote()->getItemsCollection();
            foreach($quoteItems as $item)
            {
                $this->cart->removeItem($item->getId());
                $this->cart->saveQuote();
            }
            $objQuote->setData('order_id', null);
            $this->quoteRepository->save($objQuote);
        }

    	if($customerSession->getMultiUserEnable()==2){
			$cart = $this->cart;
			$cart->truncate()->saveQuote();
	    }
	    return true;
    }
}