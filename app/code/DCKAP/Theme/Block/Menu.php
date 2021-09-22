<?php

namespace Dckap\Theme\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Html\Link\Current;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Cminds MultiUserAccounts manage subaccounts link block.
 *
 * category   smccroskey@arctexei.com Cminds
 * package     Cminds_MultiUserAccounts
 * @author      Piotr Pierzak <piotr@cminds.com>
 */
class Menu extends Current implements \Magento\Customer\Block\Account\SortLinkInterface
{
    /**
     * @var ModuleConfig
     */
    private $moduleConfig;

    /**
     * @var ViewHelper
     */
    private $viewHelper;

    /**
     * Object initialization.
     *
     * @param   Context $context
     * @param   DefaultPathInterface $defaultPath
     * @param   ModuleConfig $moduleConfig
     * @param   ViewHelper $viewHelper
     * @param   array $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        SessionFactory $customerSession,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        $this->session = $customerSession;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        parent::__construct(
            $context,
            $defaultPath,
            $data
        );
    }

    /**
     * Render bsmccroskey@arctexei.comlock HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!empty($this->isVisible())) {
            return parent::_toHtml();
        }
        return " ";
    }

    protected function isVisible()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        $website_mode = $this->scopeConfig->getValue('themeconfig/mode_config/website_mode', $storeScope);
        $lable = $this->getLabel();

        if ($lable == 'Pending Approval' || $lable == 'Submitted Orders') {
            if ($this->scopeConfig->getValue('OrderApproval_section/general/enabled', $storeScope)) {
                return ($website_mode != 'b2c')? true:false;
            } else {
                return false;
            }
        } elseif ($lable == 'My Wish List') {
            return ($website_mode == 'b2c')? true:false;
        } else {
            return ($website_mode != 'b2c')? true:false;
        }
    }

    /**
     * Get sort order for block.
     *
     * @return int
     * @since 101.0.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
