<?php

namespace Cayan\Payment\Test\Integration\Logger;

use Cayan\Payment\Logger\Handler\DebugHandler;
use Cayan\Payment\Logger\Handler\ErrorHandler;
use Cayan\Payment\Logger\Handler\LogUploadHandler;
use Cayan\Payment\Logger\Logger;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\TestFramework\Helper\Bootstrap;

class LoggerTest extends TestCase
{
    /**
     * @var \Psr\Log\LoggerInterface|\Monolog\Logger
     */
    private $logger;

    /**
     * Test if the given handler was registered in Magento
     *
     * @dataProvider handlerIsRegisteredDataProvider
     * @param string $name
     * @param string $instance
     */
    public function testHandlerIsRegistered($name, $instance)
    {
        $handlers = $this->logger->getHandlers();
        $this->assertArrayHasKey($name, $handlers);
        $this->assertInstanceOf($instance, $handlers[$name]);
    }

    /**
     * Test if a message is written to the given log file
     *
     * @dataProvider messageIsLoggedDataProvider
     * @param string $type
     * @param string $file
     * @param string $message
     */
    public function testMessageIsLoggedToCayanLogFile($type, $file, $message)
    {
        $this->logger->{$type}($message);

        $this->assertFileExists($file);
        $this->assertFileContainsText($file, $message);
    }

    /**
     * Data provider for handlerIsRegistered test
     *
     * @return array
     */
    public function handlerIsRegisteredDataProvider()
    {
        return [
            'debug_handler' => [
                'name' => '2',
                'instance' => DebugHandler::class
            ],
            'error_handler' => [
                'name' => '1',
                'instance' => ErrorHandler::class
            ],
            'logUpload_handler' => [
                'name' => '0',
                'instance' => LogUploadHandler::class
            ]
        ];
    }

    /**
     * Data provider for messageIsLogged test
     *
     * @return array
     */
    public function messageIsLoggedDataProvider()
    {
        /** @var \Magento\Framework\Filesystem\DirectoryList */
        $directoryList = Bootstrap::getObjectManager()->get(DirectoryList::class);
        $path = $directoryList->getPath('log');
        $debugLogFile = $path . '/cayan_payment_debug.log';
        $errorLogFile = $path . '/cayan_payment_error.log';

        return [
            'debug' => [
                'type' => 'debug',
                'file' => $debugLogFile,
                'message' => 'This a debug test.'
            ],
            'info' => [
                'type' => 'info',
                'file' => $debugLogFile,
                'message' => 'This an info test.'
            ],
            'notice' => [
                'type' => 'notice',
                'file' => $debugLogFile,
                'message' => 'This a notice test.'
            ],
            'warning' => [
                'type' => 'warning',
                'file' => $errorLogFile,
                'message' => 'This a warning test.'
            ],
            'error' => [
                'type' => 'error',
                'file' => $errorLogFile,
                'message' => 'This an error test.'
            ],
            'critical' => [
                'type' => 'critical',
                'file' => $errorLogFile,
                'message' => 'This a critical test.'
            ],
        ];
    }

    /**
     * Set up the test case
     */
    protected function setUp()
    {
        parent::setUp();

        // Replace the instance of the LogUpload handler with a mock so that our tests aren't spamming the log service
        $logUploadMock = $this->getMockBuilder(LogUploadHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager->addSharedInstance($logUploadMock, LogUploadHandler::class);

        $this->logger = $this->objectManager->create(Logger::class);
    }

    /**
     * @param string $file
     * @param string $text
     * @param string $message
     */
    private function assertFileContainsText($file, $text, $message = '')
    {
        if ($message === '') {
            $message = "The text \"$text\" was not found in file \"$file\".";
        }

        $fileContents = file_get_contents($file);
        $fileContainsText = strpos($fileContents, $text) !== false;

        $this->assertTrue($fileContainsText, $message);
    }
}
