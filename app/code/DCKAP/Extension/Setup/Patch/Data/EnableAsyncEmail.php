<?php
/**
 * Enable async email notification
 * @category: Magento
 * @package: DCKAP/Extension
 * @copyright: Copyright © 2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * @license: Magento Enterprise Edition (MEE) license
 * @author: Sreedevi S<sreedevis@dckap.com>
 * @project: DDI System
 * @keywords: Module DCKAP_Extension
 */

namespace DCKAP\Extension\Setup\Patch\Data;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Config\Model\Config\Backend\Encrypted;

/**
 * Class EnableAsyncEmail
 * @package DCKAP\Extension\Setup\Patch\Data
 */
class EnableAsyncEmail implements DataPatchInterface
{

    /**
     * path to update async email settings
     */
    const PATH_ASYNC_EMAIL = 'sales_email/general/async_sending';

    /**
     * scope value
     */
    const SCOPE_ID = 0;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var Encrypted
     */
    protected $encrypted;

    /**
     * ConfigData constructor.
     * @param WriterInterface $configWriter
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param Encrypted $encrypted
     */
    public function __construct(
        WriterInterface $configWriter,
        ModuleDataSetupInterface $moduleDataSetup,
        Encrypted $encrypted
    ) {
        $this->configWriter = $configWriter;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->encrypted = $encrypted;
    }

    /**
     * Run code inside patch script
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->enableEmailAsync();
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Change Price
     */
    private function enableEmailAsync() {
        $this->configWriter->save( self::PATH_ASYNC_EMAIL, 1, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, self::SCOPE_ID);
    }

    /**
     * @return array
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getAliases()
    {
        return [];
    }
}
