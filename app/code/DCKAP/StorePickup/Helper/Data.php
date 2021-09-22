<?php
namespace Dckap\StorePickup\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * category   Dckap
 * package    Dckap_StorePickup
 */
class Data extends AbstractHelper
{

    const XML_PATH_ENABLED          = 'dckap_storepickup/general/enabled';
    const XML_PATH_DEBUG            = 'dckap_storepickup/general/debug';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @param Context             $context
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Context $context,
        ModuleListInterface $moduleList
    ) {
        $this->_logger                  = $context->getLogger();
        $this->_moduleList              = $moduleList;

        parent::__construct($context);
    }

    /**
     * Check if enabled
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getDebugStatus()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DEBUG,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getExtensionVersion()
    {
        $moduleCode = 'Dckap_StorePickup';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    /**
     *
     * @param $message
     * @param bool|false $useSeparator
     */
    public function log($message, $useSeparator = false)
    {
        if ($this->getDebugStatus()) {
            if ($useSeparator) {
                $this->_logger->addDebug(str_repeat('=', 100));
            }

            $this->_logger->addDebug($message);
        }
    }
}
