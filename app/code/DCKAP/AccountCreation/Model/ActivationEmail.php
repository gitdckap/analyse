<?php
/**
 * @author Dckap Team
 * @copyright Copyright (c) 2017 Dckap (https://www.dckap.com)
 * @package Dckap_Quoteproducts
 *
 * Copyright © 2017 Dckap. All rights reserved.
 */
namespace Dckap\AccountCreation\Model;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Dckap\AccountCreation\Setup\InstallData;

class ActivationEmail
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManagerInterface;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfigInterface;

    /**
     * ActivationEmail constructor.
     *
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManagerInterface
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        StoreManagerInterface $storeManagerInterface,
        ScopeConfigInterface $scopeConfigInterface
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->scopeConfigInterface = $scopeConfigInterface;
    }

    /**
     * If an account is activated, send an email to the user to notice it
     *
     * @param  \Magento\Customer\Api\Data\CustomerInterface $customer
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send($customer)
    {
        $siteOwnerEmail = $this->scopeConfigInterface->getValue(
            'trans_email/ident_sales/email',
            ScopeInterface::SCOPE_STORE,
            $customer->getStoreId()
        );

        $this->transportBuilder->setTemplateIdentifier('customer_activation_email')
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $customer->getStoreId(),
                ]
            )
            ->setTemplateVars(
                ['email' => $customer->getEmail() , 'firstname'=> $customer->getFirstname() ,
                'lastname'=>$customer->getLastname()]
            );

        $this->transportBuilder->addTo($customer->getEmail());
        $this->transportBuilder->setFrom(
            [
                'name'=> $this->storeManagerInterface->getStore($customer->getStoreId())->getName(),
                'email' => $siteOwnerEmail
            ]
        );

        $this->transportBuilder->getTransport()->sendMessage();
    }
}
