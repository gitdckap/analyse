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

namespace Cayan\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Theme;
use Psr\Log\LoggerInterface;
use Cayan\Payment\Model\Helper\Discount as CayanHelper;

/**
 * Payment Nonce Action
 *
 * @package Cayan\Payment\Controller
 * @author Igor Miura
 */
class Nonce extends Action
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    private $session;
    /**
     * @var \Cayan\Payment\Model\Helper\Discount
     */
    private $helper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Cayan\Payment\Model\Helper\Discount $helper
     */
    public function __construct(
        Context $context,
        LoggerInterface $logger,
        SessionManagerInterface $session,
        CayanHelper $helper
    ) {
        parent::__construct($context);

        $this->logger = $logger;
        $this->session = $session;
        $this->helper = $helper;
    }

    /**
     * Retrieve the payment nonce
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $response = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            $publicHash = $this->getRequest()->getParam('public_hash');
            $customerId = $this->session->getCustomerId();
            $result = $this->helper->getVaultNonceFromPublicHash($publicHash, $customerId);

            $response->setData(['paymentMethodNonce' => $result]);
        } catch (\Exception $e) {
            $this->logger->critical(__('An error occurred while retrieving the vault nonce: "%1"', $e->getMessage()));

            return $this->processBadRequest($response);
        }

        return $response;
    }

    /**
     * Return response for bad request
     *
     * @param \Magento\Framework\Controller\ResultInterface $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    private function processBadRequest(ResultInterface $response)
    {
        $response->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        $response->setData(['message' => __('Something went wrong.')]);

        return $response;
    }
}
