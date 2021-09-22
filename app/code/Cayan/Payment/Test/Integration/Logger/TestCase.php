<?php

namespace Cayan\Payment\Test\Integration\Logger;

use Cayan\Payment\Logger\Handler\DebugHandler;
use Cayan\Payment\Logger\Handler\ErrorHandler;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Set up the test case
     */
    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        /** @var \Magento\Framework\Filesystem\DirectoryList $directoryList */
        $directoryList = $this->objectManager->get(DirectoryList::class);
        $path = realpath($directoryList->getPath('var') . '/..');

        $this->objectManager->configure(
            [
                DebugHandler::class => [
                    'arguments' => [
                        'filePath' => $path,
                    ]
                ],
                ErrorHandler::class => [
                    'arguments' => [
                        'filePath' => $path,
                    ]
                ]
            ]
        );
    }
}
