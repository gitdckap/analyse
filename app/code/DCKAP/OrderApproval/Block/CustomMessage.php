<?php

namespace DCKAP\OrderApproval\Block;

use Magento\Customer\Block\Account\AuthorizationLink;

/**
 * Class CustomMessage
 * @package DCKAP\OrderApproval\Block
 */
class CustomMessage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;
    /**
     * @var AuthorizationLink
     */
    protected $authorizationLink;

    /**
     * CustomMessage constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param AuthorizationLink $authorizationLink
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        AuthorizationLink $authorizationLink,
        array $data = []
    )
    {
        $this->messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
        $this->authorizationLink = $authorizationLink;
        parent::__construct($context, $data);
    }


    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDisplayAction()
    {
        /** @var \Magento\Quote\Model\Quote  */
        $quote = $this->checkoutSession->getQuote();
        if(false==is_null($quote->getOrderId())) {
           $this->messageManager->addWarningMessage(__("You are editing an existing order."));

         }
    }

    /**
     * @return bool
     */
    public function getCustomerSessionLogged()
    {
        return $this->authorizationLink->isLoggedIn();
    }
}