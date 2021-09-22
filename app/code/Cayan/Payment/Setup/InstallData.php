<?php
/**
 * Cayan Payments
 *
 * @package Cayan\Payment
 * @author Igor Miura
 * @author Joseph Leedy
 * @copyright Copyright (c) 2017 Cayan (https://cayan.com/)
 * @license https://opensource.org/licenses/OSL-3.0.php Open Software License 3.0
 */

namespace Cayan\Payment\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;

/**
 * Data Installer
 *
 * @package Cayan\Payment\Setup
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Quote\Setup\QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @param \Magento\Quote\Setup\QuoteSetupFactory $quoteSetupFactory
     */
    public function __construct(QuoteSetupFactory $quoteSetupFactory)
    {
        $this->quoteSetupFactory = $quoteSetupFactory;
    }

    /**
     * Add attribute to the quote entity
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);

        $quoteInstaller->addAttribute('quote', 'cayan_giftcard_amount', ['type' => 'decimal']);
    }
}
