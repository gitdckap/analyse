<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dckap\QuickRFQ\Block\Quote;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class View extends \Magento\Framework\View\Element\Template
{
    protected $_customerSession;
    protected $themeHelper;
    protected $_registry;
    protected $regionFactory;
    protected $countryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Dckap\Theme\Helper\Data $themeHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->themeHelper = $themeHelper;
        $this->_registry = $registry;
        $this->regionFactory = $regionFactory;
        $this->countryFactory = $countryFactory;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Quote View'));
    }

    public function getDdiQuote()
    {
        $order = $this->_registry->registry('ddi_quote');
        return $order;
    }

    public function getConfigData($config)
    {
        return $this->themeHelper->getConfig($config);
    }

    public function getRegionCode($regionid)
    {
        return $this->regionFactory->create()->load($regionid)->getCode();
    }

    public function getCountryName($countryCode){
        $country = $this->countryFactory->create()->loadByCode($countryCode);
        return $country->getName();
    }
}
