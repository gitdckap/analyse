<?php
namespace Dckap\Footer\Block;

class GetData extends \Magento\Framework\View\Element\Template
{

    protected $scopeConfig;
    protected $regionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->regionFactory = $regionFactory;
        parent::__construct($context);
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getRegionCode($regionid)
    {
        return $this->regionFactory->create()->load($regionid)->getCode();
    }
}
