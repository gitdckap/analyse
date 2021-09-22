<?php
/**
 * Copyright © DCKAP Inc. All rights reserved.
 */

namespace Mageplaza\LoginAsCustomer\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Dckap
 * @package Mageplaza\LoginAsCustomer\Helper
 */
class Dckap extends \Mageplaza\LoginAsCustomer\Helper\Data
{
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * Dckap constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param AuthorizationInterface $authorization
     * @param Random $random
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        AuthorizationInterface $authorization,
        Random $random,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        parent::__construct($context, $objectManager, $storeManager, $authorization, $random);
        $this->coreSession = $coreSession;
    }

    /**
     * return multi account data from the token
     *
     * @param bool $token
     * @return mixed
     */
    public function getMultiAccountData()
    {
        $this->coreSession->start();
        return $this->coreSession->getEcommUserData();
    }
}
