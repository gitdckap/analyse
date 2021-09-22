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

namespace Cayan\Payment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\Asset\Repository;

/**
 * Data Helper
 *
 * @package Cayan\Payment\Helper
 * @author Igor Miura
 */
class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    private $assetRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\View\Asset\Repository $repository
     */
    public function __construct(Context $context, Repository $repository)
    {
        parent::__construct($context);

        $this->assetRepository = $repository;
    }

    /**
     * Retrieve the credit card type
     *
     * @param int $type
     * @return string
     */
    public function getCardType($type)
    {
        $types = $this->getCardFlags();

        return $types[(int)$type];
    }

    /**
     * Retrieve accepted Cayan card types
     *
     * @return array
     */
    public function getCardFlags()
    {
        return [
            0 => 'Unknown',
            1 => 'American Express',
            2 => 'Discover',
            3 => 'Mastercard',
            4 => 'Visa',
            5 => 'Debit',
            6 => 'EBT',
            7 => 'Giftcard',
            8 => 'Wright Express (Fleet Card)',
            9 => 'Voyager (Fleet Card / USBank Issued)',
            10 => 'JCB',
            11 => 'China Union Pay',
            12 => 'LevelUp',
        ];
    }

    /**
     * Retrieve credit card image URLs
     *
     * @return array
     */
    public function getFlagImages()
    {
        return [
            0 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Generic.png'),
            1 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/American-Express.png'),
            2 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Discover.png'),
            3 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Mastercard.png'),
            4 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Visa.png'),
            5 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Generic.png'),
            6 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Generic.png'),
            7 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Generic.png'),
            8 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Generic.png'),
            9 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Voyager.png'),
            10 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/JCB.png'),
            11 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/UnionPay.png'),
            12 => $this->assetRepository->getUrl('Cayan_Payment::images/cards/Generic.png'),
        ];
    }

    /**
     * Return CCV image url.
     *
     * @return string
     */
    public function getCcvImage()
    {
        return $this->assetRepository->getUrlWithParams('Cayan_Payment::images/ccv_image.jpeg', ['_secure' => true]);
    }

    /**
     * Mask gift card code to be shown on frontend and backend pages
     *
     * @param string $giftCardPan
     * @return string
     */
    public function maskGiftCard($giftCardPan)
    {
        return str_repeat('*', strlen($giftCardPan) - 4) . substr($giftCardPan, -4);
    }

    /**
     * Remove the specified number of characters from the beginning of the order number
     *
     * @param string $orderNumber
     * @param int $length
     * @return string
     */
    public function truncateOrderNumber($orderNumber, $length = 8)
    {
        if (strlen($orderNumber) > $length) {
            $orderNumber = substr($orderNumber, strlen($orderNumber) - $length);
        }

        return $orderNumber;
    }
}
