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

namespace Cayan\Payment\Model\Helper;

use Cayan\Payment\Gateway\Config\Gift\Config as CreditConfig;
use Cayan\Payment\Gateway\Credit\Request\CredentialsDataBuilder;
use Cayan\Payment\Helper\Data as GeneralHelper;
use Cayan\Payment\Model\Api\Card\Api as CardApi;
use Cayan\Payment\Model\Cache\Type as CayanCache;
use Cayan\Payment\Model\CodeFactory;
use Cayan\Payment\Model\CodeHistoryFactory;
use Cayan\Payment\Model\CodeInQuoteFactory;
use Cayan\Payment\Model\Ui\CreditCardConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Vault\Model\PaymentTokenFactory;
use Psr\Log\LoggerInterface;

/**
 * Discount Helper
 *
 * @package Cayan\Payment\Model
 * @author Igor Miura
 */
class Discount extends AbstractModel
{
    const MESSAGE_CODE_ALREADY_ADD = -1;
    const MESSAGE_CODE_ADDED = 1;
    const MESSAGE_INVALID_CODE = -2;

    /**
     * @var \Cayan\Payment\Gateway\Config\Gift\Config
     */
    private $creditConfig;
    /**
     * @var \Cayan\Payment\Model\CodeFactory
     */
    private $codeFactory;
    /**
     * @var \Cayan\Payment\Model\CodeInQuoteFactory
     */
    private $codeInQuoteFactory;
    /**
     * @var \Cayan\Payment\Model\CodeHistoryFactory
     */
    private $codeHistoryFactory;
    /**
     * @var \Cayan\Payment\Model\Api\Card\Api
     */
    private $cardApi;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;
    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;
    /**
     * @var \Cayan\Payment\Gateway\Credit\Request\CredentialsDataBuilder
     */
    private $credentialsDataBuilder;
    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    private $orderRepository;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;
    /**
     * @var \Magento\Vault\Model\PaymentTokenFactory
     */
    private $paymentTokenFactory;
    /**
     * @var \Cayan\Payment\Model\Cache\Type
     */
    private $cayanCache;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    private $quoteRepository;
    /**
     * @var \Cayan\Payment\Helper\Data
     */
    private $generalHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Cayan\Payment\Gateway\Config\Gift\Config $creditConfig
     * @param \Cayan\Payment\Model\CodeFactory $codeFactory
     * @param \Cayan\Payment\Model\CodeInQuoteFactory $codeInQuoteFactory
     * @param \Cayan\Payment\Model\CodeHistoryFactory $codeHistoryFactory
     * @param \Cayan\Payment\Model\Api\Card\Api $cardApi
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Cayan\Payment\Gateway\Credit\Request\CredentialsDataBuilder $credentialsDataBuilder
     * @param \Magento\Sales\Model\OrderRepository $orderRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Vault\Model\PaymentTokenFactory $paymentTokenFactory
     * @param \Cayan\Payment\Model\Cache\Type $cayanCache
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository,
     * @param \Cayan\Payment\Helper\Data $generalHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CreditConfig $creditConfig,
        CodeFactory $codeFactory,
        CodeInQuoteFactory $codeInQuoteFactory,
        CodeHistoryFactory $codeHistoryFactory,
        CardApi $cardApi,
        Session $session,
        PriceHelper $priceHelper,
        CredentialsDataBuilder $credentialsDataBuilder,
        OrderRepository $orderRepository,
        OrderFactory $orderFactory,
        PaymentTokenFactory $paymentTokenFactory,
        CayanCache $cayanCache,
        QuoteRepository $quoteRepository,
        GeneralHelper $generalHelper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->creditConfig = $creditConfig;
        $this->codeFactory = $codeFactory;
        $this->codeInQuoteFactory = $codeInQuoteFactory;
        $this->codeHistoryFactory = $codeHistoryFactory;
        $this->cardApi = $cardApi;
        $this->session = $session;
        $this->priceHelper = $priceHelper;
        $this->credentialsDataBuilder = $credentialsDataBuilder;
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->cayanCache = $cayanCache;
        $this->quoteRepository = $quoteRepository;
        $this->generalHelper = $generalHelper;
    }

    /**
     * Return the available balance in the gift card code.
     *
     * @param string $code
     * @param bool $callApi
     * @return float
     */
    public function getAvailableAmount($code, $callApi = false)
    {
        if ($this->cayanCache->test($code) === false || $callApi) {
            // Get the available amount from the Cayan API
            $this->setGiftCardBalanceFromApi($code);
        } else {
            // Check if there is an available amount in cache
            if ($this->cayanCache->test($code)) {
                return $this->cayanCache->load($code);
            }
        }

        /** @var \Cayan\Payment\Model\Code $codeModel */
        $codeModel = $this->codeFactory->create();
        /** @var \Cayan\Payment\Model\ResourceModel\Code\Collection $codeCollection */
        $codeCollection = $codeModel->getCollection()
            ->addFieldToFilter('code', $code);

        if ($codeCollection->getSize() === 0) {
            return 0;
        }

        $total = $this->codeFactory->create()
            ->getCollection()
            ->addFieldToFilter('code', $code)
            ->addFieldToSelect(['code_id', 'balance'])
            ->setPageSize(1)
            ->getFirstItem();
        $balance = $total->getBalance();

        // Save in cache
        $this->cayanCache->save($balance, $code, [CayanCache::CACHE_TAG], 86400);

        return $balance;
    }

    /**
     * Retrieve gift card codes added to the quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array
     */
    public function getAddedCodes($quote)
    {
        $codes = [];
        /** @var \Cayan\Payment\Model\CodeInQuote $codeInQuoteModel */
        $codeInQuoteModel = $this->codeInQuoteFactory->create();
        /** @var \Cayan\Payment\Model\ResourceModel\CodeInQuote\Collection $codeInQuoteCollection */
        $codeInQuoteCollection = $codeInQuoteModel->getCollection()
            ->addFieldToFilter('quote_id_fk', $quote->getId());

        if ($codeInQuoteCollection->getSize() === 0) {
            return $codes;
        }

        $codeIds = $codeInQuoteCollection->getColumnValues('code_id_fk');
        /** @var \Cayan\Payment\Model\Code $codeModel */
        $codeModel = $this->codeFactory->create();
        /** @var \Cayan\Payment\Model\ResourceModel\Code\Collection $codeCollection */
        $codeCollection = $codeModel->getCollection()
            ->addFieldToFilter('code_id', ['in' => $codeIds]);

        if ($codeCollection->getSize() === 0) {
            return $codes;
        }

        foreach ($codeInQuoteCollection as $quoteCode) {
            $code = $codeCollection->getItemById($quoteCode->getCodeIdFk())->getCode();

            if ($this->getAvailableAmount($code) > 0) {
                $codes[] = $code;
            } else {
                $quoteCode->delete();
            }
        }

        return $codes;
    }

    /**
     * Retrieve the available gift card amount applied on cart
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return float
     */
    public function getGiftcardAmountApplied($quote)
    {
        $amount = 0;

        if (!$quote) {
            return $amount;
        }

        $codes = $this->getAddedCodes($quote);

        if (count($codes) > 0) {
            foreach ($codes as $code) {
                $amount += $this->getAvailableAmount($code);
            }
        }

        $subtotal = $quote->getSubtotalWithDiscount() + $quote->getShippingAddress()->getShippingAmount()
            + $quote->getShippingAddress()->getTaxAmount();

        return $amount > $subtotal ? $subtotal : $amount;
    }

    /**
     * Add gift card code to the cart
     *
     * @param string $code
     * @param int|null $pin
     * @return int
     */
    public function addCodeToCart($code, $pin = null)
    {
        if ((float)$this->getAvailableAmount($code, true) <= 0) {
            return self::MESSAGE_INVALID_CODE;
        }

        $currentQuote = $this->session->getQuote();
        /** @var \Cayan\Payment\Model\Code $codeModel */
        $codeModel = $this->codeFactory->create();
        /** @var \Cayan\Payment\Model\ResourceModel\Code\Collection $codeCollection */
        $codeCollection = $codeModel->getCollection()
            ->addFieldToFilter('code', $code)
            ->setPageSize(1);

        if (in_array($code, $this->getAddedCodes($currentQuote), true) || $codeCollection->getSize() === 0) {
            return self::MESSAGE_CODE_ALREADY_ADD;
        }

        /** @var \Cayan\Payment\Model\CodeInQuote $codeInQuoteModel */
        $codeInQuoteModel = $this->codeInQuoteFactory->create();

        try {
            $codeInQuoteModel->addData([
                'quote_id_fk' => $currentQuote->getId(),
                'code_id_fk' => $codeCollection->getFirstItem()->getCodeId()
            ])->save();

            $currentQuote->collectTotals()->save();
        } catch (\Exception $e) {
            return 0;
        }

        // Save Code PIN Number
        $codeCollection->getFirstItem()
            ->setPin($pin)
            ->save();

        return self::MESSAGE_CODE_ADDED;
    }

    /**
     * Remove gift card code from quote
     *
     * @param int $codeId
     * @return bool
     */
    public function removeGiftCodeFromCart($codeId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->session->getQuote();
        /** @var \Cayan\Payment\Model\CodeInQuote $codeInQuoteModel */
        $codeInQuoteModel = $this->codeInQuoteFactory->create();
        /** @var \Cayan\Payment\Model\ResourceModel\CodeInQuote\Collection $codeInQuoteCollection */
        $codeInQuoteCollection = $codeInQuoteModel->getCollection()
            ->addFieldToFilter('quote_id_fk', $quote->getId())
            ->addFieldToFilter('code_id_fk', $codeId)
            ->setPageSize(1);

        if ($codeInQuoteCollection->getSize() === 0) {
            return false;
        }

        return $codeInQuoteCollection->getFirstItem()->delete();
    }

    /**
     * Retrieve gift card code max length set on admin
     *
     * @return int
     */
    public function getCodeMaxLength()
    {
        return $this->creditConfig->getMaxLength();
    }

    /**
     * Retrieve the final discount value in quote
     *
     * @param \Magento\Quote\Model\Quote|null $quote
     * @param bool $callApi
     * @return float
     */
    public function getAmountDiscountInQuote($quote = null, $callApi = false)
    {
        if (is_null($quote)) {
            $quote = $this->session->getQuote();
        }

        $amount = 0;

        if (!$quote) {
            return $amount;
        }

        $codes = $this->getAddedCodes($quote);

        if (count($codes) === 0) {
            return $amount;
        }

        foreach ($codes as $code) {
            $amount += $this->getAvailableAmount($code, $callApi);
        }

        $subtotal = $quote->getSubtotalWithDiscount() + $quote->getShippingAddress()->getShippingAmount()
            + $quote->getShippingAddress()->getTaxAmount();

        return $amount > $subtotal ? $subtotal : $amount;
    }

    /**
     * Retrieve the applied codes with coupon code and value
     *
     * @return array
     */
    public function getJsCodes()
    {
        $quote = $this->session->getQuote();
        $codes = [];

        if (!$quote) {
            return $codes;
        }

        $codesAdded = $this->getAddedCodes($quote);

        if (count($codesAdded) === 0) {
            return $codes;
        }

        foreach ($codesAdded as $code) {
            $codes[] = [
                'masked_code' => $this->generalHelper->maskGiftCard($code),
                'code' => $this->codeFactory->create()->load($code, 'code')->getCodeId(),
                'value' => $this->priceHelper->currency($this->getAvailableAmount($code), true, false)
            ];
        }

        return $codes;
    }

    /**
     * Retrieve total available for all applied gift card codes
     *
     * @param \Magento\Quote\Model\Quote|null $quote
     * @return float
     */
    public function getTotalCardsAmount($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->session->getQuote();
        }

        $amount = 0;

        if (!$quote) {
            return $amount;
        }

        $codes = $this->getAddedCodes($quote);

        if (count($codes) === 0) {
            return $amount;
        }

        foreach ($codes as $code) {
            $amount += $this->getAvailableAmount($code);
        }

        return $amount;
    }

    /**
     * Check if gift card is enabled
     *
     * @return mixed
     */
    public function isEnabled()
    {
        return $this->creditConfig->isActive();
    }

    /**
     * Retrieve the method title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->creditConfig->getTitle();
    }

    /**
     * Check if an order has gift card usages
     *
     * @param int $orderId
     * @return bool
     */
    public function hasGiftCard($orderId)
    {
        return $this->codeHistoryFactory->create()->getCollection()
            ->addFieldToFilter('order_id_fk', $orderId)
            ->count() > 0 ? true : false;
    }

    /**
     * Retrieve all codes in order with used balance
     *
     * @param $orderId
     * @return array
     */
    public function getCodesFromOrder($orderId)
    {
        $codesInfo = [];
        /** @var \Cayan\Payment\Model\CodeHistory $codeHistoryModel */
        $codeHistoryModel = $this->codeHistoryFactory->create();
        /** @var \Cayan\Payment\Model\ResourceModel\CodeHistory\Collection $codeHistoryCollection */
        $codeHistoryCollection = $codeHistoryModel->getCollection()
            ->addFieldToFilter('order_id_fk', $orderId);

        if ($codeHistoryCollection->getSize() === 0) {
            return $codesInfo;
        }

        $codeIds = $codeHistoryCollection->getColumnValues('code_id_fk');
        /** @var \Cayan\Payment\Model\Code $codeModel */
        $codeModel = $this->codeFactory->create();
        /** @var \Cayan\Payment\Model\ResourceModel\Code\Collection $codeCollection */
        $codeCollection = $codeModel->getCollection()
            ->addFieldToFilter('code_id', ['in' => $codeIds]);

        foreach ($codeHistoryCollection as $codeHistory) {
            $codesInfo[] = [
                'balance_used' => $codeHistory->getBalanceUsed(),
                'code' => $codeCollection->getItemById($codeHistory->getCodeIdFk())->getCode()
            ];
        }

        return $codesInfo;
    }

    /**
     * Retrieve the code_id by code
     *
     * @param string $code
     * @return \Magento\Framework\DataObject
     */
    public function getCodeIdByCode($code)
    {
        return $this->codeFactory->create()->getCollection()
            ->addFieldToFilter('code', $code)
            ->addFieldToSelect('code_id')
            ->setPageSize(1)
            ->getFirstItem()
            ->getCodeId();
    }

    /**
     * Add new code usage
     *
     * @param int $codeId
     * @param int $orderId
     * @param float $amount
     * @return float
     */
    public function addCodeUsage($codeId, $orderId, $amount)
    {
        try {
            $codeModel = $this->codeFactory->create()->load($codeId);

            if ($amount > $codeModel->getBalance()) {
                $amount = $codeModel->getBalance();
            }

            // Transmit gift card usage to Cayan API
            $data = array_merge(
                $this->credentialsDataBuilder->build([]),
                [
                    'PaymentData' => [
                        'Source' => 'Keyed',
                        'CardNumber' => $codeModel->getCode()
                    ],
                    'Request' => [
                        'Amount' => (string)$amount,
                        'InvoiceNumber' => (string)$this->orderRepository->get($orderId)->getIncrementId(),
                        'EnablePartialAuthorization' => $this->creditConfig->isPartialAuthorizationEnabled() ? 'True'
                            : 'False'
                    ]
                ]
            );

            if (!empty($codeModel->getPin())) {
                $data['PaymentData']['GiftCardPin'] = $codeModel->getPin();
            }

            $result = $this->cardApi->sale($data);

            if (is_null($result)) {
                return 0;
            }

            $approvedAmount = $this->getApprovedAmountFromResult($result);

            if ($approvedAmount === 0) {
                return $approvedAmount;
            }

            /** @var \Cayan\Payment\Model\CodeHistory $codeHistoryModel */
            $codeHistoryModel = $this->codeHistoryFactory->create();

            $codeHistoryModel->setData([
                'transaction_code' => $result->Token,
                'code_id_fk' => $codeId,
                'order_id_fk' => $orderId,
                'balance_used' => $approvedAmount
            ])->save();
        } catch (\Exception $e) {
            return 0;
        }

        return $approvedAmount;
    }

    public function void($codeHistory)
    {
        try {
            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $this->orderRepository->get($codeHistory->getOrderIdFk());
            $data = array_merge(
                $this->credentialsDataBuilder->build([]),
                [
                    'Request' => [
                        'Token' => (string)$codeHistory->getTransactionCode(),
                        'InvoiceNumber' => (string)$order->getIncrementId()
                    ]
                ]
            );
            $result = $this->cardApi->void($data);

            if (is_null($result)) {
                return 0;
            }

            if (property_exists($result, 'ApprovalStatus') && $result->ApprovalStatus === 'APPROVED') {
                $approvedAmount = $codeHistory->getBalanceUsed();
            } else {
                $approvedAmount = 0;
            }

            /** @var \Cayan\Payment\Model\CodeHistory $codeHistoryModel */
            $codeHistoryModel = $this->codeHistoryFactory->create();

            $codeHistoryModel->setData([
                'transaction_code' => $result->Token,
                'code_id_fk' => $codeHistory->getCodeIdFk(),
                'order_id_fk' => $order->getEntityId(),
                'balance_used' => -$approvedAmount
            ])->save();

            $quote = $this->quoteRepository->get($order->getQuoteId());
            $quote->setCayanGiftcardAmount($quote->getCayanGiftcardAmount() + $approvedAmount)->save();
        } catch (\Exception $e) {
            $this->_logger->error(__('An error occurred while voiding the gift card: "%1"', $e->getMessage()));
            return 0;
        }

        return $approvedAmount;
    }

    /**
     * Refund gift card sale
     *
     * @param \Cayan\Payment\Model\CodeHistory $codeHistory
     * @param float $amount
     * @return float
     */
    public function refund($codeHistory, $amount)
    {
        try {
            if ($amount > $codeHistory->getBalanceUsed()) {
                $amount = $codeHistory->getBalanceUsed();
            }

            /** @var \Magento\Sales\Api\Data\OrderInterface $order */
            $order = $this->orderRepository->get($codeHistory->getOrderIdFk());
            $data = array_merge(
                $this->credentialsDataBuilder->build([]),
                [
                    'PaymentData' => [
                        'Source' => 'PreviousTransaction',
                        'Token' => $codeHistory->getTransactionCode()
                    ],
                    'Request' => [
                        'Amount' => (string)$amount,
                        'InvoiceNumber' => (string)$order->getIncrementId(),
                        'MerchantTransactionId' => (string)$order->getQuoteId()
                    ]
                ]
            );
            $result = $this->cardApi->refund($data);

            if (is_null($result)) {
                return 0;
            }

            $approvedAmount = $this->getApprovedAmountFromResult($result);

            if ($approvedAmount === 0) {
                return $approvedAmount;
            }

            /** @var \Cayan\Payment\Model\CodeHistory $codeHistoryModel */
            $codeHistoryModel = $this->codeHistoryFactory->create();

            $codeHistoryModel->setData([
                'transaction_code' => $result->Token,
                'code_id_fk' => $codeHistory->getCodeIdFk(),
                'order_id_fk' => $codeHistory->getOrderIdFk(),
                'balance_used' => (-$amount)
            ])->save();

            $order = $this->orderRepository->get($codeHistory->getOrderIdFk());
            $quote = $this->quoteRepository->get($order->getQuoteId());

            $newQuoteValue = (float)$quote->getCayanGiftcardAmount();
            $newQuoteValue = $newQuoteValue + (float)$approvedAmount;

            $quote->setCayanGiftcardAmount($newQuoteValue);
            $quote->save();
        } catch (\Exception $e) {
            $this->_logger->error(__('An error occurred while refunding the gift card: "%1"', $e->getMessage()));

            return 0;
        }

        return $approvedAmount;
    }

    /**
     * Retrieve the total gift card amount used in an order
     *
     * @param int $orderId
     * @return float
     */
    public function getGiftcardTotalInOrder($orderId)
    {
        /** @var \Cayan\Payment\Model\ResourceModel\CodeHistory\Collection $codeHistoryCollection */
        $codeHistoryCollection = $this->codeHistoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id_fk', $orderId);
        $codeHistoryCollection->getSelect()
            ->columns(['total' => new \Zend_Db_Expr('SUM(balance_used)')])
            ->group('order_id_fk');

        $usageTotal = $codeHistoryCollection->setPageSize(1)
            ->getFirstItem()
            ->getTotal();

        return $usageTotal;
    }

    /**
     * Retrieve payment gateway token from public hash and customer id
     *
     * @param string $publicHash
     * @param int $customerId
     * @return string
     */
    public function getVaultNonceFromPublicHash($publicHash, $customerId)
    {
        try {
            return $this->paymentTokenFactory->create()->getCollection()
                ->addFieldToFilter('public_hash', $publicHash)
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('payment_method_code', CreditCardConfigProvider::METHOD_CODE)
                ->setPageSize(1)
                ->getFirstItem()
                ->getGatewayToken();
        } catch (\Exception $e) {
        }

        return null;
    }

    /**
     * Refund all applied gift cards when an order is cancelled
     *
     * @param int $orderId
     * @param bool $void
     * @return bool
     */
    public function refundGiftCardInOrder($orderId, $void = false)
    {
        $usages = $this->codeHistoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id_fk', $orderId);
        $refunded = true;

        foreach ($usages as $usage) {
            if (!$void) {
                $refundResult = $this->refund($usage, $usage->getBalanceUsed());
            } else {
                $refundResult = $this->void($usage);
            }

            if ((float)$refundResult !== (float)$usage->getBalanceUsed()) {
                $refunded = false;
            }
        }

        return $refunded;
    }

    /**
     * Retrieve and store the current available balance for the given code from the Cayan API
     *
     * @param string $code
     */
    private function setGiftCardBalanceFromApi($code)
    {
        try {
            /**
             * @var $quote Quote
             */
            $quote = $this->session->getQuote();

            //Generate increment_id if it don't exist in the quote
            if (is_null($quote->getReservedOrderId())) {
                $quote->reserveOrderId();
            }

            // Prepare api request data
            $data = array_merge(
                $this->credentialsDataBuilder->build([]),
                [
                    'PaymentData' => [
                        'Source' => 'Keyed',
                        'CardNumber' => $code
                    ],
                    'Request' => [
                        'InvoiceNumber' => (string)$quote->getReservedOrderId()
                    ]
                ]
            );

            $result = $this->cardApi->balanceInquiry($data);

            if (is_null($result) || !property_exists($result, 'ApprovalStatus')
                || $result->ApprovalStatus !== 'APPROVED') {
                return;
            }

            /** @var \Cayan\Payment\Model\Code $codeModel */
            $codeModel = $this->codeFactory->create();
            $codeCollection = $codeModel->getCollection()
                ->setPageSize(1)
                ->addFieldToFilter('code', $code);
            $giftCardBalance = (float)$result->Gift->RedeemableBalance;

            if ($codeCollection->getSize() > 0) {
                $codeCollection->getFirstItem()
                    ->setBalance($giftCardBalance)
                    ->save();
            } else {
                $newCode = $this->codeFactory->create();
                $newCode->setData([
                    'code' => $code,
                    'balance' => $giftCardBalance
                ])->save();
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Retrieve the approved amount from the provided result object
     *
     * @param \stdClass $result
     * @return float
     */
    private function getApprovedAmountFromResult(\stdClass $result)
    {
        if (property_exists($result, 'ApprovalStatus') && $result->ApprovalStatus === 'APPROVED') {
            return (float)$result->Gift->ApprovedAmount;
        }

        return 0;
    }
}
